/*===============================================================================
* Copyright 2014(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.gateway;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.gatewayconnection.SubAccountUser;
import com.controlpad.pay_fac.payment_info.TokenRequest;
import com.controlpad.pay_fac.report.gateway.GatewayTransaction;
import com.controlpad.pay_fac.tokenization.CardTokenResponse;
import com.controlpad.pay_fac.tokenization.TokenizeCardResponse;
import com.controlpad.pay_fac.transaction.TransactionUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_common.util.GsonUtil;
import com.sun.xml.ws.fault.ServerSOAPFaultException;
import com.usaepay.api.jaxws.*;
import org.apache.commons.lang3.ObjectUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;

import javax.xml.soap.SOAPFault;
import javax.xml.ws.soap.SOAPFaultException;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.math.RoundingMode;
import java.net.MalformedURLException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class USAEpay implements Gateway{

    private final Logger logger = LoggerFactory.getLogger(USAEpay.class);

    private final String PRODUCTION_URL = "www.usaepay.com";
    private final String SANDBOX_URL = "sandbox.usaepay.com";

    private IDUtil idUtil;
    private DateTimeFormatter batchDateFormatter;
    private DateTimeFormatter closedDateTimeFormatter;
    private DateTimeFormatter dbDateTimeFormatter;
    private Map<Integer, TransactionResult> errorMap = new HashMap<>();

    USAEpay(IDUtil idUtil) {
        this.idUtil = idUtil;
        batchDateFormatter = DateTimeFormat.forPattern("yyyy-MM-dd");
        closedDateTimeFormatter = DateTimeFormat.forPattern("MM/dd/yyyy'T'HH:mm:ss");
        dbDateTimeFormatter = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss");
        setupErrorMap();
    }

    @Override
    public Transaction saleCard(SqlSession session, GatewayConnection gatewayConnection, String remoteAddress,
                                        Transaction transaction, TransactionType transactionType) {
        try {
            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);

            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

            com.usaepay.api.jaxws.TransactionResponse response;
            boolean authOnly = StringUtils.equalsIgnoreCase(transaction.getStatusCode(), "a");
            if (authOnly) {
                response = client.runAuthOnly(token, createTransactionRequest(transaction));
            } else {
                response = client.runSale(token, createTransactionRequest(transaction));
            }

            Transaction savedTransaction = new Transaction(null, transaction.getPayeeUserId(),
                    transaction.getPayerUserId(), transaction.getTeamId(), response.getRefNum().toString(), transactionType.slug,
                    transaction.getAmount(), transaction.getSalesTax(), transaction.getShipping(), transaction.getStatusCode(), null,
                    gatewayConnection.getId(), transaction.getDescription(), transaction.getAccountHolder());

            savedTransaction.setSwiped(transaction.getCard().getEncMagstripe() != null || transaction.getCard().getMagstripe() != null);

            setResultAndStatus(response, savedTransaction, authOnly);

            TransactionUtil.insertTransaction(session, savedTransaction, idUtil, transaction.getAffiliatePayouts());

            return savedTransaction;
        } catch (MalformedURLException|GeneralFault_Exception Exception) {
            logger.error(Exception.getMessage(), Exception);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public Transaction saleCheck(SqlSession session, GatewayConnection gatewayConnection,
                                         Transaction transaction, String remoteAddress, TransactionType transactionType) {
        try {
            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);

            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

            com.usaepay.api.jaxws.TransactionResponse response = client.runCheckSale(token,
                    createCheckTransactionRequest(transaction, "checkSale"));

            Transaction savedTransaction = new Transaction(null, transaction.getPayeeUserId(),
                    transaction.getPayerUserId(), transaction.getTeamId(), response.getRefNum().toString(), transactionType.slug,
                    transaction.getAmount(), transaction.getSalesTax(), transaction.getShipping(), null, null,
                    gatewayConnection.getId(), transaction.getDescription(), transaction.getAccountHolder());

            setResultAndStatus(response, savedTransaction, false);

            TransactionUtil.insertTransaction(session, savedTransaction, idUtil, transaction.getAffiliatePayouts());

            return savedTransaction;
        } catch (MalformedURLException|GeneralFault_Exception Exception) {
            logger.error(Exception.getMessage(), Exception);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    public com.usaepay.api.jaxws.TransactionResponse epayTransactionStatus(String gatewayTransactionId, GatewayConnection gatewayConnection,
                                                                           String remoteAddress) throws Exception {

        UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);
        UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

        return client.getTransactionStatus(token, new BigInteger(gatewayTransactionId));
    }

    public Transaction updateTransactionStatus(SqlSession session, Transaction currentTransaction, GatewayConnection gatewayConnection,
                                               String remoteAddress) {
        try {
            if (currentTransaction != null) {

                com.usaepay.api.jaxws.TransactionResponse transactionResponse = epayTransactionStatus(currentTransaction.getGatewayReferenceId(), gatewayConnection, remoteAddress);

                currentTransaction.setStatusCode(transactionResponse.getStatusCode());
                session.getMapper(TransactionMapper.class).updateTransactionStatus(currentTransaction);

                long external_batch_id = transactionResponse.getBatchRefNum().longValue();
                if (external_batch_id == -2 || external_batch_id == -1)
                    return currentTransaction;

                TransactionBatch transactionBatch = session.getMapper(TransactionBatchMapper.class).findForExternalId(gatewayConnection.getId(), String.valueOf(external_batch_id));
                if (transactionBatch != null && !ObjectUtils.equals(currentTransaction.getBatchId(), transactionBatch.getId())) {
                    currentTransaction.setBatchId(transactionBatch.getId());
                    session.getMapper(TransactionMapper.class).updateBatchId(currentTransaction);
                }

            }

            return currentTransaction;
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "usaepay error");
        }
    }

    public Transaction refundTransaction(GatewayConnection gatewayConnection, Transaction originalTransaction,
                                                 Transaction refund, String remoteAddress, String clientId) {

        if ((StringUtils.equals(originalTransaction.getStatusCode(), "A") || StringUtils.equals(originalTransaction.getStatusCode(), "P")) &&
                refund.getAmount().compareTo(originalTransaction.getAmount()) == 0) {

            if(voidTransaction(gatewayConnection, originalTransaction, remoteAddress)) {
                // Refund util will automatically update this transaction
                refund.updateResultAndCode(TransactionResult.Settled);
                refund.setGatewayConnectionId(gatewayConnection.getId());
                refund.setTransactionType(TransactionType.VOID.slug);
                return refund;
            }
        }

        UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);

        try {
            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

            com.usaepay.api.jaxws.TransactionResponse transactionResponse = client.refundTransaction(
                    token, new BigInteger(originalTransaction.getGatewayReferenceId()), refund.getAmount().doubleValue());

            refund.setGatewayConnectionId(gatewayConnection.getId());
            refund.setGatewayReferenceId(transactionResponse.getRefNum().toString());

            setResultAndStatus(transactionResponse, refund, false);

            return refund;
        } catch (Exception e) {
            logger.error(
                    String.format("Failed to refund transaction\nMessage:%s\nTransaction:%s\nRefund:%s",
                    e.getMessage(), originalTransaction.getId(), GsonUtil.getGson().toJson(refund)),
                    e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    private boolean voidTransaction(GatewayConnection gatewayConnection,
                                   Transaction transaction, String remoteAddress) {
        switch (transaction.getStatusCode()) {
            case "S":
                throw new ResponseException(HttpStatus.PRECONDITION_FAILED, "Transaction already settled. Please process a refund.");
            case "R":
            case "V":
                throw new ResponseException(HttpStatus.OK, "Transaction already refunded.");
            case "A":
            case "P":
                try {
                    UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);
                    UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

                    return client.voidTransaction(token, new BigInteger(transaction.getGatewayReferenceId()));
                } catch (MalformedURLException|GeneralFault_Exception|ServerSOAPFaultException Exception) {
                    return false;
                }
            default:
                throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Transaction status code '" + transaction.getStatusCode() + "' unrecognized");
        }
    }

    public boolean closeBatch(GatewayConnection gatewayConnection, String remoteAddress) {
        UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);

        try {
            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));
            return client.closeBatch(token, BigInteger.ZERO);
        } catch (Exception e) {
            return false;
        }
    }

    public CardTokenResponse saveCard(CreditCardData creditCardData, GatewayConnection gatewayConnection, String remoteAddress) {
        try {
            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);

            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

            return new CardTokenResponse(client.saveCard(token, creditCardData));
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to tokenize credit card data");
        }
    }

    @Override
    public TokenizeCardResponse tokenizeCard(TokenRequest tokenRequestData, GatewayConnection gatewayConnection, String remoteAddress){
        try {
            CreditCardData creditCardData = setTokenCreditCard(tokenRequestData);

            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), remoteAddress);

            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

            return new TokenizeCardResponse(client.saveCard(token, creditCardData));
        }
        catch (SOAPFaultException e){
            logger.error("Fail to save card: " + e.getMessage(), e);
            if(e.getMessage().toLowerCase().contains("invalid card number")){
                return new TokenizeCardResponse(8, "Error from gateway: Invalid TokenRequest Number");
            }else if(e.getMessage().toLowerCase().contains("invalid expiration date")){
                return new TokenizeCardResponse(9, "Error from gateway: Invalid Expiration Date");
            }else {
                return new TokenizeCardResponse(10, "Error from gateway: " + e.getMessage());
            }
        }
        catch (Exception e) {
            logger.error("Fail to save card: " + e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to tokenize credit card data");
        }
    }

    @Override
    public Boolean isAccountSame(GatewayConnection currentConnection, GatewayConnection newConnection, SqlSession session) {
        // TODO fix this
        if(!StringUtils.equals(currentConnection.getPrivateKey(), newConnection.getPrivateKey())){
            try{
                TransactionMapper transactionMapper = session.getMapper(TransactionMapper.class);
//                String referenceId = transactionMapper.fin(currentConnection.getId() + "");
//                if(referenceId == null){
//                    return true;
//                }
//                com.usaepay.api.jaxws.TransactionResponse transactionResponse = epayTransactionStatus(referenceId, newConnection, null);
            } catch (Exception e){
                return false;
            }
            return true;
        }
        return true;
    }

    @Override
    public boolean checkCredentials(GatewayConnection gatewayConnection) {
        try {
            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));
            AccountDetails accountDetails = client.getAccountDetails(usaepay.getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), ""));
            return accountDetails != null;
        } catch (SOAPFaultException soapFault) {
            return false;
        } catch (Exception e) {
            // Checking for a messy validation error message, I so hate people that don't code to consistent standards
            if (!StringUtils.equals(e.getMessage(), "Incorrect sourcekey length")) {
                // Unexpected error
                logger.error("USAePay.checkCredentials()", e);
            }
            return false;
        }
    }

    @Override
    public CommonResponse createSubAccount(String clientName, SqlSession clientSession, GatewayConnection masterConnection, SubAccountUser subAccountUser) {
        throw new ResponseException(HttpStatus.BAD_REQUEST, "Create sub account not supported for USAePay");
    }

    @Override
    public boolean updateSubAccount(GatewayConnection gatewayConnection, SubAccountUser subAccountUser) {
        return false;
    }

    @Override
    public Transaction importTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction) {
        try {

            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), "");
            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));
            TransactionObject transactionObject = client.getTransaction(token, new BigInteger(transaction.getGatewayReferenceId()));
            if (transactionObject.getTransactionType().equalsIgnoreCase("credit") ^
                    transaction.getTransactionType().equalsIgnoreCase("refund")) {
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction type mismatch");
            }

            TransactionDetail details = transactionObject.getDetails();

            String description = (transaction.getDescription() != null ? transaction.getDescription() : details.getDescription());

            Transaction saveTransaction = new Transaction(null,
                    transaction.getPayeeUserId(), transaction.getPayerUserId(), transaction.getTeamId(), transaction.getGatewayReferenceId(),
                    transaction.getTransactionType(), new Money(details.getAmount()), new Money(details.getTax()), BigDecimal.ZERO, null, null,
                    gatewayConnection.getId(), description, transactionObject.getAccountHolder()
            );

            if (transactionObject.getCreditCardData() != null) {
                saveTransaction.setSwiped(transactionObject.getCreditCardData().isCardPresent());
            }

            switch (TransactionType.findBySlug(transaction.getTransactionType())) {
                case CREDIT_CARD_SALE:
                case CREDIT_CARD_SUB:
                case DEBIT_CARD_SALE:
                case DEBIT_CARD_SUB:
                    if (!StringUtils.equals(transactionObject.getTransactionType(), "Auth Only") &&
                            !StringUtils.equals(transactionObject.getTransactionType(), "Sale")){
                        throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction type mismatch");
                    }
                    break;
                case REFUND:
                    if (!StringUtils.equals(transactionObject.getTransactionType(), "Credit")) {
                        throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction type mismatch");
                    }
                    break;
                default:
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "transactionType invalid");
            }
            setResultAndStatus(transactionObject, saveTransaction);

            TransactionUtil.insertTransaction(sqlSession, saveTransaction, idUtil, null);

            return saveTransaction;
        } catch (Exception e) {
            if (e instanceof SOAPFaultException) {
                SOAPFault fault = ((SOAPFaultException)e).getFault();
                if (StringUtils.equals(fault.getFaultString(), "20001: Specified transactions was not found.")) {
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction not found for import");
                }
            }
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "usaepay error");
        }
    }

    @Override
    public Transaction captureTransaction(SqlSession sqlSession, GatewayConnection gatewayConnection, Transaction transaction, String clientId) {
        try {
            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), "");

            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));

            com.usaepay.api.jaxws.TransactionResponse response = client.captureTransaction(token, new BigInteger(transaction.getGatewayReferenceId()), transaction.getAmount().doubleValue());
            setResultAndStatus(response, transaction, false);

            if (transaction.getResultCode() != 1) {
                // Logging failures for now to learn of any cases to handle
                MDC.put("gatewayResponse", GsonUtil.getGson().toJson(response));
                logger.error("USAePay failed to capture transaction");
            }

            sqlSession.getMapper(TransactionMapper.class).updateTransactionStatusOrderId(transaction);

            return transaction;
        } catch (MalformedURLException|GeneralFault_Exception Exception) {
            logger.error(Exception.getMessage(), Exception);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }
    }

    @Override
    public Object getTransaction(GatewayConnection gatewayConnection, Transaction transaction) {
        try {
            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), "");
            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));
            return client.getTransaction(token, new BigInteger(transaction.getGatewayReferenceId()));
        } catch (Exception e) {
            logger.error("getGatewayTransaction failed", e);
            return null;
        }
    }

    @Override
    public List<TransactionBatch> searchGatewayBatches(SqlSession clientSession, GatewayConnection gatewayConnection, DateTime startDate, DateTime endDate, BigInteger page, BigInteger limit) {
        try {
            UeSecurityToken token = getToken(gatewayConnection.getPrivateKey(), gatewayConnection.getPin(), "");
            UeSoapServerPortType client = usaepay.getClient(getUrl(gatewayConnection));
            SearchParamArray searchParamArray = new SearchParamArray();
            if (startDate != null) {
                SearchParam searchParam = new SearchParam();
                searchParam.setField("Closed");
                searchParam.setType("gt");
                searchParam.setValue(startDate.toString(batchDateFormatter));
                searchParamArray.getSearchParam().add(searchParam);
            }
            if (endDate != null) {
                endDate.plusDays(1); // We want the requested end date to be included
                SearchParam searchParam = new SearchParam();
                searchParam.setField("Closed");
                searchParam.setType("lt");
                searchParam.setValue(endDate.toString(batchDateFormatter));
                searchParamArray.getSearchParam().add(searchParam);
            }
            if (searchParamArray.getSearchParam().isEmpty()) {
                // Add in a default search parameter because it can't be empty
                SearchParam searchParam = new SearchParam();
                searchParam.setField("sequence");
                searchParam.setType("gt");
                searchParam.setValue("0");
                searchParamArray.getSearchParam().add(searchParam);
            }

            // matchAll, start, limit, sort
            BatchSearchResult batchSearchResult = client.searchBatches(token, searchParamArray, true,
                    page.subtract(java.math.BigInteger.ONE).multiply(limit), limit, "closed desc");

            List<TransactionBatch> gatewayBatches = new ArrayList<>(batchSearchResult.getBatchesReturned().intValue());

            if (batchSearchResult.getBatchesReturned().compareTo(BigInteger.ZERO) > 0) {
                for (BatchStatus batchStatus : batchSearchResult.getBatches().getItem()) {
                    DateTime closedDateTime = closedDateTimeFormatter.parseDateTime(batchStatus.getClosed());
                    gatewayBatches.add(new TransactionBatch(batchStatus.getBatchRefNum().toString(),
                            BigDecimal.valueOf(batchStatus.getNetAmount()).setScale(2, RoundingMode.HALF_DOWN),
                            closedDateTime, batchStatus.getTransactionCount()));
                }
            }
            return gatewayBatches;
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unexpected error");
        }
    }

    @Override
    public List<GatewayTransaction> searchTransactions(SqlSession clientSession, GatewayConnection gatewayConnection, String externalBatchId, BigInteger page, BigInteger limit) {
        // TODO implement
        return null;
    }

    private UeSecurityToken getToken(String sourceKey, String pin, String clientIP) {
        try {
            return usaepay.getToken(sourceKey, (pin == null ? "" : pin), clientIP);
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Unable to get usaepay token");
        }
    }

    private String getUrl(GatewayConnection gatewayConnection) {
        return gatewayConnection.getIsSandbox() ? SANDBOX_URL : PRODUCTION_URL;
    }

    private TransactionRequestObject createTransactionRequest(Transaction transaction) {
        TransactionRequestObject request = new TransactionRequestObject();
        request.setSoftware("Payman - " + transaction.getPayeeUserId());
        if (transaction.getBillingAddress() != null) {
            request.setAccountHolder(transaction.getBillingAddress().getFullName());
        } else {
            request.setAccountHolder(transaction.getAccountHolder());
        }
        request.setCustomerID(transaction.getPayerUserId());

        request.setDetails(createTransactionDetail(transaction));

        request.setCreditCardData(getCreditCardData(transaction));
        if (transaction.getShippingAddress() != null) {
            request.setShippingAddress(convertAddress(transaction.getShippingAddress()));
        }
        if (transaction.getBillingAddress() != null) {
            request.setBillingAddress(convertAddress(transaction.getBillingAddress()));
        }

        return request;
    }

    private CreditCardData getCreditCardData(Transaction transaction) {
        CreditCardData ccData = new CreditCardData();
        if (transaction.getCard().getToken() != null) { // Only need token
            ccData.setCardNumber(transaction.getCard().getToken());
            return ccData;
        }
        if (transaction.getCard().getMagstripe() != null) {
            ccData.setMagStripe(transaction.getCard().getMagstripe());
        } else if (transaction.getCard().getEncMagstripe() != null) {
            ccData.setMagStripe(transaction.getCard().getEncMagstripe());
        }else {
            ccData.setCardNumber(transaction.getCard().getNumber());
            ccData.setCardExpiration(transaction.getCard().getExpirationDate());
            ccData.setCardCode(transaction.getCard().getCode());
        }

        if (transaction.getBillingAddress() != null) {
            if (transaction.getBillingAddress().getStreet() != null) {
                ccData.setAvsStreet(transaction.getBillingAddress().getStreet());
            }
            if (transaction.getBillingAddress().getPostalCode() != null) {
                ccData.setAvsZip(transaction.getBillingAddress().getPostalCode());
            }
        }

        return ccData;
    }

    private CreditCardData setTokenCreditCard(TokenRequest tokenRequest){
        CreditCardData creditCardData = new CreditCardData();
        if(tokenRequest.getCard() != null){
            Card card = tokenRequest.getCard();
            creditCardData.setCardNumber(card.getNumber());
            creditCardData.setCardExpiration(card.getExpirationDate());
            creditCardData.setCardCode(card.getCode());
        }
        // TODO support magstripe in the future

        if(tokenRequest.getAddress() != null){
            if(tokenRequest.getAddress().getStreet() != null){
                creditCardData.setAvsStreet(tokenRequest.getAddress().getStreet());
            }
            if(tokenRequest.getAddress().getPostalCode() != null){
                creditCardData.setAvsZip(tokenRequest.getAddress().getPostalCode());
            }
        }
        return creditCardData;
    }

    private Address convertAddress(com.controlpad.payman_common.address.Address paymanAddress) {
        Address address = new Address();
        address.setStreet(paymanAddress.getLine1());
        address.setZip(paymanAddress.getPostalCode());
        if (StringUtils.isNotBlank(paymanAddress.getLine2())) {
            address.setStreet2(paymanAddress.getLine2());
        }
        if (StringUtils.isNotBlank(paymanAddress.getCity())) {
            address.setCity(paymanAddress.getCity());
        }
        if (StringUtils.isNotBlank(paymanAddress.getState())) {
            address.setState(paymanAddress.getState());
        }
        if (StringUtils.isNotBlank(paymanAddress.getEmail())) {
            address.setEmail(paymanAddress.getEmail());
        }
        if (StringUtils.isNotBlank(paymanAddress.getPhoneNumber())) {
            address.setPhone(paymanAddress.getPhoneNumber());
        }
        if (StringUtils.isNotBlank(paymanAddress.getFaxNumber())) {
            address.setFax(paymanAddress.getFaxNumber());
        }
        if (StringUtils.isNotBlank(paymanAddress.getFirstName())) {
            address.setFirstName(paymanAddress.getFirstName());
        }
        if (StringUtils.isNotBlank(paymanAddress.getLastName())) {
            address.setLastName(paymanAddress.getLastName());
        }
        if (StringUtils.isNotBlank(paymanAddress.getCountryCode())) {
            address.setCountry(paymanAddress.getCountryCode());
        }
        if (StringUtils.isNotBlank(paymanAddress.getCompany())) {
            address.setCompany(paymanAddress.getCompany());
        }

        return address;
    }

    private TransactionRequestObject createCheckTransactionRequest(Transaction transaction, String command) {
        TransactionRequestObject request = new TransactionRequestObject();
        request.setCommand(command);
        request.setSoftware("Payman - " + transaction.getPayeeUserId());
        request.setAccountHolder(transaction.getAccountHolder());

        CheckData chkData = new CheckData();
        chkData.setRouting(transaction.getBankAccount().getRouting());
        chkData.setAccount(transaction.getBankAccount().getNumber());

        // TODO We can support check number and license number/state in the future if needed

        request.setCheckData(chkData);

        request.setDetails(createTransactionDetail(transaction));

        if (transaction.getShippingAddress() != null) {
            request.setShippingAddress(convertAddress(transaction.getShippingAddress()));
        }
        if (transaction.getBillingAddress() != null) {
            request.setBillingAddress(convertAddress(transaction.getBillingAddress()));
        }

        return request;
    }

    private TransactionDetail createTransactionDetail(Transaction transaction) {
        TransactionDetail details = new TransactionDetail();
        details.setTerminal(transaction.getPayeeUserId());
        details.setAmount(transaction.getAmount().doubleValue());
        if (transaction.getSubtotal() != null)
            details.setSubtotal(transaction.getSubtotal().doubleValue());
        if (transaction.getSalesTax() != null)
            details.setTax(transaction.getSalesTax().doubleValue());
        if (transaction.getShipping() != null)
            details.setShipping(transaction.getShipping().doubleValue());
        details.setPONum(transaction.getPoNumber());
        details.setOrderID("Seller ID:" + transaction.getPayeeUserId());
        details.setInvoice(transaction.getOrderId());
        details.setDescription(transaction.getDescription());
        return details;
    }

    private void setResultAndStatus(TransactionObject transactionObject, Transaction transaction) {
        switch (transactionObject.getStatus()) {
            case "Settled":
                transaction.updateResultAndCode(TransactionResult.Settled);
                break;
            case "Authorized (Will not be captured)":
                transaction.updateResultAndCode(TransactionResult.Authorized);
                break;
            case "Authorized (Pending Settlement)":
            case "Pending Settlement":
                transaction.updateResultAndCode(TransactionResult.Success);
                break;
            case "Voided":
            case "Voided (Funds Released)": // Only to be used when void is expected
                transaction.updateResultAndCode(TransactionResult.Voided);
                break;
            default:
            case "Error":
                transaction.updateResultAndCode(TransactionResult.Unexpected);
                // Not supported for now
                break;
        }
    }

    private void setResultAndStatus(com.usaepay.api.jaxws.TransactionResponse transactionResponse, Transaction transaction, boolean authOnly) {
        if (transactionResponse.isIsDuplicate()) {
            transaction.updateResultAndCode(TransactionResult.Duplicate_Transaction);
        } else if (StringUtils.equals(transactionResponse.getResultCode(), "A")) {
            if (StringUtils.equalsIgnoreCase(transactionResponse.getStatusCode(), "A") || authOnly) {
                transaction.updateResultAndCode(TransactionResult.Authorized);
            } else {
                transaction.updateResultAndCode(TransactionResult.Success);
            }
        } else {
            int errorCode = transactionResponse.getErrorCode().intValue();
            if (errorMap.containsKey(errorCode)) {
                transaction.updateResultAndCode(errorMap.get(errorCode));
            } else if (errorCode == 17) {
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction out of balance");
            } else {
                MDC.put("transactionResponse", GsonUtil.getGson().toJson(transactionResponse));
                logger.error("USAePay unexpected error code");
                MDC.remove("transactionResponse");
                transaction.updateResultAndCode(TransactionResult.Unexpected);
            }
        }
    }

    private void setupErrorMap() {
        errorMap.put(11, TransactionResult.Invalid_Card_Number);
        errorMap.put(12, TransactionResult.Invalid_Card_Number);
        errorMap.put(13, TransactionResult.Invalid_Card_Number);
        errorMap.put(14, TransactionResult.Invalid_Card_Number);

        errorMap.put(17, TransactionResult.Card_Expired);

        errorMap.put(31, TransactionResult.Duplicate_Transaction);
        errorMap.put(32, TransactionResult.Maximum_Limit);
        errorMap.put(33, TransactionResult.Minimum_Limit);
        errorMap.put(34, TransactionResult.Billing_Info_Wrong);

        errorMap.put(38, TransactionResult.Invalid_Routing_Number);
        errorMap.put(39, TransactionResult.Invalid_Checking_Number);
        errorMap.put(40, TransactionResult.Check_Transactions_Not_Supported);
        errorMap.put(43, TransactionResult.Declined);

        errorMap.put(70, TransactionResult.Maximum_Limit);

        errorMap.put(91, TransactionResult.Card_Not_Supported);
        errorMap.put(93, TransactionResult.Duplicate_Transaction);
        errorMap.put(94, TransactionResult.Declined); // Email address was blocked by the EmailBlocker fraud module

        errorMap.put(140, TransactionResult.Unexpected); // Error executing transaction

        errorMap.put(342, TransactionResult.Invalid_Code); // Invalid CVV
        errorMap.put(345, TransactionResult.Declined);
        errorMap.put(363, TransactionResult.Minimum_Limit);
        errorMap.put(369, TransactionResult.Insufficient_Funds);
        errorMap.put(371, TransactionResult.Card_Not_Supported);  // Processor indicated that card is not allowed to perform this transaction
        errorMap.put(378, TransactionResult.Card_Not_Supported);  //  Service code on card indicates card not valid for transaction method
        errorMap.put(379, TransactionResult.Insufficient_Funds);
        errorMap.put(381, TransactionResult.Invalid_Card_Number);
        errorMap.put(382, TransactionResult.Lost_Or_Stolen);
        errorMap.put(384, TransactionResult.Hard_Decline);
        errorMap.put(385, TransactionResult.Invalid_Account_Number);
        errorMap.put(387, TransactionResult.Card_Expired);
        errorMap.put(388, TransactionResult.Transaction_Limit);
        errorMap.put(389, TransactionResult.Unexpected); // Security violation

        errorMap.put(390, TransactionResult.Transaction_Limit);
        errorMap.put(398, TransactionResult.Hard_Decline); // Do Not Honor (05)

        errorMap.put(2034, TransactionResult.Billing_Info_Wrong);

        errorMap.put(10003, TransactionResult.Card_Not_Supported);
        errorMap.put(10004, TransactionResult.Card_Not_Supported);
        errorMap.put(10006, TransactionResult.Card_Not_Supported);

        errorMap.put(10016, TransactionResult.Invalid_Magstripe_Data);

        errorMap.put(10057,TransactionResult.Card_Not_Supported);

        errorMap.put(10107, TransactionResult.Billing_Zip_Code_Not_Found);
        errorMap.put(10109, TransactionResult.Billing_Zip_Code_Does_Not_Match_Billing);
        errorMap.put(10110, TransactionResult.Billing_Zip_Code_Does_Not_Match_Billing);
        errorMap.put(10111, TransactionResult.Billing_Zip_Code_Does_Not_Match_Billing);
        errorMap.put(10116, TransactionResult.Invalid_Code); // Unable to verify card ID number
        errorMap.put(10119, TransactionResult.Invalid_Magstripe_Data);

        errorMap.put(10127, TransactionResult.Hard_Decline); // Hard decline
        errorMap.put(10204, TransactionResult.Hard_Decline); // Pick up card
        errorMap.put(10205, TransactionResult.Hard_Decline); // Do not honor
        errorMap.put(10212, TransactionResult.Declined); // Invalid transaction

        errorMap.put(10214, TransactionResult.Invalid_Account_Number);
        errorMap.put(10215, TransactionResult.Declined); // Invalid Issuer
        errorMap.put(10225, TransactionResult.Declined); // Unable to locate Record

        errorMap.put(10251, TransactionResult.Insufficient_Funds);
        errorMap.put(10255, TransactionResult.Invalid_Code); // Invalid Pin
        errorMap.put(10257, TransactionResult.Declined); // Transaction not permitted

        errorMap.put(10262, TransactionResult.Declined); // Restricted card
        errorMap.put(10265, TransactionResult.Transaction_Limit); // Excess withdrawal count
        errorMap.put(10275, TransactionResult.Invalid_Code); // Too many pin tries
        errorMap.put(10278, TransactionResult.Merchant_Invalid); // No checking account <- is this a mid error?
        errorMap.put(10297, TransactionResult.Invalid_Code); // CVV failure

        errorMap.put(10342, TransactionResult.Unexpected); // Could not connect to processor

        errorMap.put(19092, TransactionResult.Timeout);

        errorMap.put(30004, TransactionResult.Card_Not_Supported);
        errorMap.put(30010, TransactionResult.Card_Not_Supported);
    }

}