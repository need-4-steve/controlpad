#ifndef _CELEDGER_H
#define _CELEDGER_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceLedger : public CDbPlus
{
public:
	CceLedger(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string batch_id, string user_id, 
		string ledger_type, string amount, string event_date);
	const char *Edit(int socket, int system_id, string id, string batch_id, string user_id, 
		string ledger_type, string amount, string event_date);
	const char *Get(int socket, int system_id, string id);

	const char *QueryLedger(int socket, int system_id, string search, string sort);
	const char *QueryLedgerUser(int socket, int system_id, string user_id, string search, string sort);
	const char *QueryLedgerBatch(int socket, int system_id, string batch_id, string search, string sort);
	const char *QueryLedgerBalance(int socket, int system_id, string search, string orderby, string orderdir, string offset, string limit);

private:
	string m_Json;
	list<string> m_Columns;
	CDb *m_pDB;
};

#endif