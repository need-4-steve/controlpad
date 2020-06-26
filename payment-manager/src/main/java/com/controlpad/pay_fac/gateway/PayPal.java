package com.controlpad.pay_fac.gateway;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.PayPalCapturePayment;
import com.controlpad.pay_fac.transaction.PayPalCreatePayment;
import com.controlpad.pay_fac.transaction.TransactionResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction.Transaction;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.util.GsonUtil;
import com.paypal.api.openidconnect.Userinfo;
import com.paypal.api.payments.*;
import com.paypal.api.payments.Payment;
import com.paypal.base.rest.APIContext;
import com.paypal.base.rest.PayPalRESTException;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;

import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Locale;

public class PayPal implements Gateway{

    private final Logger logger = LoggerFactory.getLogger(PayPal.class);

    private IDUtil idUtil;

    PayPal(IDUtil idUtil) {
        this.idUtil = idUtil;
    }

    public CommonResponse<Payment> createPaypalPayment(GatewayConnection gatewayConnection, PayPalCreatePayment payment){
        APIContext apiContext = buildAPIContext(gatewayConnection);

        try {
            // Convert incoming payment info into a paypal payment and call create on the api
            Payment createdPayment = buildPaypalPayment(payment).create(apiContext);

            if(createdPayment != null) {
                // Make sure payment was created
                switch (createdPayment.getState()) {
                    case "created":
                        CommonResponse<Payment> response = new CommonResponse<>(true, 1, "payment created");
                        response.setData(createdPayment);
                        return response;
                    case "failed":
                        CommonResponse<Payment> errorResponse = new CommonResponse<>(false, 6, createdPayment.getFailureReason());
                        errorResponse.setData(createdPayment);
                        return errorResponse;
                    default:
                        logger.error(String.format("Unexpected result from PayPal created payment! ID: %s, state: %s", createdPayment.getId(), createdPayment.getState()));
                        throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
                }
            } else {
                logger.error("No response object from PayPal");
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
            }
        } catch (PayPalRESTException e) {
            logger.error("salePayPal: PayPalRESTException caught", e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    public TransactionResponse executePaypalPayment(PayPalCapturePayment payPalCapturePayment, GatewayConnection gatewayConnection,
                                                    SqlSession session){
        //Configure Environment
        APIContext apiContext = buildAPIContext(gatewayConnection);

        //Configure payment to execute
        Payment payment = new Payment().setId(payPalCapturePayment.getPaypalPaymentId());

        PaymentExecution paymentExecution = new PaymentExecution();
        paymentExecution.setPayerId(payPalCapturePayment.getPaypalPayerId());
        Payment executedPayment;

        //try to execute payment
        try{
            executedPayment = payment.execute(apiContext, paymentExecution);
            if(executedPayment != null){
                switch (executedPayment.getState()) {
                case "approved":
                    Transaction transaction = createTransactionFromPaypalSale(payPalCapturePayment, executedPayment, gatewayConnection);
                    TransactionUtil.insertTransaction(session, transaction, idUtil, payPalCapturePayment.getAffiliatePayouts());
                    TransactionResponse transactionResponse = new TransactionResponse(transaction, true);

                    session.commit();
                    return transactionResponse;
                case "failed":
                    return new TransactionResponse(false, 6, executedPayment.getFailureReason());
                default:
                    logger.error(String.format("Unexpected result from PayPal created payment! ID: %s, state: %s", executedPayment.getId(), executedPayment.getState()));
                    throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
                }
            }else{
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
            }
        } catch (PayPalRESTException e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error");
        }
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress,
                                        Transaction transaction, TransactionType transactionType) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Paypal gateway doesn't support card payment directly");
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection,
                                         Transaction transaction, String remoteAddress, TransactionType transactionType) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Paypal gateway doesn't support e-check payment");
    }

    @Override
    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection, String remoteAddress) {
        //TODO check the state of payment, is there a mid state between approved - > completed?

        return null;
    }

    @Override
    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction transaction,
                                                 Transaction refund, String remoteAddress, String clientId) {
        //configure refund request
        Amount amount = new Amount().setCurrency("USD")
                .setTotal(refund.getAmount().toString());

        if(transaction.getGatewayReferenceId() == null){
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Gateway Reference ID cannot be null");
        }
        Sale sale = new Sale().setId(transaction.getGatewayReferenceId());
        logger.info("Refund payment ID: " + sale.getId());
        RefundRequest refundRequest = new RefundRequest();
        refundRequest.setAmount(amount);

        //try to request a refund
        try{
            DetailedRefund detailedRefund = sale.refund(buildAPIContext(gatewayConnection), refundRequest);
            //handle response from paypal
            if(detailedRefund != null) {
                refund.setGatewayReferenceId(detailedRefund.getId());
                refund.setGatewayConnectionId(gatewayConnection.getId());

                String state = detailedRefund.getState();
                switch (state) {
                    case "pending":
                    case "completed":
                        refund.setStatusCode("P");
                        refund.updateResultAndCode(TransactionResult.Success.getResultCode());
                        return refund;
                    case "failed":
                        refund.updateResultAndCode(TransactionResult.Unexpected.getResultCode());
                        refund.setStatusCode("D");
                        return refund;
                    default:
                        // Pass through to error state
                }
            }
            logger.error(String.format("Unexpected result from PayPal refund payment! ID: %s\n, amount: %s",
                    GsonUtil.getGson().toJson(detailedRefund), amount));
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error: unexpected state");
        } catch (PayPalRESTException e){
            logger.error(String.format("Failed to refund transaction. Transaction ID: %s", transaction.getId()), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Gateway error: unexpected state");
        }
    }

    //Don't need to have a customer ID
    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest tokenRequest, GatewayConnection gatewayConnection, String remoteAddress) {
        try {
            CardType cardType = tokenRequest.getType();
            Card card = tokenRequest.getCard();
            if(card == null){
                return new TokenizeCardResponse(3, "Error: Card field cannot be empty");
            }
            if(cardType == CardType.UNKNOWN){
                return new TokenizeCardResponse(3, "Error: Invalid TokenRequest Number");
            }

            CreditCard creditCard = new CreditCard();
            creditCard.setNumber(card.getNumber())
                    .setExpireMonth(card.getMonth())
                    .setExpireYear(card.getYear())
                    .setType(cardType.getSlug());
            return new TokenizeCardResponse(creditCard.create(buildAPIContext(gatewayConnection)));
        } catch (PayPalRESTException e) {
            logger.error(String.format("Fail to tokenize card: %s", tokenRequest), e.getDetails());
            return new TokenizeCardResponse(10, "Error from gateway: " + e.getDetails());
        } catch(Exception e){
            logger.error(String.format("Unexpected error happened when tokenize card: %s", tokenRequest), e);
            return new TokenizeCardResponse(10, "Error from gateway: " + e.getMessage());
        }
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        if(currentConnection == null){
            return false;
        }
        return StringUtils.equals(currentConnection.getUsername(), newConnection.getUsername());
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        try {
            Userinfo userinfo = Userinfo.getUserinfo(buildAPIContext(gatewayConnection));
            return (userinfo != null);
        } catch (PayPalRESTException e) {
            return false;
        }
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Create sub account not supported for PayPal");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        return false;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "PayPal not supported");
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "PayPal not supported");
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        return null;
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate,
                                                       DateTime endDate, BigInteger page, BigInteger limit) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "PayPal not supported");
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {
        return null;
    }

    private CreditCard buildCreditCard(Transaction transaction){
        CreditCard creditCard = new CreditCard();
        Card card = transaction.getCard();
        creditCard.setExpireMonth(card.getMonth())
                .setExpireYear(card.getYear())
                .setNumber(card.getNumber())
                .setType(card.getType().getSlug());

        return creditCard;
    }

    private CreditCardToken buildCreditCardToken(Transaction transaction){
        return new CreditCardToken()
                .setCreditCardId(transaction.getCard().getToken());
    }

    private Payer buildCreditCardPayer(Transaction transaction){
        FundingInstrument fundingInstrument = new FundingInstrument();
        if(transaction.getCard().getToken() == null){
            fundingInstrument.setCreditCard(buildCreditCard(transaction));
        }else{
            fundingInstrument.setCreditCardToken(buildCreditCardToken(transaction));
        }
        List<FundingInstrument> fundingInstrumentList = new ArrayList<>();
        fundingInstrumentList.add(fundingInstrument);
        Payer payer = new Payer();
        payer.setPaymentMethod("credit_card");
        payer.setFundingInstruments(fundingInstrumentList);
        return payer;
    }

    private Details buildAmountDetails(BigDecimal tax, BigDecimal subtotal, BigDecimal shipping){
        Details details = new Details();
        if (tax != null) {
            details.setTax(tax.toString());
        }
        if (shipping != null) {
            details.setShipping(shipping.toString());
        }
        if (subtotal != null) {
            details.setSubtotal(subtotal.toString());
        }
        return details;
    }

    private Amount buildAmount(Transaction transaction){
        return new Amount().setCurrency("USD")
                .setTotal(String.format("%.2f", transaction.getAmount()))
                .setDetails(buildAmountDetails(
                        transaction.getSalesTax(),
                        transaction.getSubtotal(),
                        transaction.getShipping()
                ));
    }

    private List<com.paypal.api.payments.Transaction> buildTransactions(Transaction transaction){
        com.paypal.api.payments.Transaction paypalTransaction = new com.paypal.api.payments.Transaction();
        paypalTransaction.setAmount(buildAmount(transaction))
                .setDescription(transaction.getDescription());

        return Collections.singletonList(paypalTransaction);
    }

    private RedirectUrls buildRedirectUrl(PayPalCreatePayment payment){
        return new RedirectUrls().setCancelUrl(payment.getCancelUrl())
                .setReturnUrl(payment.getProcessUrl());
    }

    private Payment buildPaypalPayment(PayPalCreatePayment payment) {
        Amount amount = new Amount("USD", String.format(Locale.US, "%.2f", payment.getTotal()));
        amount.setDetails(buildAmountDetails(payment.getTax(), payment.getSubtotal(), payment.getShipping()));

        com.paypal.api.payments.Transaction transaction = new com.paypal.api.payments.Transaction();
        transaction.setAmount(amount);
        transaction.setDescription(payment.getDescription());

        return new Payment("sale", new Payer().setPaymentMethod("paypal"))
                .setTransactions(Collections.singletonList(transaction))
                .setRedirectUrls(buildRedirectUrl(payment));
    }

    private Payment buildCardPayment(Transaction transaction){
        return new Payment("sale", buildCreditCardPayer(transaction)).setTransactions(buildTransactions(transaction));
    }

    private APIContext buildAPIContext(GatewayConnection gatewayConnection) {
        return new APIContext(
                gatewayConnection.getUsername(),
                gatewayConnection.getPrivateKey(),
                (gatewayConnection.getIsSandbox() ? "sandbox" : "live")
        );
    }

    private Transaction createTransactionFromPaypalSale(PayPalCapturePayment capturePayment, Payment payment, GatewayConnection gatewayConnection) {
        Sale sale = payment.getTransactions().get(0).getRelatedResources().get(0).getSale();
        String description = null;
        String payerName = null;
        if (payment.getTransactions().isEmpty() ||
                payment.getTransactions().get(0).getRelatedResources().isEmpty()) {
            description = payment.getTransactions().get(0).getDescription();
            payerName = getPayerName(payment);
        }

        BigDecimal shipping = (sale.getAmount().getDetails().getShipping() == null ?
            BigDecimal.ZERO : new Money(sale.getAmount().getDetails().getShipping()));
        BigDecimal tax = (sale.getAmount().getDetails().getTax() == null ?
            BigDecimal.ZERO : new Money(sale.getAmount().getDetails().getTax()));

        return new Transaction(null,
                capturePayment.getPayeeUserId(), capturePayment.getPayerUserId(),
                gatewayConnection.getTeamId(), capturePayment.getPaypalPaymentId(),
                TransactionType.PAYPAL_SALE.slug,
                new Money(sale.getAmount().getTotal()),
                tax,
                shipping,
                "P",
                1,
                gatewayConnection.getId(),
                description,
                payerName);
    }

    private String getPayerName(Payment payment) {
        if (payment.getPayer() != null && payment.getPayer().getPayerInfo() != null) {
            return StringUtils.join(
                    payment.getPayer().getPayerInfo().getFirstName(),
                    " ",
                    payment.getPayer().getPayerInfo().getLastName()
            );
        }
        return null;
    }

    private void setErrorResultCode(String reason, Transaction transaction) {
        switch (reason) {
            case "REDIRECT_REQUIRED":
                logger.error("Redirect required response from card payment"); // TODO meta data
            case "UNABLE_TO_COMPLETE_TRANSACTION":
            case "INVALID_PAYMENT_METHOD":
            case "CANNOT_PAY_THIS_PAYEE":
            case "PAYEE_FILTER_RESTRICTIONS":
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
            case "PAYER_CANNOT_PAY":
                transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
            default:
                logger.error(String.format(Locale.US, "Unexpected failure reason from paypal: %s", reason));
                transaction.updateResultAndCode(TransactionResult.Declined.getResultCode());
        }
    }
}