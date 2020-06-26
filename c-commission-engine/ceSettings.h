#ifndef _CESETTINGS_H
#define _CESETTINGS_H

#include "dbplus.h"
#include "dbbulk.h"
#include <string>

using namespace std;

/////////////////////////
// Handle signup bonus //
/////////////////////////
class CceSettings : private CDbPlus
{
public:
	CceSettings(CDb *pDB, string origin);

	const char *Query(int socket, string search, string sort);
	const char *QuerySystem(int socket, int system_id, string search, string sort);
	const char *Get(int socket, int system_id, string webpage, string user_id, string varname);
	const char *Set(int socket, string webpage, string user_id, string varname, string value);
	const char *Disable(int socket, string varname);
	const char *Enable(int socket, string varname);
	const char *SetSystem(int socket, int system_id, string webpage, string user_id, string varname, string value);
	const char *GetTimeZones(int socket, string sort);

private:
	CDb *m_pDB;
};

#endif