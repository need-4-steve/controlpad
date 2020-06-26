#include "ceLedgerTotals.h"
#include "db.h"
#include <stdlib.h> // atoi //
#include "payments.h"

/////////////////
// Constructor //
/////////////////
CceLedgerTotals::CceLedgerTotals(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("ledger", "ce_ledger_totals");

	m_Columns.push_back("id");
	m_Columns.push_back("system_id");
	m_Columns.push_back("user_id");
	m_Columns.push_back("amount");
	m_Columns.push_back("created_at");
	m_Columns.push_back("updated_at");

	CezJson::SetOrigin(origin);
}

/////////////////////////////////////////////
// Query all of ledger for a valid balance //
/////////////////////////////////////////////
const char *CceLedgerTotals::QueryBalance(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::querybatch error", "A database connection needs to be made first");

	map <string, int> mask;

	m_Json = CDbPlus::QueryDB(m_pDB, socket, 0, system_id, m_Columns, mask, search, sort);
	return m_Json.c_str();
}