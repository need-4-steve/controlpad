package com.controlpad.pay_fac.payment.file;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.pay_fac.util.TeamConverterUtil;
import com.controlpad.payman_common.ach.ACH;
import com.controlpad.payman_common.ach.ACHMapper;
import com.controlpad.payman_common.payment.PaymentMapper;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.payment_file.PaymentFileMapper;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.File;
import java.math.BigDecimal;
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;


@RestController
@CrossOrigin(
        methods = {RequestMethod.GET, RequestMethod.OPTIONS},
        maxAge = 86400,
        origins = "*",
        allowedHeaders = "*"
)
@RequestMapping(value = {"/payout-files", "/payment-files"}) // TODO remove /payout-files once it's unused
public class PaymentFileController {

    private static final Logger logger = LoggerFactory.getLogger(PaymentFileController.class);
    private static final List<String> sortables = Arrays.asList(
            "id", "-id", "submitted_at", "-submitted_at", "created_at", "-created_at"
    );

    @Autowired
    ClientConfigUtil clientConfigUtil;

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<PaymentFile> listPaymentFiles(HttpServletRequest request,
                                                           @RequestParam(value = "submitted", required = false) Boolean submitted,
                                                           @RequestParam(value = "startDate", required = false) String startDate,
                                                           @RequestParam(value = "endDate", required = false) String endDate,
                                                           @RequestParam(value = "page", defaultValue = "1") Long page,
                                                           @RequestParam(value = "count", defaultValue = "25") Integer count,
                                                           @RequestParam(value = "teamId", required = false) String teamId,
                                                           @RequestParam(value = "sortBy", required = false) String sortBy) {
        teamId = TeamConverterUtil.convert(teamId);

        ParamValidations.checkPageCount(count, page);
        // Validate sortBy
        if (sortBy != null && !sortables.contains(sortBy)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST,
                    "sortBy must be one of the following: " + sortables.stream().collect(Collectors.joining(",")));
        }

        PaymentFileMapper paymentFileMapper = RequestUtil.getClientSqlSession(request).getMapper(PaymentFileMapper.class);

        List<PaymentFile> data = paymentFileMapper.search(startDate, endDate, teamId, submitted, sortBy, count, (page -1) * count);
        Long totalRecords = paymentFileMapper.getSearchCount(startDate, endDate, teamId, submitted);
        return new PaginatedResponse<>(totalRecords, count, data);
    }

    @Authorization(clientSqlSession = true, readPrivilege = 7)
    @RequestMapping(value = "/{id}", method = RequestMethod.GET)
    public PaymentFile getPaymentFile(HttpServletRequest request,
                                    @PathVariable("id") Long id) {

        return RequestUtil.getClientSqlSession(request).getMapper(PaymentFileMapper.class).findById(id);
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "/{id}/mark-submitted", method = RequestMethod.GET)
    public void markSubmitted(HttpServletRequest request,
                              @PathVariable("id") Long id) {

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);
        PaymentMapper paymentMapper = sqlSession.getMapper(PaymentMapper.class);

        PaymentFile paymentFile = paymentFileMapper.findById(id);
        if (paymentFile == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "PaymentFile doesn't exist for id");
        }
        if (paymentFile.getAchId() == null &&
                (paymentFile.getCredits().compareTo(BigDecimal.ZERO) > 0 || paymentFile.getDebits().compareTo(BigDecimal.ZERO) > 0)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "PaymentFile must be downloaded and submitted before marking submitted");
        }
        if (paymentFileMapper.markSubmitted(id) == 0) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "PaymentFile already marked submitted");
        }

        // Update any user account validations tied to the file
        sqlSession.getMapper(UserAccountMapper.class).markValidationSubmittedForPaymentFileId(paymentFile.getId());

        paymentMapper.markPaidForFileId(paymentFile.getId());

        sqlSession.commit();
    }

    //TODO retrieve client info from clientMap in ClientConfigUtil
    @Authorization(clientSqlSession = true, readPrivilege = 7, clientSqlAutoCommit = true)
    @RequestMapping(value = "/{id}/file", method = RequestMethod.GET)
    public void downloadFile(HttpServletRequest request,
                             HttpServletResponse response,
                             @PathVariable("id") Long id,
                             @RequestParam(value = "type", defaultValue = "nacha") String type,
                             @RequestParam(value = "achId", required = false, defaultValue = "1") Long achId,
                             @RequestParam(value = "stripQuote", defaultValue = "true") Boolean stripQuotes) {

        if (!StringUtils.equals(type, "nacha")) {
            // We may eventually have new file types
            throw new ResponseException(HttpStatus.BAD_REQUEST, "nacha is the only supported type currently");
        }

        SqlSession sqlSession = RequestUtil.getClientSqlSession(request);
        PaymentFileMapper paymentFileMapper = sqlSession.getMapper(PaymentFileMapper.class);

        PaymentFile paymentFile = paymentFileMapper.findById(id);
        if (paymentFile == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "Payment file doesn't exist");
        }
        if (paymentFile.getSubmittedAt() != null) {
            achId = paymentFile.getAchId();
        }

        ACH ach = sqlSession.getMapper(ACHMapper.class).findForId(achId);
        Long previousAchId = paymentFile.getAchId();
        if (ach == null) {
            throw new ResponseException(HttpStatus.BAD_REQUEST, "ACH doesn't exist for achId");
        }

        //TODO for now we are returing a static 'file' example, needs to be wired up against an S3 for production, else local hard drive for dev
        /**
         * Controller need to write data into ServletOutputStream
         * 1. try to create NachaConverter which will try to find/download the payment file
         * 2. -If it is local storage, try to read NACHA file locally first; if failed, generate NACHA file
         *    -If it is not local storage, try to retrieve NACHA file from S3 server; if failed, generate NACHA file.
         * 3. write the NACHA file into ServletOutputStream
         */
        //TODO upload NACHA file, eliminate race condition, make sure if file submmitted, we will down load the latest NACHA file from S3 server
        String clientId = RequestUtil.getClientId(request);
        try(NachaConverter nachaConverter = new NachaConverter(paymentFile.getFileName(), clientId, clientConfigUtil, stripQuotes)) {
            File nachaFile = nachaConverter.getNachaFile(ach, paymentFile.getSubmittedAt() != null);
            nachaConverter.writeOutputStream(response.getOutputStream(), nachaFile);
            response.flushBuffer();
        } catch (Exception e) {
            logger.error(e.getMessage(), e);
            throw new ResponseException(HttpStatus.INTERNAL_SERVER_ERROR, "Failed to export file");
        }


        if (paymentFile.getSubmittedAt() == null) {
            // Mark the most recent ach used to download the file
            paymentFileMapper.updateAchId(paymentFile.getId(), achId);
        }
    }
}
