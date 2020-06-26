#ifndef _SEED_H
#define _SEED_H

#include "debug.h"
#include "db.h"

class CSeedSystem
{
public:
	std::list <CUser> m_UsersLL; // Map makes user lookup way easy and quick //
};

/////////////////////////////
// Use seed data for gtest //
/////////////////////////////
class CSeed : CDebug
{
public:
	CSeed();
	void UnitedLeagueRules(CDb *pDB, int system_id);
	void UnitedLeagueGameRules(CDb *pDB, int system_id);
	void UnitedLeagueGenBase(CDb *pDB, int start_system_id); // Generate base for United League //
	bool UnitedLeagueGenData(CDb *pDB, int start_system_id); // Generate data for United League //
	void UnitedPreload(CDb *pDB, int usertype, std::string user_id, std::string signup_date);

	void Hope5000GenBase(CDb *pDB, int system_id); // Generate base plan for Hope5000 //
	void Hope5000GenData(CDb *pDB, int system_id); // Generate data for Hope5000 //
		
	// Need to test breakaway type //

	// Need to test binary type //

	//std::map <int, CSeedSystem> m_SystemMap;

	std::stringstream m_RankRulesSS;
	int m_RankRuleCount;

	std::stringstream m_CommRulesSS;
	int m_CommRuleCount;

	int m_AltUserCount;
	std::stringstream m_AddUsersStr;
};

#endif