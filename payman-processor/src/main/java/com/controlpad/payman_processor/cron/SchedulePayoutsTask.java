package com.controlpad.payman_processor.cron;


import com.controlpad.payman_common.team.PayoutScheme;
import com.controlpad.payman_common.team.Team;
import com.controlpad.payman_common.team.TeamMapper;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.Locale;

public class SchedulePayoutsTask implements Runnable {

    private static final Logger logger = LoggerFactory.getLogger(SchedulePayoutsTask.class);

    private SqlSessionUtil sqlSessionUtil;
    private String clientId;
    private String teamId;

    public SchedulePayoutsTask (SqlSessionUtil sqlSessionUtil, String clientId, String teamId) {
        this.sqlSessionUtil = sqlSessionUtil;
        this.clientId = clientId;
        this.teamId = teamId;
    }

    @Override
    public void run() {
        DateTime now = DateTime.now();
        try (SqlSession clientSession = sqlSessionUtil.openSession(clientId, false)) {
            Team team = clientSession.getMapper(TeamMapper.class).findById(teamId);
            PayoutScheme payoutScheme = PayoutScheme.findBySlug(team.getConfig().getPayoutScheme());
            switch (payoutScheme) {
                case AUTO_SCHEDULE:
                case AUTO_SCHEDULE_DAILY_WITHDRAW:
                    scheduleJob(clientSession, now, payoutScheme);
                    break;
                default:
                    logger.error(String.format(Locale.US, "Wrong payout scheme(%s) for client(%s)", payoutScheme.getSlug(), clientId));
            }
        }
    }

    private void scheduleJob(SqlSession clientSession, DateTime now, PayoutScheme payoutScheme) {
        PayoutJob payoutJob = new PayoutJob(now.toString("YYYY-MM-dd HH:mm:ss"), "inactive", teamId, payoutScheme.getSlug());
        clientSession.getMapper(PayoutJobMapper.class).insert(payoutJob);
        clientSession.commit();
    }

    public String getClientId() {
        return clientId;
    }

    public String getTeamId() {
        return teamId;
    }
}
