/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.payment_file;

import com.controlpad.pay_fac.ControllerTest;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.google.gson.reflect.TypeToken;
import org.junit.Test;
import org.springframework.beans.factory.annotation.Autowired;


public class PaymentFileControllerTest extends ControllerTest {

    @Autowired
    PaymentFileDummyDataUtil paymentFileDummyDataUtil;

    @Test
    public void listPaymentFilesTest() {
        TypeToken<PaginatedResponse<PaymentFile>> paymentFilePaginatedToken = new TypeToken<PaginatedResponse<PaymentFile>>(){};

        // Check submitted list, check if fileForSubmit has been submitted for count check
        PaginatedResponse<PaymentFile> submittedPaymentFilesResponse = performGet("/payment-files?page=1&count=25&submitted=true", paymentFilePaginatedToken);
        assert submittedPaymentFilesResponse.getData().size() ==
                (paymentFileDummyDataUtil.getSubmittedFiles().size() + (paymentFileDummyDataUtil.getFileForSubmit().getSubmittedAt() != null ? 1 : 0));
        submittedPaymentFilesResponse.getData().forEach(paymentFile -> {
            assert paymentFile.getSubmittedAt() != null;
        });

        // Check not submitted
        PaginatedResponse<PaymentFile> nonSubmittedFilesResponse = performGet("/payment-files?page=1&count=25&submitted=false", paymentFilePaginatedToken);
        System.out.println("Payment files not submitted: " + paymentFileDummyDataUtil.getNonSubmittedFiles().size());
        System.out.println("Payment file for submit submitted: " + paymentFileDummyDataUtil.getFileForSubmit().getSubmittedAt());
        assert nonSubmittedFilesResponse.getData().size() == (paymentFileDummyDataUtil.getNonSubmittedFiles().size() - (paymentFileDummyDataUtil.getFileForSubmit().getSubmittedAt() != null ? 1 : 0));
        nonSubmittedFilesResponse.getData().forEach(paymentFile -> {
            assert paymentFile.getSubmittedAt() == null;
        });

        String yesterday = paymentFileDummyDataUtil.getCreatedAt().minusDays(1).toString("YYYY-MM-dd HH:mm:ss");
        String tomorrow = paymentFileDummyDataUtil.getCreatedAt().plusDays(1).toString("YYYY-MM-dd HH:mm:ss");
        String allFilesPath = String.format("/payment-files?page=1&count=25&startDate=%s&endDate=%s", yesterday, tomorrow);
        PaginatedResponse<PaymentFile> allPaymentFilesForDate = performGet(allFilesPath, paymentFilePaginatedToken);
        assert allPaymentFilesForDate.getData().size() == (paymentFileDummyDataUtil.getNonSubmittedFiles().size() + paymentFileDummyDataUtil.getSubmittedFiles().size());
        assert allPaymentFilesForDate.getTotalPage() == 1;
        assert allPaymentFilesForDate.getTotal() == allPaymentFilesForDate.getData().size();

        String noFilesPath = String.format("/payment-files?page=1&count=25&startDate=%s&endDate=%s", tomorrow, yesterday);
        PaginatedResponse<PaymentFile> emptyPaymentFilesForBadDate = performGet(noFilesPath, paymentFilePaginatedToken);
        assert emptyPaymentFilesForBadDate.getData().isEmpty();
        assert emptyPaymentFilesForBadDate.getTotal() == 0;

        PaginatedResponse<PaymentFile> team1PaymentFiles = performGet("/payment-files?page=1&count=25&teamId=1", paymentFilePaginatedToken);
        assert (team1PaymentFiles.getTotal() == team1PaymentFiles.getData().size()) && (team1PaymentFiles.getData().size() == paymentFileDummyDataUtil.getTeam1FileCount());

        PaginatedResponse<PaymentFile> team2PaymentFiles = performGet("/payment-files?page=1&count=25&teamId=2", paymentFilePaginatedToken);
        assert (team2PaymentFiles.getTotal() == team2PaymentFiles.getData().size()) && (team2PaymentFiles.getData().size() == paymentFileDummyDataUtil.getTeam2FileCount());
    }

    @Test
    public void getPaymentFileTest() {
        TypeToken<PaymentFile> paymentFileTypeToken = new TypeToken<PaymentFile>(){};
        PaymentFile missingPaymentFile = performGet("/payment-files/0", paymentFileTypeToken);
        assert missingPaymentFile == null;

        PaymentFile downloadPaymentFile = performGet("/payment-files/" + paymentFileDummyDataUtil.getDownloadFile().getId(), paymentFileTypeToken);
        assert downloadPaymentFile != null;
        assert downloadPaymentFile.getId().equals(paymentFileDummyDataUtil.getDownloadFile().getId());
    }

    @Test
    public void markPaymentFileSubmittedTest() {
        PaymentFileMapper paymentFileMapper = getSqlSession().getMapper(PaymentFileMapper.class);

        performBadGetRequest("/payment-files/0/mark-submitted");
        Long submittedId = paymentFileDummyDataUtil.getSubmittedFiles().get(0).getId();
        performBadGetRequest("/payment-files/" + submittedId + "/mark-submitted");

        Long notDownloadedId = paymentFileDummyDataUtil.getNonSubmittedFiles().get(0).getId();
        performBadGetRequest("/payment-files/" + notDownloadedId + "/mark-submitted");

        PaymentFile fileForSubmit = paymentFileDummyDataUtil.getFileForSubmit();
        performGet("/payment-files/" + fileForSubmit.getId() + "/mark-submitted", new TypeToken<Object>(){});
        fileForSubmit.setSubmittedAt(paymentFileMapper.findById(fileForSubmit.getId()).getSubmittedAt());

        assert paymentFileMapper.findById(fileForSubmit.getId()).getSubmittedAt() != null;

        // Make sure account validation is marked submitted
        UserAccountMapper userAccountMapper = getSqlSession().getMapper(UserAccountMapper.class);
        assert userAccountMapper.findCurrentUserAccountValidation(paymentFileDummyDataUtil.getUserAccountValidation().getUserId()).getSubmittedAt() != null;
    }

    @Test
    public void downloadPaymentFileTest() {
        PaymentFile downloadPaymentFile = paymentFileDummyDataUtil.getDownloadFile();
        PaymentFile submittedFile = paymentFileDummyDataUtil.getSubmittedFiles().get(0);

        // Validation of type
        performBadGetRequest("/payment-files/" + downloadPaymentFile.getId() + "/file?type=derp");

        // Validation of ach
        performBadGetRequest("/payment-files/" + downloadPaymentFile.getId() + "/file?achId=0");

        // Non existing files throw a bad request
        performBadGetRequest("/payment-files/0/file?achId=1");

        // Cannot select achId when file already submitted
        performBadGetRequest("/payment-files/" + submittedFile.getId() + "/file?achId=1");

        // Must select achId if file hasn't been downloaded
        performBadGetRequest("/payment-files/" + downloadPaymentFile.getId() + "/file");

        performBadGetRequest("/payment-files/" + downloadPaymentFile.getId() + "/file?achId=0");

        // TODO check file once that is all wired up
        String file = performGet("/payment-files/" + downloadPaymentFile.getId() + "/file?achId=1");
        System.out.println("Nacha File:\n" + file);
    }

}
