package com.controlpad.pay_fac.report;


import com.controlpad.payman_common.util.GsonUtil;

import java.math.BigDecimal;

public class Totals {

    private Integer count;
    private BigDecimal total;

    public Integer getCount() {
        return count;
    }

    public BigDecimal getTotal() {
        return total;
    }

    public void setTotal(BigDecimal total) {
        this.total = total;
    }

    public void setCount(Integer count) {
        this.count = count;
    }

    @Override
    public String toString() {
        return GsonUtil.getGson().toJson(this);
    }
}
