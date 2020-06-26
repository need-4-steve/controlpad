#include "ceApiKey.h"
#include "db.h"
#include "ezCrypt.h"

#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceApiKey::CceApiKey(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("apikeys", "ce_apikeys");
	CezJson::SetOrigin(origin);
}

//////////////////////
// Add a new apikey //
//////////////////////
const char *CceApiKey::Add(int socket, int sysuser_id, int system_id, string label)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "apikey::add error", "A database connection needs to be made first");
	if (is_alphanum(label) == false)
		return SetError(400, "API", "apikey::add error", "The notes is not alphanumeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Run Crypt generation //
	CezCrypt crypt;
	string salt = crypt.GenSalt();
	CezCrypt crypt2;
	string apikey = crypt2.GenSha256();
	string apikeyhash = crypt2.GenPBKDF2(m_pDB->m_pSettings->m_HashPass.c_str(), salt.c_str(), apikey.c_str()); // No password //

	// Prepare the columns //
	map <string, string> columns;
	columns["sysuser_id"] = IntToStr(sysuser_id);
	columns["system_id"] = IntToStr(system_id);
	columns["label"] = label;
	columns["salt"] = salt;
	columns["apikeyhash"] = apikeyhash;

	// Run DB add //
	string json = CDbPlus::AddDB(m_pDB, socket, sysuser_id, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxApiKeys);
	
	// Return actual apikey and not apikeyhash //
	string oldstr = "\"apikeyhash\":\""+apikeyhash+"\"";
	string newstr = "\"apikey\":\""+apikey+"\"";
	StrReplace(json, oldstr, newstr);

	// Empty the salt //
	string saltold = "\"salt\":\""+salt+"\",";
	string saltnew = "";
	StrReplace(json, saltold, saltnew);

	return json.c_str();
}

////////////////////////////////////////
// Change notes and system for apikey //
////////////////////////////////////////
const char *CceApiKey::Edit(int socket, int sysuser_id, int system_id, string id, string label)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "apikey::edit error", "A database connection needs to be made first");
	if (CValidate::is_number(id) == false)
		return SetError(400, "API", "apikey::add error", "The apikeyid is not numeric");
	if (CValidate::is_alphanum(label) == false)
		return SetError(400, "API", "apikey::add error", "The notes is not alphanumeric");

	list <string> unique;
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["label"] = label;

	// Run DB add //
	return CDbPlus::EditDB(m_pDB, socket, sysuser_id, system_id, id, "id", unique, columns, mask);
}

//////////////////////////////////////////
// Query all apikeys for a given system //
//////////////////////////////////////////
const char *CceApiKey::Query(int socket, int sysuser_id, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "apikey::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("system_id");
	columns.push_back("id");
	columns.push_back("label");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, sysuser_id, system_id, columns, mask, search, sort);
}

///////////////////////
// Disable an apikey //
///////////////////////
const char *CceApiKey::Disable(int socket, int sysuser_id, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "apikey::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "apikey::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, sysuser_id, system_id, id, "id");
}

//////////////////////
// Enable an apikey //
//////////////////////
const char *CceApiKey::Enable(int socket, int sysuser_id, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "apikey::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "apikey::enable error", "The id is not numeric");
 
 	return CDbPlus::EnableDB(m_pDB, socket, sysuser_id, system_id, id, "id");
}