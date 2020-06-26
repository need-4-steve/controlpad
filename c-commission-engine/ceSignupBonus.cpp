#include "ceSignupBonus.h"
//#include "db.h"
//#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceSignupBonus::CceSignupBonus(CDb *pDB, string origin)
{
	m_pDB = pDB;
	m_AddCount = 0;
	CDbPlus::Setup("signupbonus", "ce_signupbonus");
	CezJson::SetOrigin(origin);
}
	
//////////////////////////
// For external viewing //
//////////////////////////
const char *CceSignupBonus::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("from_user_id");
	columns.push_back("batch_id");
	columns.push_back("amount");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////////
// Internal on commission run //
////////////////////////////////
bool CceSignupBonus::AddBulk(bool pretend, int socket, int system_id, string user_id, string from_user_id, int batch_id, string signupbonus)
{
	if (pretend == true)
		return true;

	if (m_pDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::AddBulk - m_pDB == NULL");
	//if (user_id == "0")
	//	return CDbPlus::Debug(DEBUG_DEBUG, "CceSignupBonus::AddBulk - user_id == 0");

	// Scrub inputs //
	if (system_id < 1)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::AddBulk - system_id < 1");
	if (is_userid(user_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::AddBulk - invalid user_id value");
	if (batch_id < 1)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::AddBulk - batch_id < 1");
	if (is_decimal(signupbonus) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::AddBulk - invalid signupbonus value");

	// User 0 can never get the signup bonus //
	if (user_id == "0")
		return CDbPlus::Debug(DEBUG_INFO, "CceSignupBonus::AddBulk - user_id == 0");

	// Map out the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = user_id;
	columns["from_user_id"] = from_user_id;
	columns["batch_id"] = IntToStr(batch_id);
	columns["signupbonus"] = signupbonus;

	// Do a bulk add
	if ((m_AddCount = BulkAdd(m_pDB, socket, "ce_signupbonus", columns, &m_strAddSQL, m_AddCount)) == -1)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::AddBulk - dblus.BulkAdd signupbonus breakage Error");

	return true;
}

////////////////////////////////////
// Do the last flush after adding //
////////////////////////////////////
bool CceSignupBonus::FinishBulk(bool pretend, int socket)
{
	if (pretend == true)
		return true;

	if (m_pDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CceSignupBonus::FinishBulk - m_pDB == NULL");

	if (BulkFinish(m_pDB, socket, &m_strAddSQL) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CDb::Flush - BulkFinish Error");

	return true;
}


/*
/////////////////
// Constructor //
/////////////////
CcePayout::CcePayout(CDb *pDB)
{
	m_pDB = pDB;

	CDbPlus::Setup("grandtotals", "ce_grandtotals");
}

//////////////////////////////////////////////
// Get results for grandpayout to authorize //
//////////////////////////////////////////////
const char *CcePayout::Query(int system_id, string authorized, string search, string sort)
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

	return CDbPlus::QueryDB(m_pDB, 0, system_id, columns, mask, search, sort);
}

/////////////////////////////
// Authorize a grandpayout //
/////////////////////////////
const char *CcePayout::Auth(int system_id, string id, string authorized)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::auth error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "payout::auth error", "The id is not numeric");
	if (authorized.size() > 5)
		return SetError(400, "API", "payout::auth error", "The authorized value is either true or false");
	if ((strcmp(authorized.c_str(), "true") != 0) && (strcmp(authorized.c_str(), "false") != 0))
		return SetError(400, "API", "payout::auth error", "The authorized value is either true or false");

	return m_pDB->AuthGrandPayout(system_id, atoi(id.c_str()), authorized.c_str());
}

/////////////////////////////
// Authorize a grandpayout //
/////////////////////////////
const char *CcePayout::AuthBulk(int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::authbulk error", "A database connection needs to be made first");

	return m_pDB->AuthGrandBulk(system_id);
}

///////////////////////////////////
// Disable a grand payout record //
///////////////////////////////////
const char *CcePayout::Disable(int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "payout::disable error", "The id is not numeric");

	return m_pDB->DisableGrandPayout(system_id, atoi(id.c_str()));
}

//////////////////////////////////
// Enable a grand payout record //
//////////////////////////////////
const char *CcePayout::Enable(int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payout::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "payout::disable error", "The id is not numeric");

	return m_pDB->EnableGrandPayout(system_id, atoi(id.c_str()));
}
*/