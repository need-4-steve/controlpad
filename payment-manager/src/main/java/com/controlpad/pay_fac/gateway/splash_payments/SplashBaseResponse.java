package com.controlpad.pay_fac.gateway.splash_payments;


import java.util.List;

public class SplashBaseResponse<T> {

    private SplashResponseBody<T> response;
    private List<SplashResponseBody<T>> responses;

    public List<T> getDataList() {
        if (response != null) {
            return response.data;
        } else if (responses != null && !responses.isEmpty()){
            return responses.get(0).data;
        }
        return null;
    }

    public T getData() {
        if (response != null && response.data != null && !response.data.isEmpty()) {
            return response.data.get(0);
        } else if (responses != null && responses.size() > 0 && responses.get(0).data != null && !responses.get(0).data.isEmpty()){
            return responses.get(0).data.get(0);
        }
        return null;
    }

    public List<SplashResponseError> getErrors() {
        if (response != null) {
            return response.errors;
        } else if (responses != null && !responses.isEmpty()){
            return responses.get(0).errors;
        } else {
            return null;
        }
    }

    public static class SplashResponseError {

    }


    public static class SplashResponseBody<A> {
        private List<A> data;
        private List<SplashResponseError> errors;
    }
}