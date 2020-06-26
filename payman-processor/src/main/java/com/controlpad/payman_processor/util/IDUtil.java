package com.controlpad.payman_processor.util;

import org.apache.commons.lang3.StringUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;

import java.math.BigInteger;
import java.security.SecureRandom;
import java.util.HashSet;
import java.util.Set;


@Component
public class IDUtil {

    private static final Logger logger = LoggerFactory.getLogger(IDUtil.class);

    private SecureRandom random;
    private Set<Long> uniqueFilter = new HashSet<>(); // Filter duplicate randoms in the same millisecond
    private long currentTimestamp = 0; // Track the current millis for clearing uniqueFilter and not recreating time prefix
    private String currentTimeString; // Beginning of the id per millis

    public IDUtil() {
        try {
            random = SecureRandom.getInstance("SHA1PRNG");
        } catch (Exception e) {
            logger.error("Couldn't get secure random.", e);
        }
    }

    // generate and id consisting of a prefix character, base 36 timestamp of 9 characters, and 5 random characters
    public synchronized String generateId() {
        return tryGenerate();
    }

    private String tryGenerate() {
        // Try 10 times before fail, seems to work fine testing against high load
        // generated 10mil ids in 11 seconds with 4 conflicts, Retry of 10 is generous
        // Don't want to block for too long when other connections are waiting on an id
        for(int i = 0; i < 10; i++) {
            // Always check to see if time string changed, this can help push into a new set of randoms if we are getting conflicts
            String timeString = createTimeString();

            Long randomLong = (long)(random.nextDouble() * 2176782335L);
            // Check random against filter to help prevent duplicate id's
            if (uniqueFilter.contains(randomLong)) {
                continue;
            }
            uniqueFilter.add(randomLong);

            // Id consists of prefix + timestring + random
            return String.format("%s%s",
                    timeString,
                    StringUtils.leftPad(BigInteger.valueOf(randomLong).toString(36), 6, "0"));
        }

        logger.error("Failed to generate unique id after 10 tries");
        throw new RuntimeException("Failed to generate id");
    }

    private String createTimeString() {
        long timeMillis = System.currentTimeMillis();
        if (currentTimestamp != timeMillis) {
            currentTimestamp = timeMillis;
            uniqueFilter.clear();
            currentTimeString = StringUtils.leftPad(
                    BigInteger.valueOf(System.currentTimeMillis()).toString(36),
                    9,
                    "0");
        }
        return currentTimeString;
    }
}
