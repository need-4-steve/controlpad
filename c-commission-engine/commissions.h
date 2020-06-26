#ifndef _COMMISSION_H
#define _COMMISSION_H

///////////////////
// commissions.h //
///////////////////

#include "ceExtQualify.h"
#include "debug.h"
#include "db.h"
#include "rulesbasiccomm.h"
#include "rulescomm.h"
#include "rulesrank.h"
#include "rulesrankbonus.h"
#include "rulespoolpot.h"
#include "ezJson.h"
#include "affiliate.h"
#include "ceUserStats.h"
#include "faststart.h"

#include <time.h>
#include <map>
 
// Only 3 commission types and a pool //
#define COMMRULE_HYBRIDUNI			1 // Meet criteria or get skipped over on commissions //
#define COMMRULE_BREAKAWAY			2 // A person breaks out of a downline to start their own branch //
#define COMMRULE_BINARY				3 // Take the two top legs. Pay the lesser of the two //
 
// Qualifying Types //
#define QLFY_PERSONAL_SALES				1	
#define QLFY_GROUP_SALES				2
#define QLFY_SIGNUP_COUNT				3
#define QLFY_RANK						4 // Hope5000. Bonus tied to rank and count. Uplevel tied to other 3 criteria //
#define QLFY_CUSTOMER_COUNT				5
#define QLFY_AFFILIATE_COUNT			6
#define QLFY_CUSTAFFIL_COUNT			7 // Customer and Affiliate //
#define QLFY_CUSTLVLONE_COUNT			8 // Customer Level 1 count //
#define QLFY_AFFILVLONE_COUNT			9 // Affiliate Level 1 count //
#define QLFY_GROUP_USED					10 // United League - Tokens Used //
#define QLFY_GROUP_SALESUSED			11 // Combined Sales and Used //
#define QLFY_MYWHOLESALE				12 // STACK type 2
#define QLFY_MYRETAIL					13 // STACK type 2
#define QLFY_TEAMVOLUMEWHOLESALE		14 // Team GEN user defined in ce_systems // Chalkatour needed //
#define QLFY_TEAMVOLUMERETAIL			15 // Team GEN user defined in ce_systems // Chalkatour needed //
#define QLFY_RANKSUMLEG					16 // Sum of all //
#define QLFY_RANKSUMLVL1				17 // Only your immediate downline //
#define QLFY_RESELLER_COUNT				18 
#define QLFY_RESELLER_LVL1_COUNT		19 
#define QLFY_TEAMANDMYWHOLESALE			20 
#define QLFY_PSQ						21 // Number of personally sponsored qualified //
#define QLFY_BOUGHT_AND_SOLD			22 // United's new type //
#define QLFY_CUSTOMERANDMYWHOLESALE		23 // United needed customers volume compessed up //
#define QLFY_ITEMCOUNTWHOLESALE_PV      24 // Piphany - PV Wholesale //
#define QLFY_ITEMCOUNTRETAIL_PV	        25 // Piphany - PV Retail //
#define QLFY_UNIQUEUSERSRECEIPTS		26 // TZIN - Advocate Program // How many unique users per all receipts for given time period //
#define QLFY_EXTERNAL_QUALIFY			27 // Orginally for training class completion, but designed to be universal // 
#define QLFY_ITEMCOUNTWHOLESALE_EV      28 // Maverick - EV Itemcount Wholesale Enterprise Volume//
#define QLFY_ITEMCOUNTRETAIL_EV	        29 // Maverick - EV Itemcount Retail Enterprise Volume//
#define QLFY_MY_WHOLE_RETAIL	        30 // Maverick - WHOLESALE and RETAIL sales // Fast Start //

// UserTypes to seperate different types of sales //
#define USERTYPE_RESELLER		1
#define USERTYPE_CUSTOMER		2
#define USERTYPE_AFFILIATE		3

#define COMMTYPE_RANK			1
#define COMMTYPE_BASIC			2

// Payout type //
#define PAYOUT_WHOLESALE		1
#define PAYOUT_RETAIL			2

// Ledger types //
#define LEDGER_NACHA			1
#define LEDGER_REWARDS			2
#define LEDGER_GRANDTOTAL		3 // Tokens Purchased //
#define LEDGER_TRANSFER			4 // Tokens Played //
#define LEDGER_CM_PURCHASED		5
#define LEDGER_CM_USED			6
#define LEDGER_BONUS			7 // Bonus workaround for united //
#define LEDGER_CUST_PAYOUT		8 // Custom payout by united league //
#define LEDGER_REPAIR			9 // Reserved to repair mistakes client might make //
#define LEDGER_SIGNUPBONUS		10 // All signup bonuses for user added together for batch //
#define LEDGER_POOLBONUS		11 // Pool bonus //
#define LEDGER_RANKGENBONUS		12 // Rank Gen Bonus // Chalk-Coutier bonus //
#define LEDGER_COMMREFUND		13 // Has a receipt refund been issued after commissions have been paid out //
#define LEDGER_EARLY_PAYOUT		14 // For United quick start bonus //

// Handle slight differences in alternate cores //
#define ALTCORE_UNITED_MAIN		1
#define ALTCORE_UNITED_GAME		2

// Track Checkpoint Commission Processing //
#define CHECKPOINT_STARTED			1
#define CHECKPOINT_GETUSERS1		2
#define CHECKPOINT_USERSCOUNT		3
#define CHECKPOINT_RECEIPTCOUNT		4
#define CHECKPOINT_RECURSIONLOOP	5
#define CHECKPOINT_READINDB1		6
#define CHECKPOINT_CALCSALES1		7
#define CHECKPOINT_CALCUSED			8

// Inifinity bonus tracking //
#define CHECKPOINT_RANKRULES1		9
#define CHECKPOINT_BREAKDOWN1		10
#define CHECKPOINT_COMMISSIONS1		11
#define CHECKPOINT_INFINITYCAP1		12
#define CHECKPOINT_GETUSERS2		13
#define CHECKPOINT_READINDB2		14
#define CHECKPOINT_CALCSALES2		15

// Actual Database logging //
#define CHECKPOINT_RANKRULES2		16
#define CHECKPOINT_BREAKDOWN2		17
#define CHECKPOINT_COMMISSIONS2		18
#define CHECKPOINT_USERSTATS		19
#define CHECKPOINT_SIGNUPBONUS		20
#define CHECKPOINT_LEDGER			21
#define CHECKPOINT_LEDGER_TOTALS	22
#define CHECKPOINT_FINISHED			23

// Prevent eexception fault //
#define GENERATION_MAX			10000 // This seems like a really high limit //

#define DORANK_STANDARD			1
#define DORANK_CHECKMATCH		2

////////////////////////////////////////////////////////
// Process commission calculations through this class //
////////////////////////////////////////////////////////
class CCommissions : CDebug, CezJson
{
public:
	CCommissions(); // Set inital defaults //
	~CCommissions(); // Cleanup //
	
	// This should be the only extrnal function we run //
	bool RunSpawnProc(CDb *pDB, int socket, int proc_count, CezSettings *pSettings, const char *start_date, const char *end_date);
	bool RunSpeed(CDb *pDB, int socket, int system_id, int start_sys_id, int end_sys_id, bool pretend, const char *start_date, const char *end_date);

	const char *SetRankOverride(int rank);
	const char *Run(CDb *pDB, int socket, int system_id, int comm_type, bool pretend, bool onlygrand, const char *start_date, const char *end_date, string affiliate_id, bool compression_enabled);
	const char *RunPool(CDb *pDB, int socket, int system_id, int batch_id, int poolpot_id, int qualify_type, int amount, const char *start_date, const char *end_date);

	// Needed public for CalcUsed values //
	std::map <std::string, CUser> m_UsersMap; // Makes user lookup way easy and quick //
	std::list <CReceipt> m_ReceiptsLL;
	std::map <std::string, CAffiliate> m_AffiliateMap; // Extend values for memory optimization //
	list <CExtQualify> m_ExtQualifyList;

	bool CalcUsed(int socket, int system_id, CDb *pDB, const char *start_date, const char *end_date);
	//bool CalcUsedSpeed(int system_id, int start_sys_id, int end_sys_id, CDb *pDB, const char *start_date, const char *end_date);

	bool RunUsed(int socket, int system_id, CDb *pDB, const char *start_date, const char *end_date);

	bool DoCheckMatch(CDb *pDB, int socket, bool pretend, int system_id, int batch_id, const char *start_date, const char *end_date);
	
	// CDb needs access to these for memory optimization //
	int m_Generation; 
	bool BreakawayFinal(bool pretend, int socket, CUser *puser, CReceipt *preceipt, const char *start_date, const char *end_date);
	bool HybridUniFinal(bool pretend, int socket, CUser *preceiptuser, CUser *puser, CReceipt *preceipt, const char *start_date, const char *end_date);

private:

	CDb *m_pDB;
	
	list <CRulesBasicComm> m_RulesBasicCommLL;
	list <CRulesComm> m_RulesCommLL;
	list <CRulesRank> m_RulesRankLL;
	list <CRulesRank> m_CMRulesRankLL;
	list <CRulesPool> m_RulesPoolLL;
	list <CRulesRankBonus> m_RulesRankBonusLL;
	list <CFastStartRules> m_FastStartRulesLL;

	std::string m_JSON;

	int m_SystemID; 
	int m_CommType; 
	int m_BatchID; 
	double m_ReceiptsWholesaleTotal;
	double m_ReceiptsRetailTotal;
	double m_InfinityTotal;
	double m_GrandTotal;
	double m_GrandAchvBonus;
	double m_GrandBonus;
	double m_GrandSignupBonus;

	int m_GenLimit; // What is the maxium defined generation payout? //
	int m_AltCore; // Track if handle like united calcualtions //

	int m_RankMax;
	int m_CMRankMax;

	int m_RankRuleMissedCount;
	string m_StrRankRuleMissedSQL;

	//map <string, int> m_MapRankLegSum;
	map <string, int> m_MapRankLvl1Sum;

	//map <string, string> m_MapRankLegData;
	map <string, string> m_MapRankLvl1Data;

	CceUserStats m_UserStats;

	// Commission Functions //
	bool PreCleanup(int socket, int system_id, int batch_id, int cleanup);
	bool IsRecursionLoop(int system_id);
	bool RecursionLadder(CUser *puser);
	void Reset();
	bool ReadInDB(int socket, int system_id, int piggy_id, int batch_id, const char *start_date, const char *end_date); // Read in the database information //
	//void BuildStatCount();
	void BuildStatCountAll(int system_id, const char *start_date, const char *end_date);
	bool ApplyBasicCommRules(bool pretend, int socket, int system_id, int batch_id, const char *end_date);
	bool BasicReceiptUpLadder(int socket, CUser *puser, CRulesBasicComm *prule, CReceipt *preceipt, double commission);
	bool QualifyCompareRules(CUser *puser, int qualifytype, int startthreshold, int endthreshold);
	double ActualUserValue(CUser *puser, int qualifytype);

	bool DoRankRules(bool pretend, int socket, int system_id, int batch_id, list <CRulesRank> *pRulesRank, string tablename); // Test to see if someone moved up a rank //
	bool TestPSQ(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank);
	bool TestRankRules(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank);
	bool TestRankSumLeg(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank);
	bool TestRankLvl1(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank);
	bool TestRankComparison();

	bool TestExtQualify(bool pretend, int socket, CRulesRank *pRule, CUser *puser);

	void ApplyRankRules(bool pretend, int socket, CRulesRank *pRule, CUser *puser, int system_id, int rulestype);
	void ApplyRankBonus(bool pretend, int socket, int system_id, int m_BatchID, string userid, int rank);
	bool ApplyUpRankLeg(CUser *puser, string rankuser, int rank);

	bool CalcRankSum(int rank, bool add); 
	bool IsSameLeg(string legdata, string leg_userid);
	
	bool CalcSalesAndStats(int socket, int system_id, const char *start_date, const char *end_date);
	
	void BuildPSQ(int socket, int psq_limit, const char *end_date);

	bool CalcCommBreakdown(bool pretend, int socket, int system_id, const char *start_date, const char *end_date);
	bool FinishCommissions(bool pretend, int socket);

	bool ReceiptUpLadder(CUser *puser, CReceipt *preceipt, int teamgenmax, const char *start_date, const char *end_date);
	
	bool BinaryFinal(bool pretend, int socket, CUser *puser);
	bool FinishUserStats(bool pretend, int socket, int system_id, int batch_id, const char *end_date);
	bool LegRankCalc(bool pretend, list <CRankGenBonus> *RankGenBonus, int socket, int system_id, int batch_id, CUser *pBaseUser, CUser *puser, const char *end_date);
	bool BuildLegRankGenData(string baseuserid, CUser *puser, map <int, int> &MapRankLegSum, map <int, string> &MapRankLegData, int rank, int countgen, int endgen);
	bool LegRankLadder(bool pretend, list <CRankGenBonus> *RankGenBonus, int socket, int system_id, int batch_id, CUser *puser, int rank, int rigcount, string finaluserdata, const char *end_date, int generation);

	bool UpdateNewAdvisor(bool pretend, int socket, int system_id);
	string GetNewAdvisor(CUser *puser);
	bool RebuildCommLegsWAdvisor(int comm_type);
	bool BuildLedger(bool pretend, int socket, int system_id, int batch_id, float signupbonus, const char *start_date, const char *end_date);
	
	bool DoCheckMatchCommission(int socket, int system_id, int batch_id, CUser *puser, int ref_id, const char *baseuser_id, double amount, std::list <CRulesComm> *pRulesComm, const char *end_date);
	bool DoCheckMatchUsed(int socket, int system_id, int batch_id, CUser *puser, int ref_id, const char *baseuser_id, double amount, std::list <CRulesComm> *pRulesComm, const char *end_date);

	bool DoFastStartBonuses(bool pretend, int socket, int system_id, const char *start_date, const char *end_date);

	bool BuildGrandTotals(bool pretend, int socket, int system_id, const char *date_last_earned);
	const char *BuildJSON(string affiliate_id);
	const char *BuildGrandTotalJSON();


	int m_RankGenBonusCount;
	string m_RankGenBonusSQL;

	int m_LegRankCount;
	//int m_LegRankGen2Count;
	//int m_LegRankGen3Count;
	string m_LegRankSQL;
	//string m_LegRankGen2SQL;
	//string m_LegRankGen3SQL;

	bool m_CompressionEnabled;
};

#endif

/*
- Compensatoon Plan Types
	- #1. Stair Step
	- #2. Hybrid Uni-level
	- #3. Binary
- Qualifying Factors (Any payment you recieve is based on a qualification you met)
	- Title/Rank
	- Personal Purchases/Sales 
	- Sponsor Sign-up Count
	- Revenue Generated (Total company revenue)
- Breakage vs Compress
	- Missed qualifiers goes back to company
	- Look through upline to find another person to pay
- Payout percentage can change on each generation

- Achievement bonus
	- It's typicaly a 1 time thing
	- Qualifying Factors are met
	- Bump in commission. (Example: goes from %5 to %10 commission for that month
	  Often may be a fixed dollar amount such as a rank advancement = $500
- Pool
	- Has an expiration date (Could be 1 month. Could be 2 years)
		- Allow no expiration date (Just in case)
	- Qualifying factor needs to be met
		- Dollar amount assigned to each person signed up
		- Or Percentage of certain sales goes into a pot (may be any combination of revenue generation)
- Ability to turn on/off commission for a certain products (Example: Sales kits are non-commissionable)
	- Do these still apply for bonuses or pools as well  YES?

-----------
-- Notes --
-----------
Commissionable Volume All products are assigned a comissionable volume or CV? 
Do some products have higher commission values Yes?
Instead of basing calculations of sales receipts... Do calculations on commissionable value as per each product  ? 
All commissionsons are caculated on CV Bonus and pool values will vary on what they are based on
*/