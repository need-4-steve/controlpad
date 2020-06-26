package com.controlpad.pay_fac.user_account;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.common.CommonResponse;
import com.controlpad.payman_common.user_account.UserAccount;
import com.controlpad.payman_common.user_account.UserAccountMapper;
import com.controlpad.payman_common.user_account.UserAccountValidation;
import org.apache.commons.lang3.BooleanUtils;
import org.apache.commons.lang3.StringUtils;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

@Component
public class UserAccountUtil {

    @Autowired
    ClientConfigUtil clientConfigUtil;

    public void putUserAccount(SqlSession session, UserAccount account, String clientId) {
        UserAccountMapper userAccountMapper = session.getMapper(UserAccountMapper.class);
        boolean existsForUserId = userAccountMapper.existsUserAccount(account.getUserId());

        // If validation feature is off all are considered valid
        if (!clientConfigUtil.getClientFeatures(clientId).getAccountValidation()) {
            account.setValidated(true);
        }

        // Null if account validation is turned on
        if (account.getValidated() == null) {
            if (!existsForUserId) {
                // If current account doesn't exist then we need to validate
                account.setValidated(false);
            } else {
                // If hash is not the same then we will need to validate
                UserAccount currentAccount = userAccountMapper.findAccountForUserId(account.getUserId());
                account.setValidated(currentAccount.getValidated() && StringUtils.equals(currentAccount.getHash(), account.getHash()));
            }
        }

        if (existsForUserId) {
            userAccountMapper.updateUserAccount(account);
        } else {
            userAccountMapper.insert(account);
        }
    }

    public CommonResponse validateUserAccount(SqlSession session, String userId, String amount1, String amount2) {
        UserAccountMapper userAccountMapper = session.getMapper(UserAccountMapper.class);

        Boolean validated = userAccountMapper.isAccountValidatedForUserId(userId);
        if (BooleanUtils.isTrue(validated)) {
            return new CommonResponse(true, 1, "Account Validated.");
        }

        UserAccountValidation accountValidation = userAccountMapper.findCurrentUserAccountValidation(userId);

        if (accountValidation == null) {
            return new CommonResponse(false, 3, "Account validation not submitted yet.");
        }

        if ((!StringUtils.equals(amount1, accountValidation.getAmount1()) || !StringUtils.equals(amount2, accountValidation.getAmount2()))&&
                (!StringUtils.equals(amount1, accountValidation.getAmount2()) || !StringUtils.equals(amount2, accountValidation.getAmount1()))) {
            return new CommonResponse(false, 2, "Incorrect amount.");
        }

        UserAccount currentUserAccount = userAccountMapper.findAccountForUserId(accountValidation.getUserId());
        if (!StringUtils.equals(currentUserAccount.getHash(), accountValidation.getAccountHash())) {
            return new CommonResponse(false, 4, "Incorrect Account");
        } else {
            currentUserAccount.setValidated(true);
            userAccountMapper.updateUserAccount(currentUserAccount); // Perform a full update to override any possible race condition exploit
            return new CommonResponse(true, 1, "Account Validated.");
        }
    }
}