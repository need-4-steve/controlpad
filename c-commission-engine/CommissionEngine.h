#ifndef _COMMISSION_ENGINE_H
#define _COMMISSION_ENGINE_H

////////////////////////
// CommissionEngine.h //
////////////////////////

#include <string>

#include "debug.h"
#include "db.h"
#include "ezNetwork.h"
#include "ezEntry.h"
#include "commissions.h"
#include "ezSettings.h"

// Types of ways to handle incoming data //
#define PROC_FCGI		1 // Depricated //
#define PROC_SOCKETS	2
#define PROC_SSL		3

//#define LIVE_INI_FILENAME	"/etc/ceapi/ce.settings.ini"
//#define SIM_INI_FILENAME	"/etc/ceapi/sim.settings.ini"
#define INI_PATH			"/etc/ceapi/"

////////////////////////////////////
// Manage all pieces in one place //
////////////////////////////////////
class CCommissionEngine : public CDebug
{
public:
	CCommissionEngine(); 
	bool Startup(string inifile); // Load .ini setting here on startup //
	bool LoadINI(CezSettings *psettings, const char *inifilename); // Load values from .ini file //
	bool CreateMaster(); // Create master database //
	bool Migrate(); // Create master database //
	bool Rollback();
	bool API(); // Startup the commission engine //
	bool RunSim(int system_id, string start_date, string end_date);
	bool CronCommissions(); // Run cron to calc commissions //

	bool RebuildLevel(int system_id);
	bool RebuildAllLevels(const char *start_date, const char *end_date);

	bool SeedFromLive(int system_id, const char *start_date, const char *end_date);

	bool Test(); // Built in testing //
	bool TestSS(stringstream& query);

	bool SeedSystem(int sysuserid);
	bool SeedData(int system_id, int recordcount, const char *start_date, const char *end_date);
	bool CommRun(int system_id, const char *start_date, const char *end_date);

	bool InitUnited(void);
	bool United(const char *start_date, const char *end_date); // Drop database, create and generate specific test data //
	void CalcUsedSpeed(int start_sys_id, int end_sys_id, const char *start_date, const char *end_date);

	bool ClearBatch(int batch_id);
	bool RebuildTotals(int system_id, int batch_id);

	bool Resume(int system_id);

	bool CheckLoop(int system_id);
	bool CheckLoopAll(void);

	bool ResetApiKey(int sysuserid);
	bool ResetSysUserPassword(string sysuser_id, string password);
	bool ResetUserPassword(int system_id, string user_id, string password);
	bool RebuildUpline(string system_id);

	
//private:

	CDb m_DB; // Our connection and database functions //
	CezNetwork m_Network; // Handle incoming network connections //
	CezSettings m_Settings;

	CDb *m_pDB; // Needed to reuse db connection on checking for recursion loops //
	CezSettings *m_pSettings;

	CezEntry m_LiveEntry;
	//CezEntry m_SimEntry;

	bool m_StartedUp;

};

#endif