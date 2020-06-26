#ifndef _SOCKETINV_DB_H
#define _SOCKETINV_DB_H

//////////
// db.h //
//////////

#include "Compile.h"

// Handle compiling postgres //
#ifdef COMPILE_POSTGRES
#ifdef COMPILE_UBUNTU
#include <postgresql/libpq-fe.h>
#endif
#ifdef COMPILE_OSX
#include <libpq-fe.h>
#endif
#endif

// Handle compiling MySQL //
#ifdef COMPILE_MYSQL
#include <mysql/mysql.h>
#endif

#include <list>
#include <map>
#include <string>
#include <sstream>

#include "debug.h"
#include "ezJson.h"
#include "convert.h"

#include "receipts.h"
#include "levels.h"
#include "user.h"
#include "rulesrank.h"
#include "rulesrankbonus.h"
#include "rulescomm.h"
#include "rulesbasiccomm.h"
#include "system.h"
#include "bankaccount.h"
#include "payuser.h"
#include "rulespool.h"

#include "validate.h"
#include "ezSettings.h"
#include "dbbulk.h"

#include "ConnPool.h"

#include "rankgenbonus.h"

#include "faststart.h"

// Only a couple tweaks and we can allow both systems //
// Plus I already have test data for mysql  		  //
#define DB_MYSQL				1
#define DB_POSTGRES				2

#define MAX_QUERY_LEN			2049 // Predefined buffer size //
#define MAX_CMD_LEN				256
#define MAX_FIELD_LEN			1024 // Need for internal buffer in GetFirstDB //

#define MASTER_ACCOUNT			"master@commissions.com"
#define INITAL_MASTER_PASSWORD	"my.co#5YvhgW34&&.gf:gf*()23oties.com"

#define UPLINE_PARENT_ID		1
#define UPLINE_SPONSOR_ID 		2
#define UPLINE_ADVISOR_ID 		3

#define SESSION_TIMEOUT			24 // Minutes //

#define MAX_SQL_APPEND			5000 // Maximum bulk INSERTS on one statement //	

/*
///////////////////////////////////////////////
// Container while we build Lvl1 rank values //
///////////////////////////////////////////////
class CStatsLvlRank
{
public:
	CStatsLvlRank();
	string m_UserID;
	int m_Rank;
	int m_Total;
};

//////////////////////////////////////////////
// Container while we build Leg rank values //
//////////////////////////////////////////////
class CStatsLegRank
{
public:
	CStatsLegRank();
	string m_UserID;
	int m_Rank;
	int m_Total;
	int m_Generation;
};
*/

////////////////////////////////
// Need temp storage for cron //
////////////////////////////////
class CTmpCommission
{
public:
	void SetVars(int id, int commtype, string url, string username, string password);
	int m_ID;
	int m_CommType;
	int m_BatchID;
	string m_URL;
	string m_Username;
	string m_Password;
};

/////////////////////////////////////////
// Handle speeding up achvbonus lookup //
/////////////////////////////////////////
class CAchvLookup
{
public:
	map <int, double> m_Amount;
};

///////////////////////////////////
// Handle Grandtotal information //
///////////////////////////////////
class CGrandTotal
{
public:
	int m_ID;
    string	m_UserID;
    double m_Amount;
};

///////////////////////////////////
// Need on return of GetReceipts //
///////////////////////////////////
class CReceiptTotal
{
public:
	CReceiptTotal();
	double m_WholesaleTotal;
	double m_RetailTotal;
};

/////////////////////////////////////////////
// Manage all database calls in this class //
/////////////////////////////////////////////
class CDb : public CezJson, CConvert, CDbBulk, CValidate
{
public:
	CDb(); // Run connection here //
	~CDb(); // Disconnect from database here //

	// Speed up transactions? //
	bool Begin(int socket);
	bool Commit(int socket);

	string LookupIP(const char *hostname);
	bool Connect(CezSettings *psettings);
	bool ConnectSim(CezSettings *psettings);
	bool Disconnect();
	bool TestJson(const char *json1, const char *json2);
	bool IsConnected(); // Find out if the database is connected //

	///////////////////////////
	// Linked List functions //
	///////////////////////////
	int GetUserCount(int socket);
	bool GetBasicCommRules(int socket, int system_id, list <CRulesBasicComm> *pRulesBasicComm);
	int GetRankRules(int socket, int system_id, list <CRulesRank> *pRulesRank, string tablename);
	bool GetCommRules(int socket, int system_id, list <CRulesComm> *pRulesComm);
	bool GetUsers(int socket, int system_id, bool include_disabled, map <string, CUser> &UsersMap, int upline, const char *start_date, const char *end_date);
	bool GetRanks(int socket, int system_id, int batch_id, map <string, CUser> &UsersMap);
	bool GetUserEnds(int socket, int system_id, map <string, CUser> &UsersMap, const char *start_date, const char *end_date);
	bool GetAchvBonuses(int socket, int system_id); // This needed to speed up lookup //
	bool GetRankBonusRules(int socket, int system_id, list <CRulesRankBonus> *pRulesRankBonus); // This needed to speed up lookup //
	int GetReceiptCount(int socket, int system_id, const char *start_date, const char *end_date);
	double GetReceipts(int socket, int system_id, map <string, CUser> &UsersMap, list <CReceipt> &ReceiptsLL, const char *start_date, const char *end_date, CReceiptTotal *preceipts);
	bool GetRankGenBonus(int socket, int system_id, std::list <CRankGenBonus> *pRankGenBonus);
	bool GetStatLvl1Rank(int socket, int system_id, int batch_id, int rank, map <string, int> &pStatsLvlRank);
	bool GetStatLegRank(int socket, int system_id, int batch_id, int generation, int rank, map <string, int> &pStatsLegRank);
	bool GetFastStartRules(int socket, int system_id, list <CFastStartRules> *pFastStartRules);

	string MemLookupTitle(int rank, list <CRulesRank> *pRulesRank);

	//bool ReceiptHybrid(bool pretend, int system_id, void *pcomm, const char *start_date, const char *end_date);
	//bool ReceiptBreakaway(bool pretend, int system_id, void *pcomm, const char *start_date, const char *end_date);

	bool GetPoolRules(int socket, int system_id, int poolpot_id, list <CRulesPool> *pRulesPool);
	bool GetSystemsUsed(int socket, int system_id, list <int> *pSystemsUsed);
	bool GetSystemsSpeed(int socket, int system_id, int start_sys_id, int end_sys_id, list <CSystem> *pSystemsUsed);
	bool GetGroupUsed(int socket, int system_id, map <string, CUser> &UsersMap);
	bool GetGrandTotals(int socket, int system_id, int batch_id, list <CGrandTotal> *pGrandTotals);
	bool GetCMCommRules(int socket, int system_id, list <CRulesComm> *pRulesComm);
	bool GetLedgerRecs(int socket, int system_id, int batch_id, int ledger_type, list <CGrandTotal> *pGrandTotals);

	string GetSysUserSalt(int socket, int sysuser_id);
	bool UpdateSysUserApiKey(int socket, int sysuser_id, const char *apikey);

	bool IsGroupUsedRank(int socket, int system_id);
	bool SetRankOverride(int rank);
	bool AddRank(int socket, int system_id, int batch_id, CUser *puser, int rank, bool breakage, double achvbonus, int rankrule_id);
	bool AddRankBonus(int socket, int system_id, int batch_id, string user_id, int rank, double bonus);

	bool IsAchvBonusPaid(int system_id, const char *user_id, int rank);
	bool SetSyncGrand(int socket, int grand_id);
	bool IsCheckMatchRule(int socket, int system_id);
	bool ResetCheckmatch(int socket, int system_id, int batch_id);

	int GetAltCore(int socket, int system_id);
	int GetBaseSystemID(int socket, int system_id);
	int GetBaseBatchID(int socket, int system_id);
	int GetInfinityCap(int socket, int system_id);
	int GetGenLimit(int socket, int system_id);
	bool AddReceiptBreakdown(int socket, int system_id, int batch_id, int id, int receipt_id, const char *user_id, double amount, int commrule_id, int generation, double percent, bool infinitybonus, int comm_type, string metadata_onadd, int inv_type, double dollar);
	int AddBatch(bool pretend, int socket, int system_id, const char *start_date, const char *end_date);
	bool UpdateBatch(bool pretend, int socket, int system_id, int batch_id, double receipts_wholesale, double receipts_retail, double commissions, double achv_bonuses, double bonuses, double pools);
	bool AddCommission(int socket, int system_id, int batch_id, const char *user_id, double amount); 
	bool AddBinaryLedger(int socket, int system_id, int batch_id, const char *user_id, double commission, double firstleg, double secondleg, double groupsales);
	bool AddPoolPayout(int socket, int system_id, int batch_id, int poolpot_id, const char *user_id, double amount);
	int GetSystemCommType(int socket, int system_id); // Get the commission type //
	string GetSystemCompression(int socket, int system_id); // Is compression enabled on the system? //
	string GetSignupBonus(int socket, int system_id); // Get the signup bonus in ref to a system //
	bool UpdateGrandTotal(int batch_id, int system_id, const char *user_id, double amount);

	bool DoExtBreakdown(int socket, int system_id, int batch_id);

	bool AddUserStat(bool month, int socket, int system_id, int batch_id, CUser *puser, string first_id, double firstsales, string second_id, double second_sales);
	/*
	bool AddUserStat(bool month, int socket, int system_id, int batch_id, string user_id, double group_wholesale_sales, double group_retail_sales, double group_used, 
		double customer_wholesale_sales, double customer_retail_sales, double affiliate_wholesale_sales, double affiliate_retail_sales, 
		int signup_count, int affiliate_count, int customer_count, 
		double LVL1_personal_sales, int LVL1_signup_count, int LVL1_affiliate_count, int LVL1_customer_count,
		double firstleg, double secondleg, string firstleg_id, string secondleg_id);
	*/

	bool AddLedger(int socket, int system_id, int batch_id, const char *user_id, int ref_id, int ledger_type, int from_system_id, const char *from_user_id, double amount, int generation, const char *event_date);

	bool UpdatePoolPots(int socket, int poolpot_id, double receipts);

	//////////////////////////////////////
	// Commission Engine database calls //
	//////////////////////////////////////
	bool CronCommissions(); // Run the cron commissions calculations //
	bool CronCommMonth(int socket, int altcore, string dayofmonth, string mstartdate, string enddate);
	bool CronProcLoop(CConn *conn, int socket, const char *startdate, const char *enddate);
	bool CronCheckMatch(int socket, const char *start_date, const char *end_date);
	bool CurlUpdatedURL(int socket, int system_id, const char *updated_url, const char *updated_username, const char *updated_password);

	bool ClearBatch(int socket, int batch_id);
	bool ResetSysUserPassword(int socket, string sysuser_id, string password);

	// Rights //
	bool IsRightsSystem(int socket, int system_id, int sysuser_id); // Does sysuser have rights to the system? //
	bool IsUserRightsSystem(int socket, int system_id, string user_id); // Does user have rights to the system? //

	// Auth //
	int AuthAPIUser(int socket, const char *email, const char *api_key);
	int AuthSysUser(int socket, const char *email, const char *authpass, const char *ipaddress);
	int CheckSessionUser(int socket, const char *email, const char *sessionkey);

	// System users //
	const char *AddSystemUser(int socket, const char *firstname, const char *lastname, const char *email, const char *password, const char *ipaddress);
	//const char *LoginSystemUser(const char *email, const char *password, const char *ipaddress);
	const char *EditSystemUser(int socket, int sysuser_id, const char *email, const char *password, const char *ipaddress);
	const char *QuerySystemUsers(int socket);
	const char *DisableSystemUser(int socket, int sysuser_id);
	const char *EnableSystemUser(int socket, int sysuser_id);
	const char *ReissueApiKey(int socket, int sysuser_id);

	// Systems //
	const char *DisableSystem(int socket, int system_id);
	const char *EnableSystem(int socket, int system_id);

	// Receipts //
	const char *QueryReceiptSum(int socket, int system_id, string searchsql, string sqlend);
	const char *QueryBreakdown(int socket, int system_id, int receipt_id);
	const char *QueryBreakdownAlt(int socket, int system_id, string receipt_id, string searchsql, string sqlend);

	// Pool Pot //
	const char *RunPoolPot(int socket, int system_id, int poolpotid); // Needed for testing //

	// Commissions calculated //
	const char *QueryBatches(int socket, int system_id);
	const char *QueryBatchesAlt(int socket, int system_id, string searchsql, string sqlend);
	const char *QueryUserComm(int socket, int system_id, const char *user_id);
	const char *QueryBatchComm(int socket, int system_id, int batch_id);

	// These needed to continue on with payout //
	const char *QueryGrandPayout(int socket, int system_id, const char *authorized, string searchsql, string sqlend);
	const char *DisableGrandPayout(int socket, int system_id, int grand_id);
	const char *EnableGrandPayout(int socket, int system_id, int grand_id);

	const char *AuthGrandPayout(int socket, int system_id, int grand_id, const char *authorized); // Switch to ledger //
	const char *AuthGrandBulk(int socket, int system_id); // Switch to ledger //
	const char *SyncWithPayman(int socket, int system_id); // Switch to ledger //
	const char *SyncWithNacha(int socket, int system_id); // Switch to ledger //

	///////////////////////
	// Payment functions //
	///////////////////////
	const char *InitiateValidation(int socket, int system_id, const char *user_id, double amount1, double amount2); // Make entries for inital validation //
	const char *ValidateBankAccount(int socket, int system_id, const char *user_id, const char *amount1, const char *amount2); // Verify the entries are correct //
	const char *QueryUserPayments(int socket, int system_id, const char *user_id); 
	const char *QueryBatchPayments(int socket, int system_id, int batch_id);
	const char *GetNoPayUsers(int socket, int system_id, int batch_id);

	/////////////////
	// Check Match //
	/////////////////
	// Hidden from user (United) //
	const char *AddCheckMatch(int socket, int system_id, int batch_id, int match_rule_id, const char *user_id, const char *match_user_id, double amount, double percent);
	const char *EditCheckMatch(int socket, int system_id, int match_id, int batch_id, int match_rule_id, const char *user_id, const char *match_user_id, double amount, double percent);
	
	// User accessible //
	const char *QueryBatchCheckMatch(int socket, int system_id, int match_id);
	const char *QueryUserCheckMatch(int socket, int system_id, const char *user_id);
	const char *DisableCheckMatch(int socket, int system_id, int match_id);
	const char *EnableCheckMatch(int socket, int system_id, int match_id);

	///////////
	// Bonus //
	///////////
	const char *QueryBonusUser(int socket, int system_id, const char *user_id);
	bool BonusToLedger(int socket, int system_id, int batch_id, const char *start_date, const char *end_date);
	bool RankGenBonusToLedger(int socket, int system_id, int batch_id, const char *end_date);
	double GetBatchBonus(int socket, int system_id, const char *start_date, const char *end_date);

	////////////
	// Ledger //
	////////////
	// Hidden from user //
	const char *AddLedger(int socket, int system_id, const char *user_id, int trans_type, const char *trans_date, int from_system_id, int from_user_id, double from_amount);
	const char *EditLedger(int socket, int system_id, int ledger_id, const char *user_id, int trans_type, const char *trans_date, int from_system_id, int from_user_id, double from_amount);
	
	const char *RebuildLedgerTotals(bool pretend, int socket, int system_id);
	const char *RebuildReceiptTotals(bool pretend, int socket, int system_id);

	// These should only be user (United) accessable //
	// Why wasn't this finished implemented??? //
	const char *QueryUserLedger(int socket, int system_id, const char *user_id);
	const char *DisableLedger(int socket, int system_id, int ledger_id);
	const char *EnableLedger(int socket, int system_id, int ledger_id);

	const char *QueryLedger(int socket, int system_id);
	const char *QueryLedgerUser(int socket, int system_id, const char *user_id);
	const char *QueryLedgerBalance(int socket, int system_id, string searchsql, string sqlend);

	// For rewards access //
	const char *GetUserBalanceLedger(int socket, int system_id, const char *user_id);
	const char *UseCreditLedger(int socket, int system_id, const char *user_id, double amount, const char *trans_date);

	// Additional access //
	const char *AuthLedger(int socket, int system_id, const char *user_id);
	const char *AuthBulkLedger(int socket, int system_id); // Authorize all user payouts NACHA

	// How do we shift grandtotals to ledger functionality //
	
	// Payment class will access this //
	int AddBankPayoutFile(int socket, int system_id, int batch_id, const char *filename);
	bool AddBankPayment(int socket, int system_id, int batch_id, const char *user_id, double amount, int payoutfile_id);

	// Speed up functions //
	bool AddGroupUsed(int socket, int system_id, const char *user_id, double amount, const char *start_date, const char *end_date);

	// Checkpoint functions //
	string GetStartDateCP(int socket, int system_id);
	string GetEndDateCP(int socket, int system_id);
	int AddCP(bool pretend, int socket, int system_id, int batch_id, int checkpoint, int newcheckpoint);
	int EditCP(bool pretend, int socket, int system_id, int batch_id, int checkpoint, int newcheckpoint);
	int GetCP(int socket, int system_id, const char *start_date, const char *end_date);
	int GetCPBatchID(int socket, int system_id);

	// Cleanup functions //
	bool CleanupRanks(int socket, int system_id, int batch_id);
	bool CleanupBreakdown(int socket, int system_id, int batch_id);
	bool CleanupCommissions(int socket, int system_id, int batch_id);
	bool CleanupUserstats(int socket, int system_id, int batch_id);
	bool CleanupLedger(int socket, int system_id, int batch_id);

	//////////////////////
	// Helper functions //
	//////////////////////
	bool Flush(bool pretend, int socket, int system_id, int batch_id); // Push the INSERT's into the database //
	bool FlushGrand(bool pretend, int socket, int system_id, int batch_id);
	bool FlushLevels(int socket);
	bool Clear();

	CBankAccount *GetBankAccount(int socket, int system_id, const char *user_id);
	bool BuildPayUserList(int socket, int system_id, int batch_id, list <CPayUser> *pPayUsersLL);

	// Our custom database functions //
	CConn *ExecDB(int socket, const char *query);
	//CConn *ExecDB(int socket, stringstream& query);
	CConn *ExecDB(int socket, basic_ostream<char> &query);
	CConn *ExecDB(bool autorelease, int socket, const char *query);
	//CConn *ExecDB(bool autorelease, int socket, std::stringstream& query);
	CConn *ExecDB(bool autorelease, int socket, basic_ostream<char> &query);
	bool ExecDB(CConn *conn, bool autorelease, string sql);
	int GetFirstDB(int socket, const char *query); // Grab the first entry INT format //
	//int GetFirstDB(int socket, stringstream& query);
	//int GetFirstDB(CConn *conn, stringstream& query);

	int GetFirstDB(int socket, basic_ostream<char> &query);
	int GetFirstDB(CConn *conn, basic_ostream<char> &query);

	string GetFirstCharDB(int socket, const char *query);
	//string GetFirstCharDB(int socket, stringstream& query);
	string GetFirstCharDB(int socket, basic_ostream<char> &query);

	bool RebuildLevel(int socket, int system_id);
	bool RebuildAllLevels(int socket, const char *start_date, const char *end_date);
	bool IsRecursionLoop(int socket, int system_id, int upline);
	bool RecursionLadder(CUser *puser, int system_id, int upline);

	bool FetchRow(CConn *conn);
	const char *RowMap(CConn *conn, int row);

	CezSettings *m_pSettings;
	CConnPool m_ConnPool; // Manage database connection pool //
	int m_ConnType; // Live vs Sim //
//private:

	// Database variables for mysql and postgresql // 
#ifdef COMPILE_MYSQL
	MYSQL *m_myConn;
	MYSQL_RES *m_myResult;
#else 
	void *m_myConn; // Placeholder //
	void *m_myResult;
#endif

#ifdef COMPILE_POSTGRES
	//PGconn *m_pgConn;
	//PGresult *m_pgResult;
#else
	//void *m_pgConn;
	//void *m_pgResult;
#endif

	//int m_pgRowMax;
	//int m_pgCurrentRow;
	//map <int, string> m_RowMap;

	string m_JSON; 

	string m_RetStr;

	// Flush SQL vars //
	//string m_strBreakdownSQL; 
	string m_strAchvSQL;
	int m_AchvCount;
	//string m_strBreakSQL;
	string m_strBinarySQL;

	// New way of speeding database up //
	map <string, map <string, string> > m_RankMap; // Allow reverse lookup to see if rank has been set //
	int m_RankCount;
	string m_strRankSQL;

	int m_RankBonusCount;
	string m_strRankBonusSQL;

	int m_BreakdownCount;
	string m_strBreakdownSQL;

	int m_CommCount;
	string m_strCommSQL;

	map <string, double> m_GrandAmountMap;
	stringstream m_LevelsSS;
	int m_LevelsCount;
	//int m_UserStatTotalCount;
	//string m_strStatTotalSQL;
	map <string, CAchvLookup> m_AchvLookup;

	//int m_UserStatMonthCount;
	//string m_strStatMonthSQL;
	//int m_UserStatMonthLVL1Count;
	//string m_strStatMonthLVL1SQL;
	//int m_UserStatMonthLegCount;
	//string m_strStatMonthLegSQL;

	int m_LedgerCount;
	string m_strLedgerSQL;

	// Internal buffer //
	CBankAccount m_BankAcount;

	int m_RecurrGen;

	// Payments //
	//list <CPayUser> m_PayUsersLL;

	// Speedup Maphash //
	map <int, string> m_AutoAuthMap;
	map <int, string> m_GrandAmountZero;

	const char *BuildLevels(int socket, int system_id, int user_id, int sponsor_id);
	bool RebuildLevelsLadder(int socket, int system_id, map <string, CUser> &UsersMap, string user_id, string sponsor_id, int generation);

	int NumFields();
};

#endif