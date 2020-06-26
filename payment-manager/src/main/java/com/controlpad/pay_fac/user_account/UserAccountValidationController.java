package com.controlpad.pay_fac.user_account;

import com.controlpad.pay_fac.common.PaginatedResponse;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.pay_fac.interceptor.Authorization;
import com.controlpad.pay_fac.util.ParamValidations;
import com.controlpad.pay_fac.util.RequestUtil;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

import javax.servlet.http.HttpServletRequest;
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
@RequestMapping("/user-account-validations")
public class UserAccountValidationController {

    private static final List<String> sortables = Arrays.asList(
            "id", "-id", "submitted_at", "-submitted_at", "created_at", "-created_at"
    );

    @Authorization(readPrivilege = 2, clientSqlSession = true)
    @RequestMapping(value = "/{id}/user-id", method = RequestMethod.GET)
    public String getUserForValidation(HttpServletRequest request,
                                       @PathVariable("id") Long id) {

        return RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class).findUserIdForValidationId(id);
    }

    @Authorization(readPrivilege = 7, clientSqlSession = true)
    @RequestMapping(value = "", method = RequestMethod.GET)
    public PaginatedResponse<UserAccountValidation> searchValidations(HttpServletRequest request,
                                                         @RequestParam(value = "paymentFileId", required = false) Long paymentFileId,
                                                         @RequestParam(value = "userId", required = false) String userId,
                                                         @RequestParam(value = "sortBy", required = false) String sortBy,
                                                         @RequestParam(value = "page", defaultValue = "1") Long page,
                                                         @RequestParam(value = "count", defaultValue = "25") Integer count) {

        ParamValidations.checkPageCount(count, page);
        // Validate sortBy
        if (sortBy != null && !sortables.contains(sortBy)) {
            throw new ResponseException(HttpStatus.BAD_REQUEST,
                    "sortBy must be one of the following: " + sortables.stream().collect(Collectors.joining(",")));
        }

        UserAccountMapper userAccountMapper = RequestUtil.getClientSqlSession(request).getMapper(UserAccountMapper.class);
        List<UserAccountValidation> validations = userAccountMapper.searchValidations(paymentFileId, userId, sortBy, count, (page - 1) * count);

        Integer totalRecords = userAccountMapper.getValidationCountForSearch(paymentFileId, userId);

        return new PaginatedResponse<>(totalRecords.longValue(), count, validations);
    }
}