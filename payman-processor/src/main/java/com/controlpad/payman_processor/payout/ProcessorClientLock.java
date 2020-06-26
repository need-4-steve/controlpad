package com.controlpad.payman_processor.payout;

import org.springframework.stereotype.Component;

import java.util.HashMap;
import java.util.Map;


@Component
public class ProcessorClientLock {

    private Map<String, Object> clientLocks = new HashMap<>();

    public synchronized Object getClientLock(String clientId) {
        if (!clientLocks.containsKey(clientId)) {
            clientLocks.put(clientId, new Object());
        }
        return clientLocks.get(clientId);
    }

}
