#include "ceBonus.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceBonus::CceBonus(CDb *pDB, string origin)
{
	m_pDB = pDB;
	m_BulkCount = 0;

	CDbPlus::Setup("bonus", "ce_bonus");
	CezJson::SetOrigin(origin);
}

//////////////////////
// Add bonus record //
//////////////////////
const char *CceBonus::Add(int socket, int system_id, string user_id, string amount, string bonus_date)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::add error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bonus::add error", "The user_id is not numeric");
	if (is_decimal(amount) == false)
		return SetError(400, "API", "bonus::add error", "The amount is not decimal");
	if (is_date(bonus_date) == false)
		return SetError(400, "API", "bonus::add error", "The bonus_date is not a date");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = user_id;
	columns["amount"] = amount;
	columns["bonus_date"] = bonus_date;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxBonuses);
}

///////////////////////
// Edit bonus record //
///////////////////////
const char *CceBonus::Edit(int socket, int system_id, string id, string user_id, string amount, string bonus_date)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "bonus::edit error", "The id is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bonus::edit error", "The user_id is not numeric");
	if (is_decimal(amount) == false)
		return SetError(400, "API", "bonus::edit error", "The amount is not decimal");
	if (is_date(bonus_date) == false)
		return SetError(400, "API", "bonus::edit error", "The bonus_date is not a date");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["user_id"] = user_id;
	columns["amount"] = amount;
	columns["bonus_date"] = bonus_date;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

/////////////////////////
// Query bonus records //
/////////////////////////
const char *CceBonus::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("bonus_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////////
// Query bonus user records //
//////////////////////////////
const char *CceBonus::QueryUser(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::queryuser error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bonus::queryuser error", "The user_id is not numeric");

	return m_pDB->QueryBonusUser(socket, system_id, user_id.c_str());
}

//////////////////////////
// Disable bonus record //
//////////////////////////
const char *CceBonus::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "bonus::edit error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

/////////////////////////
// Enable bonus record //
/////////////////////////
const char *CceBonus::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "bonus::edit error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

////////////////////////
// Get a bonus record //
////////////////////////
const char *CceBonus::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bonus::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "bonus::edit error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("bonus_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}

///////////////////////////////////////////////////
// Allow Bulk Adding when doing a commission run //
///////////////////////////////////////////////////
bool CceBonus::BulkAdd(int socket, int system_id, int batch_id, string user_id, double amount, const char *bonus_date)
{
	if (m_pDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CceBonus::BulkAdd - A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CceBonus::BulkAdd - The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (amount < 0)
		return false;
	if (strlen(bonus_date) == 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CceBonus::BulkAdd - The rank must be greater than 0");

	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	//columns["batch_id"] = IntToStr(batch_id);
	columns["user_id"] = user_id;
	columns["amount"] = DoubleToStr(amount);
	columns["bonus_date"] = bonus_date;

	if ((m_BulkCount = CDbBulk::BulkAdd(m_pDB, socket, "ce_bonus", columns, &m_BulkSQL, m_BulkCount)) == -1)
		return CDbPlus::Debug(DEBUG_ERROR, "CceBonus::BulkAdd - missedcount == -1");

	return true;
}

/////////////////////////////////////////
// Finish off last entries of Bulk Add //
/////////////////////////////////////////
bool CceBonus::BulkFinish(int socket)
{
	if (CDbBulk::BulkFinish(m_pDB, socket, &m_BulkSQL) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CceBonus::BulkFinish - BulkFinish == false");

	m_BulkCount = 0;
	return true;
}