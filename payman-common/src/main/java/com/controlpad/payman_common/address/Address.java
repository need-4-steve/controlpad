package com.controlpad.payman_common.address;


import com.controlpad.payman_common.util.NameParser;
import com.controlpad.payman_common.validation.AlwaysCheck;
import com.controlpad.payman_common.validation.FullCheck;
import org.hibernate.validator.constraints.NotBlank;

import javax.validation.constraints.NotNull;
import javax.validation.constraints.Pattern;

public class Address {

    @NotNull(message = "address.line1 required", groups = AlwaysCheck.class)
    private String line1;
    private String line2;
    @NotBlank(message = "address.city required", groups = FullCheck.class)
    private String city;
    @NotBlank(message = "address.state required", groups = FullCheck.class)
    private String state;
    @NotBlank(message = "address.countryCode required", groups = FullCheck.class)
    private String countryCode;
    @NotNull(message = "address.postalCode required", groups = AlwaysCheck.class)
    private String postalCode;

    private String firstName;
    private String lastName;
    private String fullName;
    private String email;
    private String company;
    @Pattern(regexp = "^[0-9]{10,13}$", message = "address.phoneNumber should be 10 to 13 digits", groups = AlwaysCheck.class)
    private String phoneNumber;
    @Pattern(regexp = "^[0-9]{10,13}$", message = "address.faxNumber should be 10 to 13 digits", groups = AlwaysCheck.class)
    private String faxNumber;

    public Address(){

    }

    public Address(String line1, String postalCode){
        this.line1 = line1;
        this.postalCode = postalCode;
    }

    public Address(String line1, String line2, String city, String state, String postalCode, String fullName) {
        this.line1 = line1;
        this.line2 = line2;
        this.city = city;
        this.state = state;
        this.postalCode = postalCode;
        this.fullName = fullName;
    }

    public Address(String line1, String line2, String city, String state, String postalCode, String firstName, String lastName) {
        this.line1 = line1;
        this.line2 = line2;
        this.city = city;
        this.state = state;
        this.postalCode = postalCode;
        this.firstName = firstName;
        this.lastName = lastName;
    }

    public Address setLine1(String line1) {
        this.line1 = line1;
        return this;
    }

    public Address setLine2(String line2) {
        this.line2 = line2;
        return this;
    }

    public Address setCity(String city) {
        this.city = city;
        return this;
    }

    public Address setState(String state){
        this.state = state;
        return this;
    }

    public Address setCountryCode(String countryCode) {
        this.countryCode = countryCode;
        return this;
    }

    public Address setPostalCode(String postalCode) {
        this.postalCode = postalCode;
        return this;
    }

    public String getStreet(){
        return line2 == null ? line1 : line1 + " " + line2;
    }

    public String getFullAddress(){
        return getStreet() + ", " + city + ", " + state + ", " + countryCode;
    }

    public String getFullName() {
        if (fullName != null) {
            return fullName;
        } else {
            return lastName == null ? firstName : firstName + " " + lastName;
        }
    }

    public void parseFullName() {
        if (fullName == null) {
            return;
        }
        NameParser nameParser = new NameParser(fullName);
        firstName = nameParser.getFirstName();
        lastName = nameParser.getLastName();
    }

    public String getLine1() {
        return line1;
    }

    public String getLine2() {
        return line2;
    }

    public String getCity() {
        return city;
    }

    public String getState(){
        return state;
    }

    public String getCountryCode() {
        return countryCode;
    }

    public String getPostalCode() {
        return postalCode;
    }

    public String getFirstName() {
        if (firstName == null && fullName != null) {
            parseFullName();
        }
        return firstName;
    }

    public String getLastName() {
        if (lastName == null && fullName != null) {
            parseFullName();
        }
        return lastName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
    }

    public String getPhoneNumber() {
        return phoneNumber;
    }

    public String getEmail() {
        return email;
    }

    public String getCompany() {
        return company;
    }

    public String getFaxNumber() {
        return faxNumber;
    }

    public void setFirstName(String firstName) {
        this.firstName = firstName;
    }

    public void setLastName(String lastName) {
        this.lastName = lastName;
    }
}
