#ifndef _CELEDGERTOTALS_H
#define _CELEDGERTOTALS_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceLedgerTotals : public CDbPlus
{
public:
	CceLedgerTotals(CDb *pDB, string origin);
	const char *QueryBalance(int socket, int system_id, string search, string sort);

private:
	string m_Json;
	list<string> m_Columns;
	CDb *m_pDB;
};

#endif