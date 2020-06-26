#ifndef _EZNACHA_H
#define _EZNACHA_H

#include "debug.h"
#include "ezACH.h"
#include "bankaccount.h"

#include <stdio.h>
#include <iostream>
#include <fstream>
#include <string>

// Limits determined by character limit in nacha format
#define MAX_CREDITS 999999999999 // money in cents $$$$$$$$$$cc
#define MAX_DEBITS 999999999999
#define MAX_FILE_ENTRIES 99999999
#define DUMMY_BLOCK "9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999" // 94 '9's

///////////////////////////////////////////////
// Handle all of our encryption and API keys //
///////////////////////////////////////////////
class CezNacha : CDebug
{
public:
	CezNacha();

	bool NachaFile(const char *fileName, char fileId, const char *clientId);
	bool WriteFileHeader(CezACH *pACH, int fileIdModifier);
	bool WriteBatchHeader(const char *batchNumber, CezACH *pACH, const char *entryDesc);
	bool WriteEntry(CBankAccount *paccount, double amount, const char *paymentId, long odfi);
	bool WriteBatchControl(int batchNumber, CezACH *pACH);
	bool WriteFileControl();
	bool CheckFileControlWritten();
	bool WriteBlockClose();

private:
	std::ofstream m_File;
	std::string m_DT;
    
    bool m_FileHeaderWritten;
    bool m_BatchOpen;
    bool m_FileControlWritten;

    long m_FileCredits;  // File control record, sum of all credits for file
    long m_BatchCredits; // Batch control record, sum of all credits for that batch
    int m_BatchCount; // Number of batches. You only really need one batch because the transaction/batch type won't change
    long m_BatchHash; // The sum of all entry account routing numbers (first 8 digits only). If greater than 10 characters, clip the left side
    long m_FileHash; // The sum of all batchHashes. If greater than 10 clip left side
    long m_RowCount; // Used to determine number of blocks (10 lines per block)
    long m_FileEntries; // File control record info
    long m_BatchEntries; // Batch control record info

	std::string Truncate(bool left, int maxLength, std::string value);
	int MakeCheckSum(int length, std::string value);
	std::string Pad0(int length, std::string value);
	std::string Pad0(int length, double value);
	std::string PadRight(int length, std::string value);
	std::string PadLeft(int length, std::string value);
	std::string GetDateTime();
	std::string GetDate();
	std::string GetDatePlusDay();
	std::string Capitalize(std::string mixstr);

	// Internal buffers //
    std::string m_TimeBuff;
    std::string m_CapStr;
};

#endif
