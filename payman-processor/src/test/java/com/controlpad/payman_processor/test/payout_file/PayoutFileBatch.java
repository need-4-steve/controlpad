/*===============================================================================
* Copyright 2016(c) ControlPad
*==============================================================================*/
package com.controlpad.payman_processor.test.payout_file;

import java.util.ArrayList;
import java.util.List;

public class PayoutFileBatch {

    private String classCode;
    private String entryClassCode;
    private String entryDesc;
    private List<PayoutFileEntry> entries;

    public PayoutFileBatch(String classCode, String entryClassCode, String entryDesc) {
        this.classCode = classCode;
        this.entryClassCode = entryClassCode;
        this.entryDesc = entryDesc;
        this.entries = new ArrayList<>();
    }

    public List<PayoutFileEntry> getEntries() {
        return entries;
    }

    public void addEntry(PayoutFileEntry fileEntry) {
        entries.add(fileEntry);
    }
}
