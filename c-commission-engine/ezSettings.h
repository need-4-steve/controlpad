#ifndef _SETTINGS_H
#define _SETTINGS_H

#include "Compile.h"
#include <string>

#ifdef UNITED
// -1 is unlimited
#define MAX_SYSYEMS			-1 // Per systemuser //
#define MAX_USERS			-1 // Per system //
#define MAX_RECEIPTS		-1 // Per system //
#define MAX_RANKRULES		-1 // Per system //
#define MAX_COMMRULES		-1 // Per system //
#define MAX_APIKEYS			-1 // Per system //
#define MAX_BONUSES			-1 // Per system //
#define MAX_POOLS			-1 // Per system //
#define MAX_POOLRULES		-1 // Per system //

#else
// Default Max Values //
#define MAX_SYSYEMS			100 // Per systemuser //
#define MAX_USERS			1000000 // Per system //
#define MAX_RECEIPTS		1000000 // Per system //
#define MAX_RANKRULES		500 // Per system //
#define MAX_COMMRULES		500 // Per system //
#define MAX_APIKEYS			100 // Per system //
#define MAX_BONUSES			1000000 // Per system //
#define MAX_POOLS			5000 // Per system //
#define MAX_POOLRULES		10000 // Per system //

#endif

using namespace std;

////////////////////////////
// Handle SSL connecitons //
////////////////////////////
class CezSettings
{
public:
	CezSettings();
	//bool CopyFrom(CezSettings *pSettings);
//	bool EnableSim(); // Turn on simulations database connections //
//	bool DisableSim(); // Need for simulations //

	// Live database settings //
	std::string m_DatabaseName;
	std::string m_Username;
	std::string m_Password;
	std::string m_Hostname;
	unsigned short m_ListenPort; // Needed for network accept connections //

	bool m_ApiConnPoolDynamic; // Allow API optimization for database connections //

	// Simulation database settings //
/*	std::string m_simDatabaseName;
	std::string m_simUsername;
	std::string m_simPassword;
	std::string m_simHostname;
	unsigned short m_simListenPort; // Needed for network accept connections //
*/
	int m_NetworkType; // Process via sockets, ssl or FastCGI //
	int m_DBType; // mysql or postgresl? // found in db.h //
	std::string m_HashPass; // Authentication //
	std::string m_CertFile; // SSL //
	std::string m_KeyFile; // SSL //
	bool m_Daemon; // Daemon //
	int m_PayProc; // Payment Processing Type //

	int m_MultProcCount;

	std::string m_PaymanURL;
	std::string m_PaymanAPIkey;

	// Max Values
	int m_MaxSystems;
	int m_MaxUsers;
	int m_MaxReceipts;
	int m_MaxRankRules;
	int m_MaxCommRules;
	int m_MaxApiKeys;
	int m_MaxBonuses;
	int m_MaxPools;
	int m_MaxPoolRules;

	int m_ConnPoolCount;

	string m_LogFile;
	string m_IniFile;
	string m_FullSQL; // For debugging //

	// Options //
	int m_MaxRankBonusGen;
	bool m_DisableAdvisorSQL;
	bool m_DisableLvlRankSQL;
	bool m_DisableUserStatsSQL;
	bool m_DisableCarrerRanksSQL;

	string m_JwtSecret;

	string m_ReportsUser1;
	string m_ReportsUser2;
	string m_ReportsUser3;
	string m_ReportsUser4;
	string m_ReportsUser5;
	string m_ReportsUser6;

	bool m_LegRankGen;
};

#endif