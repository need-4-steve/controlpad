#include "ezNacha.h"

#include <iostream>     // std::cout, std::endl
#include <iomanip>      // std::setw
#include <math.h>       // floor 
#include <sstream>      // std::stringstream
#include <stdlib.h> 	// atoi

/////////////////
// Constructor //
/////////////////
CezNacha::CezNacha()
{
	m_FileHeaderWritten = false;
    m_BatchOpen = false;
    m_FileControlWritten = false;

    m_FileCredits = 0;  // File control record, sum of all credits for file
    m_BatchCredits = 0; // Batch control record, sum of all credits for that batch
    m_BatchCount = 0; // Number of batches. You only really need one batch because the transaction/batch type won't change
    m_BatchHash = 0; // The sum of all entry account routing numbers (first 8 digits only). If greater than 10 characters, clip the left side
    m_FileHash = 0; // The sum of all batchHashes. If greater than 10 clip left side
    m_RowCount = 0; // Used to determine number of blocks (10 lines per block)
    m_FileEntries = 0; // File control record info
    m_BatchEntries = 0; // Batch control record info
}

/////////////////////////
// Open the nacha file //
/////////////////////////
bool CezNacha::NachaFile(const char *fileName, char fileId, const char *clientId) //throws IOException
{
    //File clientDir = new File(NACHA_FILEPATH_BASE + clientId + File.separator);
    //if (!clientDir.exists() && !clientDir.mkdir())
    //    return Debug(DEBUG_ERROR, "CezNacha::NachaFile - ");

    //String filePath = clientDir.getAbsolutePath() + File.separator + fileName;
   
    m_DT = GetDateTime();
    m_File.open(fileName);
    if (!m_File.is_open())
    	return Debug(DEBUG_ERROR, "CezNacha::NachaFile - m_File is not open");

    return true;
}

///////////////////////////
// Write the File Header //
///////////////////////////
bool CezNacha::WriteFileHeader(CezACH *pACH, int fileIdModifier)
{
    if (m_FileHeaderWritten)
        return Debug(DEBUG_ERROR, "CezNacha::WriteFileHeader - Nacha file can have only one header");
    
    m_FileHeaderWritten = true;
    m_RowCount++;

    m_File << "101"; // Record type and priority code
    m_File << PadLeft(10, pACH->getDestinationRoute());
    m_File << PadLeft(10, pACH->getOriginRoute());
    m_File << GetDateTime(); // YYMMDDHHMM
    m_File << fileIdModifier; // File Id Modifier (A-Z or 0-9)[1]
    m_File << "094101"; // Block Size: 094, Blocking Factor: 10, Format Code: 1
    m_File << PadRight(23, Capitalize(pACH->getDestinationName()));
    m_File << PadRight(23, Capitalize(pACH->getOriginName()));
  	m_File << std::setw(8); // Reference Code: (Alphameric)[8]  *unused?
  	m_File << "\n"; // New Line //
  	return true;
}

////////////////////////////
// Write the batch header //
////////////////////////////
bool CezNacha::WriteBatchHeader(const char *batchNumber, CezACH *pACH, const char *entryDesc)
{
    if (CheckFileControlWritten() == false)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteFileHeader - CheckFileControlWritten() == false");
    if (m_BatchOpen)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteFileHeader - Must close a nacha batch with a control record before creating a new header");

    m_BatchOpen = true;
    m_RowCount++;
    m_BatchCount++;

    m_File << "5220"; // Record Type | Service Code: Credits Only
    //m_File << Truncate(true, 23, ach->getCompanyName());
    m_File << PadRight(16, Capitalize(pACH->getCompanyName()));
    m_File << std::setw(20); // Company Discretionary Data (Alphameric)[20] *Unused for now
    m_File << PadRight(10, Capitalize(pACH->getCompanyId())); // Company Identification (Alphameric)[10]
    m_File << "CCD"; // Standard Entry Class Code (Alphameric)[3]  *CCD
    m_File << PadRight(10, entryDesc); // Company Identification (Alphameric)[10]
    m_File << GetDate(); // Company Descriptive Date (YYMMDD)  *Used for display to receiver
    m_File << GetDatePlusDay(); // Effective Entry Date (YYMMDD) next business day?  *When you intend a batch of entries to be settled
    m_File << std::setw(3); // Settlement Date (Numeric)[3] *inserted by ACH operator, leave blank
    m_File << "1"; // Originator Status Code (Alphameric)[1]  *Depository Financial Institution
    m_File << Pad0(8, pACH->getODFI()); // Originating DFI Id (TTTTAAAA)
    m_File << Pad0(7, batchNumber); //Batch Number (Numeric)[7]
    m_File << "\n";

    return true;
}

////////////////////////////////////////
// Write an entry into the nacha file //
////////////////////////////////////////
bool CezNacha::WriteEntry(CBankAccount *paccount, double amount, const char *paymentId, long odfi)
{
    if (CheckFileControlWritten() == false)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteEntry - CheckFileControlWritten() == false");
    if (!m_BatchOpen)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteEntry - Must open a batch by writing a batch header before adding entries");
        
    m_RowCount++;
    m_BatchEntries++;
    m_FileEntries++;
    m_BatchCredits += floor(amount * 100);
    m_BatchHash += MakeCheckSum(8, paccount->m_RoutingNumber);

    m_File << "6"; // Record Type
    m_File << paccount->m_AccountType; /// Transaction Code (Numeric)[2] | 22 - credit | 32 - savings credit
    m_File << paccount->m_RoutingNumber; // DFI id (TTTTAAAA) + Check Digit (Numeric) *Routing number
  	m_File << PadRight(17, paccount->m_AccountNumber); //getDFINumber()); // DFI account number (Alphameric)[17] * Account number
  	m_File << Pad0(10, amount); // [10]Amount: ($$$$$$$$¢¢)
    m_File << PadRight(15, paymentId); // Identification Number (Alphameric)[15]  *Using payout batch id
    m_File << PadRight(22, paccount->m_HolderName); // Receiving Individual/Company name (Alphameric)[22]
    m_File << std::setw(2); // Discretionary Data (Alphameric)[2] *Unused for now
    m_File << "0"; // Addenda Record Indicator (0 or 1)[1] *Not used for now
    
    // Trace Number (Numeric)[15] ODFI + Incremental
    m_File << Pad0(8, odfi);
    m_File << Pad0(7, m_FileEntries);
    m_File << "\n";

    return true;
}

//////////////////////////
// Handle batch control //
//////////////////////////
bool CezNacha::WriteBatchControl(int batchNumber, CezACH *pACH)
{
    if (CheckFileControlWritten() == false)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteBatchControl - CheckFileControlWritten() == false");
    if (!m_BatchOpen)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteBatchControl - No batch open to write a control record for");
       
    m_BatchOpen = false;
    m_RowCount++;
    m_FileCredits += m_BatchCredits;
    m_FileHash += m_BatchHash;

    m_File << "8220"; // Record Type | Service Code: Credits Only
    m_File << Pad0(6, m_BatchEntries); // Entry/Addenda Count (Numeric)[6]
    m_File << Truncate(true, 10, Pad0(10, m_BatchHash)); //Entry Hash: (Numeric)[10] - clip the left side
    m_File << std::setw(12); // Total Batch Debit Amount ($$$$$$$$$$¢¢) *Unused for now
    m_File << Pad0(12, m_BatchCredits); // Total Batch Credits Amount ($$$$$$$$$$¢¢)
    m_File << PadRight(10, pACH->getCompanyId()); // Company Identification (Alphameric)[10]
    m_File << std::setw(19); // Message Authentication Code (Alphameric)[19]  *Unused for now
    m_File << std::setw(6); // Reserved (Blank)[6]
    m_File << Pad0(8, pACH->getODFI()); // ODFI id (TTTTAAAA)[8]
    m_File << Pad0(7, batchNumber); //Batch Number (Numeric)[7]
    m_File << "\n";

    // Reset batch info counters
    m_BatchEntries = 0;
    m_BatchCredits = 0;
    m_BatchHash = 0;

    return true;
}

/////////////////////////////
// Handle the file control //
/////////////////////////////
bool CezNacha::WriteFileControl()
{
    if (!m_FileHeaderWritten)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteBatchControl - Must write a file header first");
    if (m_FileEntries == 0)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteBatchControl - No entries exist");
	if (m_BatchOpen)
		return Debug(DEBUG_ERROR, "CezNacha::WriteBatchControl - Cannot close a file with a batch open");

    if (CheckFileControlWritten() == false)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteBatchControl - CheckFileControlWritten() == false");
    
    m_FileControlWritten = true;
    m_RowCount++;

    m_File << "9";
    m_File << Pad0(6, m_BatchCount); // Batch Count (Numeric)[6]
    m_File << Pad0(6, (m_RowCount / 10 + (m_RowCount % 10 == 0 ? 0 : 1))); // Block Count (Numeric)[6]
    m_File << Pad0(6, m_FileEntries);
    m_File << Truncate(true, 10, Pad0(10, m_FileHash)); // File Entry Hash (Numeric)[10] <- Sum of batch entry hashes - clip left
    m_File << Pad0(12, 0); // Total Debit Amount ($$$$$$$$$$¢¢)  *Unused for payouts
    m_File << Pad0(12, m_FileCredits); // Total Credit Amount ($$$$$$$$$$¢¢)
   	WriteBlockClose();

    return true;
}

/////////////////////////////
// Do a file control check //
/////////////////////////////
bool CezNacha::CheckFileControlWritten()
{
    if (m_FileControlWritten)
    	return Debug(DEBUG_ERROR, "CezNacha::WriteEntry - Cannot write after file control");
       
    return true;
}

///////////////////////////
// Close the write block //
///////////////////////////
bool CezNacha::WriteBlockClose()
{
    int remaining = (int) (m_RowCount % 10);
    if (remaining > 0)
    {
        for (int i = 0; i < (10 - remaining); i++)
        {
            m_File << "\n"; // Don't write line at eof
            m_File << DUMMY_BLOCK;
        }
    }

    m_File.close();
    return true;
}

/*
    public boolean delete() {
        return file != null && file.delete();
    }

    @Override
    public void close() throws IOException {
        if (fileWriter != null) {
            fileWriter.close();
        }
        fileWriter = null;
    }
*/
/*
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
*/

///////////////////////
// Truncate a string //
///////////////////////
std::string CezNacha::Truncate(bool left, int maxLength, std::string value)
{
    if (left)
    {
        return value.substr((value.size() - maxLength), value.size());
    } 
    else
    {
        return value.substr(0, maxLength);
    }
}

////////////////////////////////////////////////////////////////////////////
// Make Checksum based on number of characters to sum together from value //
////////////////////////////////////////////////////////////////////////////
int CezNacha::MakeCheckSum(int length, std::string value)
{
	int retval = 0;
	int index;
	for (index=0; index < length; index++)
	{
		char tmpstr[100];
		memset(tmpstr, 0, 100);
		sprintf(tmpstr, "%c", value.at(index));
		retval += atoi(tmpstr);
	}

	return retval;
}

/////////////////////////////////
// Auto convert money to cents //
/////////////////////////////////
std::string CezNacha::Pad0(int length, std::string value)
{
	std::string retstr;
	int maxcount = (int)length-value.size();
	int index;
	for (index=0; index < maxcount; index++)
	{
		retstr += "0";
	}

	retstr += value;
	return retstr;
}

////////////////////////
// Handle double type //
////////////////////////
std::string CezNacha::Pad0(int length, double value)
{
	std::stringstream ss;
	ss << value;
	return Pad0(length, ss.str());
}

//////////////////////////////////////////
// Add padding to the left for a string //
//////////////////////////////////////////
std::string CezNacha::PadRight(int length, std::string value)
{
    std::stringstream ss;
    std::string retstr;

    ss << value << std::setw(length - value.size());
    retstr = ss.str();
    return retstr;
}

//////////////////////////////////////////
// Add padding to the left for a string //
//////////////////////////////////////////
std::string CezNacha::PadLeft(int length, std::string value)
{
    std::stringstream ss;
    std::string retstr;

    ss << std::setw(length - value.size()) << value;
    retstr = ss.str();
    return retstr;
}

////////////////////////////////
// Grab the current date/time //
////////////////////////////////
std::string CezNacha::GetDateTime()
{
	time_t rawtime;
    tm* timeinfo;
    char buffer[80];

    time(&rawtime);
    timeinfo = localtime(&rawtime);

    //std::strftime(buffer,80,"%y-%m-%d-%H-%M-%S",timeinfo);
    strftime(buffer, 80, "%y%m%d%H%M", timeinfo);

    m_TimeBuff = buffer;
    return m_TimeBuff;
}

////////////////////////////////
// Grab the current date/time //
////////////////////////////////
std::string CezNacha::GetDate()
{
	time_t rawtime;
    tm* timeinfo;
    char buffer[80];

    time(&rawtime);
    timeinfo = localtime(&rawtime);

    //std::strftime(buffer,80,"%y-%m-%d-%H-%M-%S",timeinfo);
    strftime(buffer, 80, "%y%m%d", timeinfo);

    m_TimeBuff = buffer;
    return m_TimeBuff;
}

////////////////////////////////
// Grab the current date/time //
////////////////////////////////
std::string CezNacha::GetDatePlusDay()
{
	time_t rawtime;
    tm* timeinfo;
    char buffer[80];

    time(&rawtime);

    rawtime += (24 * 60 * 60); // Add 1 day //

    timeinfo = localtime(&rawtime);

    //std::strftime(buffer,80,"%y-%m-%d-%H-%M-%S",timeinfo);
    strftime(buffer, 80, "%y%m%d", timeinfo);

    m_TimeBuff = buffer;
    return m_TimeBuff;
}

/////////////////////////
// Capitolize a string //
/////////////////////////
std::string CezNacha::Capitalize(std::string mixstr)
{
	m_CapStr.clear(); // Clear internal buffer //

	int maxcount = (int)mixstr.size();
	int index;
	for (index=0; index < maxcount; index++)
	{
		m_CapStr += toupper(mixstr.at(index));
	}

	return m_CapStr; // Return internal buffer //
}