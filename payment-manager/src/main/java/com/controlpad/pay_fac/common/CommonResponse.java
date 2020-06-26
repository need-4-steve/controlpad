/*===============================================================================
* Copyright 2015(c) ControlPad
*==============================================================================*/
package com.controlpad.pay_fac.common;

public class CommonResponse<T> {
    private Boolean success;
    private Integer statusCode;
    private String description;
    private T data;

    public CommonResponse(boolean success) {
        this.success = success;
    }

    public CommonResponse(boolean success, String description) {
        this.success = success;
        this.description = description;
    }

    public CommonResponse(Boolean success, Integer statusCode, String description) {
        this.success = success;
        this.description = description;
        this.statusCode = statusCode;
    }

    public Boolean getSuccess() {
        return success;
    }

    public Integer getStatusCode() {
        return statusCode;
    }

    public void setStatusCode(Integer statusCode) {
        this.statusCode = statusCode;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public CommonResponse<T> setData(T data) {
        this.data = data;
        return this;
    }

    public T getData() {
        return data;
    }
}
