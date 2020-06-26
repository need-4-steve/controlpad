#include "ceSettings.h"

/////////////////
// Constructor //
/////////////////
CceSettings::CceSettings(CDb *pDB, string origin)
{
	m_pDB = pDB;
	CezJson::SetOrigin(origin);
}

//////////////////////////////////////
// Get Settings for a given webpage //
//////////////////////////////////////
const char *CceSettings::Query(int socket, string search, string sort)
{
	CDbPlus::Setup("settings", "ce_settings");

	if (m_pDB == NULL)
		return SetError(409, "API", "settings::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("webpage");
	columns.push_back("user_id");
	columns.push_back("varname");
	columns.push_back("value");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");
 
	return CDbPlus::QueryDB(m_pDB, socket, 0, 0, columns, mask, search, sort);
}

//////////////////////////////////////
// Get Settings for a given webpage //
//////////////////////////////////////
const char *CceSettings::QuerySystem(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("settings", "ce_settings");

	if (m_pDB == NULL)
		return SetError(409, "API", "settings::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("webpage");
	columns.push_back("user_id");
	columns.push_back("varname");
	columns.push_back("value");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////
// Get a given variable settings //
///////////////////////////////////
const char *CceSettings::Get(int socket, int system_id, string webpage, string user_id, string varname)
{
	CDbPlus::Setup("settings", "ce_settings");

	if (m_pDB == NULL)
		return SetError(409, "API", "settings::set error", "A database connection needs to be made first");
	if ((is_alphanum(webpage) == false) && (webpage.size() != 0))
		return SetError(400, "API", "settings::set error", "The webpage is not alpha numeric");
	if ((is_userid(user_id) == false) && (user_id.size() != 0))
		return SetError(400, "API", "settings::set error", "The userid is not valid");
	if (is_alphanum(varname) == false)
		return SetError(400, "API", "settings::set error", "The varname is not valid");

	string sort = "limit=10&offset=0&orderby=varname&orderdir=asc";

	// Build WHERE //
	stringstream searchraw;
	searchraw << " WHERE varname='" << varname << "' AND disabled='false'";
	searchraw << " AND system_id='" << system_id << "' ";
	if (webpage.size() != 0)
		searchraw << " AND webpage='" << webpage << "' ";
	if (user_id.size() != 0)
		searchraw << " AND user_id='" << user_id << "' ";

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("webpage");
	columns.push_back("user_id");
	columns.push_back("varname");
	columns.push_back("value");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

//////////////////////////
// Set a settings value //
//////////////////////////
const char *CceSettings::Set(int socket, string webpage, string user_id, string varname, string value)
{
	return SetSystem(socket, 0, webpage, user_id, varname, value);
}

/////////////////////////////////////////////////////////////////////////////
// Delete a settings. This used specifically for loginsite and logout site //
/////////////////////////////////////////////////////////////////////////////
const char *CceSettings::Disable(int socket, string varname)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "settings::set error", "A database connection needs to be made first");
	if (is_alphanum(varname) == false)
		return SetError(400, "API", "settings::set error", "The varname is not valid");

	stringstream ss;
	ss << "UPDATE ce_settings SET disabled='true' WHERE varname='" << varname << "' AND system_id='0'";
	if (m_pDB->ExecDB(socket, ss.str().c_str()) == NULL)
		return SetError(409, "API", "settings::set error", "There was a problem with a database query");

	return SetJson(200, "");
}

/////////////////////////////////////////////////////////////////////////////
// Delete a settings. This used specifically for loginsite and logout site //
/////////////////////////////////////////////////////////////////////////////
const char *CceSettings::Enable(int socket, string varname)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "settings::set error", "A database connection needs to be made first");
	if (is_alphanum(varname) == false)
		return SetError(400, "API", "settings::set error", "The varname is not valid");

	stringstream ss;
	ss << "UPDATE ce_settings SET disabled='false' WHERE varname='" << varname << "' AND system_id='0'";
	if (m_pDB->ExecDB(socket, ss.str().c_str()) == NULL)
		return SetError(409, "API", "settings::set error", "There was a problem with a database query");

	return SetJson(200, "");
}

//////////////////////////////////////////////
// Set a given settings value with a system //
//////////////////////////////////////////////
const char *CceSettings::SetSystem(int socket, int system_id, string webpage, string user_id, string varname, string value)
{
	CDbPlus::Setup("settings", "ce_settings");

	if (m_pDB == NULL)
		return SetError(409, "API", "settings::set error", "A database connection needs to be made first");
	if ((is_alphanum(webpage) == false) && (webpage.size() != 0))
		return SetError(400, "API", "settings::set error", "The webpage is not alpha numeric");
	if ((is_userid(user_id) == false) && (user_id.size() != 0))
		return SetError(400, "API", "settings::set error", "The userid is not valid");
	if (is_alphanum(varname) == false)
		return SetError(400, "API", "settings::set error", "The varname is not valid");
	if (is_json(value) == false)
		return SetError(400, "API", "settings::set error", "The value is not valid");

	// Handle json escape sequence //
	CConvert conv;
	string quote = "\"";
	string escquote = "\\\"";
	value = conv.StrReplace(value, quote, escquote);

	Debug(DEBUG_DEBUG, "CceSettings::SetSystem - value", value);

	// Build WHERE //
	stringstream ssWhere;
	ssWhere << " WHERE varname='" << varname << "' ";
	if (system_id > 0)
		ssWhere << " AND system_id='" << system_id << "' ";
	if (webpage.size() != 0)
		ssWhere << " AND webpage='" << webpage << "' ";
	if (user_id.size() != 0)
		ssWhere << " AND user_id='" << user_id << "' ";

	stringstream ss0;
	if (m_pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_settings " << ssWhere.str()) == 0) // Not found in database //
	{
		// Add Record //
		list <string> unique; // Nothing //
		map <string, int> mask;

		// Prepare the columns //
		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["webpage"] = webpage;
		columns["user_id"] = user_id;
		columns["varname"] = varname;
		columns["value"] = value;

		return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxPools);
	}
	else
	{
		// Update Record //
		stringstream ss1;
		string id = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT id FROM ce_settings " << ssWhere.str());

		list <string> unique; // Nothing //
		map <string, int> mask;

		// Prepare the columns //
		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["webpage"] = webpage;
		columns["user_id"] = user_id;
		columns["varname"] = varname;
		columns["value"] = value;
		columns["disabled"] = "false";

		return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
	}

	Debug(DEBUG_ERROR, "CceSettings::Set -  It should never get here");
	return SetError(409, "API", "settings::set error", "It should never get here");
}

//////////////////////////////////////////////
// Get json of all timezones to select from //
//////////////////////////////////////////////
const char *CceSettings::GetTimeZones(int socket, string sort)
{
	CDbPlus::Setup("timezones", "pg_timezone_names");

	if (m_pDB == NULL)
		return SetError(409, "API", "settings::gettimezones error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("name");
	columns.push_back("abbrev");
	columns.push_back("utc_offset");
	columns.push_back("is_dst");

	string search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, 0, columns, mask, search, sort);
}