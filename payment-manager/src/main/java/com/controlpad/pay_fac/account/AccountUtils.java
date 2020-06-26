/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.account;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.account.AccountMapper;
import org.apache.ibatis.session.SqlSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

@Component
public class AccountUtils {

    @Autowired
    ClientConfigUtil clientConfigUtil;

    public void addAccount(SqlSession session, Account account) {
        AccountMapper accountMapper = session.getMapper(AccountMapper.class);

        accountMapper.insert(account);
    }

    public void updateAccount(SqlSession session, Account account) {

        AccountMapper accountMapper = session.getMapper(AccountMapper.class);

        Account currentAccount = accountMapper.findForId(account.getId());
        if (currentAccount != null) {
            accountMapper.update(account);
        }
    }

}
