package com.controlpad.payman_common.merchant;

public class Merchant {

    private String id;
    private String email;
    private String type;

    public Merchant() {
    }

    public Merchant(String id, String type) {
        this.id = id;
        this.type = type;
    }

    public Merchant(String id, String email, String type) {
        this.id = id;
        this.email = email;
        this.type = type;
    }

    public String getId() {
        return id;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getType() {
        return type;
    }
}
