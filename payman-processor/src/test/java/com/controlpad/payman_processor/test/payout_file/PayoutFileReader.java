/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor.test.payout_file;

import com.controlpad.payman_common.account.AccountType;
import com.controlpad.payman_processor.payout.file.PayoutFileWriter;
import org.apache.commons.lang3.ArrayUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.commons.lang3.math.NumberUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.*;
import java.text.DecimalFormat;
import java.util.ArrayList;
import java.util.List;

import static com.controlpad.payman_processor.payout.file.PayoutFileWriter.PAYOUT_FILEPATH_BASE;

public class PayoutFileReader implements Closeable {
    private static final Logger logger = LoggerFactory.getLogger(PayoutFileWriter.class);

    private DecimalFormat decimalFormat = new DecimalFormat("#0.00");

    private BufferedReader reader = null;
    private List<PayoutFileBatch> payoutFileBatchList;

    public PayoutFileReader(String fileName, String clientId) throws IOException {
        File clientDir;
        logger.info("Try to read file: " + fileName + " Client ID: " + clientId);
        if(Boolean.parseBoolean(System.getProperty("LOCAL_STORAGE"))){
            //Program is running on dev
            logger.info("PayoutFileWriter: writing file in DEBUG mode...");
            String OS = System.getProperty("os.name");
            if(OS.startsWith("Windows")){
                clientDir = new File("C:" + PAYOUT_FILEPATH_BASE + clientId + File.separator);
            }else{
                clientDir = new File(System.getProperty("user.home", "") + PAYOUT_FILEPATH_BASE + clientId + File.separator);
            }
        }else{
            //Program is running on server
            logger.info("PayoutFileWriter: writing file on s3 server...");
            clientDir = new File(System.getProperty("user.home", "") + PAYOUT_FILEPATH_BASE + clientId + File.separator);
        }

        if (!clientDir.exists() && !clientDir.mkdirs()) {
            throw new RuntimeException("Unable to create payouts directory");  // /payouts must be owned by the tomcat user
        }
        String filePath = clientDir.getAbsolutePath() + File.separator + fileName;
        reader = new BufferedReader(new FileReader(new File(filePath)));
    }

    public List<PayoutFileBatch> run() throws IOException {
        payoutFileBatchList = new ArrayList<>();
        String line;
        PayoutFileBatch currentBatch = null;
        while(reader.ready()) {
            String[] data = reader.readLine().split("\\t");
            System.out.println("Data Read: " + ArrayUtils.toString(data));
            if (StringUtils.equals(data[0], "#")) {
                // Create a batch and add to batch list
                currentBatch = new PayoutFileBatch(data[1], data[2], data[3]);
                payoutFileBatchList.add(currentBatch);
            } else if (currentBatch != null){
                // Create an entry and add to batch
                currentBatch.addEntry(
                        new PayoutFileEntry(AccountType.getTypeForTransactionCode(NumberUtils.createInteger(data[0])),
                        data[1], data[2], data[3], data[4], data[5]));
            } else {
                throw new RuntimeException("Payout file format was wrong");
            }
        }
        return payoutFileBatchList;
    }

    public List<PayoutFileBatch> getAllBatches() {
        return payoutFileBatchList;
    }

    @Override
    public void close() throws IOException {
        if (reader != null) {
            reader.close();
        }
        reader = null;
    }
}
