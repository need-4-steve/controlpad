package com.controlpad.payman_processor.transaction_processing;

public class TransactionUpdateResult {
    public static final int STOP = 0;
    public static final int UPDATED = 1;
    public static final int BATCH_CREATED = 2;
    public static final int SKIP = 4;
    public static final int ERROR = 8;
}
