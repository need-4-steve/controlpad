#ifndef _EZREPORTS_H
#define _EZREPORTS_H

#include "Compile.h"
#include "debug.h"
#include "db.h"
#include "dbplus.h"
#include <string>

using namespace std;

///////////////////////////////////////////////
// Handle all of our encryption and API keys //
///////////////////////////////////////////////
class CezReports : CDbPlus
{
public:
	CezReports(CDb *pDB, string origin);

	bool CalcAll(int socket, int system_id, int batch_id);

	// Allow query of repcompiled data // 
	const char *QueryAuditRanks(int socket, int system_id, string batch_id);
	const char *QueryAuditUsers(int socket, int system_id, string batch_id);
	const char *QueryAuditGen(int socket, int system_id, string batch_id);

	const char *QueryBatches(int socket, int system_id, string search, string sort);
	const char *QueryRanks(int socket, int system_id, string search, string sort);
	const char *QueryAchvBonus(int socket, int system_id, string search, string sort);
	const char *QueryCommissions(int socket, int system_id, string search, string sort);
	const char *QueryUserStats(int socket, int system_id, string search, string sort);
	const char *QueryUserStatsLvl1(int socket, int system_id, string search, string sort);

	bool CalcPreLegRankGen(int socket, int system_id, int batch_id, int rankmax, map <string, CUser> &UsersMap); // Do pre calculation of ce_pre_legrankgen table //

private:
	// Precompile data for reports //
	bool CalcRankAudit(int socket, int system_id, int batch_id);
	bool CalcUsersAudit(int socket, int system_id, int batch_id);
	bool CalcGenerationAudit(int socket, int system_id, int batch_id);
	
	bool UsersCalcAllGen(int socket, int system_id, int batch_id, string user_id);
	string GetRandomUser(int socket, int system_id, int batch_id);

	string m_Json;
	CDb *m_pDB;
};

#endif