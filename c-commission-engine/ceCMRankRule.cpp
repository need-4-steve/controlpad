#include "ceCMRankRule.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceCMRankRule::CceCMRankRule(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("cmrankrule", "ce_cmrankrules");
	CezJson::SetOrigin(origin);
}

////////////////////////////////////////////////
// Define the rank where user goes up a level //
////////////////////////////////////////////////
const char *CceCMRankRule::Add(int socket, int system_id, string label, string rank, string qualify_type, string qualify_threshold, string achvbonus, 
	string breakage, string rulegroup, string maxdacleg, string sumrankstart, string sumrankend)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmrankrule::add error", "A database connection needs to be made first");
	if (is_alphanum(label) == false)
		return SetError(400, "API", "cmrankrule::add error", "The label is not alpha numeric");
	if (is_number(rank) == false)
		return SetError(400, "API", "cmrankrule:add error", "The rank is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "cmrankrule:add error", "The qualifytype is not numeric");
	if (is_number(qualify_threshold) == false)
		return SetError(400, "API", "cmrankrule:add error", "The qualifythreshold is not numeric");
	if (achvbonus.size() != 0)
	{
		if (is_number(achvbonus) == false)
			return SetError(400, "API", "cmrankrule:add error", "The achvbonus is not numeric");
	}
	else
	{
		achvbonus = "0";
	}
	if ((breakage != "true") && (breakage != "false"))
		return SetError(400, "API", "cmrankrule:add error", "The breakage is not true or false");
	if (rulegroup.size() == 0)
		rulegroup = "0";
	if (is_number(rulegroup) == false)
		return SetError(400, "API", "cmrankrule:add error", "The rulegroup is not numeric");
	if (is_number(maxdacleg) == false)
		maxdacleg = "0"; // Default max dac leg to zero // 
	if (is_number(sumrankstart) == false)
		sumrankstart = "0"; 
	else if (atoi(sumrankstart.c_str()) > 999)
		return SetError(400, "API", "cmrankrule:add error", "The sumrankstart can only be 1-99");
	if (is_number(sumrankend) == false)
		sumrankend = "0";
	else if (atoi(sumrankend.c_str()) > 999)
		return SetError(400, "API", "cmrankrule:add error", "The sumrankend can only be 1-99");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["label"] = label;
	columns["rank"] = rank;
	columns["qualify_type"] = qualify_type;
	columns["qualify_threshold"] = qualify_threshold;
	columns["achvbonus"] = achvbonus;
	columns["breakage"] = breakage;
	columns["rulegroup"] = rulegroup;
	columns["maxdacleg"] = maxdacleg;
	columns["sumrankstart"] = sumrankstart;
	columns["sumrankend"] = sumrankend;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxRankRules);
}

/////////////////////////////
// Allow editing of a rank //
/////////////////////////////
const char *CceCMRankRule::Edit(int socket, int system_id, string id, string label, string rank, string qualify_type, string qualify_threshold,
	string achvbonus, string breakage, string rulegroup, string maxdacleg, string sumrankstart, string sumrankend)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmrankrule:edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "cmrankrule:edit error", "The id is not numeric");
	if (is_alphanum(label) == false)
		return SetError(400, "API", "cmrankrule:edit error", "The label is not alpha numeric");
	if (is_number(rank) == false)
		return SetError(400, "API", "cmrankrule:edit error", "The rank is not numeric");
	if (is_number(qualify_type) == false)
		return SetError(400, "API", "cmrankrule:edit error", "The qualifytype is not numeric");
	if (is_number(qualify_threshold) == false)
		return SetError(400, "API", "cmrankrule:edit error", "The qualifythreshold is not numeric");
	if (achvbonus.size() != 0)
	{
		if (is_number(achvbonus) == false)
			return SetError(400, "API", "cmrankrule:edit error", "The achvbonus is not numeric");
	}
	else
	{
		achvbonus = "0";
	}
	if ((breakage != "true") && (breakage != "false"))
		return SetError(400, "API", "cmrankrule:edit error", "The breakage is not true or false");
	if (rulegroup.size() == 0)
		rulegroup = "0";
	if (is_number(rulegroup) == false)
		return SetError(400, "API", "cmrankrule:edit error", "The rulegroup is not numeric");
	if (is_number(maxdacleg) == false)
		maxdacleg = "0"; // Default max dac leg to zero // 
	if (is_number(sumrankstart) == false)
		sumrankstart = "0"; 
	else if (atoi(sumrankstart.c_str()) > 999)
		return SetError(400, "API", "cmrankrule:edit error", "The sumrankstart can only be 1-99");
	if (is_number(sumrankend) == false)
		sumrankend = "0";
	else if (atoi(sumrankend.c_str()) > 999)
		return SetError(400, "API", "cmrankrule:edit error", "The sumrankend can only be 1-99");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["label"] = label;
	columns["rank"] = rank;
	columns["qualify_type"] = qualify_type;
	columns["qualify_threshold"] = qualify_threshold;
	columns["achvbonus"] = achvbonus;
	columns["breakage"] = breakage;
	columns["rulegroup"] = rulegroup;
	columns["maxdacleg"] = maxdacleg;
	columns["sumrankstart"] = sumrankstart;
	columns["sumrankend"] = sumrankend;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

/////////////////////////////////
// Query ranks with pagenation //
/////////////////////////////////
const char *CceCMRankRule::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmrankrule:queryalt error", "A database connection needs to be made first");

	// Handle pagination values //
	if (search.length() != 0)
	{
		if (is_qstring(search) == false)
			return SetError(400, "API", "cmrankrule::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}
	if (sort.length() != 0) 
	{
		if (is_qstring(sort) == false)
			return SetError(400, "API", "cmrankrule::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("label");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("achvbonus");
	columns.push_back("breakage");
	columns.push_back("rulegroup");
	columns.push_back("maxdacleg");
	columns.push_back("sumrankstart");
	columns.push_back("sumrankend");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////
// Disable the given rank //
////////////////////////////
const char *CceCMRankRule::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmrankrule:disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "cmrankrule:disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////
// Enable given rank //
///////////////////////
const char *CceCMRankRule::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmrankrule:enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "cmrankrule:enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////////
// Grab individual rank rule //
///////////////////////////////
const char *CceCMRankRule::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "cmrankrule:get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "cmrankrule:get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("label");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("achvbonus");
	columns.push_back("breakage");
	columns.push_back("rulegroup");
	columns.push_back("maxdacleg");
	columns.push_back("sumrankstart");
	columns.push_back("sumrankend");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}