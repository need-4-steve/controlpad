package com.controlpad.pay_fac.payment;

import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.payman_user.PayManUser;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_common.util.GsonUtil;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import java.math.BigDecimal;
import java.util.List;
import java.util.Locale;

@RestController
@RequestMapping(value = "/payments")
public class PaymentController {

    private static final Logger logger = LoggerFactory.getLogger(PaymentController.class);

    private DateTimeFormatter dtf = DateTimeFormat.forPattern("YYYY-MM-dd HH:mm:ss.S");

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<Payment> listPayments(HttpServletRequest request,
                                                   @RequestParam(value = "paymentFileId", required = false) Long paymentFileId,
                                                   @RequestParam(value = "paymentBatchId", required = false) String paymentBatchId,
                                                   @RequestParam(value = "teamId", required = false) String teamId,
                                                   @RequestParam(value = "userId", required = false) String userId,
                                                   @RequestParam(value = "accountId", required = false) Long accountId,
                                                   @RequestParam(value = "returned", required = false) Boolean returned,
                                                   @RequestParam(value = "type", required = false) String type,
                                                   @RequestParam(value = "startDate", required = false) String startDate,
                                                   @RequestParam(value = "endDate", required = false) String endDate,
                                                   @RequestParam("page") Long page,
                                                   @RequestParam("count") Integer count) {

        ParamValidations.checkPageCount(count, page);

        PayManUser authUser = RequestUtil.getAuthUser(request);
        if (authUser.getPrivilege().getReadPrivilege() > 7) {
            userId = authUser.getId();
        }

        PaymentMapper paymentMapper = RequestUtil.getClientSqlSession(request).getMapper(PaymentMapper.class);

        Long totalCount = paymentMapper.searchCount(paymentFileId, userId, paymentBatchId, accountId, teamId, returned, type, startDate, endDate);

        if (totalCount == 0) {
            // No results
            return new PaginatedResponse<>(null, 0, null);
        }
        List<Payment> payments = paymentMapper.search(paymentFileId, userId, paymentBatchId, accountId, teamId, returned, type,
                startDate, endDate, (page - 1) * count, count);

        return new PaginatedResponse<>(totalCount, count, page, payments);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{id}", method = RequestMethod.GET)
    public Payment getPayment(HttpServletRequest request,
                                              @PathVariable("id") String id) {

        Payment payment = RequestUtil.getClientSqlSession(request).getMapper(PaymentMapper.class).findPaymentById(id);
        RequestUtil.checkOwnerRead(request, payment.getUserId());

        return payment;
    }

    /**
     * If a payment gets returned.
     * Marks the payout batch as returned and recreates it with a reference to the batch chain.
     * Marks user account invalid where applicable
     * @param id  Payout batch id, sent through the ach network as the entry id.
     */
    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{id}/returned", method = RequestMethod.GET)
    public CommonResponse paymentReturned(HttpServletRequest request,
                                         @PathVariable("id") String id) {

        SqlSession session = RequestUtil.getClientSqlSession(request);
        // determine payment type by id prefix then run code based on that
        char prefix = id.charAt(0);
        switch (prefix) {
            default:
            case 'p':
                return returnPayout(request, session, id);
            case 'd':
                return returnDebit(request, session, id);
        }
    }

    private CommonResponse returnPayout(HttpServletRequest request, SqlSession session, String id) {
        PaymentMapper paymentMapper = session.getMapper(PaymentMapper.class);
        Payment payment = paymentMapper.findPaymentById(id);

        if (payment == null) {
            return new CommonResponse(false, -1, "Payment does not exist");
        } else if (payment.getPaidAt() == null) {
            return new CommonResponse(false, -2, "Payment not submitted yet");
        } else if (payment.getReturned()) {
            return new CommonResponse(false, -3, "Payment already marked as returned");
        }

        paymentMapper.markReturned(id);
        switch(PaymentType.findForSlug(payment.getType())){
            case SALES_TAX:
            case CONSIGNMENT:
            case FEE:
                logger.error("Abnormal returned batch type: " + payment.getType() + " BatchId: " + payment.getId());
                session.commit();
                return new CommonResponse(true, 1, "Payout marked as returned");
            case WITHDRAW:
            case MERCHANT:
            case AFFILIATE:
                UserBalancesMapper userBalancesMapper = session.getMapper(UserBalancesMapper.class);
                UserBalances userBalances = userBalancesMapper.find(payment.getUserId(), payment.getTeamId());
                if (userBalances == null) {
                    MDC.put("payment", GsonUtil.getGson().toJson(payment));
                    logger.error("No user balance found for payment return client");
                    throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR);
                }
                Entry returnEntry = new Entry(userBalances.getId(), payment.getAmount(), null, null, payment.getId(), PaymentType.FAILED_PAYMENT.slug, true);
                session.getMapper(EntryMapper.class).insert(returnEntry);
                userBalancesMapper.add(userBalances.getId(), BigDecimal.ZERO, payment.getAmount(), payment.getAmount());
                // TODO support return fee?
                boolean userAccountInvalidated = false;
                if (payment.getAccountId() == null && payment.getUserId() != null) { // If a user account payout
                    UserAccountMapper userAccountMapper = session.getMapper(UserAccountMapper.class);
                    if (userAccountMapper.isAccountValidatedForUserId(payment.getUserId())) {
                        String validationDate = userAccountMapper.findCurrentValidationDateForUser(payment.getUserId());

                        if (validationDate == null || (dtf.parseDateTime(validationDate).compareTo(dtf.parseDateTime(payment.getPaidAt())) < 0)) {
                            userAccountMapper.markAccountInvalid(payment.getUserId());
                            userAccountInvalidated = true;
                        }
                    }
                }
                session.commit();

                if (userAccountInvalidated) {
                    return new CommonResponse(true, 2, "Payout marked as returned and user account marked invalid");
                } else {
                    return new CommonResponse(true, 1, "Payout marked as returned");
                }
            default:
                logger.error(String.format(Locale.US, "Invalid type(%s) found in return payout batch(%s)", payment.getType(), id));
                throw new ResponseException(HttpStatus.BAD_REQUEST, "Unsupported payment type");
        }
    }

    // TODO rework this?
    private CommonResponse returnDebit(HttpServletRequest request, SqlSession session, String id) {
        throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Returned debits is under construction");
//        TransactionDebitMapper transactionDebitMapper = session.getMapper(TransactionDebitMapper.class);
//        TransactionChargeMapper transactionChargeMapper = session.getMapper(TransactionChargeMapper.class);
//
//        TransactionDebit debit = transactionDebitMapper.findById(id);
//        if (debit == null) {
//            return new CommonResponse(false, -1, "Payment does not exist");
//        } else if (debit.getPaymentFileId() == null || !session.getMapper(PaymentFileMapper.class).isSubmitted(debit.getPaymentFileId())) {
//            return new CommonResponse(false, -2, "Payment not submitted yet");
//        } else if (debit.getReturned()) {
//            return new CommonResponse(false, -3, "Payment already marked as returned");
//        }
//
//        transactionDebitMapper.markReturned(debit.getId());
//        Transaction originalTransaction = session.getMapper(TransactionMapper.class).findById(debit.getTransactionId());
//
//        List<Entry> entries = session.getMapper(EntryMapper.class).listByTransactionId(debit.getTransactionId());
//        TransactionCharge charge;
//        for (Entry payout : entries) {
//            charge = new TransactionCharge(payout.getUserId(), payout.getTransactionId(),
//                    payout.getAccountId(), payout.getAmount(), payout.getType());
//            transactionChargeMapper.insert(charge);
//            switch (PaymentType.findForSlug(charge.getType())) {
//                case SALES_TAX:
//                    UserBalancesMapper userBalancesMapper = session.getMapper(UserBalancesMapper.class);
//                    UserBalances userBalances = userBalancesMapper.find(debit.getUserId(), originalTransaction.getTeamId());
//                    userBalancesMapper.addSalesTax(userBalances.getId(), charge.getAmount());
//                    transactionChargeMapper.markPaid(charge.getId());
//                    break;
//                case E_WALLET:
//                    session.getMapper(EWalletMapper.class).forceSubtractBalance(charge.getUserId(), charge.getAmount());
//                    transactionChargeMapper.markPaid(charge.getId());
//                    break;
//            }
//        }
//
//        session.commit();
//        return new CommonResponse(true, 1, "Payout marked as returned");
    }
}