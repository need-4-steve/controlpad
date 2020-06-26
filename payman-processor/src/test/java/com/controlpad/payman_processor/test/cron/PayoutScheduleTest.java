package com.controlpad.payman_processor.test.cron;


import com.controlpad.payman_common.gateway_connection.GatewayConnection;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionMapper;
import com.controlpad.payman_common.gateway_connection.GatewayConnectionType;
import com.controlpad.payman_common.team.*;
import com.controlpad.payman_common.transaction_batch.TransactionBatch;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_processor.CronTest;
import com.controlpad.payman_processor.cron.PayoutsCron;
import com.controlpad.payman_processor.cron.SchedulePayoutsTask;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_processor.test.TestUtil;
import org.joda.time.DateTime;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.concurrent.ScheduledFuture;

public class PayoutScheduleTest extends CronTest {

    @Autowired
    PayoutsCron payoutsCron;
    @Autowired
    TestUtil testUtil;

    private Team team;
    private String clientId;
    private List<TransactionBatch> batches;

    @Test
    public void testChain() {
        createDummyData();
        payoutScheduleCronTest();
        schedulePayoutsTaskTest();
    }

    public void payoutScheduleCronTest() {
        TeamMapper teamMapper = getClientSqlSession().getMapper(TeamMapper.class);
        clientId = testUtil.getMockData().getTestClient().getId();
        payoutsCron.runScheduledCron();
        // Make sure only this team is scheduled
        assert payoutsCron.getScheduledMap().get(clientId).size() == 1;
        PayoutsCron.ScheduleHolder holder = payoutsCron.getScheduledMap().get(clientId).get(team.getId());
        assert holder.getCronTrigger().getExpression().equals(team.getPayoutSchedule().getCron());
        assert holder.getTask().getClientId().equals(clientId);
        assert holder.getTask().getTeamId().equals(team.getId());
        assert !holder.getFuture().isCancelled();
        ScheduledFuture oldFuture = holder.getFuture();

        // Change schedule
        team.getPayoutSchedule().setDays(Collections.singletonList(DateTime.now().plusDays(2).getDayOfWeek()));
        teamMapper.updatePayoutSchedule(team.getPayoutSchedule(), team.getId());
        getClientSqlSession().commit();
        payoutsCron.runScheduledCron();
        // Make sure only this team is scheduled
        assert payoutsCron.getScheduledMap().get(clientId).size() == 1;
        holder = payoutsCron.getScheduledMap().get(clientId).get(team.getId());
        assert oldFuture != holder.getFuture();
        assert holder.getCronTrigger().getExpression().equals(team.getPayoutSchedule().getCron());
        assert oldFuture.isCancelled();

        oldFuture = holder.getFuture();

        teamMapper.updatePayoutSchedule(null, team.getId());
        team.getConfig().setPayoutScheme("none");
        teamMapper.updateTeamConfig(team);
        getClientSqlSession().commit();
        payoutsCron.runScheduledCron();
        assert payoutsCron.getScheduledMap().get(clientId).size() == 0;
        assert oldFuture.isCancelled();
    }

    private void createDummyData() {
        PayoutSchedule payoutSchedule = new PayoutSchedule(Collections.singletonList(DateTime.now().plusDays(3).getDayOfWeek()),
                "week", 20, 3, 15);

        team = new Team("scheduled-team", "Payout Schedule Team", new TeamConfig(false, false, false, false, false, null, PayoutScheme.AUTO_SCHEDULE.getSlug()));
        team.setPayoutSchedule(payoutSchedule);

        getClientSqlSession().getMapper(TeamMapper.class).insert(team);

        GatewayConnection connection = new GatewayConnection(team.getId(), null,
                "Scheduled Team Gateway", null, "Private Key", null, null, GatewayConnectionType.MOCK.slug, true, true,
                false, false, false, true);
        getClientSqlSession().getMapper(GatewayConnectionMapper.class).insert(connection);
        TransactionBatchMapper transactionBatchMapper = getClientSqlSession().getMapper(TransactionBatchMapper.class);
        batches = new ArrayList<>(3);
        batches.add(new TransactionBatch(connection.getId(), null, null, 3, null, null));
        batches.add(new TransactionBatch(connection.getId(), null, null, 1, null, null));
        batches.add(new TransactionBatch(connection.getId(), null, null, 0, null, null));
        batches.forEach(transactionBatchMapper::insert);

        getClientSqlSession().commit();
    }

    public void schedulePayoutsTaskTest() {
        team.getConfig().setPayoutScheme(PayoutScheme.AUTO_SCHEDULE.getSlug());
        team.getPayoutSchedule().setDaysBuffer(-1);
        team.getPayoutSchedule().setBufferHourOfDay(0);
        getClientSqlSession().getMapper(TeamMapper.class).updateTeamConfig(team);
        getClientSqlSession().getMapper(TeamMapper.class).updatePayoutSchedule(team.getPayoutSchedule(), team.getId());
        getClientSqlSession().commit();

        SchedulePayoutsTask task = payoutsCron.createTask(clientId, team.getId());
        task.run();


        List<PayoutJob> payoutJobs = getClientSqlSession().getMapper(PayoutJobMapper.class).listInactiveForTeam(team.getId());
        assert payoutJobs.size() == 1;
    }

}