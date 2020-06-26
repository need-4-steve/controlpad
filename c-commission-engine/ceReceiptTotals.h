#ifndef _CERECEIPTTOTALS_H
#define _CERECEIPTTOTALS_H

#include "dbplus.h"

#include <string>

using namespace std;

class CceReceiptTotals : private CDbPlus
{
public:
	CceReceiptTotals(CDb *pDB, string origin);
	const char *QuerySum(int socket, int system_id, string search, string sort);

private:
	CDb *m_pDB;
};

#endif