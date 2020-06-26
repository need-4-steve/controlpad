package com.controlpad.payman_common.fee;


import java.util.ArrayList;

public class FeeIds extends ArrayList<Long> {

    public FeeIds() {}

    public FeeIds(long... ids) {
        for(long id: ids) {
            add(id);
        }
    }

}
