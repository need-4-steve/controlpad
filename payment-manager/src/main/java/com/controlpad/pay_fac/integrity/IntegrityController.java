package com.controlpad.pay_fac.integrity;

import com.google.gson.JsonObject;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = "/integrity")
public class IntegrityController {

    @Autowired
    ResourceSentry resourceSentry;

    @RequestMapping(value = "/stats")
    public JsonObject getMemoryStats() {
        JsonObject jsonObject = new JsonObject();
        jsonObject.addProperty("maxMemory", resourceSentry.getMaxMemory());
        jsonObject.addProperty("totalMemory", resourceSentry.getTotalMemory());
        jsonObject.addProperty("usedMemory", resourceSentry.getMemory());
        jsonObject.addProperty("uptime", resourceSentry.getUptime());
        jsonObject.addProperty("currentTime", System.currentTimeMillis());
        return jsonObject;
    }

}
