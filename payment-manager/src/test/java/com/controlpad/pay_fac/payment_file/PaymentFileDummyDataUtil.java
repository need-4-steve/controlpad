/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.payment_file;

import com.controlpad.pay_fac.datasource.SqlSessionUtil;
import com.controlpad.pay_fac.test.MockData;
import com.controlpad.pay_fac.test.TestUtil;
import com.controlpad.payman_common.common.Money;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import org.apache.ibatis.session.SqlSession;
import org.joda.time.DateTime;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.Writer;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

@Component
public class PaymentFileDummyDataUtil {

    private List<PaymentFile> submittedFiles = new ArrayList<>();
    private List<PaymentFile> nonSubmittedFiles = new ArrayList<>();
    private PaymentFile downloadFile;
    private PaymentFile fileForSubmit;
    private UserAccountValidation userAccountValidation;
    private DateTime createdAt;
    private int team1FileCount = 0;
    private int team2FileCount = 0;
    private String teamOneId;
    private String teamTwoId;

    private String clientId;

    @Autowired
    public PaymentFileDummyDataUtil(TestUtil testUtil, SqlSessionUtil sqlSessionUtil) {
        createdAt = DateTime.now();
        clientId = testUtil.getMockData().getTestClient().getId();
        teamOneId = testUtil.getMockData().getTeamOne().getId();
        teamTwoId = testUtil.getMockData().getTeamTwo().getId();
        SqlSession sqlSession = sqlSessionUtil.openSession(clientId, true);
        loadDummyData(sqlSession, testUtil.getMockData());
        sqlSession.close();
    }

    private void loadDummyData(SqlSession sqlSession, MockData mockData) {
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);
        UserAccountMapper userAccountMapper = sqlSession.getMapper(UserAccountMapper.class);

        downloadFile = new PaymentFile(null, "TestPaymentFile", "VALIDATIONS", null, null,
                new Money(1.44D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 1, 12L, 0, teamTwoId);
        fileForSubmit = new PaymentFile(null, "SubmitPaymentFile", "Payouts", null, null,
                new Money(100D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 2, 20L, 8, teamTwoId);
        fileForSubmit.setAchId(1L);

        submittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(500D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 1, 20L, 10, teamOneId));
        submittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(400D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 2, 25L, 10, teamTwoId));
        submittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(450D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 2, 22L, 9, teamTwoId));

        submittedFiles.forEach(paymentFile -> {
            paymentFileMapper.insertPaymentFile(paymentFile);
            paymentFileMapper.markSubmitted(paymentFile.getId());
        });

        nonSubmittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(333D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 1, 25L, 8, teamOneId));
        nonSubmittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(444D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 2, 27L, 11, teamTwoId));
        nonSubmittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(555D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 1, 33L, 18, teamOneId));
        nonSubmittedFiles.add(new PaymentFile(null, "SomeFile", "Payouts", null, null,
                new Money(666D), BigDecimal.ZERO, BigDecimal.ZERO, BigDecimal.ZERO, 2, 44L, 24, teamTwoId));
        nonSubmittedFiles.add(downloadFile);
        nonSubmittedFiles.add(fileForSubmit);

        nonSubmittedFiles.forEach(paymentFileMapper::insertPaymentFile);

        countTeams(submittedFiles);
        countTeams(nonSubmittedFiles);

        UserAccount userAccount = new UserAccount("PaymentFileTestUser", "Some Guy", "324377516", "115576447", "checking", null, false);
        userAccountMapper.insert(userAccount);

        userAccountValidation = UserAccountValidation.generateNew(userAccount);
        userAccountValidation.setPaymentFileId(fileForSubmit.getId());
        userAccountMapper.insertDevAccountValidation(userAccountValidation);

        createLocalPaymentFile();
    }

    public DateTime getCreatedAt() {
        return createdAt;
    }

    public List<PaymentFile> getSubmittedFiles() {
        return submittedFiles;
    }

    public List<PaymentFile> getNonSubmittedFiles() {
        return nonSubmittedFiles;
    }

    public PaymentFile getDownloadFile() {
        return downloadFile;
    }

    public PaymentFile getFileForSubmit() {
        return fileForSubmit;
    }

    public UserAccountValidation getUserAccountValidation() {
        return userAccountValidation;
    }

    public int getTeam1FileCount() {
        return team1FileCount;
    }

    public int getTeam2FileCount() {
        return team2FileCount;
    }

    private void countTeams(List<PaymentFile> paymentFiles) {
        paymentFiles.forEach(paymentFile -> {
            switch (paymentFile.getTeamId()) {
                case "company":
                    team1FileCount++;
                    break;
                case "rep":
                    team2FileCount++;
                    break;
                default:
                    throw new RuntimeException("Invalid team id");
            }
        });
    }

    public void addSubmittedFile(PaymentFile paymentFile) {
        submittedFiles.add(paymentFile);
        if (paymentFile.getTeamId().equals(teamTwoId)) {
            team2FileCount++;
        } else if (paymentFile.getTeamId().equals(teamOneId)) {
            team1FileCount++;
        }
    }

    private void createLocalPaymentFile(){
        /**
         * 1. Create a new NACHA file in local device
         * 2. Try to fetch the file by sending a request
         */
        File fileDir;
        fileDir = new File(System.getProperty("user.home", "") + PaymentFile.FILE_BASEPATH + clientId + File.separator);
        if(!fileDir.exists() && !fileDir.mkdirs()){
            throw new RuntimeException("Cannot find/create file directory at: " + fileDir.getAbsolutePath());
        }else{
            String filePath = fileDir.getAbsolutePath() + File.separator + downloadFile.getFileName();
            File file = new File(filePath);
            if(!file.exists()){
                try{
                    file.createNewFile();
                }catch (Exception e){
                    System.out.print("Fail to create/write file.");
                    e.printStackTrace();
                }
            }
            Writer writer = null;
            try{
                writer = new BufferedWriter(new FileWriter(file));
                writer.write(paymentFile);
            }catch(Exception e){
                e.printStackTrace();
            }finally {
                try{
                    if(writer != null){
                        writer.close();
                    }
                }catch(Exception e){
                    e.printStackTrace();
                }
            }

        }

    }

    private final String nachaFileExample = "101 123456789 12345678916092614330094101SOME BANK              SOME BANK                      \n" +
            "5220Some Company                        COMPID    CCDVALIDATION160926160927   1123456780000001\n" +
            "622324377516789456123        00000000201-1            Derp Derpington         0123456780000001\n" +
            "622324377516789456123        00000000091-2            Derp Derpington         0123456780000002\n" +
            "622324377516987654321        00000000072-1            Derp Derpington         0123456780000003\n" +
            "622324377516987654321        00000000032-2            Derp Derpington         0123456780000004\n" +
            "622324377516258963147        00000000183-1            Derp Derpington         0123456780000005\n" +
            "622324377516258963147        00000000213-2            Derp Derpington         0123456780000006\n" +
            "622324377516789654358912     00000000044-1            Derp Derpington         0123456780000007\n" +
            "622324377516789654358912     00000000144-2            Derp Derpington         0123456780000008\n" +
            "6223243775167458213684       00000000215-1            Derp Derpington         0123456780000009\n" +
            "6223243775167458213684       00000000145-2            Derp Derpington         0123456780000010\n" +
            "622324377516976431528497     00000000036-1            Derp Derpington         0123456780000011\n" +
            "622324377516976431528497     00000000106-2            Derp Derpington         0123456780000012\n" +
            "82200000120389253012000000000000000000000144COMPID                             123456780000001\n" +
            "9000001000002000000120389253012000000000000000000000144                                       \n" +
            "9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999\n" +
            "9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999\n" +
            "9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999\n" +
            "9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999";

    private final String paymentFile = "#\t220\tCCD\tSALES PYMT\n" +
            "22\t324377516\t100000\t24.42\t1cJ2ep000Ka1JU\tUserName_Task_Tester_1\n" +
            "22\t324377516\t100000\t24.42\t1cJ2ep000LBIvi\tUserName_Task_Tester_0\n" +
            "22\t324377516\t100000\t24.42\t1cJ2ep000Mdhd5\tUserName_Task_Tester_2\n" +
            "#\t220\tCCD\tFEE PYMT\n" +
            "22\t987654321\t987654321\t0.90\t1cJ2ep000NSH1G\tFee\n" +
            "#\t220\tCCD\tTAX PYMT\n" +
            "22\t123456789\t123456789\t7.26\t1cJ2ep000Ozo7A\tTax";

}
