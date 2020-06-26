#include "cePayout.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CcePayout::CcePayout(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("grandtotals", "ce_grandtotals");
	CezJson::SetOrigin(origin);
}

//////////////////////////////////////////////
// Get results for grandpayout to authorize //
//////////////////////////////////////////////
const char *CcePayout::Query(int socket, int system_id, string authorized, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::query error", "A database connection needs to be made first");
	if (authorized.size() > 5)
		return SetError(400, "API", "payout::query error", "The authorized value needs to be either true or false");
	if ((strcmp(authorized.c_str(), "true") != 0) && (strcmp(authorized.c_str(), "false") != 0))
		return SetError(400, "API", "payout::query error", "The authorized value needs to be either true or false");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("user_id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("amount");
	columns.push_back("authorized");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	// Handle Authorized on/off //
	search = "authorized="+authorized+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

/////////////////////////////
// Authorize a grandpayout //
/////////////////////////////
const char *CcePayout::Auth(int socket, int system_id, string id, string authorized)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::auth error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "payout::auth error", "The id is not numeric");
	if (authorized.size() > 5)
		return SetError(400, "API", "payout::auth error", "The authorized value is either true or false");
	if ((strcmp(authorized.c_str(), "true") != 0) && (strcmp(authorized.c_str(), "false") != 0))
		return SetError(400, "API", "payout::auth error", "The authorized value is either true or false");

	return m_pDB->AuthGrandPayout(socket, system_id, atoi(id.c_str()), authorized.c_str());
}

/////////////////////////////
// Authorize a grandpayout //
/////////////////////////////
const char *CcePayout::AuthBulk(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::authbulk error", "A database connection needs to be made first");

	return m_pDB->AuthGrandBulk(socket, system_id);
}

///////////////////////////////////
// Disable a grand payout record //
///////////////////////////////////
const char *CcePayout::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "payout::disable error", "The id is not numeric");

	return m_pDB->DisableGrandPayout(socket, system_id, atoi(id.c_str()));
}

//////////////////////////////////
// Enable a grand payout record //
//////////////////////////////////
const char *CcePayout::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "payout::disable error", "The id is not numeric");

	return m_pDB->EnableGrandPayout(socket, system_id, atoi(id.c_str()));
}