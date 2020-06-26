#include "ceReceiptTotals.h"
#include "db.h"
#include "ceUser.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceReceiptTotals::CceReceiptTotals(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("receipts", "ce_receipt_totals");
	CezJson::SetOrigin(origin);
}

////////////////////////////
// Query Receipt Sumation //
////////////////////////////
const char *CceReceiptTotals::QuerySum(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::querysum error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("count");
	columns.push_back("amount");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}
