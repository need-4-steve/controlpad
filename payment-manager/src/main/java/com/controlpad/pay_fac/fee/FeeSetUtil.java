package com.controlpad.pay_fac.fee;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.payman_common.fee.Fee;
import com.controlpad.payman_common.fee.FeeMapper;
import com.controlpad.payman_common.transaction.TransactionType;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

@Component
public class FeeSetUtil {

    private static final Logger logger = LoggerFactory.getLogger(FeeSetUtil.class);

    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    ClientConfigUtil clientConfigUtil;

    private Map<String, ClientFeeMaps> clientFeeMapsMap = new ConcurrentHashMap<>();

    @Scheduled(initialDelay = 0, fixedDelay = 900000L) //15 minute interval
    public void scheduledRefreshFeeMaps(){
        clientConfigUtil.getClientMap().forEach((key, client) -> {
            try (SqlSession session = sqlSessionUtil.openSession(key, true)) {
                FeeMapper feeMapper = session.getMapper(FeeMapper.class);
                clientFeeMapsMap.put(key, new ClientFeeMaps(key,
                        feeMapper.mapAllFees(),
                        feeMapper.mapTeamFeeSets()));
            } catch (Exception e) {
                MDC.put("client", client.getName());
                MDC.put("clientId", client.getId());
                logger.error("FeeSetUtil failed to cache fees", e);
                MDC.clear();
            }
        });
        //TODO test that this is okay
        clientFeeMapsMap.forEach((key, map) -> {
            if (!clientConfigUtil.getClientMap().containsKey(key)) {
                clientFeeMapsMap.remove(key);
            }
        });
    }

    public List<Fee> getFeesForSet(String clientId, String teamId, String transactionType) {
        if (clientFeeMapsMap.containsKey(clientId)) {
            return clientFeeMapsMap.get(clientId).getFeesForSet(teamId, transactionType);
        }
        return new ArrayList<>();
    }

    public BigDecimal getFeeTotalForTransaction(String clientId, String teamId, BigDecimal amount, TransactionType type) {
        BigDecimal total = BigDecimal.ZERO;
        if (clientFeeMapsMap.containsKey(clientId)) {
            List<Fee> fees = clientFeeMapsMap.get(clientId).getFeesForSet(teamId, type.slug);
            for (Fee fee : fees) {
                total = total.add(fee.calculateChargeAmount(amount));
            }
        }
        return total;
    }
}