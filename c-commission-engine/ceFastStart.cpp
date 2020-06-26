#include "ceFastStart.h"
#include "db.h"
#include <stdlib.h> // atoi //

/*
CFastStartRules::CFastStartRules()
{
	m_ID = 0;
	m_Rank = 0;
	m_QualifyType = 0;
	m_QualifyThreshold = 0;
	m_DaysCount = 0;
	m_RuleGroup = 0;
}
*/
/////////////////
// Constructor //
/////////////////
CceFastStart::CceFastStart(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("faststart", "ce_faststart");
	CezJson::SetOrigin(origin);
}

//////////////////////////
// Add a FastStart rule //
//////////////////////////
const char *CceFastStart::Add(int socket, int system_id, string rank, string qualify_type, string qualify_threshold, string days_count, string bonus, string rulegroup)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "faststart::add error", "A database connection needs to be made first");
	if (is_number(rank) == false)
		return SetError(400, "API", "faststart::add error", "The rank is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "faststart::add error", "The qualifytype is not numeric");
	if (is_number(qualify_threshold) == false)
		return SetError(400, "API", "faststart::add error", "The qualifythreshold is not numeric");
	if (is_number(days_count) == false)
		return SetError(400, "API", "faststart::add error", "The dayscount is not numeric");
	if (is_number(bonus) == false)
		return SetError(400, "API", "faststart::add error", "The achvbonus is not numeric");
	if (is_number(rulegroup) == false)
		return SetError(400, "API", "faststart::add error", "The rulegroup is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["qualify_type"] = qualify_type;
	columns["qualify_threshold"] = qualify_threshold;
	columns["days_count"] = days_count;
	columns["bonus"] = bonus;
	columns["rulegroup"] = rulegroup;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, -1);
}
	
/////////////////////////
// Edit FastStart rule //
/////////////////////////
const char *CceFastStart::Edit(int socket, int system_id, string id, string rank, string qualify_type, string qualify_threshold, string days_count, string bonus, string rulegroup)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "faststart::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "faststart::edit error", "The id is not numeric");
	if (is_number(rank) == false)
		return SetError(400, "API", "faststart::edit error", "The rank is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "faststart::edit error", "The qualifytype is not numeric");
	if (is_number(qualify_threshold) == false)
		return SetError(400, "API", "faststart::edit error", "The qualifythreshold is not numeric");
	if (is_number(days_count) == false)
		return SetError(400, "API", "faststart::edit error", "The dayscount is not numeric");
	if (is_number(bonus) == false)
		return SetError(400, "API", "faststart::edit error", "The achvbonus is not numeric");
	if (is_number(rulegroup) == false)
		return SetError(400, "API", "faststart::edit error", "The rulegroup is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["qualify_type"] = qualify_type;
	columns["qualify_threshold"] = qualify_threshold;
	columns["days_count"] = days_count;
	columns["bonus"] = bonus;
	columns["rulegroup"] = rulegroup;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

/////////////////////////////////////
// Query FastStart with pagenation //
/////////////////////////////////////
const char *CceFastStart::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "faststart::queryalt error", "A database connection needs to be made first");

	// Handle pagination values //
	if (search.length() != 0)
	{
		if (is_qstring(search) == false)
			return SetError(400, "API", "faststart::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}
	if (sort.length() != 0) 
	{
		if (is_qstring(sort) == false)
			return SetError(400, "API", "faststart::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("days_count");
	columns.push_back("bonus");
	columns.push_back("rulegroup");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////
// Disable the given rank //
////////////////////////////
const char *CceFastStart::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "faststart::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "faststart::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////
// Enable given rank //
///////////////////////
const char *CceFastStart::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "faststart::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "faststart::enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////////
// Grab individual rank rule //
///////////////////////////////
const char *CceFastStart::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "faststart::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "faststart::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("days_count");
	columns.push_back("bonus");
	columns.push_back("rulegroup");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}
