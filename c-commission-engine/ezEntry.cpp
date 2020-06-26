#include "ezEntry.h"
#include "db.h"
#include "migrations.h"
#include "CommissionEngine.h"
#include "seed.h"

CCommissionEngine g_RubyEng; 

/////////////////
// Constructor //
/////////////////
CezEntry::CezEntry()
{
	m_pDB = NULL;
}

////////////////
// Destructor //
////////////////
CezEntry::~CezEntry()
{
	Debug(DEBUG_TRACE, "CezEntry::~CezEntry - Destructor");
}

/////////////////////////////////////
// Startup the database connection //
/////////////////////////////////////
bool CezEntry::Startup(CDb *pDB, CezSettings *pSettings)
{
	if (pSettings == NULL)
		return Debug(DEBUG_DEBUG, "CezRecv::Startup - pLiveSettings == NULL");

	// Set core pointers //
	m_pDB = pDB;
	m_pSettings = pSettings;

	// Handle GLOBAL pointers //
//	g_pDB = pDB;
	
	// Do connection to database //
	if (pDB->Connect(pSettings) == false)
		return Debug(DEBUG_ERROR, "CezEntry::Startup - live - pDB->Connect == false");

	return true;
}

/*
//////////////////
// Ruby startup //
//////////////////
bool CezEntry::StartupRuby(string dbname, string username, string password, string hostname)
{
	//Debug(DEBUG_DEBUG, "CezRecv::StartupRuby - dbname = ", dbname.c_str());
	//Debug(DEBUG_DEBUG, "CezRecv::StartupRuby - username = ", username.c_str());
	//Debug(DEBUG_DEBUG, "CezRecv::StartupRuby - password = ", password.c_str());
	//Debug(DEBUG_DEBUG, "CezRecv::StartupRuby - hostname = ", hostname.c_str());

	m_RubySettings.m_DBType = 2; //dbtype;
	m_RubySettings.m_DatabaseName = dbname; //dbname;
	m_RubySettings.m_Username = username; //username;
	m_RubySettings.m_Password = password; //password;
	m_RubySettings.m_Hostname = hostname; //hostname;
	//return Startup(&m_RubySettings, &m_SimSettings);

	Debug(DEBUG_ERROR, "CezEntry::StartupRuby - Startup needs to be fixed here");

	return false;
}
*/
///////////////////////////////////////////////////////////////
// Run Startup with db conn settings pulled from config file //
///////////////////////////////////////////////////////////////
bool CezEntry::StartupRuby(string inifile)
{
	//string inifile = "live";
	if (g_RubyEng.Startup(inifile) == false)
		return Debug(DEBUG_ERROR, "CezEntry::StartupRuby2 - comm.Startup(inifile) == false");

	m_RubySettings.m_DBType = 1; //2; //dbtype;
	m_RubySettings.m_DatabaseName = g_RubyEng.m_Settings.m_DatabaseName; //dbname;
	m_RubySettings.m_Username = g_RubyEng.m_Settings.m_Username; //username;
	m_RubySettings.m_Password = g_RubyEng.m_Settings.m_Password; //password;
	m_RubySettings.m_Hostname = g_RubyEng.m_Settings.m_Hostname; //hostname;

	if (g_RubyEng.m_DB.Connect(&g_RubyEng.m_Settings) == false)
		return false;

	//return Startup(&comm.m_Settings);
	return true;
}

///////////////////////////
// Set the error display //
///////////////////////////
void CezEntry::SetDebugDisplay(string display)
{
	if (display.size() == 0)
		Debug(DEBUG_ERROR, "CezEntry::SetDebugDisplay - display parameter needed");

	CDebug::SetDisplay(atoi(display.c_str()));
}

///////////////////////////////
// Set the debug error level //
//////////////////////////////
void CezEntry::SetDebugLevel(string level)
{
	if (level.size() == 0)
		Debug(DEBUG_ERROR, "CezEntry::SetDebugDisplay - level parameter needed");

	CDebug::SetLevel(atoi(level.c_str()));
}

////////////////////////////////////
// Initialize the database tables //
////////////////////////////////////
bool CezEntry::InitTables(string hashpass)
{
	if (m_pDB == NULL)
		return Debug(DEBUG_ERROR, "CezEntry::InitTables - m_pDB == NULL");
	if (m_pDB->IsConnected() == false)
		return Debug(DEBUG_ERROR, "CezEntry::InitTables - IsConnected() == false");

	CMigrations migrate(m_pDB, hashpass.c_str(), "init");

#ifdef COMPILE_UNITED
	CSeed seed;
	int system_id = 1;
	seed.UnitedLeagueGenBase(m_pDB, system_id);
#endif

	return true;
}

//////////////////////////////
// Drop the database tables //
//////////////////////////////
bool CezEntry::DropTables(void)
{
	if (m_pDB == NULL)
		return Debug(DEBUG_ERROR, "CezEntry::DropTables - m_pDB == NULL");
	if (m_pDB->IsConnected() == false)
		return Debug(DEBUG_ERROR, "CezEntry::DropTables - IsConnected() == false");

	CMigrations migrate(m_pDB, "NONE", "rollback");
	return true;
}

//////////////////////////////
// Rebuild the levels table //
//////////////////////////////
bool CezEntry::RebuildLevels(string system_id)
{
	if (system_id.size() == 0)
		Debug(DEBUG_ERROR, "CezEntry::SetDebugDisplay - system_id parameter needed");

	if (m_pDB == NULL)
		return Debug(DEBUG_ERROR, "CezEntry::DropTables - m_pDB == NULL");
	if (m_pDB->IsConnected() == false)
		return Debug(DEBUG_ERROR, "CezEntry::DropTables - IsConnected() == false");

	int iSystem_id = atoi(system_id.c_str());

	return m_pDB->RebuildLevel(0, iSystem_id);
}