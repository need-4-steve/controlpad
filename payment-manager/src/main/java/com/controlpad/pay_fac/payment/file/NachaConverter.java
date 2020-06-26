package com.controlpad.pay_fac.payment.file;

import com.controlpad.pay_fac.client.ClientConfigUtil;
import com.controlpad.pay_fac.exceptions.ResponseException;
import com.controlpad.payman_common.ach.ACH;
import com.controlpad.payman_common.client.ControlPadClient;
import com.controlpad.payman_common.payment_file.PaymentFile;
import com.controlpad.payman_common.util.FileUtil;
import org.apache.commons.lang3.StringUtils;
import org.apache.commons.lang3.math.NumberUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.http.HttpStatus;

import java.io.*;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.Map;

public class NachaConverter implements Closeable {

    private static final Logger logger = LoggerFactory.getLogger(NachaConverter.class);
    // Limits determined by character limit in NACHA format
    public static final long MAX_CREDITS = 999999999999L; //money in cents $$$$$$$$$$cc
    public static final long MAX_DEBITS = 999999999999L;
    public static final long MAX_FILE_ENTRIES = 99999999;

    private static final String DUMMY_BLOCK =  new String(new char[94]).replace("\0", "9"); // 94 '9's

    private DateTimeFormatter dateFormatter = DateTimeFormatter.ofPattern("yyMMdd");
    private DateTimeFormatter timeFormatter = DateTimeFormatter.ofPattern("HHmm");
    private LocalDateTime dt;

    private File fileDir;
    private File paymentFile = null;
    private File nachaFile = null;

    private BufferedReader reader = null;
    private BufferedWriter writer;

    // Flags to track state so that methods can't be called out of order
    private boolean fileHeaderWritten = false;
    private boolean batchOpen = false;
    private boolean fileControlWritten = false;

    private long fileCredits;  // File control record, sum of all credits for paymentFile
    private long batchCredits; // Batch control record, sum of all credits for that batch
    private int batchCount; // Number of batches. You only really need one batch because the transaction/batch type won't change
    private long batchHash; // The sum of all entry account routing numbers (first 8 digits only). If greater than 10 characters, clip the left side
    private long fileHash; // The sum of all batchHashes. If greater than 10 clip left side
    private long rowCount; // Used to determine number of blocks (10 lines per block)
    private long fileEntries; // File control record info
    private long batchEntries; // Batch control record info

    private OutputStream fileStream;

    private String filePath;
    private String keyName;

    private boolean isLocalStorage;
    private String paymentFileName;

    private boolean stripQuotes;

    /**
     *
     * @param paymentFileName File name without the path. No official format. {Date}_{fileId}_{Reason}.nacha is a nice format for sorting by date internally though
     * @param clientId  Client id is being used to determine paymentFile path to seperate clients
     * @throws IOException
     */
    public NachaConverter(String paymentFileName, String clientId, ClientConfigUtil clientConfigUtil, Boolean stripQuotes) throws IOException {
        /**
         *  1.  Check debug property
         *      a. if this is DEBUG, try to read paymentFile locally based on operating system
         *      b. if this is not DEBUG, try to fetch paymentFile from Amazon S3 server
         *          i.  get client ID from request, then use client ID to get client position and name
         *          ii. bucketName = f(position, clientName)
         *          iii.keyName = fileName = get by paymentFile ID
         */
        isLocalStorage = Boolean.parseBoolean(System.getProperty("LOCAL_STORAGE"));
        this.paymentFileName = paymentFileName;
        this.stripQuotes = stripQuotes;

        String homeDir = System.getProperty("user.home", "/home/ubuntu/payouts");
        fileDir = new File(homeDir + PaymentFile.FILE_BASEPATH + clientId + File.separator);


        if(isLocalStorage){
            //program is running on dev
            logger.info("NachaConverter: fetching paymentFile in DEBUG mode...");
            if (!fileDir.exists()) {
                logger.error(String.format("NachaConverter: cannot find the payment file in local device. File Name:%s Client ID:%s", paymentFileName, clientId));
                throw new RuntimeException("Payout paymentFile doesn't exist: " + fileDir.getAbsolutePath());  // /payouts must be owned by the tomcat user
            }else{
                filePath = fileDir.getAbsolutePath() + File.separator + paymentFileName;
                paymentFile = new File(filePath);
                if(!paymentFile.exists()){
                    logger.error(String.format("NachaConverter: cannot find the payout paymentFile in local device. File Name:%s Client ID:%s", paymentFileName, clientId));
                    throw new RuntimeException("Payout paymentFile doesn't exist: " + fileDir.getAbsolutePath());  // /payouts must be owned by the tomcat user
                }
            }
        }else{
            logger.info("NachaConverter: fetching paymentFile on AWS S3 server...");
            if(clientConfigUtil == null){
                System.out.println("Error! clientConfigUtil is null!");
            }
            Map<String, ControlPadClient> clientMap = clientConfigUtil.getClientMap();
            ControlPadClient client = clientMap.get(clientId);
            keyName = FileUtil.generateKeyName(client.getPosition(), client.getId(), paymentFileName);

            logger.info("Home Directory: " + homeDir);
            if(!fileDir.exists() && !fileDir.mkdirs()){
                logger.error(String.format("NachaConverter: cannot find/create directory. Path:%s Client ID:%s", fileDir.getAbsolutePath(), clientId));
                throw new RuntimeException("Cannot find/create directory: " + fileDir.getAbsolutePath());  // /payouts must be owned by the tomcat user
            }else{
                filePath = fileDir.getAbsolutePath() + File.separator + paymentFileName;
                paymentFile = new File(filePath);
            }
            if(!paymentFile.exists() && !paymentFile.createNewFile()){
                logger.error(String.format("NachaConverter: cannot find/create payout paymentFile in client server. File Name:%s Client ID:%s", paymentFileName, clientId));
                throw new RuntimeException("Cannot find/create payout paymentFile in client server: " + fileDir.getAbsolutePath());
            }

        }
        dt = LocalDateTime.now();
    }

    public File getNachaFile(ACH ach, boolean returnExisting) throws Exception {
        String nachaFileName = paymentFileName + ".nacha";
        nachaFile = new File(fileDir.getAbsolutePath() + File.separator + nachaFileName);

        if(!isLocalStorage){
            if(!nachaFile.exists()){
                fileDir.mkdirs();
                nachaFile.createNewFile();
            }
            if(returnExisting && FileUtil.getFile(keyName.replace(".tsv", ".nacha"), nachaFile)){
                return nachaFile;
            }else{
                /**
                 * 1. get remote payout paymentFile
                 * 2. convert it into NACHA paymentFile and store into a paymentFile
                 * 3. upload the NACHA paymentFile back to S3 server
                 */
                logger.info("Generating NACHA paymentFile ...");
                try(OutputStream payoutStream = new FileOutputStream(paymentFile)) {
                    if (!FileUtil.getFile(keyName, payoutStream)) {
                        throw new ResponseException(HttpStatus.BAD_REQUEST, "Cannot find payout paymentFile");
                    }
                    write(new FileOutputStream(nachaFile), ach);
                    FileUtil.uploadFile(keyName.replace(".tsv", ".nacha"), nachaFile);
                    return nachaFile;
                }
            }
        }else{
            if(!nachaFile.exists()) {
                nachaFile.createNewFile();
                write(new FileOutputStream(nachaFile), ach);
                return nachaFile;
            }else{
                return nachaFile;
            }
        }
    }

    public void write(OutputStream outputStream, ACH ach) throws Exception {
        reset();

        reader = new BufferedReader(new FileReader(paymentFile));

        this.writer = new BufferedWriter(new OutputStreamWriter(outputStream));
        long odfi = ach.getODFI();
        String line;
        String[] values;
        writeFileHeader(ach);
        while((line = reader.readLine()) != null) {
            System.out.println(line);
            values = line.split("\\t");
            if (StringUtils.equals(values[0], "#")) {
                if (batchOpen) {
                    writeBatchControl(ach);
                }
                writeBatchHeader(ach, values[1], values[2], values[3]);
            } else {
                writeEntry(values[0], values[1], values[2], values[5], NumberUtils.createDouble(values[3]), values[4], odfi);
            }
        }
        if (batchOpen) {
            writeBatchControl(ach);
        }
        writeFileControl();
    }

    public void writeOutputStream(OutputStream outputStream, File nachaFile) throws Exception{
        try(FileInputStream fileInputStream = new FileInputStream(nachaFile)){
            byte[] buffer = new byte[1024];
            int count = 0;

            while((count = fileInputStream.read(buffer)) >= 0){
                outputStream.write(buffer, 0, count);
            }
        }catch(Exception e){
            throw e;
        }
    }

    /**
     *
     * @param ach
     * @throws Exception
     *
     * File Header:
     * Record Type Code(1) PriorityCode(2) ImmediateDestination(10) ImmediateOrigin(10) FileCreationDate(6)
     * FileCreationTime(4) FileIDModifier(1) RecordSize(3) BlockingFactor(2) FormatCode(1)
     * ImmediateDestinationName(23) ImmediateOriginName(23) ReferenceCode(8)
     */
    private void writeFileHeader(ACH ach) throws Exception {
        if (fileHeaderWritten) {
            throw new RuntimeException("Nacha paymentFile can have only one header");
        }
        fileHeaderWritten = true;
        rowCount++;

        writer.write("101"); // Record type and priority code
        writer.write(padLeft(10, ach.getDestinationRoute())); // Immediate Destination (bTTTTAAAAC)
        writer.write(padLeft(10, ach.getOriginRoute())); // Immediate Origin (bTTTTAAAAC)
        writer.write(dt.format(dateFormatter)); // File Creation Date (YYMMDD)
        writer.write(dt.format(timeFormatter)); // File Creation Time (HHMM)
        //TODO: paymentFile ID should not be a fixed number
        writer.write("0"); // File Id Modifier (A-Z or 0-9)[1]
        writer.write("094101"); // Block Size: 094, Blocking Factor: 10, Format Code: 1
        writer.write(truncate(23, padRight(23, StringUtils.capitalize(ach.getDestinationName())))); // Immediate Destination Name (Alphameric)[23]
        writer.write(truncate(23, padRight(23, StringUtils.capitalize(ach.getOriginName())))); // Immediate Origin Name (Alphameric)[23]
        writer.write(padRight(8, "")); // Reference Code: (Alphameric)[8]  *unused?
        writeLine();
    }

    /**
     *
     * @param ach
     * @param serviceCode
     * @param entryClassCode
     * @param entryDesc
     * @throws Exception
     *
     * Company Batch Header:
     * RecordTypeCode(1) ServiceClassCode(2-4)
     * CompanyName(5-20) CoDiscData(21-40)
     * CompanyID(10) SEC(3) EntryDescription(10) CoDescriptiveDate(6)
     * EffectiveEntryDate(6) SettlementDate(3)
     * OriginatorStatusCode(1) ODFI_ID(8) BatchNumber(7)
     */
    private void writeBatchHeader(ACH ach, String serviceCode, String entryClassCode, String entryDesc) throws Exception {
        checkFileControlWritten();
        if (batchOpen) {
            throw new RuntimeException("Must close a nacha batch with a control record before creating a new header");
        }
        batchOpen = true;
        rowCount++;
        batchCount++;

        writer.write("5"); // Record Type
        writer.write(serviceCode); // Service Code
        writer.write(truncate(16, padRight(16, StringUtils.capitalize(ach.getCompanyName())))); // Company Name (Alphameric)[15]
        writer.write(truncate(20, padRight(20, ""))); // Company Discretionary Data (Alphameric)[20] *Unused for now
        writer.write(padRight(10, ach.getCompanyId())); // Company Identification (Alphameric)[10]
        writer.write(entryClassCode); // Standard Entry Class Code (Alphameric)[3]  *CCD
        writer.write(padRight(10, entryDesc)); // Company Entry Description (Alphameric)[10]
        writer.write("      "); // Company Descriptive Date (YYMMDD)  *Used for display to receiver
        writer.write(dt.plusDays(1).format(dateFormatter)); // Effective Entry Date (YYMMDD) next business day?  *When you intend a batch of entries to be settled
        writer.write("000"); // Settlement Date (Numeric)[3] *inserted by ACH operator
        writer.write("1"); // Originator Status Code (Alphameric)[1]  *Depository Financial Institution
        writer.write(pad(8, ach.getODFI())); // Originating DFI Id (TTTTAAAA)
        writer.write(pad(7, batchCount)); //Batch Number (Numeric)[7]
        writeLine();
    }

    /**
     *
     * @param transactionCode
     * @param routing
     * @param number
     * @param name
     * @param amount
     * @param paymentId
     * @param odfi
     * @throws Exception
     *
     * RecordTypeCodeM(1) TransactionCodeM(2-3) ReceivingDFIIdentification#M(4-11) CheckDigitM(12) DFIAccountNumberR(13-29)
     * AmountM(30-39) IndividualIdentificationNumberO(40-54)
     * IndividualNameR(55-76)
     * DiscretionaryDataO(77-78) AddendaRecordIndicatorM(79) TraceNumberM(80-94)
     */
    private void writeEntry(String transactionCode, String routing, String number, String name, double amount, String paymentId, long odfi) throws Exception {
        checkFileControlWritten();
        if (!batchOpen) {
            throw new RuntimeException("Must open a batch by writing a batch header before adding entries");
        }
        rowCount++;
        batchEntries++;
        fileEntries++;
        batchCredits += Math.floor(amount * 100);
        batchHash += (NumberUtils.createInteger(routing.substring(0, 8).replaceFirst("^0+(?!$)", ""))); // Sum of the first 8 digits of the routing number

        writer.write("6"); // Record Type
        writer.write(transactionCode); // Transaction Code (Numeric)[2] | 22 - credit | 32 - savings credit
        writer.write(routing); // DFI id (TTTTAAAA) + Check Digit (Numeric) *Routing number
        writer.write(padRight(17, number)); // DFI account number (Alphameric)[17] * Account number
        writer.write(pad(10, amount)); // [10]Amount: ($$$$$$$$¢¢)
        writer.write(padRight(15, paymentId.replace("-", ""))); // Identification Number (Alphameric)[15]  *Using payout batch id
        writer.write(truncate(22, padRight(22, (stripQuotes ? name.replace("'", "") : name)))); // Receiving Individual/Company name (Alphameric)[22]
        writer.write(padRight(2, "")); // Discretionary Data (Alphameric)[2] *Unused for now
        writer.write("0"); // Addenda Record Indicator (0 or 1)[1] *Not used for now
        // Trace Number (Numeric)[15] ODFI + Incremental
        writer.write(pad(8, odfi));
        writer.write(pad(7, fileEntries));
        writeLine();
    }

    /**
     *
     * @param ach
     * @throws Exception
     *
     * RecordTypeCodeM(1) ServiceClassCodeM(2-4) Entry/AddendaCountM(5-10) EntryHash(11-20) TotalDebit$AmountM(21-32) CompanyIDNumberM(45-54)
     * MessageAuthenticationCodeO(55-73)
     * ReservedN/A(74-79)
     * OriginatingDFIIdentification#M(80-87) BatchNumberM(88-94)
     */
    private void writeBatchControl(ACH ach) throws Exception {
        checkFileControlWritten();
        if (!batchOpen) {
            throw new RuntimeException("No batch open to write a control record for");
        }
        batchOpen = false;
        rowCount++;
        fileCredits += batchCredits;
        fileHash += batchHash;

        writer.write("8220"); // Record Type | Service Code: Credits Only
        writer.write(pad(6, batchEntries)); // Entry/Addenda Count (Numeric)[6]
        writer.write(truncateLeft(10, pad(10, batchHash))); //Entry Hash: (Numeric)[10] - clip the left side
        writer.write(pad(12, 0)); // Total Batch Debit Amount ($$$$$$$$$$¢¢) *Unused for now
        writer.write(pad(12, batchCredits)); // Total Batch Credits Amount ($$$$$$$$$$¢¢)
        writer.write(padRight(10, ach.getCompanyId())); // Company Identification (Alphameric)[10]
        writer.write(padRight(19, "")); // Message Authentication Code (Alphameric)[19]  *Unused for now
        writer.write(padRight(6, "")); // Reserved (Blank)[6]
        writer.write(pad(8, ach.getODFI())); // ODFI id (TTTTAAAA)[8]
        writer.write(pad(7, batchCount)); //Batch Number (Numeric)[7]

        // Reset batch info counters
        batchEntries = 0;
        batchCredits = 0;
        batchHash = 0;
        writeLine();
    }

    private void writeFileControl() throws Exception {
        if (!fileHeaderWritten) {
            throw new RuntimeException("Must write a paymentFile header first");
        }
        if (batchOpen) {
            throw new RuntimeException("Cannot close a paymentFile with a batch open");
        }
        checkFileControlWritten();
        fileControlWritten = true;
        rowCount++;

        writer.write("9");
        writer.write(pad(6, batchCount)); // Batch Count (Numeric)[6]
        writer.write(pad(6, (rowCount / 10 + (rowCount % 10 == 0 ? 0 : 1)))); // Block Count (Numeric)[6]
        writer.write(pad(8, fileEntries)); // Entry/Addenda Count (Numeric)[8]
        writer.write(truncateLeft(10, pad(10, fileHash))); // File Entry Hash (Numeric)[10] <- Sum of batch entry hashes - clip left
        writer.write(pad(12, 0)); // Total Debit Amount ($$$$$$$$$$¢¢)  *Unused for payouts
        writer.write(pad(12, fileCredits)); // Total Credit Amount ($$$$$$$$$$¢¢)
        writer.write(padRight(39, " ")); // Reserved block
        writeBlockClose();
        writer.flush();
    }

    private void checkFileControlWritten() {
        if (fileControlWritten) {
            throw new RuntimeException("Cannot write after paymentFile control");
        }
    }

    private void writeBlockClose() throws Exception {
        int remaining = (int) (rowCount % 10);
        if (remaining > 0) {
            for (int i = 0; i < (10 - remaining); i++) {
                writeLine(); // Don't write line at eof
                writer.write(DUMMY_BLOCK);
            }
        }
    }

    public long getBatchEntries() {
        return batchEntries;
    }

    public int getBatchCount() {
        return batchCount;
    }

    public long getFileEntries() {
        return fileEntries;
    }

    public long getFileCredits() {
        return fileCredits;
    }

    private void writeLine() throws Exception {
        writer.write("\n");
    }

    private String truncate(int maxLength, String value) {
        if (value.length() <= maxLength)
            return value;
        return value.substring(0, maxLength);
    }

    private String truncateLeft(int maxLength, String value) {
        if (value.length() <= maxLength)
            return value;
        return value.substring((value.length() - maxLength), value.length());
    }

    private String pad(int length, int value) {
        return String.format("%1$0" + length + "d", value); // Numeric aligns right pad with 0's
    }

    private String pad(int length, long value) {
        return String.format("%1$0" + length + "d", value); // Numeric aligns right pad with 0's
    }

    // Auto convert money to cents
    private String pad(int length, double value) {
        return pad(length, (int)(value * 100)); // Numeric aligns right pad with 0's
    }

    private String padRight(int length, String value) {
        return String.format("%1$-" + length + "s", value); // Alphameric aligns left
    }

    private String padLeft(int length, String value) {
        return String.format("%1$" + length + "s", value);
    }

    private void reset() {
        fileCredits = 0;
        batchCredits = 0L;
        batchCount = 0;
        batchHash = 0;
        fileHash = 0;
        rowCount = 0;
        fileEntries = 0;
        batchEntries = 0;
    }

    @Override
    public void close() throws IOException {
        if (reader != null) {
            reader.close();
        }
        if(writer != null){
            writer.close();
        }
        //Delete local payment file, if it is not local storage
        //local NACHA file is used by Controller, therefore, we need to delete it in controller
        if(!isLocalStorage){
            logger.info("NachaConverter: deleting local files...");
            if(!paymentFile.delete()){
                logger.error(String.format("NachaConverter close(): fail to delete local payment file: %s", paymentFile.getAbsoluteFile()));
            }
            if(!nachaFile.delete()){
                logger.error(String.format("NachaConverter close(): fail to delete local NACHA file: %s", nachaFile.getAbsoluteFile()));
            }
        }
    }
}
