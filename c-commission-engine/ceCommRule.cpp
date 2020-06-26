#include "ceCommRule.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceCommRule::CceCommRule(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("commrule", "ce_commrules");
	CezJson::SetOrigin(origin);
}

////////////////////////////////////////////
// Define the commission payout structure //
////////////////////////////////////////////
const char *CceCommRule::Add(int socket, int system_id, string rank, string generation, string infinitybonus, string percent, string dollar, string inv_type, string event, string paytype)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commrule::add error", "A database connection needs to be made first");
	if (is_number(rank) == false)
		return SetError(400, "API", "commrule::add error", "The rank is not numeric");
	if (is_number(generation) == false)
		return SetError(400, "API", "commrule::add error", "The generation is not numeric");
	if (infinitybonus.size() == 0) // Make legacy compatible //
		infinitybonus = "false"; // Default it to false //
	if (is_boolean(infinitybonus) == false)
		return SetError(400, "API", "commrule::add error", "The infinitybonus is boolean (true or false)");
	if (is_decimal(percent) == false)
		return SetError(400, "API", "commrule::add error", "The percent is not numeric");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "commrule::add error", "The invtype is not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "commrule::add error", "The invtype must bet between 1-5");
	if (is_number(event) == false)
		return SetError(400, "API", "commrule::add error", "The event is not numeric");
	if ((atoi(event.c_str()) < 1) || (atoi(event.c_str()) > 2))
		return SetError(400, "API", "commrule::add error", "The event must bet between 1-2");
	if (is_number(paytype) == false)
		return SetError(400, "API", "commrule::add error", "The paytype is not numeric");

	if (percent.size() == 0)
		percent = "0";
	if (dollar.size() == 0)
		dollar = "0";

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["rank"] = rank;
	columns["generation"] = generation;
	columns["infinitybonus"] = infinitybonus;
	columns["percent"] = percent;
	columns["dollar"] = dollar;
	columns["inv_type"] = inv_type;
	columns["event"] = event;
	columns["paytype"] = paytype;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxCommRules);
}

/////////////////////////////////////
// Editing of commission is needed //
/////////////////////////////////////
const char *CceCommRule::Edit(int socket, int system_id, string id, string rank, string generation, string infinitybonus, string percent, string dollar, string inv_type, string event, string paytype)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commrule::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "commrule::edit error", "The id is not numeric");
	if (is_number(rank) == false)
		return SetError(400, "API", "commrule::edit error", "The rank is not numeric");
	if (is_number(generation) == false)
		return SetError(400, "API", "commrule::edit error", "The generation is not numeric");
	if (infinitybonus.size() == 0) // Make legacy compatible //
		infinitybonus = "false"; // Default it to false //
	if (is_boolean(infinitybonus) == false)
		return SetError(400, "API", "commrule::edit error", "The infinitybonus is not boolean (true or false)");
	if (is_decimal(percent) == false)
		return SetError(400, "API", "commrule::edit error", "The percent is not numeric");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "commrule::edit error", "The invtype is not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "commrule::edit error", "The invtype must bet between 1-5");
	if (is_number(event) == false)
		return SetError(400, "API", "commrule::edit error", "The event is not numeric");
	if ((atoi(event.c_str()) < 1) || (atoi(event.c_str()) > 4))
		return SetError(400, "API", "commrule::edit error", "The event must bet between 1-4");
	if (is_number(paytype) == false)
		return SetError(400, "API", "commrule::edit error", "The paytype is not numeric");

	if (percent.size() == 0)
		percent = "0";
	if (dollar.size() == 0)
		dollar = "0";

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["rank"] = rank;
	columns["generation"] = generation;
	columns["infinitybonus"] = infinitybonus;
	columns["percent"] = percent;
	columns["dollar"] = dollar;
	columns["inv_type"] = inv_type;
	columns["event"] = event;
	columns["paytype"] = paytype;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

//////////////////////////////////////
// List all of the commission rules //
//////////////////////////////////////
const char *CceCommRule::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commrule.query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("rank");
	columns.push_back("generation");
	columns.push_back("percent");
	columns.push_back("dollar");
	columns.push_back("inv_type");
	columns.push_back("event");
	columns.push_back("paytype");
	columns.push_back("infinitybonus");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////////////////
// Deleting of commission is needed //
//////////////////////////////////////
const char *CceCommRule::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commrule.disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "commrule.disable error", "The id is not numeric");
	
	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

////////////////////////
// Enable a comm rule //
////////////////////////
const char *CceCommRule::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commrule.enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "commrule.enable error", "The id is not numeric");
	
	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////
// Get a commission rule //
//////////////////
const char *CceCommRule::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commrule::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "commrule::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("rank");
	columns.push_back("generation");
	columns.push_back("percent");
	columns.push_back("dollar");
	columns.push_back("inv_type");
	columns.push_back("event");
	columns.push_back("paytype");
	columns.push_back("infinitybonus");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}