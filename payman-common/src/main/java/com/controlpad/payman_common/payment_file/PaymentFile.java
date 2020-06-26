package com.controlpad.payman_common.payment_file;

import java.io.File;
import java.math.BigDecimal;

public class PaymentFile {

    public static final String FILE_BASEPATH = File.separator + "payouts" + File.separator;

    private Long id;
    private Long achId;
    private String fileName;
    private String description;
    private String submittedAt;
    private String teamId;
    private String createdAt;
    private BigDecimal credits;
    private BigDecimal eWalletCredits;
    private BigDecimal stayCredits;
    private BigDecimal debits;
    private Integer batchCount;
    private Long entryCount;
    private Integer transactionCount;

    //This member is for table join with ACH
    private String bankName;

    public PaymentFile() {

    }

    public PaymentFile(String fileName, String description, BigDecimal credits, BigDecimal eWalletCredits, BigDecimal stayCredits,
                       BigDecimal debits, Integer batchCount, Long entryCount, Integer transactionCount, String teamId) {
        this(null, fileName, description, null, null, credits, eWalletCredits, stayCredits,
                debits, batchCount, entryCount, transactionCount, teamId);
    }

    public PaymentFile(Long id, String fileName, String description, String submittedAt, String createdAt,
                       BigDecimal credits, BigDecimal eWalletCredits, BigDecimal stayCredits, BigDecimal debits, Integer batchCount,
                       Long entryCount, Integer transactionCount, String teamId) {
        this.id = id;
        this.fileName = fileName;
        this.description = description;
        this.submittedAt = submittedAt;
        this.createdAt = createdAt;
        this.credits = credits;
        this.eWalletCredits = eWalletCredits;
        this.stayCredits = stayCredits;
        this.debits = debits;
        this.batchCount = batchCount;
        this.entryCount = entryCount;
        this.transactionCount = transactionCount;
        this.teamId = teamId;
    }

    public PaymentFile(Long achId, String fileName, String description, String teamId, String submittedAt,
                       BigDecimal credits, BigDecimal debits, Integer batchCount, Long entryCount, Integer transactionCount) {
        this.achId = achId;
        this.fileName = fileName;
        this.description = description;
        this.teamId = teamId;
        this.submittedAt = submittedAt;
        this.credits = credits;
        this.debits = debits;
        this.batchCount = batchCount;
        this.entryCount = entryCount;
        this.transactionCount = transactionCount;
    }

    public Long getId() {
        return id;
    }

    public Long getAchId() {
        return achId;
    }

    public String getFileName() {
        return fileName;
    }

    public String getTeamId() {
        return teamId;
    }

    public String getDescription() {
        return description;
    }

    public String getSubmittedAt() {
        return submittedAt;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public BigDecimal getCredits() {
        return credits;
    }

    public BigDecimal geteWalletCredits() {
        return eWalletCredits;
    }

    public BigDecimal getStayCredits() {
        return stayCredits;
    }

    public BigDecimal getDebits() {
        return debits;
    }

    public Integer getBatchCount() {
        return batchCount;
    }

    public Long getEntryCount() {
        return entryCount;
    }

    public Integer getTransactionCount() { return transactionCount; }

    public void setSubmittedAt(String submittedAt) {
        this.submittedAt = submittedAt;
    }

    public void setAchId(Long achId) {
        this.achId = achId;
    }

    public void setCredits(BigDecimal credits) {
        this.credits = credits;
    }

    public void setDebits(BigDecimal debits) {
        this.debits = debits;
    }

    public void setBatchCount(Integer batchCount) {
        this.batchCount = batchCount;
    }

    public void setEntryCount(Long entryCount) {
        this.entryCount = entryCount;
    }
}
