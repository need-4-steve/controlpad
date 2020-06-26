package com.controlpad.pay_fac.integrity;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

import java.lang.management.ManagementFactory;
import java.lang.management.RuntimeMXBean;
import java.util.Locale;

@Component
public class ResourceSentry {

    private static final Logger logger = LoggerFactory.getLogger(ResourceSentry.class);

    private volatile long memory = 0;
    private volatile long maxMemory = 0;
    private volatile long totalMemory = 0;
    private volatile long uptime = 0;

    @Scheduled(fixedDelay = 30000L, initialDelay = 0L) // Every 30 seconds
    public void checkResources() {
        checkMemory();
    }

    @Scheduled(fixedDelay = 1000L)
    public void checkUptime() {
        RuntimeMXBean rb = ManagementFactory.getRuntimeMXBean();
        uptime = rb.getUptime();
    }

    private static final long MEGABYTE = 1024L * 1024L;

    private static long bytesToMegabytes(long bytes) {
        return bytes / MEGABYTE;
    }

    private void checkMemory() {
        // Get the Java runtime
        Runtime runtime = Runtime.getRuntime();
        // Calculate the used memory
        memory = bytesToMegabytes(runtime.totalMemory() - runtime.freeMemory());
        maxMemory = bytesToMegabytes(runtime.maxMemory());
        totalMemory = bytesToMegabytes(runtime.totalMemory());

        if (memory > (maxMemory * .7)) {
            // over 70% of max memory is being used
            logger.error(String.format(Locale.US, "Memory is running high on payman: %s/%s", totalMemory, maxMemory));
        }
    }

    public long getMemory() {
        return memory;
    }

    public long getMaxMemory() {
        return maxMemory;
    }

    public long getTotalMemory() {
        return totalMemory;
    }

    public long getUptime() {
        return uptime;
    }
}