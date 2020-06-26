package com.controlpad.payman_common.transaction;


public enum TransactionResult {

    Success(true, 1, "P", "Successful"),
    Settled(true, 1, "S", "Successful"),
    Authorized(true, 1, "A", "Successful"),
    Approval_Required(true, 2, "D", "Pending approval"),
    Refunded(true, 5, "P", "Refunded"),
    Voided(true, 9, "S", "Voided"),
    Transaction_Not_Found(false, 11, "E", "Transaction not found"),
    Transaction_Not_Authorized(false, 12, "D", "Transaction not authorized"),
    Transaction_Incorrect_type(false, 13, "E", "Transaction type incorrect"),
    Declined(false, 20, "D", "Declined."),
    Lost_Or_Stolen(false, 21, "D", "Card might be lost or stolen. Contact issuer"),
    Invalid_Code(false, 22, "D", "Security code (pin/cvv) rejected"),
    Card_Expired(false, 23, "D", "Card is expired"),
    Hard_Decline(false, 24, "D", "Declined. Please contact bank or issuer."),
    Insufficient_Funds(false, 25, "D", "Insufficient Funds"),
    Duplicate_Transaction(false, 27, "D", "Duplicate transaction detected"),
    Billing_Info_Wrong(false, 30, "D", "Billing info doesn't match account"),
    Billing_Zip_Code_Not_Found(false, 31, "D", "Billing zip code not found"),
    Billing_Zip_Code_Does_Not_Match_Billing(false, 32, "D", "Billing zip code does not match address"),
    Invalid_Card_Number(false, 40, "D", "Invalid card number"),
    Invalid_Expiration_Date(false, 41, "D", "Invalid Expiration Date"),
    Invalid_Magstripe_Data(false, 42, "E", "Invalid card swipe data"),
    Card_Not_Supported(false, 43, "D", "Card not supported"),
    Invalid_Routing_Number(false, 50, "D", "Invalid routing number"),
    Invalid_Checking_Number(false, 51, "D", "Invalid checking number"),
    Invalid_Account_Number(false, 52, "D", "Invalid account number"),
    Account_Not_Validated(false, 55, "D", "Account not validated"),
    Card_Transactions_Not_Supported(false, 60, "D", "Card transactions are not supported"),
    Check_Transactions_Not_Supported(false, 61, "D", "Check transactions are not supported"),
    Maximum_Limit(false, 70, "D", "Transaction amount too large"),
    Minimum_Limit(false, 71, "D", "Transaction amount too small"),
    Maximum_Tax(false, 72, "D", "Transaction tax amount exceeded"),
    Balance_Lower(false, 75, "D", "Balance smaller than transaction amount"),
    Transaction_Limit(false, 79, "D", "Transaction limit reached"),
    Transaction_Not_Settled(false, 80, "D", "Transaction must be settled before issuing a refund"),
    Timeout(false, 90, "E", "Request timed out. Please try again later."),
    Processor_Error(false, 91, "E", "Processor error. Please try again later."),
    Merchant_Invalid(false, 98, "E", "Merchant account error. Please contact support."),
    Unexpected(false, 99, "E", "Unexpected error occured. Please try later.");

    private boolean success;
    private int resultCode;
    private String statusCode;
    private String message;

    TransactionResult(boolean success, int resultCode, String statusCode, String message) {
        this.success = success;
        this.resultCode = resultCode;
        this.statusCode = statusCode;
        this.message = message;
    }

    public static TransactionResult findById(int resultCode) {
        for (TransactionResult transactionResult : values()) {
            if (transactionResult.resultCode == resultCode) {
                return transactionResult;
            }
        }
        return Unexpected;
    }

    public boolean getSuccess() {
        return success;
    }

    public int getResultCode() {
        return resultCode;
    }

    public String getStatusCode() {
        return statusCode;
    }

    public String getMessage() {
        return message;
    }
}