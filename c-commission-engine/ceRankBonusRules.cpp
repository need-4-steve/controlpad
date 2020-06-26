#include "ceRankBonusRules.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceRankBonusRules::CceRankBonusRules(CDb *pDB, string origin)
{
	m_pDB = pDB;

	//CDbPlus::Setup("rankgenbonusrules", "ce_rankgenbonusrules");
	CezJson::SetOrigin(origin);
}

////////////////////////////////////////////////
// Define the rank where user goes up a level //
////////////////////////////////////////////////
const char *CceRankBonusRules::Add(int socket, int system_id, string rank, string bonus)
{
	CDbPlus::Setup("rankgenbonusrules", "ce_rankgenbonusrules");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::add error", "A database connection needs to be made first");
	if (is_number(rank) == false)
		return SetError(400, "API", "rankbonusrules::add error", "The rank is not numeric");
	if (is_number(bonus) == false)
		return SetError(400, "API", "rankbonusrules::add error", "The bonus is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["bonus"] = bonus;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxRankRules);
}

/////////////////////////////
// Allow editing of a rank //
/////////////////////////////
const char *CceRankBonusRules::Edit(int socket, int system_id, string id, string rank, string bonus)
{
	CDbPlus::Setup("rankgenbonusrules", "ce_rankgenbonusrules");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::edit error", "A database connection needs to be made first");
	if (is_number(rank) == false)
		return SetError(400, "API", "rankbonusrules::edit error", "The rank is not numeric");
	if (is_number(bonus) == false)
		return SetError(400, "API", "rankbonusrules::edit error", "The bonus is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["bonus"] = bonus;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

/////////////////////////////////
// Query ranks with pagenation //
/////////////////////////////////
const char *CceRankBonusRules::Query(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("rankgenbonusrules", "ce_rankgenbonusrules");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::query error", "A database connection needs to be made first");

	// Handle pagination values //
	if (search.length() != 0)
	{
		if (is_qstring(search) == false)
			return SetError(400, "API", "rankbonusrules::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}
	if (sort.length() != 0) 
	{
		if (is_qstring(sort) == false)
			return SetError(400, "API", "rankbonusrules::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("rank");
	columns.push_back("bonus");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////
// Disable the given rank //
////////////////////////////
const char *CceRankBonusRules::Disable(int socket, int system_id, string id)
{
	CDbPlus::Setup("rankbonusrules", "ce_rankbonusrules");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "rankbonusrules::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////
// Enable given rank //
///////////////////////
const char *CceRankBonusRules::Enable(int socket, int system_id, string id)
{
	CDbPlus::Setup("rankbonusrules", "ce_rankbonusrules");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "rankbonusrules::enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////////
// Grab individual rank rule //
///////////////////////////////
const char *CceRankBonusRules::Get(int socket, int system_id, string id)
{
	CDbPlus::Setup("rankbonusrules", "ce_rankbonusrules");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "rankbonusrules::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("rank");
	columns.push_back("bonus");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}

//////////////////////////
// Query the user bonus //
//////////////////////////
const char *CceRankBonusRules::QueryBonus(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("rankbonus", "ce_rankbonus");

	if (m_pDB == NULL)
		return SetError(409, "API", "rankbonusrules::get error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("event_date");
	columns.push_back("generation");
	columns.push_back("rank");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}