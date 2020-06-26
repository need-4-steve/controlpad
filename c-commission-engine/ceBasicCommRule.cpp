#include "ceBasicCommRule.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceBasicCommRule::CceBasicCommRule(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("basiccommrule", "ce_basic_commrules");
	CezJson::SetOrigin(origin);
}

////////////////////////////////////////////
// Define the commission payout structure //
////////////////////////////////////////////
const char *CceBasicCommRule::Add(int socket, int system_id, string generation, string qualify_type, string start_threshold, string end_threshold, string inv_type, string event, string percent, string modulus, string paylimit, string pv_override, string paytype, string rank)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "basiccommrule::add error", "A database connection needs to be made first");
	if (is_number(generation) == false)
		return SetError(400, "API", "basiccommrule::add error", "The generation is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "basiccommrule::add error", "The qualifytype is not not numeric");
	if (is_number(start_threshold) == false)
		return SetError(400, "API", "basiccommrule::add error", "The startthreshold is not numeric");
	if (is_number(end_threshold) == false)
		return SetError(400, "API", "basiccommrule::add error", "The endthreshold is not not numeric");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "basiccommrule::add error", "The invtype is not not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "basiccommrule::add error", "The invtype must bet between 1-5");
	if (is_number(event) == false)
		return SetError(400, "API", "basiccommrule::add error", "The event is not not numeric");
	if ((atoi(event.c_str()) < 1) || (atoi(event.c_str()) > 2))
		return SetError(400, "API", "basiccommrule::add error", "The event must bet between 1-2");
	if (is_decimal(percent) == false)
		return SetError(400, "API", "basiccommrule::add error", "The percent is not numeric");
	if (modulus.size() == 0)
	{
		modulus = "0";
	}
	else if (is_number(modulus) == false)
		return SetError(400, "API", "basiccommrule::add error", "The modulus is not numeric");
	if (is_number(paylimit) == false)
		return SetError(400, "API", "basiccommrule::add error", "The paylimit is not not numeric");
	if ((pv_override != "t") && (pv_override != "true"))
		pv_override = "f";
	if (is_number(paytype) == false)
		return SetError(400, "API", "basiccommrule::add error", "The paytype is not numeric");

	if (rank.size() == 0)
	{

	}
	else if ((is_number(rank) == false))
		return SetError(400, "API", "basiccommrule::add error", "The rank is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["generation"] = generation;
	columns["qualify_type"] = qualify_type;
	columns["start_threshold"] = start_threshold;
	columns["end_threshold"] = end_threshold;
	columns["inv_type"] = inv_type;
	columns["event"] = event;
	columns["percent"] = percent;
	columns["modulus"] = modulus;
	columns["paylimit"] = paylimit;
	columns["pv_override"] = pv_override;
	columns["paytype"] = paytype;
	columns["rank"] = rank;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxCommRules);
}

/////////////////////////////////////
// Editing of commission is needed //
/////////////////////////////////////
const char *CceBasicCommRule::Edit(int socket, int system_id, string id, string generation, string qualify_type, string start_threshold, string end_threshold, string inv_type, string event, string percent, string modulus, string paylimit, string pv_override, string paytype, string rank)
{
	Debug(DEBUG_TRACE, "CceBasicCommRule::Edit - TOP");

	if (m_pDB == NULL)
		return SetError(409, "API", "basiccommrule::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The id is not numeric");
	if (is_number(generation) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The generation is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "basiccommrule::add error", "The qualifytype is not not numeric");
	if (is_number(start_threshold) == false)
		return SetError(400, "API", "basiccommrule::add error", "The startthreshold is not not numeric");
	if (is_number(end_threshold) == false)
		return SetError(400, "API", "basiccommrule::add error", "The endthreshold is not not numeric");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The invtype is not not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "basiccommrule::edit error", "The invtype must bet between 1-5");
	if (is_number(event) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The event is not not numeric");
	if ((atoi(event.c_str()) < 1) || (atoi(event.c_str()) > 2))
		return SetError(400, "API", "basiccommrule::edit error", "The event must bet between 1-2");
	if (is_decimal(percent) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The percent is not numeric");
	if (modulus.size() == 0)
	{
		modulus = "0";
	}
	else if (is_number(modulus) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The modulus is not numeric");
	if (is_number(paylimit) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The paylimit is not not numeric");
	if ((pv_override != "t") && (pv_override != "true"))
		pv_override = "f";
	if (is_number(paytype) == false)
		return SetError(400, "API", "basiccommrule::edit error", "The paytype is not numeric");
	
	if (rank.size() == 0)
	{

	}
	else if ((is_number(rank) == false))
		return SetError(400, "API", "basiccommrule::edit error", "The rank is not numeric");

	//Debug(DEBUG_TRACE, "CceBasicCommRule::Edit - MID");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["generation"] = generation;
	columns["qualify_type"] = qualify_type;
	columns["start_threshold"] = start_threshold;
	columns["end_threshold"] = end_threshold;
	columns["inv_type"] = inv_type;
	columns["event"] = event;
	columns["percent"] = percent;
	columns["modulus"] = modulus;
	columns["paylimit"] = paylimit;
	columns["pv_override"] = pv_override;
	columns["paytype"] = paytype;
	columns["rank"] = rank;

	Debug(DEBUG_TRACE, "CceBasicCommRule::Edit - End");

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

//////////////////////////////////////
// List all of the commission rules //
//////////////////////////////////////
const char *CceBasicCommRule::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "basiccommrule::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("generation");
	columns.push_back("qualify_type");
	columns.push_back("start_threshold");
	columns.push_back("end_threshold");
	columns.push_back("inv_type");
	columns.push_back("event");
	columns.push_back("percent");
	columns.push_back("modulus");
	columns.push_back("paylimit");
	columns.push_back("pv_override");
	columns.push_back("paytype");
	columns.push_back("rank");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////////////////
// Deleting of commission is needed //
//////////////////////////////////////
const char *CceBasicCommRule::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "basiccommrule::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "basiccommrule::disable error", "The id is not numeric");
	
	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

////////////////////////
// Enable a comm rule //
////////////////////////
const char *CceBasicCommRule::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "basiccommrule::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "basiccommrule::enable error", "The id is not numeric");
	
	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////
// Get a commission rule //
//////////////////
const char *CceBasicCommRule::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "basiccommrule::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "basiccommrule::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("generation");
	columns.push_back("qualify_type");
	columns.push_back("start_threshold");
	columns.push_back("end_threshold");
	columns.push_back("inv_type");
	columns.push_back("event");
	columns.push_back("percent");
	columns.push_back("modulus");
	columns.push_back("paylimit");
	columns.push_back("pv_override");
	columns.push_back("paytype");
	columns.push_back("rank");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}