/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor.test.payout_file;

import com.controlpad.payman_common.account.Account;
import com.controlpad.payman_common.account.AccountType;

public class PayoutFileEntry {

    private String paymentId;
    private Account account;
    private String amount;

    public PayoutFileEntry(AccountType accountType, String accountRouting, String accountNumber, String amount, String paymentId, String accountName) {
        account = new Account(accountName, accountRouting, accountNumber, accountType.slug);
        this.paymentId = paymentId;
        this.amount = amount;
    }

    public String getPaymentId() {
        return paymentId;
    }

    public Account getAccount() {
        return account;
    }

    public String getAmount() {
        return amount;
    }
}
