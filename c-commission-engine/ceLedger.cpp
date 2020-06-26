#include "ceLedger.h"
#include "db.h"
#include <stdlib.h> // atoi //
#include "payments.h"

/////////////////
// Constructor //
/////////////////
CceLedger::CceLedger(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("ledger", "ce_ledger");

	m_Columns.push_back("id");
	m_Columns.push_back("system_id");
	m_Columns.push_back("batch_id");
	m_Columns.push_back("ref_id");
	m_Columns.push_back("user_id");
	m_Columns.push_back("ledger_type");
	m_Columns.push_back("amount");
	m_Columns.push_back("from_system_id");
	m_Columns.push_back("from_user_id");
	m_Columns.push_back("event_date");
	m_Columns.push_back("generation");
	m_Columns.push_back("authorized");
	m_Columns.push_back("transaction_id");
	m_Columns.push_back("disabled");
	m_Columns.push_back("created_at");
	m_Columns.push_back("updated_at");

	CezJson::SetOrigin(origin);
}

////////////////////////
// Add a ledger entry //
////////////////////////
const char *CceLedger::Add(int socket, int system_id, string batch_id, string user_id, string ledger_type, string amount, string event_date)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::add error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "ledger::add error", "The batchid is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "ledger::add error", "The userid is not valid");
	if (is_number(ledger_type) == false)
		return SetError(400, "API", "ledger::add error", "The ledgertype is not numeric");
	if (is_decimal(amount) == false)
		return SetError(400, "API", "ledger::add error", "The amount is not decimal");
	if (is_date(event_date) == false)
		return SetError(400, "API", "ledger::add error", "The evendate is not in correct date format YYYY-MM-DD");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["batch_id"] = batch_id;
	columns["ref_id"] = "-1"; // -1 is added through API //
	columns["user_id"] = user_id;
	columns["ledger_type"] = ledger_type;
	columns["amount"] = amount;
	columns["event_date"] = event_date;
	columns["authorized"] = "true";

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, -1);
}

/////////////////////
// Edit Pool Rules //
/////////////////////
const char *CceLedger::Edit(int socket, int system_id, string id, string batch_id, string user_id, string ledger_type, string amount, string event_date)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "ledger::edit error", "The id is not numeric");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "ledger::edit error", "The batchid is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "ledger::edit error", "The userid is not valid");
	if (is_number(ledger_type) == false)
		return SetError(400, "API", "ledger::edit error", "The ledgertype is not numeric");
	if (is_decimal(amount) == false)
		return SetError(400, "API", "ledger::edit error", "The amount is not decimal");
	if (is_date(event_date) == false)
		return SetError(400, "API", "ledger::edit error", "The evendate is not in correct date format YYYY-MM-DD");
	
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["batch_id"] = batch_id;
	columns["ref_id"] = "-1"; // -1 is added through API //
	columns["user_id"] = user_id;
	columns["ledger_type"] = ledger_type;
	columns["amount"] = amount;
	columns["event_date"] = event_date;
	columns["authorized"] = "true";

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

///////////////////////////////
// Get a given ledger record //
///////////////////////////////
const char *CceLedger::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "ledger::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("ref_id");
	columns.push_back("user_id");
	columns.push_back("ledger_type");
	columns.push_back("amount");
	columns.push_back("event_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}

//////////////////////////////////////////
// Query all of ledger for given system //
//////////////////////////////////////////
const char *CceLedger::QueryLedger(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::query error", "A database connection needs to be made first");

	map <string, int> mask;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, m_Columns, mask, search, sort);
}

///////////////////////////////////////////////////
// Query all of ledger for given system and user //
///////////////////////////////////////////////////
const char *CceLedger::QueryLedgerUser(int socket, int system_id, string user_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::queryuser error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "ledger::queryuser error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	search = search+"&userid="+user_id;
	sort = "orderby=userid&"+sort;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, m_Columns, mask, search, sort);
}

///////////////////////////
// Query ledger by batch //
///////////////////////////
const char *CceLedger::QueryLedgerBatch(int socket, int system_id, string batch_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::querybatch error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "ledger::querybatch error", "The batchid can only be a number");

	map <string, int> mask;

	search = search+"&batchid="+batch_id;
	sort = "orderby=batchid&"+sort;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, m_Columns, mask, search, sort);
}

/////////////////////////////////////////////
// Query all of ledger for a valid balance //
/////////////////////////////////////////////
const char *CceLedger::QueryLedgerBalance(int socket, int system_id, string search, string orderby, string orderdir, string offset, string limit)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::queryledgerbalance error", "A database connection needs to be made first");

	if (m_pDB == NULL)
		return SetError(409, "API", "ledger::queryledgerbalance error", "A database connection needs to be made first");

	// Handle pagination values //
	if ((search.length()!=0) && (is_alphanum(search) == false))
		return SetError(400, "API", "ledger::queryledgerbalance error", "The search string needs to be in a date format: 1-1-2000");
	if ((orderdir != "asc") && (orderdir != "desc"))
		return SetError(409, "API", "ledger::queryledgerbalance error", "The orderdir variable needs to be either asc or desc");
	if (is_number(offset) == false)
		return SetError(400, "API", "ledger::queryledgerbalance error", "The offset is not numeric");
	if (is_number(limit) == false)
		return SetError(400, "API", "ledger::queryledgerbalance error", "The limit is not numeric");

	if ((orderby != "id") && 
		(orderby != "systemid") && 
		(orderby != "userid") && 
		(orderby != "amount") && 
		(orderby != "createdat") && 
		(orderby != "updatedat"))
		return SetError(409, "API", "user::queryalt error", "The orderby variable needs to be either id, systemid, userid, amount, createdat or updatedat");

	// Repair columns cause underscore (_) not allowed in HTTP header section //
	if (orderby == "userid")
		orderby = "user_id";
	else if (orderby == "systemid")
		orderby = "system_id";
	else if (orderby == "createdat")
		orderby = "created_at";
	else if (orderby == "updatedat")
		orderby = "updated_at";

	// Handle search string //
	string searchsql;
	if (search.length()!=0)
	{
		// Handle dates and timestamps differently //
		if (orderby == "id")
			searchsql = " AND "+orderby+"='"+search+"'";
		else if ((orderby == "signup_date") || (orderby == "created_at") || (orderby == "updated_at"))
			searchsql = " AND "+orderby+"::DATE='"+search+"%'";
		else
			searchsql = " AND "+orderby+"::TEXT ILIKE '"+search+"'";
	}

	// Build SQL ending sorting/limit/offset here //
	string sqlend = searchsql+" ORDER BY "+orderby+" "+orderdir+" OFFSET "+offset+" LIMIT "+limit;

	return m_pDB->QueryLedgerBalance(socket, system_id, searchsql, sqlend);
}