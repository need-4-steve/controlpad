#include "ceCMCommRule.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceCMCommRule::CceCMCommRule(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("cmcommrule", "ce_cmcommrules");
	CezJson::SetOrigin(origin);
}


////////////////////////////////
// Add Check Match Comm Rules //
////////////////////////////////
const char *CceCMCommRule::Add(int socket, int system_id, string rank, string generation, string percent)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmcommrule::add error", "A database connection needs to be made first");
	if (is_number(rank) == false)
		return SetError(400, "API", "cmcommrule::add error", "The rank is not numeric");
	if (is_number(generation) == false)
		return SetError(400, "API", "cmcommrule::add error", "The generation is not numeric");
	if (is_decimal(percent) == false)
		return SetError(400, "API", "cmcommrule::add error", "The percent is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["generation"] = generation;
	columns["percent"] = percent;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, -1);	
}

/////////////////////////////////
// Edit Check Match Comm Rules //
/////////////////////////////////
const char *CceCMCommRule::Edit(int socket, int system_id, string cmcommrule_id, string rank, string generation, string percent)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmcommrule::edit error", "A database connection needs to be made first");
	if (is_number(cmcommrule_id) == false)
		return SetError(400, "API", "cmcommrule::edit error", "The commruleid is not numeric");
	if (is_number(rank) == false)
		return SetError(400, "API", "cmcommrule::edit error", "The rank is not numeric");
	if (is_number(generation) == false)
		return SetError(400, "API", "cmcommrule::edit error", "The generation is not numeric");
	if (is_decimal(percent) == false)
		return SetError(400, "API", "cmcommrule::edit error", "The percent is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["generation"] = generation;
	columns["percent"] = percent;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, cmcommrule_id, "id", unique, columns, mask);	
}

//////////////////////////////////
// Query Check Match Comm Rules //
//////////////////////////////////
const char *CceCMCommRule::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmcommrule::query error", "A database connection needs to be made first");
	
	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("rank");
	columns.push_back("generation");
	columns.push_back("percent");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////////////
// Disable Check Match Comm Rules //
////////////////////////////////////
const char *CceCMCommRule::Disable(int socket, int system_id, string cmcommrule_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmcommrule::disable error", "A database connection needs to be made first");
	if (is_number(cmcommrule_id) == false)
		return SetError(400, "API", "disablecmcommrule error", "The commruleid is not numeric");
	
	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, cmcommrule_id, "id");
}

///////////////////////////////////
// Enable Check Match Comm Rules //
///////////////////////////////////
const char *CceCMCommRule::Enable(int socket, int system_id, string cmcommrule_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmcommrule::enable error", "A database connection needs to be made first");
	if (is_number(cmcommrule_id) == false)
		return SetError(400, "API", "cmcommrule::enable error", "The commruleid is not numeric");
	
	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, cmcommrule_id, "id");
}