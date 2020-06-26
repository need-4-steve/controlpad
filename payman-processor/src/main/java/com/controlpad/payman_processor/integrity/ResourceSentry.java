package com.controlpad.payman_processor.integrity;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.MDC;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.lang.management.ManagementFactory;
import java.lang.management.RuntimeMXBean;
import java.util.Locale;

@Component
public class ResourceSentry {

    private static final Logger logger = LoggerFactory.getLogger(ResourceSentry.class);

    @Scheduled(fixedDelay = 60000L) // every minute
    public void checkResources() {
        checkMemeory();
    }

    private static final long MEGABYTE = 1024L * 1024L;

    private static long bytesToMegabytes(long bytes) {
        return bytes / MEGABYTE;
    }

    private void checkMemeory() {
        // Get the Java runtime
        Runtime runtime = Runtime.getRuntime();
        // Calculate the used memory
        long memory = bytesToMegabytes(runtime.totalMemory() - runtime.freeMemory());
        long maxMemory = bytesToMegabytes(runtime.maxMemory());
        long totalMemory = bytesToMegabytes(runtime.totalMemory());

        RuntimeMXBean rb = ManagementFactory.getRuntimeMXBean();
        long uptime = rb.getUptime();

        if (memory > (maxMemory * .7)) {
            // over 70% of max memory is being used
            MDC.put("totalMemory", String.valueOf(totalMemory));
            MDC.put("maxMemory", String.valueOf(maxMemory));
            MDC.put("usedMemory", String.valueOf(memory));
            MDC.put("uptime", String.valueOf(uptime));
            logger.error("Memory is running high on payman processor");
            MDC.remove("totalMemory");
            MDC.remove("maxMemory");
            MDC.remove("usedMemory");
            MDC.remove("uptime");
        }
    }

}