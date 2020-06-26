package com.controlpad.payman_processor.cron;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.gateway.GatewayUtil;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_processor.payout_processing.PaymentBatchProcessingTask;
import com.controlpad.payman_processor.payout_processing.PayoutProcessingTask;
import com.controlpad.payman_processor.util.IDUtil;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.scheduling.concurrent.ThreadPoolTaskScheduler;
import org.springframework.scheduling.support.CronTrigger;
import org.springframework.stereotype.Component;

import java.util.List;

@Component
public class PayoutJobSchedulerCron {

    private static final Logger logger = LoggerFactory.getLogger(PayoutJobSchedulerCron.class);

    @Autowired
    ThreadPoolTaskScheduler payoutScheduler;
    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    GatewayUtil gatewayUtil;
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    IDUtil idUtil;
    @Autowired
    UserAccountValidationCron userAccountValidationCron;

    DateTimeFormatter formatter = DateTimeFormat.forPattern("YYYY-MM-dd HH:mm:ss.S");

    @Scheduled(initialDelay = 45000, fixedDelay = 300000)
    private void scheduleJobs() {
        DateTime now = DateTime.now();
        clientConfigUtil.getClientMap().forEach((k, client) -> {
            try(SqlSession clientSession = sqlSessionUtil.openSession(client.getId(), true)) {
                PayoutJobMapper payoutJobMapper = clientSession.getMapper(PayoutJobMapper.class);
                List<Team> teams = clientSession.getMapper(TeamMapper.class).list();
                teams.forEach(team -> {
                    List<PayoutJob> payoutJobs = payoutJobMapper.listInactiveForTeam(team.getId());
                    payoutJobs.forEach(payoutJob -> {
                        int result = payoutJobMapper.markQueued(payoutJob.getId());
                        if (result > 0) {
                            logger.warn("startAt: " + payoutJob.getStartAt());
                            DateTime jobTime = formatter.parseDateTime(payoutJob.getStartAt());
                            if (now.plusMinutes(5).compareTo(jobTime) >= 0) {
                                payoutScheduler.submit(createTask(client, payoutJob));
                            } else {
                                payoutScheduler.schedule(createTask(client, payoutJob),
                                        new CronTrigger(jobTime.toString("ss mm HH dd MM ?")));
                            }
                        }
                    });
                });
            } catch (Exception e) {
                logger.error("scheduleJobs() error for Client: " + k, e);
            }
        });
    }

    private Runnable createTask(ControlPadClient client, PayoutJob payoutJob) {
        Runnable task;

        PayoutScheme payoutScheme = PayoutScheme.findBySlug(payoutJob.getPayoutScheme());
        switch (payoutScheme) {
            case BATCH_TO_PROVIDER:
                task = new PaymentBatchProcessingTask(sqlSessionUtil, client.getId(), payoutJob.getId());
                break;
            case USER_ACCOUNT_VALIDATION:
                return () -> userAccountValidationCron.payoutUserAccountValidations(client, payoutJob.getTeamId(), payoutJob);
            default:
                task = new PayoutProcessingTask(sqlSessionUtil, clientConfigUtil, gatewayUtil, idUtil,
                        client.getId(), payoutJob.getId());
        }
        return task;
    }
}
