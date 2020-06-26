#include "cePoolRule.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CcePoolRule::CcePoolRule(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("poolrule", "ce_poolrules");
	CezJson::SetOrigin(origin);
}
 
////////////////////
// Add Pool Rules //
////////////////////
const char *CcePoolRule::Add(int socket, int system_id, string pool_id, string start_rank, string end_rank, string qualify_type, string qualify_threshold)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "poolrule::add error", "A database connection needs to be made first");
	if (is_number(pool_id) == false)
		return SetError(400, "API", "poolrule::add error", "The poolid is not numeric");
	if (is_number(start_rank) == false)
		return SetError(400, "API", "poolrule::add error", "The startrank is not numeric");
	if (is_number(end_rank) == false)
		return SetError(400, "API", "poolrule::add error", "The endrank is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "poolrule::add error", "The qualifytype is not numeric");
	if (is_number(qualify_threshold) == false)
		return SetError(400, "API", "poolrule::add error", "The qualifythreshold is not numeric");
	
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["pool_id"] = pool_id;
	columns["start_rank"] = start_rank;
	columns["end_rank"] = end_rank;
	columns["qualify_type"] = qualify_type;
	columns["qualify_threshold"] = qualify_threshold;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxPoolRules);
}

/////////////////////
// Edit Pool Rules //
/////////////////////
const char *CcePoolRule::Edit(int socket, int system_id, string id, string start_rank, string end_rank, string qualify_type, string qualify_threshold)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "poolrule::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "poolrule::edit error", "The id is not numeric");
	if (is_number(start_rank) == false)
		return SetError(400, "API", "poolrule::edit error", "The startrank is not numeric");
	if (is_number(end_rank) == false)
		return SetError(400, "API", "poolrule::edit error", "The endrank is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "poolrule::add error", "The qualifytype is not numeric");
	if (is_number(qualify_threshold) == false)
		return SetError(400, "API", "poolrule::edit error", "The qualifythreshold is not numeric");
	
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["start_rank"] = start_rank;
	columns["end_rank"] = end_rank;
	columns["qualify_type"] = qualify_type;
	columns["qualify_threshold"] = qualify_threshold;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

//////////////////////
// Query Pool Rules //
//////////////////////
const char *CcePoolRule::Query(int socket, int system_id, string pool_id, string search, string sort)
{
	Debug(DEBUG_TRACE, "CcePoolRule::Query - TOP");

	if (m_pDB == NULL)
		return SetError(409, "API", "poolrule::query error", "A database connection needs to be made first");
	if (is_number(pool_id) == false)
		return SetError(400, "API", "poolrule::query error", "The poolid is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("pool_id");
	columns.push_back("start_rank");
	columns.push_back("end_rank");
	columns.push_back("qualify_threshold");
	columns.push_back("qualify_type");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	// Force the search on pool_id //
	search = search+"&poolid="+pool_id;
	sort = "orderby=pool_id&"+sort;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////
// Disable Pool Rules //
////////////////////////
const char *CcePoolRule::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "poolrule::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "poolrule::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////
// Enable Pool Rules //
///////////////////////
const char *CcePoolRule::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "poolrule::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "poolrule::enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////////
// Grab individual rank rule //
///////////////////////////////
const char *CcePoolRule::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "rankrule::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "rankrule::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("pool_id");
	columns.push_back("start_rank");
	columns.push_back("end_rank");
	columns.push_back("qualify_threshold");
	columns.push_back("qualify_type");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}