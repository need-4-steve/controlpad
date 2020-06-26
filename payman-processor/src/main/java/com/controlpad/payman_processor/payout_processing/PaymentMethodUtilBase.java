package com.controlpad.payman_processor.payout_processing;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.entry.Entry;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.transaction_charge.TransactionCharge;
import com.controlpad.payman_common.user_balances.UserBalances;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.ibatis.session.SqlSession;

import java.io.Closeable;
import java.util.List;

public abstract class PaymentMethodUtilBase implements Closeable {

    private SqlSession clientSession;
    private IDUtil idUtil;
    private ControlPadClient client;
    private Team team;

    public PaymentMethodUtilBase(SqlSession clientSession, IDUtil idUtil, ControlPadClient client, Team team) {
        this.clientSession = clientSession;
        this.idUtil = idUtil;
        this.client = client;
        this.team = team;
    }

    protected SqlSession getClientSession() {
        return clientSession;
    }

    public IDUtil getIdUtil() {
        return idUtil;
    }

    public ControlPadClient getClient() {
        return client;
    }

    public Team getTeam() {
        return team;
    }

    abstract void processWithdraws(UserBalances userBalance, List<Entry> withdraws);
    abstract void processTaxes(List<Entry> taxes, UserBalances userBalance);
    abstract void processTaxCharges(List<TransactionCharge> taxCharges, UserBalances userBalance);
    abstract void processConsignment(List<Entry> consignments, UserBalances userBalance);
    abstract void processFees(Long feeId, List<Entry> fees, UserBalances userBalance);
    void onComplete() throws Exception  {} // Success only
    abstract boolean isPaymentsCreated();
}
