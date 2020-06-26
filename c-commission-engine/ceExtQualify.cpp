#include "ceExtQualify.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceExtQualify::CceExtQualify(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("extqualify", "ce_extqualify");
	CezJson::SetOrigin(origin);
}

///////////////////////////////////////
// Define the external qualify value //
///////////////////////////////////////
const char *CceExtQualify::Add(int socket, int system_id, string user_id, string varid, string value, string eventdate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "extqualify::add error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "extqualify::add error", "The rank is not numeric");
	if (is_number(varid) == false)
		return SetError(400, "API", "extqualify::add error", "The varid is not numeric");
	if (is_number(value) == false)
		return SetError(400, "API", "extqualify::add error", "The value is not numeric");
	if (is_date(eventdate) == false)
		return SetError(400, "API", "extqualify::add error", "The eventdate is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = user_id;
	columns["varid"] = varid;
	columns["value"] = value;
	columns["event_date"] = eventdate;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxCommRules);
}

/////////////////////////////////////
// Editing of commission is needed //
/////////////////////////////////////
const char *CceExtQualify::Edit(int socket, int system_id, string id, string user_id, string varid, string value, string eventdate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "extqualify::edit error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "extqualify::edit error", "The rank is not numeric");
	if (is_number(varid) == false)
		return SetError(400, "API", "extqualify::edit error", "The varid is not numeric");
	if (is_number(value) == false)
		return SetError(400, "API", "extqualify::edit error", "The value is not numeric");
	if (is_date(eventdate) == false)
		return SetError(400, "API", "extqualify::edit error", "The eventdate is not numeric");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = user_id;
	columns["varid"] = varid;
	columns["value"] = value;
	columns["event_date"] = eventdate;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

//////////////////////////////////////
// List all of the commission rules //
//////////////////////////////////////
const char *CceExtQualify::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "extqualify::query error", "A database connection needs to be made first");
/*	if (is_userid(user_id) == false)
		return SetError(400, "API", "extqualify::query error", "The userid is not numeric");
	if (is_date(start_date) == false)
		return SetError(400, "API", "extqualify::query error", "The startdate is not valid");
	if (is_date(end_date) == false)
		return SetError(400, "API", "extqualify::query error", "The enddate is not valid");
*/	
	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("user_id");
	columns.push_back("varid");
	columns.push_back("value");
	columns.push_back("event_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");
/*
	// Handle userid search //
	if (search.size() == 0)
	{
		if (user_id.size() != 0)
			search = "userid="+user_id;

		// Handle startdate search //
		if ((start_date.size() != 0) && (search.size() == 0))
			search = "startdate="+start_date;
		else if ((start_date.size() != 0) && (search.size() != 0)) 
			search = search+"&startdate="+start_date;

		// Handle enddate search //
		if ((end_date.size() != 0) && (search.size() == 0))
			search = "enddate="+end_date;
		else if ((end_date.size() != 0) && (search.size() != 0))
			search = search+"&enddate="+end_date;
	}

	// Still need to handle sort //
*/
	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////////////////
// Deleting of commission is needed //
//////////////////////////////////////
const char *CceExtQualify::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "extqualify::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "extqualify::disable error", "The id is not numeric");
	
	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

////////////////////////
// Enable a comm rule //
////////////////////////
const char *CceExtQualify::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "extqualify::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "extqualify::enable error", "The id is not numeric");
	
	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

///////////////////////////
// Get a commission rule //
//////////////////
const char *CceExtQualify::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "extqualify::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "extqualify::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("user_id");
	columns.push_back("varid");
	columns.push_back("value");
	columns.push_back("event_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}

/////////////////////////////////
// Internal use in commissions //
/////////////////////////////////
bool CceExtQualify::ReadInData(int socket, int system_id, string startdate, string enddate, list <CExtQualify> *pExtQualifyList)
{
	if (m_pDB == NULL)
		return Debug(DEBUG_ERROR, "CceExtQualify::ReadInData - A database connection needs to be made first");

	stringstream ssCount;
	if (m_pDB->GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_extqualify WHERE system_id='" << system_id << "' AND event_date::DATE >='" << startdate << "' AND event_date::DATE <='" << enddate << "'") == 0)
		return Debug(DEBUG_INFO, "CceExtQualify::ReadInData - no records");

	stringstream ssSQL;
	CConn *conn;
	if ((conn = m_pDB->ExecDB(socket, ssSQL << "SELECT user_id, varid, value FROM ce_extqualify WHERE system_id='" << system_id << "' AND event_date::DATE >='" << startdate << "' AND event_date::DATE <='" << enddate << "'")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetUsers - ExecDB user Error #1");
 
	while (m_pDB->FetchRow(conn) == true)
    {
    	CExtQualify newextqualify;
    	newextqualify.m_UserID = conn->m_RowMap[0].c_str();
    	newextqualify.m_VarID = conn->m_RowMap[1].c_str();
    	newextqualify.m_Value = StrToInt(conn->m_RowMap[2].c_str());

    	// Add to the linked list  //
    	pExtQualifyList->push_back(newextqualify);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CceExtQualify::ReadInData - ThreadReleaseConn == false");

	return true;
}
