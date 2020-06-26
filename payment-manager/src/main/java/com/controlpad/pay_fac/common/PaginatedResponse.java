/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.common;


import java.util.List;

public class PaginatedResponse<T> {
    private Long total;
    private Long totalPage;
    private Long currentPage;
    private Integer perPage;
    private Integer count;
    private List<T> data;


    public PaginatedResponse(){

    }

    public PaginatedResponse(Long total, int count, List<T> data) {
        this.total = total;
        this.perPage = count;
        this.count = (data != null ? data.size() : 0);
        if (count == 0) {
            this.totalPage = 0L;
        } else if (total % count != 0) {
                this.totalPage = total / count + 1;
        } else {
                this.totalPage = total / count;
        }
        this.data = data;
    }

    public PaginatedResponse(Long total, int count, Long currentPage, List<T> data) {
        this(total, count, data);
        this.perPage = count;
        this.currentPage = currentPage;
        this.count = (data != null ? data.size() : 0);
    }

    public Long getTotal() {
        return total;
    }

    public Long getTotalPage() {
        return totalPage;

    }

    public Integer getCount() {
        return count;
    }

    public Long getCurrentPage() {
        return currentPage;
    }

    public List<T> getData() {
        return data;
    }

    public void setTotal(Long total) {
        this.total = total;
    }

    public void setTotalPage(Long totalPage) {
        this.totalPage = totalPage;
    }

    public void setData(List<T> data) {
        this.data = data;
    }

    @Override
    public String toString(){
        String str =  "Total: " + this.total + "\nTotal Page:" + this.totalPage
                    + "\nData Information:";

        if (data != null) {
            for (T aData : data) {
                str += aData.toString() + "\n";
            }
        }

        return str;
    }
}
