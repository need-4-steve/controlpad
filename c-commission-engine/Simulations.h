#ifndef _SIMULATIONS_H
#define _SIMULATIONS_H

#include "db.h"
#include "dbbulk.h"
#include "dbplus.h"
#include "debug.h"
#include "validate.h"

#include <string>

// Easy seed options //
#define COPY_ONLY_USERS			1 // Seed receipts //
#define COPY_ONLY_RECEIPTS		2 // Seed users //
#define COPY_USERS_RECEIPTS		3 // Copy both. Seed neither //
#define SEED_BOTH				4 // Seed both receipts and users //
#define COPY_SEED_NONE			5 // Neither copy or seed /

#define SIM_SEED_WIDE			1
#define SIM_SEED_DEEP			2

#define SIM_USER_MAX 			1000000
#define SIM_RECEIPT_MAX	 		1000000
#define SIM_DEEP_LVL1_MIN		5
#define SIM_DEEP_LVL1_MAX		10
#define SIM_WIDE_LVL1_MIN		80
#define SIM_WIDE_LVL1_MAX		100
#define SIM_RECEIPT_PERCENT		100
#define SIM_MIN_PRICE			1
#define SIM_MAX_PRICE			5000

#define SIM_MIN_FRAUD			1000
#define SIM_MAX_FRAUD			5000
#define SIM_MIN_FRAUD2			1
#define SIM_MAX_FRAUD2			2

/////////////////////////////////////
// Package bankaccount information //
/////////////////////////////////////
class CSimulations : private CDbPlus, CDbBulk
{
public:
	CSimulations();
	bool CopySeed(CDb *pLiveDB, CDb *pSimDB, int socket, int system_id, int seed_type, int copyseedoption, int users_max, 
				int receipts_max, int min_price, int max_price, string start_date, string end_date);
	const char *Run(CDb *pLiveDB, CDb *pSimDB, int socket, int system_id, string start_date, string end_date);

	// Allow commission engine access these for the command line //
	bool SeedUsers(CDb *pDB, int socket, int system_id, int usersmax, int min_lvl_one, int max_lvl_one);
	bool SeedReceipts(CDb *pDB, int socket, int system_id, int users_max, int receipts_max, int min_price, int max_price, string start_date);
	bool SeedFraudReceipts(CDb *pDB, int socket, int system_id, int user_id, int receipts_max, int min_price, int max_price, string start_date);
	
	// This needed if using Copy Specific tables below //
	bool SetSimDbPtr(CDb *pSimDb);

//private:
	bool CopyFromLive(CDb *pLiveDB, int socket, int system_id, string start_date, string end_date);
	bool PurgeTable(int socket, int system_id, string tablename, string id_column);
	bool CopyTable(CDb *pLiveDB, int socket, int system_id, string tablename, string seqname, string system_id_column, list <string> columns, string endsql);

	bool CopySystemUser(CDb *pLiveDB, int socket, int sysuser_id);
	bool CopySystem(CDb *pLiveDB, int socket, int system_id);
	bool CopyRankRules(CDb *pLiveDB, int socket, int system_id);
	bool CopyCommRules(CDb *pLiveDB, int socket, int system_id);
	bool CopyUsers(CDb *pLiveDB, int socket, int system_id);
	bool CopyReceipts(CDb *pLiveDB, int socket, int system_id, string start_date, string end_date);

	string BuildUpline(string user_id, int upline_type);

	bool IsFile(string filename);

	CDb *m_pSimDB;

	map <string, string> m_ParentLookup;
	map <string, string> m_SponsorLookup;

	string m_Json;
};

#endif