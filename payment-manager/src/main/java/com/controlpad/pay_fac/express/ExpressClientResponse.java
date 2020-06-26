package com.controlpad.pay_fac.express;


import com.controlpad.pay_fac.common.CommonResponse;

public class ExpressClientResponse extends CommonResponse<ExpressClientResponse.ExpressClientData> {


    public ExpressClientResponse(Boolean success, Integer statusCode, String description) {
        super(success, statusCode, description);
    }

    public ExpressClientResponse(ExpressClientData data) {
        super(true, 1, "Created client");
        this.setData(data);
    }

    public static class ExpressClientData {
        private String apiKey;

        public ExpressClientData(String apiKey) {
            this.apiKey = apiKey;
        }

        public String getApiKey() {
            return apiKey;
        }
    }
}
