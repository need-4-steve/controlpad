////////////////////////
// CommissionEngine.h //
////////////////////////

#include "CommissionEngine.h"
#include "SimpleIni.h"
#include "migrations.h"

#include "Simulations.h"
#include "seed.h"

#include "ConnPool.h"

#include "ezCopy.h"

#include "ceAffiliate.h"
#include "ceCommRule.h"
#include "cePayments.h"
#include "ceRankRule.h"
#include "ceReceipt.h"
#include "ceSystem.h"
#include "ceUser.h"

#include "ezCrypt.h"
#include "ezDaemon.h"
#include "ezReports.h"
#include "ezSSL.h"

#include "ezCrypt.h"

#include "convert.h"

#include "ezTok.h"

#include <sstream>

CCommissionEngine *g_pCommEng = NULL;

char g_DebugLevel;
char g_DebugDisplay;

CDb *g_pDB;
extern CDb *g_pSimDB;

//bool g_ConnBank[MAX_CONN_LOOP+1];

string g_TimeZone;

/////////////////
// Constructor //
/////////////////
CCommissionEngine::CCommissionEngine()
{
	m_StartedUp = false;

	//g_DebugLevel = 6;
	//g_DebugDisplay = 1;

	SetLevel(g_DebugLevel);
    SetDisplay(g_DebugDisplay);

	// Always load ini file settings //
	m_Settings.m_DBType = 0;
	m_Settings.m_Daemon = false;
	m_pDB = NULL;

	//if  (LoadINI(&m_LiveSettings, LIVE_INI_FILENAME) == false) // Load live settings //
	//	exit(1);
	//if (LoadINI(&m_SimSettings, SIM_INI_FILENAME) == false) // Load sim settings //
	//	exit(1);

	// Initially point to live //
	m_pDB = &m_DB; 
	m_pSettings = &m_Settings;
	SetLogFile(m_Settings.m_LogFile);
}

///////////////////////////////////////
// Load .ini setting here on startup //
///////////////////////////////////////
bool CCommissionEngine::Startup(string inifile)
{
	m_StartedUp = true;

	// Verify the ini file is present //
	stringstream filename;
	filename << INI_PATH << inifile << ".ini";
	Debug(DEBUG_INFO, "CCommissionEngine::Startup - ini filename", filename.str().c_str());
	if (FILE *file = fopen(filename.str().c_str(), "r"))
		fclose(file);
	else
	{
	    Debug(DEBUG_ERROR, "CCommissionEngine::Startup - invalid ini filename", filename.str());
	    exit(1);
	}

	// Load the ini file into settings //
	if  (LoadINI(&m_Settings, filename.str().c_str()) == false) // Load live settings //
	{
		Debug(DEBUG_ERROR, "CCommissionEngine::Startup - Couldn't load ini file", filename.str());
		exit(1);
	}

	m_Settings.m_IniFile = inifile;

	return true;
} 

///////////////////////////////////
// Load from the config.ini file //
///////////////////////////////////
bool CCommissionEngine::LoadINI(CezSettings *psettings, const char *inifilename)
{
	// Make sure the filename is available //
	if (FILE *file = fopen(inifilename, "r"))
		fclose(file);
    else
        return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - Invalid filename", inifilename);

	const char *psystype = NULL;
	const char *phostname = NULL;
	const char *pusername = NULL;
	const char *ppassword = NULL;
	const char *pdatabasename = NULL;
	const char *phashpass = NULL;
	const char *pconnpoolcount = NULL;

	CSimpleIniA ini(true, true, true);

	// Handle database section //
	if (ini.LoadFile(inifilename) < 0) throw "Load failed for setting.ini";
	if ((phostname = ini.GetValue("database", "hostname", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find hostname in database section");
	psettings->m_Hostname = phostname;
	if ((pusername = ini.GetValue("database", "username", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find username in database section");
	psettings->m_Username = pusername;
	if ((ppassword = ini.GetValue("database", "password", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find password in database section");
	psettings->m_Password = ppassword;
	if ((pdatabasename = ini.GetValue("database", "database", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database in database section");
	psettings->m_DatabaseName = pdatabasename;
	if ((phashpass = ini.GetValue("crypt", "hashpass", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find hashpass in crypt section");
	psettings->m_HashPass = phashpass;
	if ((psystype = ini.GetValue("database", "type", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find type");
	if (strcmp(psystype, "mysql") == 0) // Create via mysql //
		psettings->m_DBType = DB_MYSQL;
	else if ((strcmp(psystype, "postgresql") == 0) || (strcmp(psystype, "postgres") == 0))
		psettings->m_DBType = DB_POSTGRES;
	else
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - database system type not defined");
	if ((pconnpoolcount = ini.GetValue("database", "connpool", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find connpool in database section");
	psettings->m_ConnPoolCount = atoi(pconnpoolcount);

	// Is simulation enabled? //
	//const char *psim;
	//if ((psim = ini.GetValue("database", "simulation", NULL)) == NULL)
	//	return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - database - simulation not defined");
	//if (strcmp(psim, "true") == 0)
	//	psettings->m_SimEnabled = true;
	//else if (strcmp(psim, "false") == 0)
	//	psettings->m_SimEnabled = false;

	// Handle network section //
	const char *pListenPort;
	if ((pListenPort = ini.GetValue("network", "listen_port", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find network - listen_port");
	psettings->m_ListenPort = atoi(pListenPort);

	// Handle debug settings //
	const char *plogfile;
	if ((plogfile = ini.GetValue("debug", "logfile", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find debug - logfile");
	psettings->m_LogFile = plogfile;
	const char *pdebug;
	if ((pdebug = ini.GetValue("debug", "level", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find debug - level");
	g_DebugLevel = atoi(pdebug); // Set the current debug level //
	if ((pdebug = ini.GetValue("debug", "display", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find debug - display");
	if (strcmp(pdebug, "screen") == 0)
		g_DebugDisplay = DEBUG_SCREEN;
	else if (strcmp(pdebug, "file") == 0)
		g_DebugDisplay = DEBUG_FILE;
	else if (strcmp(pdebug, "both") == 0)
		g_DebugDisplay = DEBUG_BOTH;
	else
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - debug display not defined");
	const char *pfullsql;
	if ((pfullsql = ini.GetValue("debug", "fullsql", NULL)) == NULL)
	{
		psettings->m_FullSQL = "false";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find debug - [debug] fullsql");
	}
	else
	{
		psettings->m_FullSQL = pfullsql;
	}

	// Handle Processing Type //
	const char *pproctype;
	if ((pproctype = ini.GetValue("run", "type", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - run - type not defined");

	// Enable Multi-process //
	const char *pmulticount;
	if ((pmulticount = ini.GetValue("multiproc", "count", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - run - type not defined");
	psettings->m_MultProcCount = atoi(pmulticount);

	std::string proctype;
	proctype = pproctype;
	if (strcmp(proctype.c_str(), "sockets") == 0)
		psettings->m_NetworkType = PROC_SOCKETS;
	else if (strcmp(proctype.c_str(), "ssl") == 0)
		psettings->m_NetworkType = PROC_SSL;

	const char *pcertfile;
	if ((pcertfile = ini.GetValue("ssl", "certfile", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - ssl - certfile not defined");
	psettings->m_CertFile = pcertfile;
	const char *pkeyfile;
	if ((pkeyfile = ini.GetValue("ssl", "keyfile", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - ssl - keyfile not defined");
	psettings->m_KeyFile = pkeyfile;

	const char *pdaemon;
	if ((pdaemon = ini.GetValue("startup", "daemon", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - startup - daemon not defined");
	if (strcmp(pdaemon, "true") == 0)
		psettings->m_Daemon = true;
	else if (strcmp(pdaemon, "false") == 0)
		psettings->m_Daemon = false;

	const char *ppayproc;
	if ((ppayproc = ini.GetValue("payments", "processing", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - startup - payments/processing not defined");
	if (strcmp(ppayproc, "payman") == 0)
		psettings->m_PayProc = PAY_PROC_PAYMAN;
	else if (strcmp(ppayproc, "local") == 0)
		psettings->m_PayProc = PAY_PROC_LOCAL;

	const char *ppaymanurl;
	if ((ppaymanurl = ini.GetValue("payman", "url", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database - payman - url");
	psettings->m_PaymanURL = ppaymanurl;

	const char *ppaymanapikey;
	if ((ppaymanapikey = ini.GetValue("payman", "apikey", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database - payman - apikey");
	psettings->m_PaymanAPIkey = ppaymanapikey;
/*
	// Load the simulations settings //
	if ((phostname = ini.GetValue("sim-database", "hostname", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database - sim hostname");
	psettings->m_simHostname = phostname;
	if ((pusername = ini.GetValue("sim-database", "username", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database - sim username");
	psettings->m_simUsername = pusername;
	if ((ppassword = ini.GetValue("sim-database", "password", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database - sim password");
	psettings->m_simPassword = ppassword;
	if ((pdatabasename = ini.GetValue("sim-database", "master_db", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find database - sim master_db");
	psettings->m_simDatabaseName = pdatabasename;
	const char *pSimListenPort;
	if ((pSimListenPort = ini.GetValue("sim-network", "listen_port", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find network - sim listen_port");
	psettings->m_simListenPort = atoi(pSimListenPort);
*/
	// Load max value settings //
	const char *pmaxsystems;
	if ((pmaxsystems = ini.GetValue("max", "systems", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max systems");
	psettings->m_MaxSystems = atoi(pmaxsystems);
	const char *pmaxusers;
	if ((pmaxusers = ini.GetValue("max", "users", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max users");
	psettings->m_MaxUsers = atoi(pmaxusers);
	const char *pmaxreceipts;
	if ((pmaxreceipts = ini.GetValue("max", "receipts", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max receipts");
	psettings->m_MaxReceipts = atoi(pmaxreceipts);
	const char *pmaxrankrules;
	if ((pmaxrankrules = ini.GetValue("max", "rankrules", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max rankrules");
	psettings->m_MaxRankRules = atoi(pmaxrankrules);
	const char *pmaxcommrules;
	if ((pmaxcommrules = ini.GetValue("max", "commrules", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max commrules");
	psettings->m_MaxCommRules = atoi(pmaxcommrules);
	const char *pmaxapikeys;
	if ((pmaxapikeys = ini.GetValue("max", "apikeys", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max apikeys");
	psettings->m_MaxApiKeys = atoi(pmaxapikeys);
	const char *pmaxbonuses;
	if ((pmaxbonuses = ini.GetValue("max", "bonuses", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max bonuses");
	psettings->m_MaxBonuses = atoi(pmaxbonuses);
	const char *pmaxpools;
	if ((pmaxpools = ini.GetValue("max", "pools", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max pools");
	psettings->m_MaxPools = atoi(pmaxpools);
	const char *pmaxpoolrules;
	if ((pmaxpoolrules = ini.GetValue("max", "poolrules", NULL)) == NULL)
		return Debug(DEBUG_ERROR, "CCommissionEngine::LoadINI - couldn't find max poolrules");
	psettings->m_MaxPoolRules = atoi(pmaxpoolrules);

	// rankgenbonus //
	const char *pmaxrankbonusgen;
	if ((pmaxrankbonusgen = ini.GetValue("max", "rankgenbonus", NULL)) == NULL)
	{
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find max rankgenbonus");
		psettings->m_MaxRankBonusGen = 0;
	}
	else
		psettings->m_MaxRankBonusGen = atoi(pmaxrankbonusgen);

	// Options //
	const char *pdisableadvisorsql;
	if ((pdisableadvisorsql = ini.GetValue("options", "disableadvisorsql", NULL)) == NULL)
	{
		psettings->m_DisableAdvisorSQL = false;
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [options] disableadvisorsql");
	}
	else
	{
		if (strcmp(pdisableadvisorsql, "true") == 0)
			psettings->m_DisableAdvisorSQL = true;
		else if (strcmp(pdisableadvisorsql, "false") == 0)
			psettings->m_DisableAdvisorSQL = false;
	}

	const char *pdisablelvl1ranksql;
	if ((pdisablelvl1ranksql = ini.GetValue("options", "disablelvl1ranksql", NULL)) == NULL)
	{
		psettings->m_DisableLvlRankSQL = false;
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [options] disablelvl1ranksql");
	}
	else
	{
		if (strcmp(pdisablelvl1ranksql, "true") == 0)
			psettings->m_DisableLvlRankSQL = true;
		else if (strcmp(pdisablelvl1ranksql, "false") == 0)
			psettings->m_DisableLvlRankSQL = false;
	}

	const char *pdisableuserstats;
	if ((pdisableuserstats = ini.GetValue("options", "disableuserstatssql", NULL)) == NULL)
	{
		psettings->m_DisableUserStatsSQL = false;
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [options] disableuserstatssql");
	}
	else
	{
		if (strcmp(pdisableuserstats, "true") == 0)
			psettings->m_DisableUserStatsSQL = true;
		else if (strcmp(pdisableuserstats, "false") == 0)
			psettings->m_DisableUserStatsSQL = false;
	}

	const char *pdisabledcarrerranks;
	if ((pdisabledcarrerranks = ini.GetValue("options", "disabledcarrerrankssql", NULL)) == NULL)
	{
		psettings->m_DisableCarrerRanksSQL = false;
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [options] disabledcarrerrankssql");
	}
	else
	{
		if (strcmp(pdisabledcarrerranks, "true") == 0)
			psettings->m_DisableCarrerRanksSQL = true;
		else if (strcmp(pdisabledcarrerranks, "false") == 0)
			psettings->m_DisableCarrerRanksSQL = false;
	}

	// JWT Secret //
	const char *pjwtsecret;
	if ((pjwtsecret = ini.GetValue("crypt", "jwtsecret", NULL)) == NULL)
	{
		psettings->m_JwtSecret = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [crypt] jwtsecret");
	}
	else
	{
		psettings->m_JwtSecret = pjwtsecret;
	}

	// Report Users //
	const char *preportsuser1;
	if ((preportsuser1 = ini.GetValue("reports", "user1", NULL)) == NULL)
	{
		psettings->m_ReportsUser1 = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] user1");
	}
	else
	{
		psettings->m_ReportsUser1 = preportsuser1;
	}

	const char *preportsuser2;
	if ((preportsuser2 = ini.GetValue("reports", "user2", NULL)) == NULL)
	{
		psettings->m_ReportsUser2 = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] user2");
	}
	else
	{
		psettings->m_ReportsUser2 = preportsuser2;
	}

	const char *preportsuser3;
	if ((preportsuser3 = ini.GetValue("reports", "user3", NULL)) == NULL)
	{
		psettings->m_ReportsUser3 = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] user3");
	}
	else
	{
		psettings->m_ReportsUser3 = preportsuser3;
	}

	const char *preportsuser4;
	if ((preportsuser4 = ini.GetValue("reports", "user4", NULL)) == NULL)
	{
		psettings->m_ReportsUser4 = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] user4");
	}
	else
	{
		psettings->m_ReportsUser4 = preportsuser4;
	}

	const char *preportsuser5;
	if ((preportsuser5 = ini.GetValue("reports", "user5", NULL)) == NULL)
	{
		psettings->m_ReportsUser5 = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] user5");
	}
	else
	{
		psettings->m_ReportsUser5 = preportsuser5;
	}

	const char *preportsuser6;
	if ((preportsuser6 = ini.GetValue("reports", "user6", NULL)) == NULL)
	{
		psettings->m_ReportsUser6 = "";
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] user6");
	}
	else
	{
		psettings->m_ReportsUser6 = preportsuser6;
	}

	const char *plegrankgen;
	if ((plegrankgen = ini.GetValue("reports", "legrankgen", NULL)) == NULL)
	{
		psettings->m_LegRankGen = false;
		Debug(DEBUG_WARN, "CCommissionEngine::LoadINI - couldn't find [reports] legrankgen");
	}
	else
	{
		if (strcmp(plegrankgen, "true") == 0)
			psettings->m_LegRankGen = true;
		else if (strcmp(plegrankgen, "false") == 0)
			psettings->m_LegRankGen = false;
	}

	return true;
}

////////////////////////////////////////////////
// Initialize the master controlling database //
////////////////////////////////////////////////
bool CCommissionEngine::CreateMaster()
{
	Debug(DEBUG_INFO, "CCommissionEngine::CreateMaster");
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CreateMaster - Startup() needs to be run");

	string cmd;
	stringstream ss;

#ifdef COMPILE_LOCAL
	if (m_pSettings->m_DBType == DB_MYSQL) // Create via mysql //
	{
		ss << "mysql -u " << m_pSettings->m_Username << " -p" << m_pSettings->m_Password << " -e \"CREATE DATABASE " << m_pSettings->m_DatabaseName << "\"";
		cmd = ss.str();
		if (system(cmd.c_str()) != 0) 
			return Debug(DEBUG_ERROR, "CCommissionEngine::CreateMaster - Error creating mysql database");
	}
	else if (m_pSettings->m_DBType == DB_POSTGRES) // Create via postgresql //
	{
#ifdef COMPILE_LOCAL
		ss << "createdb " << m_pSettings->m_DatabaseName;
		cmd = ss.str();
		if (system(cmd.c_str()) != 0) 
			return Debug(DEBUG_ERROR, "CCommissionEngine::CreateMaster - Error createdb postgresql database");
#endif
	}
#endif

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->m_ConnPool.Disable();

	// Do actual initializing of the database //
	if (m_pDB->Connect(m_pSettings) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CreateMaster - Couldn't Connect");
	CMigrations migrate(m_pDB, m_pSettings->m_HashPass.c_str(), "init");

	return true;
} 

////////////////////////////
// Create master database //
////////////////////////////
bool CCommissionEngine::Migrate()
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Migrate - Startup() needs to be run");

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->m_ConnPool.Disable();
	if (m_pDB->Connect(m_pSettings) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Migrate - Couldn't Connect");
	CMigrations migrate(m_pDB, m_pSettings->m_HashPass.c_str(), "migrate");

	return true;
}

////////////////////////////
// Create master database //
////////////////////////////
bool CCommissionEngine::Rollback()
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Rollback - Startup() needs to be run");

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->m_ConnPool.Disable();
	if (m_pDB->Connect(m_pSettings) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Migrate - Couldn't Connect");
	CMigrations migrate(m_pDB, m_pSettings->m_HashPass.c_str(), "rollback");

	return true;
}

////////////////////////////////////////////
// Do actual startup of commission engine //
////////////////////////////////////////////
bool CCommissionEngine::API()
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::API - Startup() needs to be run");

	Debug(DEBUG_MESSAGE, "The Commission Engine is starting...");

	g_pCommEng = this;

	if ((m_pSettings->m_NetworkType == PROC_SOCKETS) ||
		(m_pSettings->m_NetworkType == PROC_SSL))
	{
		//Debug(DEBUG_DEBUG, "CCommissionEngine::Startup - PROC_SOCKETS");
		
		// Run as a daemon //
		if (m_pSettings->m_Daemon == true)
		{
			Debug(DEBUG_DEBUG, "CCommissionEngine::Startup - Daemon Started");
			CezDaemon daemon;
			daemon.Startup(); // Run as a daemon //
		}

		// Do inital database pool connections here //
		m_Settings.m_ApiConnPoolDynamic = true; // This allows each new comm-eng connection to make a new db connection //
											// Then that db connection is released after use //
		m_DB.Connect(&m_Settings); 

		// Set the timezone for the connection pool //
		g_TimeZone = m_DB.GetFirstCharDB(0, "SELECT value FROM ce_settings WHERE system_id=0 AND varname='timezone'");

		// Needed for simulations //
		g_pDB = &m_DB;

		// Handle network socket listening //
		if (m_Network.Startup(&m_DB, &m_Settings) == false)
			Debug(DEBUG_ERROR, "CCommissionEngine::Startup - m_Network.Startup() == false #1");
		
 
		// Allow to sift between both live and sim network connections //
		while (!g_exit_request)
		{
			if (m_Network.pSelectFull() == false)
			{
				// It already barks debug errors in pSelectFull() //
			}
		}
	}
	else
	{
		Debug(DEBUG_ERROR, "CCommissionEngine::Startup - return false");
		return false;
	}

	return true;
}

//////////////////////
// Run a simulation //
//////////////////////
bool CCommissionEngine::RunSim(int system_id, string start_date, string end_date)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - Startup() needs to be run");

	int socket = 0;

	if (system_id < 1)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - Error: system_id < 1");

	Debug(DEBUG_TRACE, "CCommissionEngine::RunSim - Beginning of simulations");
	m_DB.Connect(&m_Settings);

	// Needed for simulations //
	g_pDB = &m_DB;

	// Set the timezone for the connection pool //
	g_TimeZone = g_pDB->GetFirstCharDB(socket, "SELECT value FROM ce_settings WHERE system_id=0 AND varname='timezone'");

	// Set default date here for now //
	//string start_date = "2017-1-1";
	//string end_date = "2017-1-31";

	// Need connection to live and sim database //
	// Problem!!!! //
/*
	// Copy/Seed the data //
	CSimulations sim;
	if (sim.CopySeed(&m_LiveDB, &m_SimDB, socket, system_id, SIM_SEED_DEEP, COPY_USERS_RECEIPTS, SIM_USER_MAX, SIM_RECEIPT_MAX, SIM_MIN_PRICE, SIM_MAX_PRICE, start_date, end_date) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - Problems with sim.CopySeed");

	// Run the simulation //  
	if (sim.Run(&m_LiveDB, &m_SimDB, socket, system_id, start_date, end_date) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - sim.Start() failed");
*/
	return true;
}

//////////////////////////////////
// Run cron to calc commissions //
//////////////////////////////////
bool CCommissionEngine::CronCommissions()
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CronCommissions - Startup() needs to be run");

	Debug(DEBUG_INFO, "CCommissionEngine::CronCommissions");

	m_pDB->m_ConnPool.Disable();

	m_pDB->Connect(m_pSettings);
	m_pDB->CronCommissions();
	return true;
}

////////////////////////////////////////////
// Rebuild given level for a given system //
////////////////////////////////////////////
bool CCommissionEngine::RebuildLevel(int system_id)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RebuildLevel - Startup() needs to be run");

	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);
	int socket = 0;
	m_pDB->RebuildLevel(socket, system_id);
	m_pDB->FlushLevels(socket);
	return true;
}

bool CCommissionEngine::RebuildAllLevels(const char *start_date, const char *end_date)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RebuildAllLevels - Startup() needs to be run");

	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);
	int socket = 0;
	m_pDB->RebuildAllLevels(socket, start_date, end_date);
	m_pDB->FlushLevels(socket);
	return true;
}

///////////////////////////////////////
// Seed Users and Receipts from live //
///////////////////////////////////////
bool CCommissionEngine::SeedFromLive(int system_id, const char *start_date, const char *end_date)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::SeedFromLive - Startup() needs to be run");

/*
	int socket = 0;

	// Problem - we need a connection to live and sim BOTH //

	if (system_id < 1)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - Error: system_id < 1");
	if (strlen(start_date) == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - Error: strlen(start_date) == 0");
	if (strlen(end_date) == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RunSim - Error: strlen(end_date) == 0");

	Debug(DEBUG_TRACE, "CCommissionEngine::RunSim - Beginning of simulations");
	m_LiveDB.Connect(&m_LiveSettings);
	m_SimDB.Connect(&m_SimSettings);

	// Needed for simulations //
	g_pDB = &m_LiveDB;
	g_pSimDB = &m_SimDB;

	int piggy_id = 1;
	system_id = 2;

	// DELETE Users from non-piggy system //
	stringstream ssDelUsers;
	if (m_pDB->ExecDB(true, socket, ssDelUsers << "DELETE FROM ce_users WHERE system_id=" << system_id) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Run - Problems DELETEing users before copy");

	// DELETE Receipts from non-piggy system //
	stringstream ssDelReceipts;
	if (m_pDB->ExecDB(true, socket, ssDelReceipts << "DELETE FROM ce_receipts WHERE system_id=" << system_id) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Run - Problems DELETEing users before copy");

	// Copy all Users from piggy system //
	ezCopy copy(g_pSimDB, g_pSimDB);
	if (copy.Users(socket, piggy_id, system_id) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Run - copy.Users() == false");

	// Copy Receipts from piggy system //
	if (copy.Receipts(socket, piggy_id, system_id) == false) //, const char *startdate, const char *enddate)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Run - copy.Receipts() == false");
	
	*/
	return true;
}

///////////////////////////
// Test stub for testing //
///////////////////////////
bool CCommissionEngine::Test()
{
/*	CceReceipt ceReceipt(g_pDB, "");
	int socket = 0;
	int system_id= 1;
	string qty = "1";
	string receipt_id = "238052";
	string user_id = "15525";
	string wholesale_price = "15";
	string wholesale_date = "2018-07-01 07:00:07";
	string retail_price = "25";
	string retail_date = "2018-07-01 07:00:07";
	string inv_type = "5";
	string commissionable = "true";
	string metadata = "OEVTEN-53340";
	string producttype = "1";
	ceReceipt.AddBulk(socket, system_id, qty, receipt_id, user_id, wholesale_price, wholesale_date, retail_price, retail_date, inv_type, commissionable, metadata, producttype);
*/
/*
	CValidate val;
	string wholesale_date = "2018-08-12 23:53:58 ";
	if (val.is_timestamp(wholesale_date) == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::Test - is_timestamp == false");
	else
		Debug(DEBUG_ERROR, "CCommissionEngine::Test - is_timestamp == true");
*/
	
	CezTok tok("asdf1, qwer22, jklt333, ilmo4, cat55555, dog66, ant", ", ");

	int index;
	for (index=0; index <= tok.GetMax(); index++)
	{
		string value = tok.GetValue(index);
		
		printf("index=%d, value=%s\n", index, value.c_str());
	}
	

	/*
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Test - Startup() needs to be run");

	CezCrypt crypt;
	string result = crypt.HMAC_Generate("##key##", "This is my data");

	Debug(DEBUG_WARN, "result", result);

	string user_id = crypt.HMAC_Verify("##key##", "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ0ZW5hbnRfaWQiOiIyIiwicm9sZSI6IlN1cGVyYWRtaW4iLCJvcmdJZCI6ImZvbzEyMyIsInN1YiI6MTA5LCJpc3MiOiJodHRwOi8vY29yZS5sb2NhbC9hcGkvZXh0ZXJuYWwvYXV0aGVudGljYXRlIiwiaWF0IjoxNTIzNjM1MzY3LCJleHAiOjE1MjYyMjczNjcsIm5iZiI6MTUyMzYzNTM2NywianRpIjoiYkFzRVA3em9pQ3NidFZIdCJ9.jHDpn8xGHLIb_9OLbUzQBa3RXSb57r9Q3I-cKS1Ff_8", "testclaim");
	if (user_id == "")
	{
		Debug(DEBUG_WARN, "HMAC_Verify == false");
	}
	else
	{
		Debug(DEBUG_WARN, "HMAC_Verify == true");
	}
	*/

	return true;
}

///////////////////////
// Run a memory test //
///////////////////////
bool CCommissionEngine::TestSS(stringstream& query)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::TestSS - Startup() needs to be run");

	string sql;
	sql = query.str();

	Debug(DEBUG_WARN, "CCommissionEngine::TestSS - ", sql);

	return true;
}

//////////////////////
// Built in testing //
//////////////////////
bool CCommissionEngine::SeedSystem(int sysuserid)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::SeedSystem - Startup() needs to be run");

	m_pSettings->m_ConnPoolCount = 1;

	TimeStart();

	//if (m_pDB == NULL)
	m_pDB->Connect(m_pSettings);
	
	// Add a system //
	int socket = 0;
	
	//int sysuserid = 1;
	string stacktype = "1";
	string systemname = "TestSales";
	string commtype = "1";
	CceSystem system(m_pDB, "");
	string json = system.Add(socket, sysuserid, stacktype, systemname, commtype, "0", "1", "10", "", "false", "0", "5", "", "", "", "", "", "", "", "true");
	//Debug(DEBUG_WARN, "json", json);

	// It's just easier to pull last system from database instead of parsing json //
	stringstream ss1;
	int system_id = m_pDB->GetFirstDB(socket, ss1 << "SELECT id FROM ce_systems WHERE sysuser_id=" << sysuserid << " ORDER BY id DESC LIMIT 1");

	Debug(DEBUG_MESSAGE, "New system_id", system_id);

	// Add Rank Rules //
	//int system_id = 1;
	string breakage = "false";
	CceRankRule rankrule(m_pDB, "");
	json = rankrule.Add(socket, system_id, "Squire", "1", "2", "1", "3", breakage, "0", "0", "0", "0", "");	
	//Debug(DEBUG_WARN, "json", json);

	json = rankrule.Add(socket, system_id, "Knight", "2", "2", "1000", "6", breakage, "0", "0", "0", "0", "");	
	//Debug(DEBUG_WARN, "json", json);

	json = rankrule.Add(socket, system_id, "Paladin", "3", "2", "10000", "9", breakage, "0", "0", "0", "0", "");	
	//Debug(DEBUG_WARN, "json", json);

	// Add Commission Rules //
	CceCommRule commrule(m_pDB, "");
	json = commrule.Add(socket, system_id, "1", "1", "false", "10", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "1", "2", "false", "5", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "2", "1", "false", "10", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "2", "2", "false", "5", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "2", "3", "false", "3", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "2", "4", "false", "2", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "2", "5", "false", "1", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "1", "false", "10", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "2", "false", "5", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "3", "false", "3", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "4", "false", "2", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "5", "false", "1", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "6", "false", "1", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	json = commrule.Add(socket, system_id, "3", "7", "false", "0.5", "1", "1", "1", "0");
	//Debug(DEBUG_WARN, "json", json);

	TimeEnd();

	return true;
}

////////////////////////////////////////
// Seed random data in a given system //
////////////////////////////////////////
bool CCommissionEngine::SeedData(int system_id, int recordcount, const char *start_date, const char *end_date)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::SeedData - Startup() needs to be run");

	//m_pSettings->m_ConnPoolCount = 1;

	TimeStart();

	int usercount = recordcount; //5000; // 50000
	int receiptcount = recordcount; //5000; // 100000

	int socket = 0;
	//g_ConnBank[socket] = true;
	m_pDB->Connect(m_pSettings);
	
	CSimulations sim;
	if (sim.SeedUsers(m_pDB, socket, system_id, usercount, SIM_DEEP_LVL1_MIN, SIM_DEEP_LVL1_MAX) == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::SeedSystem - Problems seeding users");

	if (sim.SeedReceipts(m_pDB, socket, system_id, usercount, receiptcount, 5, 250, start_date) == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::SeedSystem - Problems seeding receipts");

	Debug(DEBUG_DEBUG, "CCommissionEngine::SeedSystem - End of seeding system_id", system_id);

	TimeEnd();

	return true;
}

///////////////////////////////////////////
// Check for recursion loops on a system //
///////////////////////////////////////////
bool CCommissionEngine::CheckLoop(int system_id)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CheckLoop - Startup() needs to be run");

	if (m_pDB == NULL)
	{
		Debug(DEBUG_ERROR, "CCommissionEngine::CheckLoop - m_pDB == NULL");
		return false;
	}
	else if (m_pDB != NULL)
	{
		m_pDB->Connect(m_pSettings);
	}

	m_pDB->m_ConnPool.Disable();

	int socket = 0;
	bool recurr_parent = false;
	bool recurr_sponspor = false;
	recurr_parent = m_pDB->IsRecursionLoop(socket, system_id, UPLINE_PARENT_ID);
	recurr_sponspor = m_pDB->IsRecursionLoop(socket, system_id, UPLINE_SPONSOR_ID);

	if (recurr_parent == true)
		Debug(DEBUG_ERROR, "Recurrsion Loop found PARENT_ID");
	if (recurr_sponspor == true)
		Debug(DEBUG_ERROR, "Recurrsion Loop found SPONSOR_ID");

	// Make sure to exit out if an error was found //
	if ((recurr_parent == true) || (recurr_sponspor == true))
	{
		Debug(DEBUG_ERROR, "-----------------------------------------");
		return false;
	}

	return true;
}

//////////////////////////////////////////////
// Check for recursion loops on all systems //
//////////////////////////////////////////////
bool CCommissionEngine::CheckLoopAll(void)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CheckLoopAll - Startup() needs to be run");

	Debug(DEBUG_DEBUG, "CCommissionEngine::CheckLoop - Starting the check recursion loop for all systems");
	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	std::list <int> SystemsLL;
	int socket = 0;
	if (m_pDB->GetSystemsUsed(socket, 1, &SystemsLL) == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::CheckLoopAll - Could not grab systems");

	std::list<int>::iterator i;
	for (i=SystemsLL.begin(); i != SystemsLL.end(); ++i) 
	{
		CheckLoop((*i));
	}

	return true;
}

////////////////////////////////////////////////
// Reset the API for the given system user is //
////////////////////////////////////////////////
bool CCommissionEngine::ResetApiKey(int sysuserid)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::ResetApiKey - Startup() needs to be run");

	if (sysuserid == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::ResetApiKey - Error: sysuserid = 0");

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	// Grab salt from database //
	int socket = 0;
	std::string salt = m_pDB->GetSysUserSalt(socket, sysuserid);

	// Regenerate the hash //
	CezCrypt crypt2;
	std::string apikey = crypt2.GenSha256();
	std::string apikeyhash = crypt2.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), apikey.c_str()); // No password //

	// Update the apikey hash //
	if (m_pDB->UpdateSysUserApiKey(socket, sysuserid, apikeyhash.c_str()) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::ResetApiKey - Problem updating the apikey");

	Debug(DEBUG_MESSAGE, "apikey", apikey.c_str());
	return true;
}

//////////////////////////////////
// Reset a systemusers password //
//////////////////////////////////
bool CCommissionEngine::ResetSysUserPassword(string sysuser_id, string password)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::ResetSysUserPassword - Startup() needs to be run");
	if (sysuser_id.length() == 0)
		return Debug(DEBUG_ERROR, "The sysuser_id is empty");
	if (password.length() == 0)
		return Debug(DEBUG_ERROR, "The password is empty");

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->Connect(m_pSettings);
	int socket = 0;
	bool result = m_pDB->ResetSysUserPassword(socket, sysuser_id, password);
	if (result == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::ResetSysUserPassword - Unable to reset system user password");
	else
		Debug(DEBUG_MESSAGE, "CCommissionEngine::ResetSysUserPassword - Successfully reset system user password. Be sure to restart API service");

	return result;
}

///////////////////////////
// Reset a user password //
///////////////////////////
bool CCommissionEngine::ResetUserPassword(int system_id, string user_id, string password)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::ResetUserPassword - Startup() needs to be run");
	if (system_id == 0)
		return Debug(DEBUG_ERROR, "The system_id is empty");
	if (user_id.length() == 0)
		return Debug(DEBUG_ERROR, "The user_id is empty");
	if (password.length() == 0)
		return Debug(DEBUG_ERROR, "The password is empty");

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->Connect(m_pSettings);
	int socket = 0;

	// Is it a valid user_id? //

	CceAffiliate affiliate(m_pDB, "");
	string retval = affiliate.PasswordReset(socket, system_id, user_id, password);

	if (strstr(retval.c_str(), "{\"success\":{\"status\":\"200\"}}") == NULL)
	{
		Debug(DEBUG_ERROR, "Problems resetting user password");
		return Debug(DEBUG_ERROR, "retval", retval.c_str());
	}
	else
	{
		Debug(DEBUG_MESSAGE, "Successfully reset user password");
	}
	return true;
}

//////////////////////////////////////
// Rebuild all uplines for a system //
//////////////////////////////////////
bool CCommissionEngine::RebuildUpline(string system_id)
{
	Debug(DEBUG_TRACE, "CCommissionEngine::RebuildUpline - TOP");

	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RebuildUpline - Startup() needs to be run");
	if (system_id.size() == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RebuildUpline - system_id empty");

	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	CceUser users(m_pDB, "");
	int socket = 0;
	if (users.RebuildAllUpline(m_pDB, socket, atoi(system_id.c_str())) == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RebuildUpline - Problems RebuildAllUpline");

	return true;
}

////////////////////////
// Handle init united //
////////////////////////
bool CCommissionEngine::InitUnited()
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::InitUnited - Startup() needs to be run");

	// Run drop database command //
#ifdef COMPILE_LOCAL
	if (system("dropdb ce") != 0)
		Debug(DEBUG_ERROR, "CCommissionEngine::GenTestData - error system(dropdb ce)");
#endif
	
	// Create the master database //
	CreateMaster();

	CSeed seed;
	seed.UnitedLeagueGenBase(m_pDB, 1);
	seed.UnitedLeagueGenData(m_pDB, 1);

	return true;
}

////////////////////////////
// Run United Commissions //
////////////////////////////
bool CCommissionEngine::United(const char *start_date, const char *end_date)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::United - Startup() needs to be run");

	return Debug(DEBUG_ERROR, "CCommissionEngine::United - use commrun command");

	Debug(DEBUG_MESSAGE, "CCommissionEngine::United - United Commissions Started");

	//m_pSettings->m_ConnPoolCount = 1;
	//m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	int socket = 0;

	string startdate = start_date;
 	string enddate = end_date;

	// Remove this after commissions have been stablized //

 	//m_pDB->SetRankOverride(4); // This needs to be remove when United Commissions stableize //
 	//m_pDB->CronCommMonth(socket, ALTCORE_UNITED_MAIN, "10", startdate, enddate); // United Core Type // Rank needs to be achiveved here //

	int comm_type = 1;

	CCommissions comm_core;
	Debug(DEBUG_DEBUG, "CDb::CronProcLoop - Before comm.Run");
	comm_core.Run(m_pDB, socket, 1, comm_type, false, true, start_date, end_date, "", true);
return true;
 	Debug(DEBUG_ERROR, "CCommissionEngine::United - Start processing all games...");

 	int proc_count = m_Settings.m_MultProcCount; // Make this set in ini file //
 	CCommissions comm;
 	if (comm.RunSpawnProc(m_pDB, socket, proc_count, m_pSettings, startdate.c_str(), enddate.c_str()) == false)
 		Debug(DEBUG_ERROR, "CCommissionEngine::United - RunSpawnProc returned false");
 	Debug(DEBUG_ERROR, "Finished processing all games!!!");

	m_pDB->CronCheckMatch(socket, startdate.c_str(), enddate.c_str());

	// Build the ledger totals for system=1 //
	m_pDB->RebuildLedgerTotals(false, socket, 1);
	m_pDB->RebuildReceiptTotals(false, socket, 1);

	Debug(DEBUG_ERROR, "CCommissionEngine::United - United Commissions Done");
}

////////////////////////////////////
// Clear out a bad batch to rerun //
////////////////////////////////////
bool CCommissionEngine::ClearBatch(int batch_id)
{	
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::ClearBatch - Startup() needs to be run");

	m_pSettings->m_ConnPoolCount = 1;
	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	int socket = 0;
	m_pDB->ClearBatch(socket, batch_id);

	Debug(DEBUG_MESSAGE, "CCommissionEngine::ClearBatch - Cleared batch_id", batch_id);

	return true;
}

///////////////////////////////
// Rebuild the ledger totals //
///////////////////////////////
bool CCommissionEngine::RebuildTotals(int system_id, int batch_id)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::RebuildTotals - Startup() needs to be run");

	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	int socket = 0;

	if (m_pDB->RebuildLedgerTotals(false, socket, system_id) == NULL)
		Debug(DEBUG_ERROR, "CCommissionEngine::RebuildTotals - RebuildLedgerTotals Error");
	if (m_pDB->RebuildReceiptTotals(false, socket, system_id) == NULL)
		Debug(DEBUG_ERROR, "CCommissionEngine::RebuildTotals - RebuildReceiptsTotals Error");

	CezReports reports(m_pDB, "");
	if (reports.CalcAll(socket, system_id, batch_id) == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::RebuildTotals - CalcUsersAudit Error");

	return true;
}

///////////////////
// Handle resume //
///////////////////
bool CCommissionEngine::Resume(int system_id)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::Resume - Startup() needs to be run");

	m_pDB->m_ConnPool.Disable();
	m_pDB->Connect(m_pSettings);

	int socket = 0; // Use -2 cause it doesn't naturally occur //

	string startdate = m_pDB->GetStartDateCP(socket, system_id);
	string enddate = m_pDB->GetEndDateCP(socket, system_id);

	CCommissions comm;
	comm.Run(m_pDB, socket, 1, 1, false, true, startdate.c_str(), enddate.c_str(), "", true);

	return true;
}

//////////////////////
// Run a commission //
//////////////////////
bool CCommissionEngine::CommRun(int system_id, const char *start_date, const char *end_date)
{
	if (m_StartedUp == false)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CommRun - Startup() needs to be run");

	TimeStart();

	if (system_id == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CommRun - system_id cannot be 0");
	if (strlen(start_date) == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CommRun - start_date cannot be empty");
	if (strlen(end_date) == 0)
		return Debug(DEBUG_ERROR, "CCommissionEngine::CommRun - end_date cannot be empty");

//	m_pDB->m_ConnPool.Disable();
	m_Settings.m_ApiConnPoolDynamic = false;
	m_pDB->Connect(m_pSettings);
	
	int socket = 0;

	// Set the timezone for the connection pool //
	g_TimeZone = m_pDB->GetFirstCharDB(socket, "SELECT value FROM ce_settings WHERE system_id=0 AND varname='timezone'");

	// Call commission class to do calculations //
	int commtype = m_pDB->GetSystemCommType(socket, system_id);

	string compression_str = m_pDB->GetSystemCompression(socket, system_id);
	bool compression = true;
	if (compression_str == "false")
		compression = false;
	else if (compression_str == "true")
		compression = true;

	CCommissions comm;
	string json = comm.Run(m_pDB, socket, system_id, commtype, false, true, start_date, end_date, "", compression);

	Debug(DEBUG_DEBUG, "CCommissionEngine::CommRun - json", json.c_str());

	TimeEnd();

	return true;
}
