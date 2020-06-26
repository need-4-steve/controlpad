#include "ezSettings.h"

/////////////////
// Constructor //
/////////////////
CezSettings::CezSettings()
{
	m_DBType = 0;
	m_NetworkType = 0; // Process via sockets, ssl or FastCGI //
	m_ListenPort = 0; // Needed for network accept connections //
	m_Daemon = false; // Daemon //
	m_PayProc = 0; // Payment Processing Type //
	m_MultProcCount = 0;
//	m_SimEnabled = false;

	m_ApiConnPoolDynamic = false; // 

	//m_simListenPort = 0;

	m_ConnPoolCount = 0;

	m_MaxRankBonusGen = 0;

	m_MaxSystems = MAX_SYSYEMS;
	m_MaxUsers = MAX_USERS;
	m_MaxReceipts = MAX_RECEIPTS;
	m_MaxRankRules = MAX_RANKRULES;
	m_MaxCommRules = MAX_COMMRULES;
	m_MaxApiKeys = MAX_APIKEYS;
	m_MaxBonuses = MAX_BONUSES;
	m_MaxPools = MAX_POOLS;
	m_MaxPoolRules = MAX_POOLRULES;

	m_DisableAdvisorSQL = false;
	m_DisableLvlRankSQL = false;
	m_DisableUserStatsSQL = false;
	m_DisableCarrerRanksSQL = false;

	m_LegRankGen = false;
}

/*
//////////////////////////////////
// Copy from settings passed in //
//////////////////////////////////
bool CezSettings::CopyFrom(CezSettings *pSettings)
{
	// Live database settings //
	m_DatabaseName = pSettings->m_DatabaseName;
	m_Username = pSettings->m_Username;
	m_Password = pSettings->m_Password;
	m_Hostname = pSettings->m_Hostname;
	m_ListenPort = pSettings->m_ListenPort;

	// Simulation database settings //
	m_simDatabaseName = pSettings->m_simDatabaseName;
	m_simUsername = pSettings->m_simUsername;
	m_simPassword = pSettings->m_simPassword;
	m_simHostname = pSettings->m_simHostname;
	m_simListenPort = pSettings->m_simListenPort;

	m_NetworkType = pSettings->m_NetworkType; 
	m_DBType = pSettings->m_DBType;
	m_HashPass = pSettings->m_HashPass;
	m_CertFile = pSettings->m_CertFile;
	m_KeyFile = pSettings->m_KeyFile;
	m_Daemon = pSettings->m_Daemon;
	m_PayProc = pSettings->m_PayProc; 

	m_MultProcCount = pSettings->m_MultProcCount;

	m_PaymanURL = pSettings->m_PaymanURL;
	m_PaymanAPIkey = pSettings->m_PaymanAPIkey;

	m_SimEnabled = pSettings->m_SimEnabled;

	m_ConnPoolCount = pSettings->m_ConnPoolCount;
}
*/

/*
//////////////////////////////////////////////
// Turn on simulations database connections //
//////////////////////////////////////////////
bool CezSettings::EnableSim()
{
	//m_SimEnabled = true;
}

//////////////////////////
// Need for simulations //
//////////////////////////
bool CezSettings::DisableSim()
{
	//m_SimEnabled = false;
}
*/