#include "cePools.h"
#include "db.h"
#include <stdlib.h> // atoi // 
 
/////////////////
// Constructor //
/////////////////
CcePools::CcePools(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("pool", "ce_pools");
	CezJson::SetOrigin(origin);
}

///////////////////
// Add Pool Pots //
///////////////////
const char *CcePools::Add(int socket, int system_id, string amount, string start_date, string end_date)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "pools::add error", "A database connection needs to be made first");
	if (is_number(amount) == false)
		return SetError(400, "API", "pools::add error", "The amount is not numeric");
	if (is_date(start_date) == false)
		return SetError(400, "API", "pools::add error", "The startdate is not in date format YYY-MM-DD");
	if (is_date(end_date) == false)
		return SetError(400, "API", "pools::add error", "The enddate is not in date format YYY-MM-DD");

	start_date = FixDate(start_date);
	end_date = FixDate(end_date);

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["amount"] = amount;
	columns["start_date"] = start_date;
	columns["end_date"] = end_date;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxPools);
}

////////////////////
// Edit Pool Pots //
////////////////////
const char *CcePools::Edit(int socket, int system_id, string id, string amount, string start_date, string end_date)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "pools::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "pools::edit error", "The id is not numeric");
	if (is_number(amount) == false)
		return SetError(400, "API", "pools::edit error", "The amount is not numeric");
	if (is_date(start_date) == false)
		return SetError(400, "API", "pools::edit error", "The startdate is not in date format YYY-MM-DD");
	if (is_date(end_date) == false)
		return SetError(400, "API", "pools::edit error", "The enddate is not in date format YYY-MM-DD");

	start_date = FixDate(start_date);
	end_date = FixDate(end_date);
	
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["amount"] = amount;
	columns["start_date"] = start_date;
	columns["end_date"] = end_date;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

/////////////////////
// Query Pool Pots //
/////////////////////
const char *CcePools::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "pools::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("amount");
	columns.push_back("start_date");
	columns.push_back("end_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////
// Disable Pool Pots //
///////////////////////
const char *CcePools::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "pools::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "pools::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////
// Enable Pool Pots //
///////////////////////
const char *CcePools::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "pools::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "pools::enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////
// Enable Pool Pots //
///////////////////////
const char *CcePools::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "pools::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "pools::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("amount");
	columns.push_back("start_date");
	columns.push_back("end_date");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}

/////////////////////////////////////////
// Process Commissions on the Pool Pot //
/////////////////////////////////////////
const char *CcePools::RunPool(int socket, int system_id, string poolid)
{
	return m_pDB->RunPoolPot(socket, system_id, atoi(poolid.c_str()));
}