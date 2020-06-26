#ifndef _EZ_COPY_H
#define _EZ_COPY_H

#include "db.h"
#include "dbbulk.h"
#include "dbplus.h"
#include "debug.h"
#include "validate.h"

#include <string>

/////////////////////////////////////
// Package bankaccount information //
/////////////////////////////////////
class ezCopy : private CDbPlus, CDbBulk
{
public:
	ezCopy(CDb *pFromDB, CDb *pToDB);
	bool Users(int socket, int from_system_id, int to_system_id);
	bool Receipts(int socket, int from_system_id, int to_system_id); //, const char *startdate, const char *enddate); // Not enought time. Come back //

private:
	bool CopyTable(int socket, int from_system_id, int to_system_id, string tablename, string seqname, string system_id_column, list <string> columns, string endsql);

	CDb *m_pFromDB;
	CDb *m_pToDB;
};

#endif