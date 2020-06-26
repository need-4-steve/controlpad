package com.controlpad.payman_common.util;

import com.google.gson.Gson;

public final class GsonUtil {

    private static final Gson gson = new Gson();

    public static Gson getGson() {
        return gson;
    }
}
