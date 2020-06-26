package com.controlpad.payman_processor.payout_processing;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.entry.EntryMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.payment.Payment;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment.PaymentType;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.transaction_charge.TransactionChargeMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_common.user_balances.UserBalancesMapper;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;

import java.io.IOException;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class SubAccountPayoutMethodUtil extends PaymentMethodUtilBase {

    DateTimeFormatter formatter = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss");

    private GatewayUtil gatewayUtil;

    private EntryMapper entryMapper;
    private GatewayConnectionMapper gatewayConnectionMapper;
    private PaymentMapper paymentMapper;
    private TransactionChargeMapper transactionChargeMapper;
    private UserBalancesMapper userBalancesMapper;

    private Map<String, GatewayConnection> userGatewayConnectionMap;
    private Map<Long, GatewayConnection> masterConnectionMap;
    private Map<String, UserAccount> userAccountMap;

    private boolean isPaymentCreated = false;

    public SubAccountPayoutMethodUtil(SqlSession clientSession, IDUtil idUtil, GatewayUtil gatewayUtil,
                                      ControlPadClient client, Team team, Map<String, UserAccount> userAccountMap) {
        super(clientSession, idUtil, client, team);
        this.gatewayUtil = gatewayUtil;
        this.userAccountMap = userAccountMap;
        this.entryMapper = getClientSession().getMapper(EntryMapper.class);
        this.gatewayConnectionMapper = getClientSession().getMapper(GatewayConnectionMapper.class);
        this.paymentMapper = getClientSession().getMapper(PaymentMapper.class);
        this.transactionChargeMapper = getClientSession().getMapper(TransactionChargeMapper.class);
        this.userBalancesMapper = getClientSession().getMapper(UserBalancesMapper.class);
        this.userGatewayConnectionMap = new HashMap<>();
        this.masterConnectionMap = new HashMap<>();
    }

    @Override
    void processWithdraws(UserBalances userBalance, List<Entry> withdraws) {
        if (withdraws == null || withdraws.isEmpty()) {
            return;
        }
        if (!isUserAccountValid(userBalance.getUserId())) {
            // Don't batch for invalid accounts
            return;
        }
        Payment userWithdrawPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), userBalance.getUserId(),
                null, BigDecimal.ZERO, null, null, PaymentType.WITHDRAW.slug);
        List<Long> resultIds = batchEntriesToPayment(userWithdrawPayment, withdraws);
        payoutFromGatewayConnection(getGatewayConnection(userBalance.getUserId()), userWithdrawPayment);
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
        if (taxes == null || taxes.isEmpty()) {
            return;
        }
        Payment taxPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), userBalance.getUserId(),
                null, new Money(0), null, null, PaymentType.SALES_TAX.slug);
        List<Long> resultIds = batchEntriesToPayment(taxPayment, taxes);
        chargeTaxFromGatewayConnection(getGatewayConnection(userBalance.getUserId()), taxPayment);
        paymentMapper.insert(taxPayment);
        entryMapper.setProcessedAndBatchIdForList(resultIds, taxPayment.getId());
        userBalancesMapper.addTransaction(userBalance.getId(), taxPayment.getAmount().negate());
        getClientSession().commit();
        if (!isPaymentCreated) {
            isPaymentCreated = true;
        }
    }

    @Override
    void processTaxCharges(List<TransactionCharge> taxCharges, UserBalances userBalance) {
        if (taxCharges == null || taxCharges.isEmpty()) {
            return;
        }
        // This isn't actually expected to be used, tax payments should go through company team, which won't be on a sub account
        Payment cashTaxPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), userBalance.getUserId(),
                null, new Money(0), null, null, PaymentType.SALES_TAX.slug);
        List<Long> taxChargeIds = new ArrayList<>();
        for (TransactionCharge taxCharge : taxCharges) {
            cashTaxPayment.addAmount(taxCharge.getAmount());
            // Add withdraw to payment/entry map for updating payment id later
            taxChargeIds.add(taxCharge.getId());
        }
        chargeTaxFromGatewayConnection(getGatewayConnection(userBalance.getUserId()), cashTaxPayment);
        paymentMapper.insert(cashTaxPayment);
        transactionChargeMapper.setPaidForList(cashTaxPayment.getId(), taxChargeIds);
        userBalancesMapper.addSalesTax(userBalance.getId(), cashTaxPayment.getAmount().negate());
        getClientSession().commit();
        if (!isPaymentCreated) {
            isPaymentCreated = true;
        }
    }

    @Override
    void processConsignment(List<Entry> consignments, UserBalances userBalance) {
        if (consignments == null || consignments.isEmpty()) {
            return;
        }
        Payment consignmentPayment = new Payment(getIdUtil().generateId(), getTeam().getId(), userBalance.getUserId(),
                null, new Money(0), null, null, PaymentType.CONSIGNMENT.slug);
        List<Long> resultIds = batchEntriesToPayment(consignmentPayment, consignments);
        chargeConsignmentFromGatewayConnection(getGatewayConnection(userBalance.getUserId()), consignmentPayment);
        paymentMapper.insert(consignmentPayment);
        entryMapper.setProcessedAndBatchIdForList(resultIds, consignmentPayment.getId());
        userBalancesMapper.addTransaction(userBalance.getId(), consignmentPayment.getAmount().negate());
        getClientSession().commit();
        if (!isPaymentCreated) {
            isPaymentCreated = true;
        }
    }

    @Override
    void processFees(Long feeId, List<Entry> fees, UserBalances userBalance) {
        // Not supported yet
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

    private GatewayConnection getGatewayConnection(String userId) {
        // For now only using currently active connection
        return userGatewayConnectionMap.computeIfAbsent(userId, k -> {
            List<GatewayConnection> gatewayConnections = gatewayConnectionMapper.search(getTeam().getId(), userId, null, null, true, null, true, 1, 0L);
            if (gatewayConnections.isEmpty()) {
                // Log? info
                System.out.println("Skipping user no gateway");
                return null;
            }
            GatewayConnection gatewayConnection = gatewayConnections.get(0);
            gatewayConnection.setMasterConnection(masterConnectionMap.computeIfAbsent(gatewayConnection.getMasterConnectionId(),
                    id -> gatewayConnectionMapper.findById(gatewayConnection.getMasterConnectionId())));
            return gatewayConnection;
        });
    }

    private void chargeTaxFromGatewayConnection(GatewayConnection gatewayConnection, Payment taxPayment) {
        String feeId = gatewayUtil.getGatewayApi(gatewayConnection).createTaxFee(gatewayConnection, taxPayment);
        if (feeId != null) {
            taxPayment.setReferenceId(feeId);
            taxPayment.setPaidAt(DateTime.now().toString(formatter));
        }
    }

    private void chargeConsignmentFromGatewayConnection(GatewayConnection gatewayConnection, Payment consignmentPayment) {
        String feeId = gatewayUtil.getGatewayApi(gatewayConnection).createConsignmentFee(gatewayConnection, consignmentPayment);
        if (feeId != null) {
            consignmentPayment.setReferenceId(feeId);
            consignmentPayment.setPaidAt(DateTime.now().toString(formatter));
        }
    }

    private void payoutFromGatewayConnection(GatewayConnection gatewayConnection, Payment payment) {
        String withdrawId = gatewayUtil.getGatewayApi(gatewayConnection).createWithdraw(gatewayConnection, payment);
        if (withdrawId != null) {
            payment.setReferenceId(withdrawId);
            payment.setPaidAt(DateTime.now().toString(formatter));
        }
    }

    private boolean isUserAccountValid(String userId) {
        return !BooleanUtils.isTrue(getClient().getConfig().getFeatures().getAccountValidation()) ||
                userAccountMap.containsKey(userId) && userAccountMap.get(userId).getValidated();
    }

    @Override
    boolean isPaymentsCreated() {
        return isPaymentCreated;
    }

    @Override
    public void close() throws IOException {
        // Nothing to close
    }
}
