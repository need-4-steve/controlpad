package com.controlpad.payman_processor.payout.file;


import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.account.AccountType;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.util.FileUtil;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.Closeable;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.math.BigDecimal;
import java.text.DecimalFormat;

public class PayoutFileWriter implements Closeable {
    private static final Logger logger = LoggerFactory.getLogger(PayoutFileWriter.class);
    public static final String PAYOUT_FILEPATH_BASE = File.separator + "payouts" + File.separator;

    private File file = null;
    private FileWriter fileWriter = null;

    private String keyName;
    private String clientId;

    private BigDecimal fileCredits = new Money(0D);  // File control record, sum of all credits for file
    private int batchCount = 0; // Number of batches. You only really need one batch because the transaction/batch type won't change
    private long fileHash = 0; // The sum of all batchHashes. If greater than 10 clip left side
    private long fileEntries = 0; // File control record info

    private DecimalFormat decimalFormat = new DecimalFormat("#0.00");

    private boolean isLocalStorage;


    public PayoutFileWriter(String fileName, String clientId, Long clientPosition, String clientName) throws IOException {
        this.clientId = clientId;
        File clientDir;
        String debugMode = System.getProperty("DEBUG");
        String localStorage = System.getProperty("LOCAL_STORAGE");
        if(Boolean.parseBoolean(localStorage)){
            //Program is running on dev
            logger.info("PayoutFileWriter: writing file in DEBUG mode...");
            isLocalStorage = true;
            String OS = System.getProperty("os.name");
            if(OS.startsWith("Windows")){
                clientDir = new File("C:" + PAYOUT_FILEPATH_BASE + clientId + File.separator);
            }else{
                clientDir = new File(System.getProperty("user.home", "") + PAYOUT_FILEPATH_BASE + clientId + File.separator);
            }
        }else{
            //Program is running on server, need to write on the server
            logger.info("PayoutFileWriter: writing file on AWS S3 server...");
            isLocalStorage = false;
            String homeDir = System.getProperty("user.home", "");
            logger.info("HomeDir: " + homeDir);
            clientDir = new File(homeDir + PAYOUT_FILEPATH_BASE + clientId + File.separator);
            keyName = FileUtil.generateKeyName(clientPosition, clientId, fileName);
        }

        if (!clientDir.exists() && !clientDir.mkdirs()) {
            //TODO notify error can't create client dir to save payout file
            logger.error(String.format("PayoutFileWriter: cannot create client dir to save payout file. File Name:{} Client ID:{}", fileName, clientId));
            throw new RuntimeException("Unable to create payouts directory");  // /payouts must be owned by the tomcat user
        }
        String filePath = clientDir.getAbsolutePath() + File.separator + fileName;
        logger.info("PayoutFileWriter: write file in: " + filePath);
        file = new File(filePath);
        fileWriter = new FileWriter(file);
    }

    public void writeBatchHeader(String classCode, String entryClassCode, String entryDesc) throws Exception {
        batchCount++;
        fileWriter.write('#');
        fileWriter.write('\t');
        fileWriter.write(classCode); // Service Code: Credits Only
        fileWriter.write('\t');
        fileWriter.write(entryClassCode);
        fileWriter.write('\t');
        fileWriter.write(entryDesc);
        fileWriter.write('\n');
    }

    public void writeEntry(Account account, BigDecimal amount, String paymentId) throws Exception {
        fileEntries++;
        fileCredits = fileCredits.add(amount);
        fileHash += (Integer.valueOf(account.getRouting().substring(0, 8), 10)); // Sum of the first 8 digits of the routing number

        String transactionCode;
        if (amount.compareTo(BigDecimal.ZERO) > 0) {
            transactionCode = String.valueOf(AccountType.getTypeForName(account.getType()).creditTransactionCode);
        } else {
            transactionCode = String.valueOf(AccountType.getTypeForName(account.getType()).debitTransactionCode);
        }
        fileWriter.write(transactionCode); // Transaction Code (Numeric)[2]
        fileWriter.write('\t');
        fileWriter.write(account.getRouting());
        fileWriter.write('\t');
        fileWriter.write(account.getNumber());
        fileWriter.write('\t');
        fileWriter.write(decimalFormat.format(amount));
        fileWriter.write('\t');
        fileWriter.write(paymentId); // Identification Number (Alphameric)[15]  *Using payout batch id
        fileWriter.write('\t');
        fileWriter.write(account.getName()); // Receiving Individual/Company name (Alphameric)[22]
        fileWriter.write('\n');
    }

    public BigDecimal getFileCredits() {
        return fileCredits;
    }

    public int getBatchCount() {
        return batchCount;
    }

    public long getFileHash() {
        return fileHash;
    }

    public long getFileEntries() {
        return fileEntries;
    }

    public boolean delete() {
        return file != null && file.delete();
    }

    @Override
    public void close() throws IOException {
        if (fileWriter != null) {
            fileWriter.close();
        }
        fileWriter = null;

        if(!isLocalStorage){
            if(keyName == null){
                logger.error(String.format("PayoutFileWriter: bucketName or keyName is null! client: %s", clientId));
            }
            FileUtil.uploadFile(keyName, file);
            //TODO: delete local file?
            //delete();
        }
    }
}