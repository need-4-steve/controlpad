/////////////////
// rulesrank.h //
/////////////////
#ifndef _RULESRANK_H
#define _RULESRANK_H 

#include <string>

using namespace std;

/////////////////////////////
// Handle commission rules //
/////////////////////////////
class CRulesRank
{
public:
	CRulesRank(); // Reset all values //
	void Copy(CRulesRank *RuleRank);

	int m_SystemID;
	int m_ID; // We ID for updating records later //
	int m_Rank; // What rank level does this apply to? //
	int m_QualifyType; // PERSONAL_SALES, GROUP_SALES, SIGNUP_COUNT //
	double m_QualifyThreshold; // Either dollar amount or Number of signups //
	double m_AchvBonus; // What type of Achievement bonus do they get //
	bool m_Breakage; // This puts somone at the top of their own branch. Upline no longer receives commissions from them //	
	int m_RuleGroup; // Handle grouping rules together //
	int m_MaxDacLeg; // Max number count of sales from custonmer and affiliate //
	int m_SumRankStart; // Pre-defined rank from RANKSUMLEG rule //
	int m_SumRankEnd; // Pre-defined rank from RANKSUMLEG rule //
	bool m_EndFlag;
	char m_Label[100];
	string m_VarID; // Needed for External Qualify //
};

//////////////////////////////////////
// Temp for bulk update carrer_rank //
//////////////////////////////////////
class CRulesRankTmp
{
public:
	CRulesRankTmp(); // Reset all values //
	int m_Rank;
	int m_Count;
	string m_SQL;
};

#endif