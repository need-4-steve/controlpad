/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.api_key;


import com.controlpad.payman_common.util.GsonUtil;

public class APIKeyConfig {

    private boolean processSales;
    private boolean updateAccounts;
    private boolean createPaymentFile;

    public APIKeyConfig() {}

    public APIKeyConfig(Boolean processSales, Boolean updateAccounts, Boolean createPaymentFile) {
        this.processSales = processSales;
        this.updateAccounts = updateAccounts;
        this.createPaymentFile = createPaymentFile;
    }

    public boolean getProcessSales() {
        return processSales;
    }

    public boolean getUpdateAccounts() {
        return updateAccounts;
    }

    public boolean getCreatePaymentFile() {
        return createPaymentFile;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }
}