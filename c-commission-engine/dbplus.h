#ifndef _DBPLUS_H
#define _DBPLUS_H

#include "debug.h"
#include "db.h"
#include "ezJson.h"
#include "validate.h"
#include "convert.h"

using namespace std;

///////////////////////////////////
// Handle date window comparison //
///////////////////////////////////
class CDbPlus: public CDebug, public CezJson, public CValidate, public CConvert
{
public:
	CDbPlus();
	bool Setup(string classref, string tablename); // Call in constructor of inherited class //

	const char *AddDB(CDb *pDB, int socket, int sysuser_id, int system_id, list <string> unique, map <string, string> columns, map <string, int> mask, int maxlimit);
	const char *EditDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column, list <string> unique, map <string, string> columns, map <string, int> mask);
	const char *QueryDB(CDb *pDB, int socket, int sysuser_id, int system_id, list<string> columns, map <string, int> mask, string search, string sort);
	const char *QuerySearchRawDB(CDb *pDB, int socket, int sysuser_id, int system_id, list<string> columns, map <string, int> mask, string searchraw, string sort);
	const char *EnableDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column);
	const char *DisableDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column);
	const char *DeleteDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column); // Only allow delete in certain circumstances //
	const char *GetDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column, list <string> columns, map <string, int> mask); // Get a single record //
	const char *CountDB(CDb *pDB, int socket, string column, string value);

	string ValidateSort(string sort);
	string BuildSQLEnd(string sort);

	// Needed to check for user_id before adding receipt //
	bool IsPresent(CDb *pDB, int socket, int system_id, string column, string value);

	const char *QueryInternalDB(CDb *pDB, int socket, int sysuser_id, int system_id, list<string> columns, map <string, int> mask, string searchsql, string sqlend);
//private:
	const char *PagCheck(list <string> columns, string search, string sort);
	string AddUnderScore(string orderby);
	string RemoveUnderScore(string orderby);
	string PagBuildSearch(int sysuser_id, int system_id); // Simplified  for Add, Edit, etc... //
	string PagBuildSearch(int sysuser_id, int system_id, list<string> columns, string search);
	string QStrValue(string qstring, string varname);

	bool m_IsSetup;
	string m_ClassRef;
	string m_Tablename;
	string m_Json;
};

#endif