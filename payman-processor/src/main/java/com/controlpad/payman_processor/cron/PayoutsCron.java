package com.controlpad.payman_processor.cron;


import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_common.transaction_batch.TransactionBatchMapper;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.scheduling.concurrent.ThreadPoolTaskScheduler;
import org.springframework.scheduling.support.CronTrigger;
import org.springframework.stereotype.Component;

import javax.annotation.PostConstruct;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ScheduledFuture;

@Component
public class PayoutsCron {

    private static final Logger logger = LoggerFactory.getLogger(PayoutsCron.class);

    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    ThreadPoolTaskScheduler payoutScheduler;

    // Track all futures for the use of detecting schedule/scheme changes and canceling them
    private Map<String, Map<String, ScheduleHolder>> scheduledMap = new ConcurrentHashMap<>();

    public Map<String, Map<String, ScheduleHolder>> getScheduledMap() {
        return scheduledMap;
    }


    private boolean jobsReset = false;

    @PostConstruct
    private void init() {
        /*
         * When server starts up it should reset any jobs that were queued or processing.
         * This must run before crons starts scheduling jobs
         */
        resetPayoutJobs();
    }

    private void resetPayoutJobs() {
        clientConfigUtil.getClientMap().forEach((k, v) -> {
            try (SqlSession clientSession = sqlSessionUtil.openSession(v.getId(), true)) { // Using resource block for auto close
                clientSession.getMapper(PayoutJobMapper.class).resetQueued();
                clientSession.getMapper(PayoutJobMapper.class).resetProcessing();
            } catch (Exception e) {
                logger.error("Failed to reset payout jobs for " + v.getName(), e);
            }
        });
        jobsReset = true;
    }

    @Scheduled(fixedDelay = 900000L)
    public void runScheduledCron(){
        if (!jobsReset) {
            logger.error("runScheduledCron() called before jobs were reset");
            return;
        }
        clientConfigUtil.getClientMap().forEach((k, v) -> checkClient(k));
    }

    private void checkClient(String clientId) {
        try(SqlSession clientSession = sqlSessionUtil.openSession(clientId, false)) { // Using resource block for auto close
            List<Team> teams = clientSession.getMapper(TeamMapper.class).list();
            teams.forEach(team -> {
                if (team.getConfig() == null || team.getConfig().getPayoutScheme() == null || team.getPayoutSchedule() == null) {
                    return;
                }
                PayoutScheme payoutScheme = PayoutScheme.findBySlug(team.getConfig().getPayoutScheme());
                switch (payoutScheme) {
                    case AUTO_SCHEDULE:
                        updateSchedule(team, clientSession, clientId, false);
                        break;
                    case AUTO_SCHEDULE_DAILY_WITHDRAW:
                        updateSchedule(team, clientSession, clientId, true);
                        break;
                    default:
                        clearTeamSchedule(clientId, team.getId());
                        break;
                }
            });
        } catch (Exception e) {
            logger.error("Failed to schedule payout for client: " + clientId, e);
        }
    }

    // SCHEDULE SCHEMES
    private void updateSchedule(Team team, SqlSession clientSession, String clientId, boolean daily) {
        // TODO check most recent job and see if we have missed current schedule for today
        // TODO else if last day is missed and closer than next scheduled day
        // TODO update test for this case
        ScheduleHolder teamScheduleHolder = null;
        if (scheduledMap.containsKey(clientId) && scheduledMap.get(clientId).containsKey(team.getId())) {
            teamScheduleHolder = scheduledMap.get(clientId).get(team.getId());
        }
        String cronString;
        if (daily) {
            cronString = team.getPayoutSchedule().getDailyCron();
        } else {
            cronString = team.getPayoutSchedule().getCron();
        }
        if (teamScheduleHolder != null) {
            if (!StringUtils.equals(teamScheduleHolder.cronTrigger.getExpression(), cronString)) {
                teamScheduleHolder.future.cancel(false);
            } else {
                // No change required
                return;
            }
        }
        postNewSchedule(clientId, team.getId(), cronString);
    }

    public class ScheduleHolder {

        private CronTrigger cronTrigger;
        private SchedulePayoutsTask task;
        private ScheduledFuture future;

        ScheduleHolder(String clientId, String teamId, String cronString) {
            cronTrigger = new CronTrigger(cronString);
            task = new SchedulePayoutsTask(sqlSessionUtil, clientId, teamId);
        }

        public CronTrigger getCronTrigger() {
            return cronTrigger;
        }

        public SchedulePayoutsTask getTask() {
            return task;
        }

        public ScheduledFuture getFuture() {
            return future;
        }
    }

    private void postNewSchedule(String clientId, String teamId, String cronString) {
        ScheduleHolder holder = new ScheduleHolder(clientId, teamId, cronString);
        if (!scheduledMap.containsKey(clientId)) {
            scheduledMap.put(clientId, new ConcurrentHashMap<>());
        }
        scheduledMap.get(clientId).put(teamId, holder);
        holder.future = payoutScheduler.schedule(holder.task, holder.cronTrigger);
    }

    private void clearTeamSchedule(String clientId, String teamId) {
        if (scheduledMap.containsKey(clientId) && scheduledMap.get(clientId).containsKey(teamId)) {
            ScheduleHolder holder = scheduledMap.get(clientId).remove(teamId);
            holder.future.cancel(false);
        }
    }

    // Used for testing
    public SchedulePayoutsTask createTask(String clientId, String teamId) {
        return new SchedulePayoutsTask(sqlSessionUtil, clientId, teamId);
    }
}
