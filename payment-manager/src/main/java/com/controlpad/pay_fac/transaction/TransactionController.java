/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.transaction;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.fee.FeeSetUtil;
import com.controlpad.pay_fac.gateway.GatewayUtil;
import com.controlpad.pay_fac.interceptor.APIKeyPermissions;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.payment_info.CardPayment;
import com.controlpad.pay_fac.refund.RefundUtil;
import com.controlpad.pay_fac.report.ReportsMapper;
import com.controlpad.pay_fac.transaction_processing.TransactionProcessUtil;
import com.controlpad.pay_fac.util.IDUtil;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.ResponseUtil;
import com.controlpad.pay_fac.util.TeamConverterUtil;
import com.controlpad.pay_fac.validation.PaymentValidator;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.address.Address;
import com.controlpad.payman_common.credits.CompanyCredit;
import com.controlpad.payman_common.credits.CreditsMapper;
import com.controlpad.payman_common.credits.TeamCredit;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.transaction.*;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.MoneyUtil;
import com.controlpad.payman_common.validation.PaymentChecks;
import com.controlpad.payman_common.validation.PostChecks;
import com.controlpad.payman_common.validation.SaleChecks;
import com.controlpad.payman_common.validation.TransferChecks;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.validation.BindingResult;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.math.BigDecimal;
import java.util.Random;

@RestController
@RequestMapping("/transactions")
public class TransactionController {

    private static final Logger logger = LoggerFactory.getLogger(TransactionController.class);

    private Random random = new Random();

    @Autowired
    SqlSessionUtil sqlSessionUtil;

    @Autowired
    GatewayUtil gatewayUtil;

    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    FeeSetUtil feeSetUtil;
    @Autowired
    TransactionProcessUtil transactionProcessUtil;
    @Autowired
    RefundUtil refundUtil;
    @Autowired
    IDUtil idUtil;

    @Autowired
    PaymentValidator paymentValidator;

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @ResponseStatus(HttpStatus.OK)
	@RequestMapping(value = "/sale/cash", method = RequestMethod.POST)
	public TransactionResponse cashSale(HttpServletRequest request,
                                        @RequestBody @Validated({SaleChecks.class}) Payment cashPayment) {

        TeamConverterUtil.convert(cashPayment);

        if (cashPayment.getTotal() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "total required");
        }

        if (cashPayment.getAffiliatePayouts() != null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Cannot set affiliatePayouts for a cash sale");
        }

        Transaction transaction = new Transaction(cashPayment, null, TransactionType.CASH_SALE,
                cashPayment.getName(), null, null, null);

        transaction = cashTransaction(request, transaction);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
	}

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sale/e-check", method = RequestMethod.POST)
    public TransactionResponse checkSale(HttpServletRequest request,
                                         @RequestBody @Validated({SaleChecks.class}) CheckPayment checkPayment,
                                         BindingResult result) {

        TeamConverterUtil.convert(checkPayment);

        return checkTransactionWithResponse(request, checkPayment, result, TransactionType.CHECK_SALE);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sale/credit-card", method = RequestMethod.POST)
    public TransactionResponse creditCardSale(HttpServletRequest request,
                                              @RequestBody @Validated({SaleChecks.class}) CardPayment cardPayment,
                                              BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        return cardTransactionWithResponse(request, cardPayment, TransactionType.CREDIT_CARD_SALE, result);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sale/debit-card", method = RequestMethod.POST)
	public TransactionResponse debitCardSale(HttpServletRequest request,
                                             @RequestBody @Validated({SaleChecks.class}) CardPayment cardPayment,
                                             BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        return cardTransactionWithResponse(request, cardPayment, TransactionType.DEBIT_CARD_SALE, result);
	}

    // Subscriptions

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sub/e-check", method = RequestMethod.POST)
    public TransactionResponse checkSub(HttpServletRequest request,
                                        @RequestBody @Validated({SaleChecks.class}) CheckPayment checkPayment,
                                        BindingResult result) {

        TeamConverterUtil.convert(checkPayment);

        return checkTransactionWithResponse(request, checkPayment, result, TransactionType.CHECK_SUB);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sub/credit-card", method = RequestMethod.POST)
    public TransactionResponse creditCardSub(HttpServletRequest request,
                                             @RequestBody @Validated({SaleChecks.class}) CardPayment cardPayment,
                                             BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        return cardTransactionWithResponse(request, cardPayment, TransactionType.CREDIT_CARD_SUB, result);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sub/debit-card", method = RequestMethod.POST)
    public TransactionResponse debitCardSub(HttpServletRequest request,
                                            @RequestBody @Validated({SaleChecks.class}) CardPayment cardPayment,
                                            BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        return cardTransactionWithResponse(request, cardPayment, TransactionType.DEBIT_CARD_SUB, result);
    }

    // Shipping

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/shipping/e-check", method = RequestMethod.POST)
    public TransactionResponse checkShipping(HttpServletRequest request,
                                        @RequestBody @Validated({SaleChecks.class}) CheckPayment checkPayment,
                                        BindingResult result) {

        TeamConverterUtil.convert(checkPayment);

        return checkTransactionWithResponse(request, checkPayment, result, TransactionType.E_CHECK_SHIPPING);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/shipping/credit-card", method = RequestMethod.POST)
    public TransactionResponse creditCardShipping(HttpServletRequest request,
                                             @RequestBody @Validated({SaleChecks.class}) CardPayment cardPayment,
                                             BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        return cardTransactionWithResponse(request, cardPayment, TransactionType.CREDIT_CARD_SHIPPING, result);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/shipping/debit-card", method = RequestMethod.POST)
    public TransactionResponse debitCardShipping(HttpServletRequest request,
                                            @RequestBody @Validated({SaleChecks.class}) CardPayment cardPayment,
                                            BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        return cardTransactionWithResponse(request, cardPayment, TransactionType.DEBIT_CARD_SHIPPING, result);
    }

    // E-Wallet endpoints
    /**
     *
     * Allows a User to pay another User with ewallet
     * This request differs in that the authenticated user is actually the payer not the payee
     */
    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sale/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletSale(HttpServletRequest request,
                                           @RequestBody @Validated({TransferChecks.class}) Payment internalPayment,
                                           BindingResult result) {

        TeamConverterUtil.convert(internalPayment);

        paymentValidator.validate(internalPayment, result);
        if (result.hasErrors()) {
            throw new ResponseException(result);
        }

        return eWalletTransactionWithResponse(request, internalPayment, TransactionType.E_WALLET_SALE);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sub/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletSubscription(HttpServletRequest request,
                                                   @RequestBody @Validated({TransferChecks.class}) Payment internalPayment,
                                                   BindingResult result) {

        TeamConverterUtil.convert(internalPayment);

        paymentValidator.validate(internalPayment, result);

        if (result.hasErrors()) {
            throw new ResponseException(result);
        }

        return eWalletTransactionWithResponse(request, internalPayment, TransactionType.E_WALLET_SUB);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/shipping/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletShipping(HttpServletRequest request,
                                                   @RequestBody @Validated({TransferChecks.class}) Payment internalPayment,
                                                   BindingResult result) {

        TeamConverterUtil.convert(internalPayment);

        paymentValidator.validate(internalPayment, result);

        if (result.hasErrors()) {
            throw new ResponseException(result);
        }

        return eWalletTransactionWithResponse(request, internalPayment, TransactionType.E_WALLET_SHIPPING);
    }

    private TransactionResponse eWalletTransactionWithResponse(HttpServletRequest request, Payment internalPayment,
                                                               TransactionType transactionType) {

        Transaction transaction = new Transaction(internalPayment, null, transactionType,
                null, null, null, null);
        transaction = eWalletTransaction(request, transaction, transactionType);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @RequestMapping(value = "/transfer/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletTransfer(HttpServletRequest request,
                                               @RequestBody @Validated({TransferChecks.class}) Payment internalTransfer) {

        TeamConverterUtil.convert(internalTransfer);

        SqlSession session = RequestUtil.getClientSqlSession(request);
        Transaction transaction = new Transaction(internalTransfer, null, TransactionType.E_WALLET_TRANSFER,
                null, "S", TransactionResult.Success.getResultCode(), null);
        transaction = eWalletTransfer(request, session, transaction);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction eWalletTransfer(HttpServletRequest request, SqlSession clientSession, Transaction transaction) {
        RequestUtil.checkOwnerCreate(request, transaction.getPayerUserId()); // Only the owner of the wallet can authorize payment

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getEWallet()) {
            throw ResponseUtil.getInsufficientPrivileges("E-Wallet feature disabled");
        }
        // TODO create some kind of transfer limits to promote security

        if (subtractEWalletBalance(clientSession, transaction) == null) {
            transaction.setStatusCode("D");
            transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
        } else {
            transaction.setStatusCode("S");
            transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        }


        TransactionUtil.insertTransaction(clientSession, transaction, idUtil, null);

        transactionProcessUtil.processEWalletTransfer(clientSession, RequestUtil.getClientId(request), transaction);

        clientSession.commit();
        return transaction;
    }

    /**
     *  Allows the company to credit an ewallet
     */
    @Authorization(createPrivilege = 0, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "/credit/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletCredit(HttpServletRequest request,
                                             @RequestBody @Validated({TransferChecks.class}) TransferPayment internalTransfer) {

        TeamConverterUtil.convert(internalTransfer);
        Transaction transaction = new Transaction(internalTransfer, TransactionType.E_WALLET_CREDIT);
        transaction = eWalletCredit(request, transaction);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction eWalletCredit(HttpServletRequest request, Transaction transaction) {
        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getEWallet()) {
            throw ResponseUtil.getInsufficientPrivileges("E-Wallet feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);
        transaction.setStatusCode("S");
        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);

        transactionProcessUtil.processEWalletCredit(session, transaction);

        session.commit();
        return transaction;
    }

    /**
     *  Allows the company to debit an ewallet
     */
    @Authorization(createPrivilege = 0, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "/debit/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletDebit(HttpServletRequest request,
                                             @RequestBody @Validated({TransferChecks.class}) TransferPayment internalTransfer) {

        Transaction transaction = new Transaction(internalTransfer, TransactionType.E_WALLET_DEBIT);
        transaction = eWalletDebit(request, transaction);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction eWalletDebit(HttpServletRequest request, Transaction transaction) {
        if (transaction.getDescription() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "description required");
        }

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getEWallet()) {
            throw ResponseUtil.getInsufficientPrivileges("E-Wallet feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);
        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        transaction.setStatusCode("S");

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);

        transactionProcessUtil.processEWalletDebit(session, transaction);

        session.commit();
        return transaction;
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @RequestMapping(value = "/withdraw/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletWithdraw(HttpServletRequest request,
                                               @RequestBody @Validated({SaleChecks.class}) Payment payment) {

        TeamConverterUtil.convert(payment);

        SqlSession session = RequestUtil.getClientSqlSession(request);

        Transaction transaction = new Transaction(payment, null, TransactionType.E_WALLET_WITHDRAW,
                null, null, null, null);
        transaction = eWalletWithdraw(request, session, transaction);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction eWalletWithdraw(HttpServletRequest request, SqlSession clientSession, Transaction transaction) {
        RequestUtil.checkOwnerCreate(request, transaction.getPayerUserId()); // only the owner of the wallet can request payment
        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getEWallet()) {
            throw ResponseUtil.getInsufficientPrivileges("E-Wallet feature disabled");
        }

        if (!StringUtils.equals(transaction.getPayeeUserId(), transaction.getPayerUserId())) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "payeeUserId must equal payerUserId for withdraws");
        }

        Boolean accountValidated;
        UserBalances payeeBalance = null;
        if ((accountValidated = clientSession.getMapper(UserAccountMapper.class).isAccountValidatedForUserId(transaction.getPayeeUserId())) == null
                || !accountValidated) {
            // Can't withdraw to an invalid account
            transaction.updateResultAndCode(TransactionResult.Account_Not_Validated.getResultCode());
            transaction.setStatusCode("D");
        } else if (clientSession.getMapper(TransactionMapper.class).getWithdrawTimesForUserId(transaction.getPayeeUserId(), transaction.getTeamId()) >= 3) {
            //Maximum of 3 withdraws per day per user.
            transaction.updateResultAndCode(TransactionResult.Transaction_Limit.getResultCode());
            transaction.setStatusCode("D");
        } else {
            payeeBalance = subtractEWalletBalance(clientSession, transaction);
            if (payeeBalance == null) {
                transaction.setStatusCode("D");
                transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
            } else {
                transaction.setStatusCode("S");
                transaction.setResultCode(TransactionResult.Success.getResultCode());
            }
        }

        TransactionUtil.insertTransaction(clientSession, transaction, idUtil, null);

        transactionProcessUtil.processEWalletWithdraw(clientSession, RequestUtil.getClientId(request), transaction, payeeBalance);

        clientSession.commit();
        return transaction;
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @RequestMapping(value = "/deposit/ach/e-wallet", method = RequestMethod.POST)
    public TransactionResponse depositACHEWallet(HttpServletRequest request,
                                              @RequestBody @Validated({TransferChecks.class}) TransferPayment payment) {

        RequestUtil.checkOwnerCreate(request, payment.getPayeeUserId()); // only payee can authorize a deposit

        TeamConverterUtil.convert(payment);

        if (payment.getPayerUserId() != null && !payment.getPayerUserId().equals(payment.getPayeeUserId())) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Payee and payer must be the same for deposit to e-wallet");
        } else {
            payment.setPayerUserId(payment.getPayeeUserId());
        }

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        if (!clientSession.getMapper(UserAccountMapper.class).isAccountValidatedForUserId(payment.getPayerUserId())) {
            return new TransactionResponse(TransactionResult.Account_Not_Validated);
        }

        Transaction transaction = new Transaction(null, payment.getPayeeUserId(), payment.getPayeeUserId(), payment.getTeamId(),
                null, TransactionType.ACH_DEPOSIT_E_WALLET.slug, payment.getAmount(), null, null, "P", TransactionResult.Success.getResultCode(),
                null, payment.getDescription(), null);
        TransactionUtil.insertTransaction(clientSession, transaction, idUtil, null);

        transactionProcessUtil.processEWalletDepositACH(clientSession, RequestUtil.getClientId(request), transaction);

        clientSession.commit();
        return new TransactionResponse(transaction, true);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @RequestMapping(value = "/deposit/e-check/e-wallet", method = RequestMethod.POST)
    public TransactionResponse depositECheckEwallet(HttpServletRequest request,
                                                    @RequestBody @Validated({TransferChecks.class}) CheckTransfer checkPayment) {

        RequestUtil.checkOwnerCreate(request, checkPayment.getPayeeUserId()); // only payee can authorize a deposit

        TeamConverterUtil.convert(checkPayment);

        if (!MoneyUtil.isRoutingNumberValid(checkPayment.getRoutingNumber())) {
            return new TransactionResponse("Routing number invalid");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        GatewayConnection gatewayConnection = gatewayUtil.getGatewayConnection(session, checkPayment);

        Transaction transaction = new Transaction(null, checkPayment.getPayeeUserId(), checkPayment.getPayerUserId(),
                checkPayment.getTeamId(), null, TransactionType.E_CHECK_DEPOSIT_E_WALLET.slug, checkPayment.getAmount(),
                BigDecimal.ZERO, BigDecimal.ZERO, null, null, null, checkPayment.getDescription(), checkPayment.getAccountName());

        transaction.setBankAccount(new Account(checkPayment.getAccountName(), checkPayment.getRoutingNumber(),
                checkPayment.getAccountNumber(), checkPayment.getAccountType()));

        transaction = gatewayUtil.getGatewayApi(gatewayConnection).saleCheck(session, gatewayConnection,
                transaction, request.getRemoteAddr(), TransactionType.E_CHECK_DEPOSIT_E_WALLET);

        session.commit();

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    // Team Credits Endpoints

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sale/team-credits", method = RequestMethod.POST)
    public TransactionResponse teamCreditsSale(HttpServletRequest request,
                                               @RequestBody @Validated({TransferChecks.class}) TransferPayment internalPayment) {

        RequestUtil.checkOwnerCreate(request, internalPayment.getPayerUserId()); // Only owner of credits can auth

        TeamConverterUtil.convert(internalPayment);

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getTeamCredits()) {
            throw ResponseUtil.getInsufficientPrivileges("Team-Credits feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        CreditsMapper creditsMapper = session.getMapper(CreditsMapper.class);

        TeamCredit teamCredit = creditsMapper.findTeamCreditForUserAndTeam(internalPayment.getPayerUserId(), internalPayment.getTeamId());
        if (teamCredit == null || !checkBalance(teamCredit.getBalance(), internalPayment.getAmount(), internalPayment.getPayerUserId())) {
            return new TransactionResponse("Insufficient team credits.");
        }

        creditsMapper.subtractTeamCreditsBalance(internalPayment.getPayerUserId(), internalPayment.getTeamId(), internalPayment.getAmount());

        Transaction transaction = new Transaction(internalPayment, TransactionType.TEAM_CREDITS_SALE);

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);
        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());

        session.commit();
        return new TransactionResponse(transaction, true);
    }

    @Authorization(createPrivilege = 5, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "/credit/team-credits", method = RequestMethod.POST)
    public TransactionResponse teamCreditsCredit(HttpServletRequest request,
                                                 @RequestBody @Validated({TransferChecks.class}) TransferPayment internalTransfer) {

        TeamConverterUtil.convert(internalTransfer);

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getTeamCredits()) {
            throw ResponseUtil.getInsufficientPrivileges("Team-Credits feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);
        CreditsMapper creditsMapper = session.getMapper(CreditsMapper.class);

        if (!creditsMapper.existsTeamCreditForUserAndTeam(internalTransfer.getPayeeUserId(), internalTransfer.getTeamId())) {
            creditsMapper.insertTeamCredit(new TeamCredit(internalTransfer.getPayeeUserId(), internalTransfer.getTeamId(), BigDecimal.ZERO));
        }
        creditsMapper.addTeamCreditsBalance(internalTransfer.getPayeeUserId(), internalTransfer.getTeamId(), internalTransfer.getAmount());

        Transaction transaction = new Transaction(internalTransfer, TransactionType.TEAM_CREDITS_CREDIT);

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);
        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());

        session.commit();
        return new TransactionResponse(transaction, true);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @RequestMapping(value = "/transfer/team-credits", method = RequestMethod.POST)
    public TransactionResponse teamCreditsTransfer(HttpServletRequest request,
                                                   @RequestBody @Validated({TransferChecks.class}) TransferPayment internalTransfer) {

        RequestUtil.checkOwnerCreate(request, internalTransfer.getPayerUserId()); // Only payer can auth sending their credits

        TeamConverterUtil.convert(internalTransfer);

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getTeamCredits()) {
            throw ResponseUtil.getInsufficientPrivileges("Team-Credits feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);
        CreditsMapper creditsMapper = session.getMapper(CreditsMapper.class);

        TeamCredit teamCredit = creditsMapper.findTeamCreditForUserAndTeam(internalTransfer.getPayerUserId(), internalTransfer.getTeamId());
        if (teamCredit == null || !checkBalance(teamCredit.getBalance(), internalTransfer.getAmount(), internalTransfer.getPayerUserId())) {
            return new TransactionResponse("Insufficient team credits.");
        }
        creditsMapper.subtractTeamCreditsBalance(internalTransfer.getPayerUserId(), teamCredit.getTeamId(), internalTransfer.getAmount());

        if (!creditsMapper.existsTeamCreditForUserAndTeam(internalTransfer.getPayeeUserId(), internalTransfer.getTeamId())) {
            creditsMapper.insertTeamCredit(new TeamCredit(internalTransfer.getPayeeUserId(), internalTransfer.getTeamId(), BigDecimal.ZERO));
        }
        creditsMapper.addTeamCreditsBalance(internalTransfer.getPayeeUserId(), internalTransfer.getTeamId(), internalTransfer.getAmount());

        Transaction transaction = new Transaction(internalTransfer, TransactionType.TEAM_CREDITS_TRANSFER);

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);
        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());

        session.commit();
        return new TransactionResponse(transaction, true);
    }

    // Company Credits Endpoints
    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @APIKeyPermissions(processSales = true)
    @RequestMapping(value = "/sale/company-credits", method = RequestMethod.POST)
    public TransactionResponse companyCreditsSale(HttpServletRequest request,
                                                  @RequestBody @Validated({TransferChecks.class}) TransferPayment internalPayment) {

        RequestUtil.checkOwnerCreate(request, internalPayment.getPayerUserId()); // Payer owns credits

        TeamConverterUtil.convert(internalPayment);

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getCompanyCredits()) {
            throw ResponseUtil.getInsufficientPrivileges("Company-Credits feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        CreditsMapper creditsMapper = session.getMapper(CreditsMapper.class);

        CompanyCredit companyCredit = creditsMapper.findCompanyCreditForUserId(internalPayment.getPayerUserId());
        if (companyCredit == null || !checkBalance(companyCredit.getBalance(), internalPayment.getAmount(), internalPayment.getPayerUserId())) {
            return new TransactionResponse("Insufficient company credits.");
        }
        creditsMapper.subtractCompanyCreditsBalance(internalPayment.getPayerUserId(), internalPayment.getAmount());

        Transaction transaction = new Transaction(internalPayment, TransactionType.COMPANY_CREDITS_SALE);

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);
        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());

        session.commit();
        return new TransactionResponse(transaction, true);
    }

    @Authorization(createPrivilege = 5, clientSqlSession = true, allowAPIKey = false)
    @RequestMapping(value = "/credit/company-credits", method = RequestMethod.POST)
    public TransactionResponse companyCreditsCredit(HttpServletRequest request,
                                                    @RequestBody @Validated({TransferChecks.class}) TransferPayment internalTransfer) {
        TeamConverterUtil.convert(internalTransfer);

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getCompanyCredits()) {
            throw ResponseUtil.getInsufficientPrivileges("Company-Credits feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);
        CreditsMapper creditsMapper = session.getMapper(CreditsMapper.class);

        if (!creditsMapper.existsCompanyCreditsForUserId(internalTransfer.getPayeeUserId())) {
            creditsMapper.insertCompanyCredit(new CompanyCredit(internalTransfer.getPayeeUserId(), BigDecimal.ZERO));
        }
        creditsMapper.addCompanyCreditsBalance(internalTransfer.getPayeeUserId(), internalTransfer.getAmount());

        Transaction transaction = new Transaction(internalTransfer, TransactionType.COMPANY_CREDITS_CREDIT);

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);
        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());

        session.commit();
        return new TransactionResponse(transaction, true);
    }

    //@Authorization(clientSqlSession = true, allowSessionKey = false)
    @Authorization(createPrivilege = 8, clientSqlSession = true, allowSessionKey = false)
    @RequestMapping(value = "/transfer/company-credits", method = RequestMethod.POST)
    public TransactionResponse companyCreditsTransfer(HttpServletRequest request,
                                                      @RequestBody @Validated({TransferChecks.class}) TransferPayment internalTransfer) {

        RequestUtil.checkOwnerCreate(request, internalTransfer.getPayerUserId()); // Payer owns credits

        TeamConverterUtil.convert(internalTransfer);

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getCompanyCredits()) {
            throw ResponseUtil.getInsufficientPrivileges("Company-Credits feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);
        CreditsMapper creditsMapper = session.getMapper(CreditsMapper.class);

        CompanyCredit companyCredit = creditsMapper.findCompanyCreditForUserId(internalTransfer.getPayerUserId());
        if (companyCredit == null || !checkBalance(companyCredit.getBalance(), internalTransfer.getAmount(), internalTransfer.getPayerUserId())) {
            return new TransactionResponse("Insufficient company credits.");
        }
        creditsMapper.subtractCompanyCreditsBalance(internalTransfer.getPayerUserId(), internalTransfer.getAmount());

        if (!creditsMapper.existsCompanyCreditsForUserId(internalTransfer.getPayeeUserId())) {
            creditsMapper.insertCompanyCredit(new CompanyCredit(internalTransfer.getPayeeUserId(), BigDecimal.ZERO));
        }
        creditsMapper.addCompanyCreditsBalance(internalTransfer.getPayeeUserId(), internalTransfer.getAmount());

        Transaction transaction = new Transaction(internalTransfer, TransactionType.COMPANY_CREDITS_TRANSFER);

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);
        session.getMapper(TransactionMapper.class).markProcessed(transaction.getId());

        session.commit();
        return new TransactionResponse(transaction, true);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/tax-payment/ach", method = RequestMethod.POST)
    public TransactionResponse achTaxPayment(HttpServletRequest request,
                                             @RequestParam(value = "showTransaction", defaultValue = "false") boolean showTransaction,
                                             @RequestBody @Validated({PaymentChecks.class}) Payment payment) {
        throw new ResponseException(HttpStatus.METHOD_NOT_ALLOWED, "Under construction");
//        TeamConverterUtil.convert(payment);
//
//        validatePayeeEqualsPayer(payment);
//
//        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
//        Boolean accountValid = clientSession.getMapper(UserAccountMapper.class).isAccountValidatedForUserId(payment.getPayerUserId());
//        if (accountValid == null || !accountValid) {
//            return new TransactionResponse(TransactionResult.Account_Not_Validated);
//        }
//        String clientId = RequestUtil.getClientId(request);
//        validateTaxBalance(clientSession, clientId, payment, TransactionType.ACH_PAYMENT_TAX);
//
//        Transaction transaction = new Transaction(payment, null, TransactionType.ACH_PAYMENT_TAX, null, "P", null);
//        TransactionUtil.insertTransaction(clientSession, transaction, null);
//
//        transactionProcessUtil.processACHTaxPayment(clientSession, clientId, transaction);
//
//        clientSession.commit();
//        return new TransactionResponse(transaction, showTransaction);
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/tax-payment/card", method = RequestMethod.POST)
    public TransactionResponse cardTaxPayment(HttpServletRequest request,
                                                @RequestBody @Validated({PaymentChecks.class}) CardPayment cardPayment,
                                                BindingResult result) {

        TeamConverterUtil.convert(cardPayment);

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);

        String clientId = RequestUtil.getClientId(request);

        int resultCode = validateTaxBalance(clientSession, clientId, cardPayment.getTeamId(),
                cardPayment.getPayerUserId(), cardPayment.getTotal(), TransactionType.CARD_PAYMENT_TAX);

        if (resultCode != TransactionResult.Success.getResultCode()) {
            return new TransactionResponse(TransactionResult.findById(resultCode));
        }

        TransactionResponse transactionResponse = cardTransactionWithResponse(request, cardPayment, TransactionType.CARD_PAYMENT_TAX, result);

        return transactionResponse;
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/tax-payment/e-check", method = RequestMethod.POST)
    public TransactionResponse eCheckTaxPayment(HttpServletRequest request,
                                                @RequestBody @Validated({PaymentChecks.class}) CheckPayment checkPayment) {

        TeamConverterUtil.convert(checkPayment);

        if (!MoneyUtil.isRoutingNumberValid(checkPayment.getRoutingNumber())) {
            return new TransactionResponse("Routing number invalid");
        }

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);

        String clientId = RequestUtil.getClientId(request);
        Transaction transaction = new Transaction(checkPayment, null, TransactionType.E_CHECK_PAYMENT_TAX,
                checkPayment.getName(), null, null, null);

        int resultCode = validateTaxBalance(clientSession, clientId, checkPayment.getTeamId(),
                checkPayment.getPayerUserId(), checkPayment.getTotal(), TransactionType.E_CHECK_PAYMENT_TAX);

        if (resultCode != TransactionResult.Success.getResultCode()) {
            return new TransactionResponse(TransactionResult.findById(resultCode));
        }

        transaction = checkTransaction(request, clientSession, transaction, TransactionType.E_CHECK_PAYMENT_TAX);


        TransactionResponse transactionResponse = new TransactionResponse(transaction, true,
                TransactionResult.findById(transaction.getResultCode()));

        if (transactionResponse.getSuccess())
            clientSession.commit();

        return transactionResponse;
    }

    @Authorization(createPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/tax-payment/e-wallet", method = RequestMethod.POST)
    public TransactionResponse eWalletTaxPayment(HttpServletRequest request,
                                                 @RequestBody @Validated({PaymentChecks.class}) Payment payment) {

        TeamConverterUtil.convert(payment);

        Transaction transaction = new Transaction(payment, null, TransactionType.E_WALLET_PAYMENT_TAX,
                null, null, null, null);
        transaction = eWalletTaxPayment(request, transaction);

        return new TransactionResponse(transaction, true, TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction eWalletTaxPayment(HttpServletRequest request, Transaction transaction) {
        RequestUtil.checkOwnerCreate(request, transaction.getPayerUserId()); // Payer owns wallet

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        String clientId = RequestUtil.getClientId(request);

        int resultCode = validateTaxBalance(clientSession, clientId, transaction.getTeamId(),
                transaction.getPayerUserId(), transaction.getAmount(), TransactionType.E_WALLET_PAYMENT_TAX);

        UserBalances payerBalances = null;
        if (resultCode != TransactionResult.Success.getResultCode()) {
            transaction.setStatusCode("D");
            transaction.updateResultAndCode(resultCode);
        } else if ((payerBalances = subtractEWalletBalance(clientSession, transaction)) == null) {
            transaction.setStatusCode("D");
            transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
        } else {
            transaction.setStatusCode("S");
            transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        }

        TransactionUtil.insertTransaction(clientSession, transaction, idUtil, null);
        transactionProcessUtil.processEWalletTaxPayment(clientSession, clientId, transaction, payerBalances);
        clientSession.commit();
        return transaction;
    }

    @Authorization(clientSqlSession = true, createPrivilege = 8)
    @RequestMapping(value = "", method = RequestMethod.POST)
    public Transaction createTransaction(HttpServletRequest request,
                                         @RequestBody @Validated({SaleChecks.class}) Transaction transaction,
                                         BindingResult errors) {

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);
        TransactionType transactionType = TransactionType.findBySlug(transaction.getTransactionType());
        String clientId = RequestUtil.getClientId(request);
        PayManUser authUser = RequestUtil.getAuthUser(request);

        Transaction createdTransaction;

        if (StringUtils.isNotBlank(transaction.getGatewayReferenceId())) {
            validateTransactionPost(transaction, errors, true, false, true, true,
                    false, true, false, false);
            createdTransaction = importTransaction(clientSession, transaction, transactionType);
        } else {
            int resultCode;
            switch (transactionType) {
                case CASH_SALE:
                    validateTransactionPost(transaction, errors, true, false, true, true,
                            false, false, false, false);
                    createdTransaction = cashTransaction(request, transaction);
                    break;
                case CREDIT_CARD_SALE:
                case CREDIT_CARD_SUB:
                case DEBIT_CARD_SALE:
                case DEBIT_CARD_SUB:
                case CREDIT_CARD_SHIPPING:
                case DEBIT_CARD_SHIPPING:
                case CARD_SWIPE_SALE:
                    validateTransactionPost(transaction, errors, true, false, true, true,
                            false, false, false, false);
                    if (transaction.getCard() == null) {
                        throw new ResponseException(HttpStatus.BAD_REQUEST, "card info required");
                    }
                    createdTransaction = cardTransaction(request, clientSession, transaction, transactionType);
                    break;
                case CHECK_SALE:
                case CHECK_SUB:
                case E_CHECK_SHIPPING:
                    validateTransactionPost(transaction, errors, true, false, true, true,
                            false, false, false, false);
                    if (transaction.getBankAccount() == null) {
                        throw new ResponseException(HttpStatus.BAD_REQUEST, "bankAccount info required");
                    }
                    createdTransaction = checkTransaction(request, clientSession, transaction, transactionType);
                    break;
                case REFUND:
                case REFUND_CASH:
                    RequestUtil.checkOwnerCreate(request, transaction.getPayeeUserId());
                    validateTransactionPost(transaction, errors, false, false, false, true,
                            false, false, true, false);
                    createdTransaction = refundUtil.refundTransaction(clientSession, transaction, "", clientId);
                    break;
                case CARD_PAYMENT_TAX:
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    resultCode = validateTaxBalance(clientSession, clientId, transaction.getTeamId(),
                            transaction.getPayerUserId(), transaction.getAmount(), transactionType);

                    if (resultCode != TransactionResult.Success.getResultCode()) {
                        transaction.updateResultAndCode(resultCode);
                        transaction.setStatusCode("D");
                        createdTransaction = transaction;
                    } else {
                        createdTransaction = cardTransaction(request, clientSession, transaction, transactionType);
                    }
                    break;
                case E_CHECK_PAYMENT_TAX:
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    resultCode = validateTaxBalance(clientSession, clientId, transaction.getTeamId(),
                            transaction.getPayerUserId(), transaction.getAmount(), transactionType);

                    if (resultCode != TransactionResult.Success.getResultCode()) {
                        transaction.updateResultAndCode(resultCode);
                        transaction.setStatusCode("D");
                        createdTransaction = transaction;
                    } else {
                        createdTransaction = checkTransaction(request, clientSession, transaction, transactionType);
                    }
                    break;
                case E_WALLET_CREDIT:
                    if (authUser.getPrivilege().getCreatePrivilege() > 0) {
                        throw new ResponseException(HttpStatus.FORBIDDEN, "Only admin can credit ewallet");
                    }
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    createdTransaction = eWalletCredit(request, transaction);
                    break;
                case E_WALLET_DEBIT:
                    if (authUser.getPrivilege().getWritePrivilege() > 0) {
                        throw new ResponseException(HttpStatus.FORBIDDEN, "Only admin can debit ewallet");
                    }
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    createdTransaction = eWalletDebit(request, transaction);
                    break;
                case E_WALLET_PAYMENT_TAX:
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    createdTransaction = eWalletTaxPayment(request, transaction);
                    break;
                case E_WALLET_SALE:
                case E_WALLET_SHIPPING:
                case E_WALLET_SUB:
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, false);
                    createdTransaction = eWalletTransaction(request, transaction, transactionType);
                    break;
                case E_WALLET_TRANSFER:
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    createdTransaction = eWalletTransfer(request, clientSession, transaction);
                    break;
                case E_WALLET_WITHDRAW:
                    validateTransactionPost(transaction, errors, true, true, true, true,
                            false, false, false, true);
                    createdTransaction = eWalletWithdraw(request, clientSession, transaction);
                    break;
                case PAYPAL_SALE:
                case COMPANY_CREDITS_CREDIT:
                case COMPANY_CREDITS_SALE:
                case COMPANY_CREDITS_TRANSFER:
                case TEAM_CREDITS_CREDIT:
                case TEAM_CREDITS_SALE:
                case TEAM_CREDITS_TRANSFER:
                case E_CHECK_DEPOSIT_E_WALLET:
                case E_WALLET_DEPOSIT:
                case ACH_DEPOSIT_E_WALLET:
                case ACH_PAYMENT_TAX:
                case ACH_SHIPPING:
                    // TODO payer must have a validated bank account
                    // TODO implement at another time
                default:
                    throw new ResponseException(HttpStatus.BAD_REQUEST, "transactionType invalid");
            }
        }

        clientSession.commit();
        return createdTransaction;
    }

    @Authorization(clientSqlSession = true, createPrivilege = 7)
    @RequestMapping(value = "/capture", method = RequestMethod.POST)
    public Transaction captureTransaction(HttpServletRequest request,
                                          @RequestBody Transaction transaction) {

        if (transaction.getId() == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "id required");
        }

        SqlSession clientSession = RequestUtil.getClientSqlSession(request);

        Transaction currentTransaction = clientSession.getMapper(TransactionMapper.class).findById(transaction.getId());
        if (currentTransaction == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction not found");
        }

        if (StringUtils.equals(currentTransaction.getStatusCode(), "P") || StringUtils.equals(currentTransaction.getStatusCode(), "S")) {
            // Already captured
            if (transaction.getOrderId() != null) { // Allow order id to update because e-wallet is always captured for now
                currentTransaction.setOrderId(transaction.getOrderId());
                clientSession.getMapper(TransactionMapper.class).updateOrderId(currentTransaction);
                clientSession.commit();
            }
            return currentTransaction;
        }
        if (!StringUtils.equals(currentTransaction.getStatusCode(), "A")) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction not approved");
        }
        TransactionType transactionType = TransactionType.findBySlug(currentTransaction.getTransactionType());
        switch (transactionType) {
            case CREDIT_CARD_SALE:
            case CREDIT_CARD_SUB:
            case DEBIT_CARD_SALE:
            case DEBIT_CARD_SUB:
                // Do nothing
                break;
            default:
                throw new ResponseException("Transaction type not supported for capture");
        }

        GatewayConnection gatewayConnection = clientSession.getMapper(GatewayConnectionMapper.class).findById(currentTransaction.getGatewayConnectionId());
        if (currentTransaction.getGatewayConnectionId() == null || gatewayConnection == null) {
            MDC.put("transactionId", transaction.getId());
            logger.error("Gateway connection missing for capture");
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
        }

        if (transaction.getOrderId() != null) { // Allow order id to update
            currentTransaction.setOrderId(transaction.getOrderId());
        }
        Transaction savedTransaction = gatewayUtil.getGatewayApi(gatewayConnection).captureTransaction(clientSession, gatewayConnection, currentTransaction, RequestUtil.getClientId(request));

        clientSession.commit();
        return savedTransaction;
    }
    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{id}/capture", method = RequestMethod.GET)
    public Transaction captureTransaction(HttpServletRequest request,
                                                  @PathVariable("id") String transactionId) {

        Transaction transaction = new Transaction();
        transaction.setId(transactionId);
        return captureTransaction(request, transaction);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 8)
    @RequestMapping(value = "/{transactionId}", method = RequestMethod.GET)
	public Transaction getTransaction(HttpServletRequest request,
                                      @PathVariable("transactionId") String transactionId) {

        SqlSession session = RequestUtil.getClientSqlSession(request);

        Transaction transaction = session.getMapper(TransactionMapper.class).findById(transactionId);
        validatePayerPayeeRead(RequestUtil.getAuthUser(request), transaction.getPayerUserId(), transaction.getPayeeUserId());

        return transaction;
	}

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{transactionId}/gateway-transaction", method = RequestMethod.GET)
    public Object getGatewayTransaction(HttpServletRequest request,
                                      @PathVariable("transactionId") String transactionId) {

        SqlSession session = RequestUtil.getClientSqlSession(request);

        Transaction transaction = session.getMapper(TransactionMapper.class).findById(transactionId);
        if (transaction.getGatewayConnectionId() != null) {
            GatewayConnection gatewayConnection = session.getMapper(GatewayConnectionMapper.class).findById(transaction.getGatewayConnectionId());
            return gatewayUtil.getGatewayApi(gatewayConnection).getTransaction(gatewayConnection, transaction);
        }

        return null;
    }

    // Fetch transaction from gateway and update locally
    @Authorization(readPrivilege = 2, clientSqlSession = true)
    @RequestMapping(value = "/{transaction_id}/update-status", method = RequestMethod.GET)
    public Transaction updateTransactionStatus(HttpServletRequest request,
                                               @PathVariable(value = "transaction_id") String transactionId) {

        SqlSession session = RequestUtil.getClientSqlSession(request);
        GatewayConnection gatewayConnection = session.getMapper(GatewayConnectionMapper.class).findForTransactionId(transactionId);
        Transaction currentTransaction = RequestUtil.getClientSqlSession(request).getMapper(TransactionMapper.class).findById(transactionId);
        if (currentTransaction == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction doesn't exist");
        }

        gatewayUtil.getGatewayApi(gatewayConnection).updateTransactionStatus(session, currentTransaction, gatewayConnection, request.getRemoteAddr());

        session.commit();
        return currentTransaction;
    }

    //@Authorization(clientSqlSession = true)
    @Authorization(createPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "/{transactionId}/{refundType}", method = RequestMethod.POST)
    public TransactionResponse refundTransaction(HttpServletRequest request,
                                                    @PathVariable(value = "transactionId") String transactionId,
                                                    @PathVariable(value = "refundType") String refundType,
                                                    @RequestBody @Validated({PostChecks.class}) TransactionRefund refund) {

        refund.setType(refundType);
        if (!StringUtils.equals("refund", refundType) && !StringUtils.equals("cash-refund", refundType)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Refund type not valid");
        }

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        Transaction refundTransaction = refund.asTransaction();
        refundTransaction.setForTxnId(transactionId);
        // Perform refund
        refundTransaction = refundUtil.refundTransaction(sqlSession, refundTransaction,
                request.getRemoteAddr(), RequestUtil.getClientId(request));

        // Convert to old response
        TransactionResponse refundResponse = new TransactionResponse(refundTransaction, true,
                TransactionResult.findById(refundTransaction.getResultCode()));

        if (refundResponse.getSuccess()) {
            // Only commit successes
            sqlSession.commit();
        }
        return refundResponse;
    }

    @Authorization(readPrivilege = 8, clientSqlSession = true)
    @RequestMapping(value = "/select-gateway", method = RequestMethod.GET)
    public GatewayConnection selectGateway(HttpServletRequest request,
                                           @RequestParam(value = "teamId") String teamId,
                                           @RequestParam(value = "payeeUserId") String payeeUserId,
                                           @RequestParam(value = "processCards", required = false) Boolean processCards,
                                           @RequestParam(value = "processChecks", required = false) Boolean processChecks,
                                           @RequestParam(value = "processInternal", required = false) Boolean processInternal,
                                           @RequestParam(value = "type", required = false) String type) {

        teamId = TeamConverterUtil.convert(teamId);

        GatewayConnection gatewayConnection = gatewayUtil.selectGatewayConnection(RequestUtil.getClientSqlSession(request), null, teamId,
                payeeUserId, processCards, processChecks, processInternal, type);

        if (gatewayConnection != null) {
            gatewayConnection.obscure(); // Return obscured connection
        }

        return gatewayConnection;
    }

    // Shared functions

    private Transaction importTransaction(SqlSession clientSession, Transaction transaction, TransactionType transactionType) {
        switch (transactionType) {
            case CREDIT_CARD_SALE:
            case CREDIT_CARD_SUB:
            case DEBIT_CARD_SALE:
            case DEBIT_CARD_SUB:
            case REFUND:
                // Supported types
                break;
            default:
                throw new ResponseException(HttpStatus.BAD_REQUEST, "transactionType invalid");
        }

        GatewayConnection gatewayConnection = gatewayUtil.selectGatewayConnection(clientSession, null,
                transaction.getTeamId(), transaction.getPayeeUserId(), null, null, null, null);

        if (gatewayConnection == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "No gateway found for user");
        }

        if (clientSession.getMapper(TransactionMapper.class).existsForGatewayReference(gatewayConnection.getId(), transaction.getGatewayReferenceId())) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Transaction already imported");
        }

        return gatewayUtil.getGatewayApi(gatewayConnection).importTransaction(clientSession, gatewayConnection, transaction);
    }

    private Transaction cashTransaction(HttpServletRequest request, Transaction transaction) {
        RequestUtil.checkOwnerCreate(request, transaction.getPayeeUserId()); // Only seller can auth cash transactions

        // Validate type

        SqlSession session = RequestUtil.getClientSqlSession(request);
        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        transaction.setStatusCode("S");

        TransactionUtil.insertTransaction(session, transaction, idUtil, null);

        transactionProcessUtil.processCashSale(RequestUtil.getClientId(request), session, transaction);

        session.commit();

        return transaction;
    }

    private TransactionResponse checkTransactionWithResponse(HttpServletRequest request, CheckPayment checkPayment, BindingResult result,
                                                             TransactionType transactionType) {

        paymentValidator.validate(checkPayment, result);
        if (result.hasErrors()) {
            throw new ResponseException(result);
        }

        if (!MoneyUtil.isRoutingNumberValid(checkPayment.getRoutingNumber())) {
            return new TransactionResponse("Routing number invalid");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        Transaction transaction = new Transaction(checkPayment, null, transactionType,
                checkPayment.getAccountName(), null, null, null);
        transaction.setBankAccount(new Account(checkPayment.getName(), checkPayment.getRoutingNumber(),
                checkPayment.getAccountNumber(), checkPayment.getAccountType()));

        transaction = checkTransaction(request, session, transaction, transactionType);

        return new TransactionResponse(transaction, true,
                TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction checkTransaction(HttpServletRequest request, SqlSession clientSession, Transaction transaction,
                                         TransactionType transactionType) {
        validatePayerPayeeAuth(RequestUtil.getAuthUser(request), transaction.getPayerUserId(), transaction.getPayeeUserId());

        GatewayConnection gatewayConnection = gatewayUtil.getGatewayConnection(clientSession, transaction,
                null, true, null);

        transaction = gatewayUtil.getGatewayApi(gatewayConnection).saleCheck(clientSession, gatewayConnection, transaction,
                request.getRemoteAddr(), transactionType);

        clientSession.commit();
        return transaction;
    }

    private TransactionResponse cardTransactionWithResponse(HttpServletRequest request, CardPayment cardPayment,
                                                            TransactionType transactionType, BindingResult result) {

        paymentValidator.validate(cardPayment, result);
        if (result.hasErrors()) {
            throw new ResponseException(result);
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        Transaction transaction = new Transaction(cardPayment, null, transactionType,
                cardPayment.getCardHolder(), null, null, null);

        Card card = cardPayment.getCard();
        if (card == null) {
            card = new Card();
        }
        if (cardPayment.getCardEncMagstripe() != null) {
            card.setEncMagstripe(cardPayment.getCardEncMagstripe());
        }
        if (cardPayment.getCardMagstripe() != null) {
            card.setMagstripe(cardPayment.getCardMagstripe());
        }
        if (cardPayment.getCardToken() != null) {
            card.setToken(cardPayment.getCardToken());
            card.setGatewayCustomerId(cardPayment.getGatewayCustomerId());
        }
        if (cardPayment.getCardNonce() != null) {
            card.setNonce(cardPayment.getCardNonce());
        }
        transaction.setCard(card);

        // Move card holder name to billing address if needed
        if (transaction.getBillingAddress() == null) {
            transaction.setBillingAddress(new Address());
        }
        if (transaction.getBillingAddress().getFullName() == null) {
            transaction.getBillingAddress().setFullName(cardPayment.getName());
        }

        transaction = cardTransaction(request, session, transaction, transactionType);

        return new TransactionResponse(transaction, true,
                TransactionResult.findById(transaction.getResultCode()));
    }

    private Transaction cardTransaction(HttpServletRequest request, SqlSession clientSession, Transaction transaction,
                                        TransactionType transactionType) {
        validatePayerPayeeAuth(RequestUtil.getAuthUser(request), transaction.getPayerUserId(), transaction.getPayeeUserId());

        GatewayConnection gatewayConnection = gatewayUtil.getGatewayConnection(clientSession, transaction,
                true, null, null);

        transaction = gatewayUtil.getGatewayApi(gatewayConnection).saleCard(clientSession, gatewayConnection, request.getRemoteAddr(),
                transaction, transactionType);

        clientSession.commit();

        return transaction;
    }

    private Transaction eWalletTransaction(HttpServletRequest request, Transaction transaction, TransactionType transactionType) {
        RequestUtil.checkOwnerCreate(request, transaction.getPayerUserId()); // Only the owner of the wallet can authorize payment

        if (!clientConfigUtil.getClientFeatures(RequestUtil.getClientId(request)).getEWallet()) {
            throw ResponseUtil.getInsufficientPrivileges("E-Wallet feature disabled");
        }

        SqlSession session = RequestUtil.getClientSqlSession(request);

        UserBalancesMapper userBalancesMapper = session.getMapper(UserBalancesMapper.class);

        UserBalances payerBalances = userBalancesMapper.find(transaction.getPayerUserId(), transaction.getTeamId()); // Payer wallet reference
        if (payerBalances == null
                || !checkBalance(payerBalances.getEWallet(), transaction.getAmount(), transaction.getPayeeUserId())
                || userBalancesMapper.subtractEWalletSafe(payerBalances.getId(), transaction.getAmount()) == 0) {

            transaction.updateResultAndCode(TransactionResult.Insufficient_Funds.getResultCode());
            transaction.setStatusCode("D");
            return transaction;
        }

        transaction.updateResultAndCode(TransactionResult.Success.getResultCode());
        transaction.setStatusCode("S");

        TransactionUtil.insertTransaction(session, transaction, idUtil, transaction.getAffiliatePayouts());

        transactionProcessUtil.processEWalletSale(session, RequestUtil.getClientId(request), transaction, payerBalances);

        session.commit();
        return transaction;
    }

    private boolean checkBalance(BigDecimal balance, BigDecimal amount, String userId) {
        if (balance == null || balance.compareTo(amount) < 0) {
            if (balance == null)
                logger.error(String.format("Ewallet was null for user: %d", userId));
            else if (balance.compareTo(BigDecimal.ZERO) < 0)
                logger.error(String.format("Ewallet balance was less than 0 for user: %d", userId));

            return false;
        }
        return true;
    }

    private int validateTaxBalance(SqlSession sqlSession, String clientId, String teamId,
                                   String payerUserId, BigDecimal amount, TransactionType type) {
        BigDecimal feeTotal = feeSetUtil.getFeeTotalForTransaction(clientId, teamId, amount, type);
        UserBalances userBalances = sqlSession.getMapper(UserBalancesMapper.class).find(payerUserId, teamId);
        if (userBalances == null) {
            userBalances = new UserBalances(payerUserId, teamId);
            sqlSession.getMapper(UserBalancesMapper.class).insert(userBalances);
        }
        ReportsMapper reportsMapper = sqlSession.getMapper(ReportsMapper.class);
        BigDecimal taxOwed = BigDecimal.ZERO;
        BigDecimal openTax = reportsMapper.calculateOpenTaxAmountForUser(payerUserId);
        BigDecimal pendingTax = reportsMapper.getPendingTaxPaymentsForUserId(payerUserId);
        taxOwed = taxOwed.subtract(userBalances.getSalesTax());
        if (openTax != null) {
            taxOwed = taxOwed.add(openTax);
        }
        if (pendingTax != null) {
            taxOwed = taxOwed.subtract(pendingTax);
        }
        if (taxOwed.compareTo(amount.subtract(feeTotal)) < 0) {
            return TransactionResult.Balance_Lower.getResultCode();
        }
        return TransactionResult.Success.getResultCode();
    }

    private UserBalances subtractEWalletBalance(SqlSession sqlSession, Transaction transaction) {
        UserBalancesMapper userBalancesMapper = sqlSession.getMapper(UserBalancesMapper.class);
        UserBalances balances = userBalancesMapper.find(transaction.getPayerUserId(), transaction.getTeamId());
        if (balances == null || balances.getEWallet().compareTo(transaction.getAmount()) < 0 ||
                userBalancesMapper.subtractEWalletSafe(balances.getId(), transaction.getAmount()) == 0) {
            // this means we failed to subtract balance either because record is missing, balance low, or sql check on balance failed
            return null;
        }
        return balances;
    }

    private void validateTransactionPost(Transaction transaction, BindingResult result, boolean payee, boolean payer,
                                         boolean teamId, boolean amount, boolean description, boolean referenceId,
                                         boolean forTxnId, boolean noAffiliates) {

        if (payee && StringUtils.isBlank(transaction.getPayeeUserId())) {
            result.rejectValue("payeeUserId", null, "payeeUserId required");
        }
        if (payer && StringUtils.isBlank(transaction.getPayerUserId())) {
            result.rejectValue("payerUserId", null, "payerUserId required");
        }
        if (teamId && StringUtils.isBlank(transaction.getTeamId())) {
            result.rejectValue("teamId", null, "teamId required");
        }
        if (amount && transaction.getAmount() == null) {
            result.rejectValue("amount", null, "amount required");
        }
        if (description && StringUtils.isBlank(transaction.getDescription())) {
            result.rejectValue("description", null, "description required");
        }
        if (referenceId && StringUtils.isBlank(transaction.getGatewayReferenceId())) {
            result.rejectValue("gatewayReferenceId", null, "gatewayReferenceId required");
        }
        if (forTxnId && StringUtils.isBlank(transaction.getForTxnId())) {
            result.rejectValue("forTxnId", null, "forTxnId required");
        }
        if (noAffiliates && transaction.getAffiliatePayouts() != null) {
            result.rejectValue("affiliatePayouts", null, "affiliatePayouts not allowed");
        }
        if (result.hasErrors()) {
            throw new ResponseException(result);
        }
    }

    private void validatePayerPayeeAuth(PayManUser authUser, String payerId, String payeeId) {
        if (authUser.getPrivilege().getCreatePrivilege() > 7 &&
                !StringUtils.equals(authUser.getId(), payerId) &&
                !StringUtils.equals(authUser.getId(), payeeId)) {
            throw new ResponseException(HttpStatus.FORBIDDEN, "Payer, Payee, Admin must authorize payment");
        }
    }

    private void validatePayerPayeeRead(PayManUser authUser, String payerId, String payeeId) {
        if (authUser.getPrivilege().getReadPrivilege() > 7 &&
                !StringUtils.equals(authUser.getId(), payerId) &&
                !StringUtils.equals(authUser.getId(), payeeId)) {
            throw new ResponseException(HttpStatus.FORBIDDEN, "Payer, Payee, Admin must authorize payment");
        }
    }
}