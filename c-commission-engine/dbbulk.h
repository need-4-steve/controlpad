#ifndef _DBBULK_H
#define _DBBULK_H

#include "debug.h"

#include <string>
#include <map>

using namespace std;

class CDb;

///////////////////////////////////
// Handle date window comparison //
///////////////////////////////////
class CDbBulk: public CDebug
{
public:
	CDbBulk();
	int BulkAdd(CDb *pDB, int socket, string tablename, map <string, string> columns, string *strSQL, int count);
	bool BulkFinish(CDb *pDB, int socket, string *strSQL);

	int BulkUpdate(CDb *pDB, int socket, string tablename, string column, string value, string condition, string *strSQL, int count);
};

#endif