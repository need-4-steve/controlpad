package com.controlpad.payman_common.payman_user;

import com.controlpad.payman_common.util.GsonUtil;

public class Privilege {

    private boolean superuser;
    private boolean proxy;
    private Integer readPrivilege;
    private Integer writePrivilege;
    private Integer createPrivilege;

    public Privilege(){}

    public Privilege(boolean superuser, boolean proxy, Integer readPrivilege, Integer writePrivilege, Integer createPrivilege){
        this.superuser = superuser;
        this.proxy = proxy;
        this.readPrivilege = readPrivilege;
        this.writePrivilege = writePrivilege;
        this.createPrivilege = createPrivilege;
    }

    public boolean isSuperuser() {
        return superuser;
    }

    public boolean isProxy() {
        return proxy;
    }

    public Integer getReadPrivilege() {
        return readPrivilege;
    }

    public Integer getWritePrivilege() {
        return writePrivilege;
    }

    public Integer getCreatePrivilege() {
        return createPrivilege;
    }

    @Override
    public String toString(){
        return GsonUtil.getGson().toJson(this);
    }
}
