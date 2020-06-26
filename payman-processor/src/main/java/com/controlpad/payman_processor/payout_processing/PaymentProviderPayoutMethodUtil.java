package com.controlpad.payman_processor.payout_processing;


import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.merchant.Merchant;
import com.controlpad.payman_common.merchant.MerchantMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.payment_provider.PaymentProvider;
import com.controlpad.payman_common.payment_provider.PaymentProviderMapper;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_processor.payment_provider.MockProvider;
import com.controlpad.payman_processor.payment_provider.PayQuicker;
import com.controlpad.payman_processor.payment_provider.PaymentProviderInterface;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;

import java.io.IOException;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

public class PaymentProviderPayoutMethodUtil extends PaymentMethodUtilBase {

    private static final Logger logger = LoggerFactory.getLogger(PaymentProviderPayoutMethodUtil.class);

    DateTimeFormatter formatter = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss");

    private EntryMapper entryMapper;
    private MerchantMapper merchantMapper;
    private PaymentMapper paymentMapper;
    private UserBalancesMapper userBalancesMapper;

    private PaymentProvider paymentProvider;
    private PaymentProviderInterface paymentProviderInterface;

    private boolean isPaymentCreated = false;

    PaymentProviderPayoutMethodUtil(SqlSession clientSession, IDUtil idUtil, ControlPadClient client, Team team) {
        super(clientSession, idUtil, client, team);
        this.entryMapper = getClientSession().getMapper(EntryMapper.class);
        this.merchantMapper = getClientSession().getMapper(MerchantMapper.class);
        this.paymentMapper = getClientSession().getMapper(PaymentMapper.class);
        this.userBalancesMapper = getClientSession().getMapper(UserBalancesMapper.class);

        this.paymentProvider = getClientSession().getMapper(PaymentProviderMapper.class).findById(team.getPaymentProviderId());
        if (this.paymentProvider == null) {
            throw new RuntimeException("PaymentProvider not found for team: " + team.getId());
        }
        switch (paymentProvider.getType()) {
            case "mock":
                this.paymentProviderInterface = new MockProvider();
                break;
            case "payquicker":
                this.paymentProviderInterface = new PayQuicker();
                break;
            default:
                MDC.put("team", team.getId());
                MDC.put("paymentProvider", paymentProvider.getType());
                logger.error("PaymentProvider type unsupported");
                MDC.remove("team");
                MDC.remove("paymentProvider");
        }
    }

    @Override
    public void close() throws IOException {
        // Nothing to close
    }

    @Override
    void processWithdraws(UserBalances userBalance, List<Entry> withdraws) {
        if (withdraws == null || withdraws.isEmpty()) {
            return;
        }
        Payment userWithdrawPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), userBalance.getUserId(),
                null, BigDecimal.ZERO,null, null, PaymentType.WITHDRAW.slug);
        List<Long> resultIds = batchEntriesToPayment(userWithdrawPayment, withdraws);
        Merchant merchant = merchantMapper.findById(userBalance.getUserId());
        userWithdrawPayment = paymentProviderInterface.createPayment(paymentProvider, userWithdrawPayment, merchant);
        if (userWithdrawPayment == null) {
            // Payment failed, should be logged inside the interface
            return;
        }
        userWithdrawPayment.setPaidAt(DateTime.now().toString(formatter));
        paymentMapper.insert(userWithdrawPayment);
        entryMapper.setProcessedAndBatchIdForList(resultIds, userWithdrawPayment.getId());
        userBalancesMapper.addTransaction(userBalance.getId(), userWithdrawPayment.getAmount().negate());
        getClientSession().commit();
        if (!isPaymentCreated) {
            isPaymentCreated = true;
        }
    }

    @Override
    void processTaxes(List<Entry> taxes, UserBalances userBalance) {
        throw new RuntimeException("Not moving company money via payment providers at this time");
    }

    @Override
    void processTaxCharges(List<TransactionCharge> taxCharges, UserBalances userBalance) {
        throw new RuntimeException("Not moving company money via payment providers at this time");
    }

    @Override
    void processConsignment(List<Entry> consignments, UserBalances userBalance) {
        throw new RuntimeException("Not moving company money via payment providers at this time");
    }

    @Override
    void processFees(Long feeId, List<Entry> fees, UserBalances userBalance) {
        throw new RuntimeException("Not moving company money via payment providers at this time");
    }

    @Override
    boolean isPaymentsCreated() {
        return isPaymentCreated;
    }

    private List<Long> batchEntriesToPayment(Payment payment, List<Entry> entries) {
        List<Long> entryIds = new ArrayList<>();
        for (Entry entry : entries) {
            payment.addAmount(entry.getAmount());
            // Add withdraw to payment/entry map for updating payment id later
            entryIds.add(entry.getId());
        }
        return entryIds;
    }
}
