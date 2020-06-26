package com.controlpad.pay_fac.fee;

import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.TeamFeeSet;
import com.controlpad.payman_common.fee.TeamFeeSetMap;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

class ClientFeeMaps {

    private static final Logger logger = LoggerFactory.getLogger(ClientFeeMaps.class);

    private String clientId;
    private Map<Long, Fee> feeMap;
    private TeamFeeSetMap teamFeeSetMap;

    ClientFeeMaps(String clientId, Map<Long, Fee> feeMap, TeamFeeSetMap teamFeeSetMap) {
        this.clientId = clientId;
        this.feeMap = feeMap;
        this.teamFeeSetMap = teamFeeSetMap;
    }

    TeamFeeSet getTeamFeeSet(Long teamId, String transactionType) {
        if (teamFeeSetMap != null && teamFeeSetMap.containsKey(teamId)) {
            return teamFeeSetMap.get(teamId).get(transactionType);
        }
        return null;
    }

    List<Fee> getFeesForSet(String teamId, String transactionType) {
        List<Fee> fees = new ArrayList<>();
        if (teamFeeSetMap != null && teamFeeSetMap.containsKey(teamId) && teamFeeSetMap.get(teamId).containsKey(transactionType)) {
            for (Long feeId : teamFeeSetMap.get(teamId).get(transactionType).getFeeIds()) {
                if (feeMap.containsKey(feeId)) {
                    fees.add(feeMap.get(feeId));
                } else {
                    MDC.put("teamId", teamId);
                    MDC.put("transactionType", transactionType);
                    logger.error("Fee map didn't contain feeset for type and team");
                    MDC.remove("teamId");
                    MDC.remove("transactionType");
                }
            }
        }
        return fees;
    }
}
