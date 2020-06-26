package com.controlpad.payman_processor.cron;

import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.payout_job.PayoutJob;
import com.controlpad.payman_common.payout_job.PayoutJobMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import com.controlpad.payman_processor.client.ClientConfigUtil;
import com.controlpad.payman_processor.datasource.SqlSessionUtil;
import com.controlpad.payman_processor.payout.file.PayoutFileWriter;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.scheduling.concurrent.ThreadPoolTaskExecutor;
import org.springframework.stereotype.Component;

import java.io.IOException;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;


@Component
public class UserAccountValidationCron {

    private static final Logger logger = LoggerFactory.getLogger(UserAccountValidation.class);

    @Autowired
    ClientConfigUtil clientConfigUtil;
    @Autowired
    SqlSessionUtil sqlSessionUtil;
    @Autowired
    ThreadPoolTaskExecutor payoutProcessExecutor;

    @Scheduled(cron = "0 0 9 * * MON-FRI")
    public void runScheduledCron(){
        processUserAccountValidationsAllClients();
    }

    private void processUserAccountValidationsAllClients() {
        logger.info("processUserAccountValidationsAllClients() called");
        payoutProcessExecutor.execute(() -> clientConfigUtil.getClientMap().forEach((key, client) -> {
            long startTime = System.currentTimeMillis();
            if (client.getConfig().getFeatures().getAccountValidation()) {
                logger.info("processing user account validations for client: {}", client.getId());
                payoutUserAccountValidations(client, "company", null);  // For now using team 'company'
                logger.info("Validation process finished for client: {} in: {} millis", client.getId(), (System.currentTimeMillis() - startTime));
            }
        }));
    }

    public void payoutUserAccountValidations(ControlPadClient controlPadClient, String team, PayoutJob payoutJob) {
        PayoutFileWriter payoutFileWriter = null;
        try (SqlSession clientSqlSession = sqlSessionUtil.openSession(controlPadClient.getId(), false)) {
            UserAccountMapper userAccountMapper = clientSqlSession.getMapper(UserAccountMapper.class);
            PaymentFileMapper payoutFileMapper = clientSqlSession.getMapper(PaymentFileMapper.class);
            PayoutJobMapper payoutJobMapper = clientSqlSession.getMapper(PayoutJobMapper.class);
            if (payoutJob != null) {
                payoutJobMapper.markProcessing(payoutJob.getId());
                clientSqlSession.commit();
            }
            String today = DateTime.now().toString("yyyy-MM-dd");

            String fileName = today + "_Validations.tsv";

            // List all accounts that need to be validated, validated = 0 & account_hash doesn't equal current validation record
            List<UserAccount> userAccounts = userAccountMapper.listForValidationNeeded();
            if (userAccounts.isEmpty()) {
                logger.info("No validations to submit for client: " + controlPadClient.getId());
                if (payoutJob != null) {
                    payoutJobMapper.markSkipped(payoutJob.getId());
                }
                clientSqlSession.commit();
                return;
            }

            // Create validation records for accounts that need validated
            List<UserAccountValidation> validations = new ArrayList<>();
            userAccounts.forEach(userAccount -> validations.add(UserAccountValidation.generateNew(userAccount)));
            validations.forEach(userAccountMapper::insertUserAccountValidation);

            payoutFileWriter = new PayoutFileWriter(fileName, controlPadClient.getId(),
                    controlPadClient.getPosition(), controlPadClient.getId());
            payoutFileWriter.writeBatchHeader("220", "CCD", "VALIDATION");

            for(int i = 0; i < userAccounts.size(); i++) {
                payoutFileWriter.writeEntry(userAccounts.get(i), new BigDecimal(validations.get(i).getAmount1()),
                        String.valueOf(validations.get(i).getId()) + "-1");
                payoutFileWriter.writeEntry(userAccounts.get(i), new BigDecimal(validations.get(i).getAmount2()),
                        String.valueOf(validations.get(i).getId()) + "-2");
            }

            payoutFileWriter.close();

            // Record payout file and update validation records
            PaymentFile payoutFile = new PaymentFile(fileName, "User Account Validations", payoutFileWriter.getFileCredits(),
                    new Money(0D), new Money(0D), new Money(0D), payoutFileWriter.getBatchCount(),
                    payoutFileWriter.getFileEntries(), 0, team);
            payoutFileMapper.insertPaymentFile(payoutFile);

            // Set file id to validation list and insert the validations into the database
            userAccountMapper.updateValidationPaymentFile(validations, payoutFile.getId());

            payoutFileWriter = null;
            if (payoutJob != null) {
                payoutJobMapper.markProcessed(payoutJob.getId());
            }
            clientSqlSession.commit();
        } catch (Exception e) {
            logger.error(String.format("Failed to process account validation for client: %s", controlPadClient.getId()), e);
            if (payoutFileWriter != null) {
                try {
                    payoutFileWriter.close();
                } catch (IOException closeException) {
                    logger.error("Payout file close error!", closeException);
                }
                payoutFileWriter.delete();
            }
        }
    }
}