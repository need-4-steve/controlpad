#ifndef _EZENTRY_H
#define _EZENTRY_H

#include "db.h"
#include "debug.h"
#include "ezSettings.h"

#include <string>

using namespace std;

///////////////////////////////////////
// Manage Ruby entry point functions //
///////////////////////////////////////
class CezEntry : public CDebug
{
public:
	CezEntry();
	~CezEntry();
	bool Startup(CDb *pDB, CezSettings *pSettings); // Linux //

	// United's Ruby Entry Functions //
	//bool StartupRuby(string dbname, string username, string password, string hostname); // Ruby //
	bool StartupRuby(string inifile); // Ruby //
	void SetDebugDisplay(string display);
	void SetDebugLevel(string level);
	bool InitTables(string haspass);
	bool DropTables(void);
	bool RebuildLevels(string system_id);

	CDb *m_pDB; // Our connection and database functions //
	
	CezSettings m_RubySettings; // Settings shortcut for ruby //
	CezSettings *m_pSettings; // Pointer to commission engine settings //
	CezSettings *m_pSimSettings; // Pointer to commission engine settings //
};

#endif