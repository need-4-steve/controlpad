/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor.test.cron;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import com.controlpad.payman_processor.CronTest;
import com.controlpad.payman_processor.cron.UserAccountValidationCron;
import com.controlpad.payman_processor.test.payout_file.PayoutFileBatch;
import com.controlpad.payman_processor.test.payout_file.PayoutFileEntry;
import com.controlpad.payman_processor.test.payout_file.PayoutFileReader;
import org.apache.commons.lang3.StringUtils;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

public class UserAccountValidationCronTest extends CronTest {

    @Autowired
    UserAccountValidationCron userAccountValidationCron;

    private List<UserAccount> invalidUserAccounts = new ArrayList<>();
    private List<UserAccount> submittedUserAccounts = new ArrayList<>();
    private List<UserAccountValidation> validations = new ArrayList<>();

    private List<UserAccountValidation> createdValidations = new ArrayList<>();

    @Test
    public void fullCheck() {
        UserAccountMapper userAccountMapper = getClientSqlSession().getMapper(UserAccountMapper.class);
        PaymentFileMapper payoutFileMapper = getClientSqlSession().getMapper(PaymentFileMapper.class);
        // Load dummy data
        loadDummyData(userAccountMapper);
        // Run cron
        userAccountValidationCron.payoutUserAccountValidations(getTestUtil().getMockData().getTestClient(), "rep", null);

        // Get the payout file id that was written
        UserAccountValidation tempUserAccountValidation = userAccountMapper.findCurrentUserAccountValidation(invalidUserAccounts.get(0).getUserId());
        assert tempUserAccountValidation != null;
        Long payoutFileId = tempUserAccountValidation.getPaymentFileId();
        // Should have created a file
        assert payoutFileId != null;
        // Make sure the validation count for the file equals the count for invalid accounts
        assert userAccountMapper.getValidationCountForFileId(payoutFileId) == invalidUserAccounts.size();

        // Check that validations exist for and match invalid accounts
        invalidUserAccounts.forEach(userAccount -> {
            UserAccountValidation currentValidation = userAccountMapper.findCurrentUserAccountValidation(userAccount.getUserId());
            assert currentValidation != null;
            System.out.println("Current validation: " + currentValidation.getUserId() + " " + currentValidation.getAccountHash());
            System.out.println("User Account: " + userAccount.getUserId() + " " + userAccount.getHash());
            assert StringUtils.equals(currentValidation.getAccountHash(), userAccount.getHash());
            createdValidations.add(currentValidation);
        });

        PaymentFile payoutFile = payoutFileMapper.findById(payoutFileId);
        assert payoutFile != null;
        List<PayoutFileBatch> payoutFileBatches = null;
        try (PayoutFileReader payoutFileReader = new PayoutFileReader(payoutFile.getFileName(), getTestUtil().getMockData().getTestClient().getId())) {
             payoutFileBatches = payoutFileReader.run();
        } catch (IOException e) {
            e.printStackTrace();
        }

        assert payoutFileBatches != null;
        assert payoutFileBatches.size() == 1;

        List<PayoutFileEntry> fileEntries = payoutFileBatches.get(0).getEntries();

        assert fileEntries.size() == invalidUserAccounts.size() * 2;

        UserAccount tempUserAccount;
        int fileOffset; // There are two payments created for each validation
        for (int i = 0; i < invalidUserAccounts.size(); i++) {
            fileOffset = i * 2;
            tempUserAccount = invalidUserAccounts.get(i);
            tempUserAccountValidation = createdValidations.get(i);
            // Make sure the two entries for this account are matching
            assertEntryAccountMatches(tempUserAccount, fileEntries.get(fileOffset).getAccount());
            System.out.println("amounts: " + fileEntries.get(fileOffset).getAmount() + ", " + tempUserAccountValidation.getAmount1());
            assert StringUtils.equals(fileEntries.get(fileOffset).getAmount(), tempUserAccountValidation.getAmount1());
            assert StringUtils.equals(fileEntries.get(fileOffset).getPaymentId().split("-")[0], String.valueOf(tempUserAccountValidation.getId()));
            assertEntryAccountMatches(tempUserAccount, fileEntries.get(fileOffset + 1).getAccount());
            System.out.println("amounts: " + fileEntries.get(fileOffset + 1).getAmount() + ", " + tempUserAccountValidation.getAmount2());
            assert StringUtils.equals(fileEntries.get(fileOffset + 1).getAmount(), tempUserAccountValidation.getAmount2());
            assert StringUtils.equals(fileEntries.get(fileOffset + 1).getPaymentId().split("-")[0], String.valueOf(tempUserAccountValidation.getId()));
        }
    }

    private void loadDummyData(UserAccountMapper userAccountMapper) {

        //create user accounts that will be validated
        invalidUserAccounts.add(new UserAccount("1", "User 1", "111111111", "111111", "checking", null, false));
        invalidUserAccounts.add(new UserAccount("2", "User 2", "222222222", "222222", "checking", null, false));
        invalidUserAccounts.add(new UserAccount("3", "User 3", "333333333", "333333", "checking", null, false)); // Has an old validation and should re-validate
        invalidUserAccounts.add(new UserAccount("4", "User 4", "444444444", "444444", "savings", null, false));
        invalidUserAccounts.add(new UserAccount("5", "User 5", "555555555", "555555", "checking", null, false));
        invalidUserAccounts.add(new UserAccount("6", "User 6", "666666666", "666666", "checking", null, false));
        invalidUserAccounts.add(new UserAccount("7", "User 7", "777777777", "777777", "savings", null, false));
        invalidUserAccounts.add(new UserAccount("8", "User 8", "888888888", "888888", "checking", null, false));
        invalidUserAccounts.add(new UserAccount("9", "User 9", "999999999", "999999", "checking", null, false));

        // Accounts that should be connected to existing validation records to show that they are already in the validation process
        submittedUserAccounts.add(new UserAccount("10", "User 10", "101010101", "101010", "checking", null, true));
        submittedUserAccounts.add(new UserAccount("11", "User 11", "111111119", "111119", "checking", null, false));
        submittedUserAccounts.add(new UserAccount("12", "User 12", "222222229", "222229", "savings", null, false));

        // Create validations for accounts that won't need validated
        submittedUserAccounts.forEach(userAccount -> validations.add(UserAccountValidation.generateNew(userAccount)));

        // Add an old validation that shouldn't have the same account hash as an account that will need validated
        UserAccountValidation oldValidation = UserAccountValidation.generateNew(invalidUserAccounts.get(2));
        oldValidation.setAccountHash("aaaaaaaaaaa");
        validations.add(oldValidation);

        //load user accounts into database using utility that payman uses
        invalidUserAccounts.forEach(userAccountMapper::insert);
        submittedUserAccounts.forEach(userAccountMapper::insert);

        //insert existing validations for submitted user accounts
        userAccountMapper.insertUserAccountValidations(validations);
        getClientSqlSession().commit();
    }

    private void assertEntryAccountMatches(Account invalidAccount, Account entryAccount) {
        System.out.println("assertEntryAccountMatches");
        System.out.println("invalidAccount: " + invalidAccount.toString());
        System.out.println("entryAccount: " + entryAccount.toString());
        assert StringUtils.equals(invalidAccount.getName(), entryAccount.getName()) &&
                StringUtils.equals(invalidAccount.getRouting(), entryAccount.getRouting()) &&
                StringUtils.equals(invalidAccount.getNumber(), entryAccount.getNumber()) &&
                StringUtils.equals(invalidAccount.getType(), entryAccount.getType());
    }
}
