#include "CommissionEngine.h"
#include "ceUser.h"
#include "db.h"
#include "ConnPool.h"
#include <stdlib.h> // atoi //

extern CDb *g_pDB; 
extern CCommissionEngine g_RubyEng; // Handle Ruby DB connection //

//////////////////////
// Ruby Constructor //
//////////////////////
CceUser::CceUser()
{
	m_pDB = &g_RubyEng.m_DB;
	CDbPlus::Setup("user", "ce_users");
	CezJson::SetOrigin("na");
}

/////////////////
// Constructor //
/////////////////
CceUser::CceUser(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("user", "ce_users");
	CezJson::SetOrigin(origin);
}

/////////////////////////////////////
// Add user through ruby interface //
/////////////////////////////////////
string CceUser::AddRuby(int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype)
{
	m_Retval = Add(0, system_id, user_id, parent_id, sponsor_id, signup_date, usertype, "n/a", "n/a", "na@na.com", "5555555555", "na", "na", "na", "55555"); 
	return m_Retval;
}

/////////////////////////////////////
// Edit through the ruby interface //
/////////////////////////////////////
string CceUser::EditRuby(int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype)
{
	m_Retval = Edit(0, system_id, user_id, parent_id, sponsor_id, signup_date, usertype, "n/a", "n/a", "na@na.com", "5555555555", "na", "na", "na", "55555");
	return m_Retval;
}

/////////////////////////////////////////
// Disable user through ruby interface //
/////////////////////////////////////////
string CceUser::DisableRuby(int system_id, string user_id)
{
	m_Retval = Disable(0, system_id, user_id);
	return m_Retval;
}

////////////////////////////////////////
// Enable user through ruby interface //
////////////////////////////////////////
string CceUser::EnableRuby(int system_id, string user_id)
{
	m_Retval = Enable(0, system_id, user_id);
	return m_Retval;
}

//////////////////////////////////////////////////////
// Add user into system to calculate commissions on //
//////////////////////////////////////////////////////
const char *CceUser::Add(int socket, int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype, 
	string firstname, string lastname, string email, string cell, string address, string city, string state, string zip)
{
	CDbPlus::Debug(DEBUG_TRACE, "CceUser::Add - TOP");

	if (m_pDB == NULL)
		return SetError(409, "API", "user::add error", "A database connection needs to be made first");
	//if (usertype.size() == 0) // Handle defaults //
	//	usertype = "1"; // Default to affiliate //
	if (is_userid(user_id) == false)
		return SetError(400, "API", "user::add error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_userid(parent_id) == false)
		return SetError(400, "API", "user::add error", "The parentid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_userid(sponsor_id) == false)
		return SetError(400, "API", "user::add error", "The sponsorid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_date(signup_date) == false)
		return SetError(400, "API", "user::add error", "The signupdate is not in correct date format YYYY-MM-DD");
	if (is_number(usertype) == false)
		return SetError(400, "API", "user::add error", "The usertype is not numeric");
	if ((atoi(usertype.c_str()) != 1) && (atoi(usertype.c_str()) != 2))
		return SetError(400, "API", "user::add error", "The usertype can only be 1 (affiliate) or 2 (customer)");
	
	// Validate optional values //
	if (firstname.size() != 0)
	{
		if (is_alphanum(firstname) == false)
			return SetError(400, "API", "user::add error", "The firstname can only be alpha characters");
	}
	if (lastname.size() != 0)
	{
		if (is_alphanum(lastname) == false)
			return SetError(400, "API", "user::add error", "The lastname can only be alpha characters");
	}
	if (email.size() != 0)
	{
		if (is_email(email) == false)
			return SetError(400, "API", "user::add error", "The email is invalid");
	}
	if (cell.size() != 0)
	{
		if (is_number(cell) == false)
			return SetError(400, "API", "user::add error", "The cell is invalid");
	}

	////////////////////////////////////
	// Handle optional address fields //
	////////////////////////////////////
	if (address.size() != 0)
	{
		if (is_alphanum(address) == false)
			return SetError(400, "API", "user::add error", "The address has invalid characters");
	}	
	if (city.size() != 0)
	{
		if (is_alpha(city) == false)
			return SetError(400, "API", "user::add error", "The city has invalid characters");
	}
	if (state.size() != 0)
	{
		if (state.size() != 2)
			return SetError(400, "API", "user::add error", "Invalid size limit. Only 2 characters are allowed for state");
		if (is_alpha(state) == false)
			return SetError(400, "API", "user::add error", "The state has invalid characters");
	}
	if (zip.size() != 0)
	{
		if (zip.size() >= 20)
			return SetError(400, "API", "user::add error", "Invalid size limit. Zip must be less than 20 characters long");
		if (is_zipcode(zip) == false)
			return SetError(400, "API", "user::add error", "The zip has invalid characters");
	}

#ifndef COMPILE_UNITED

	CDbPlus::Debug(DEBUG_TRACE, "CceUser::Add - Right Before IsPresent");

	// Make sure sponsor is in the database for their system //
	if (sponsor_id != "0")
	{
		CceUser ceuser(m_pDB, CezJson::m_Origin);
		if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", sponsor_id) == false)
			return SetError(400, "API", "user::add error", "The sponsor_id is not in the system");
	}

	// Make sure sponsor is in the database for their system //
	if (parent_id != "0")
	{
		CceUser ceuser(m_pDB, CezJson::m_Origin);
		if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", parent_id) == false)
			return SetError(400, "API", "user::add error", "The parent_id is not in the system");
	}
#endif

	signup_date = FixDate(signup_date);

	list <string> unique; 
	unique.push_back("user_id");

	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = user_id;
	columns["parent_id"] = parent_id;
	columns["sponsor_id"] = sponsor_id;
	columns["signup_date"] = signup_date;
	columns["usertype"] = usertype;
	columns["upline_parent"] = BuildUplineDB(socket, system_id, parent_id, UPLINE_PARENT_ID);
	columns["upline_sponsor"] = BuildUplineDB(socket, system_id, sponsor_id, UPLINE_SPONSOR_ID);
	columns["firstname"] = firstname;
	columns["lastname"] = lastname;
	columns["email"] = email;
	columns["cell"] = cell;
	columns["address"] = address;
	columns["city"] = city;
	columns["state"] = state;
	columns["zip"] = zip;

	CDbPlus::Debug(DEBUG_TRACE, "CceUser::Add - Right Before CDbPlus::AddDB");

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxUsers);
}

///////////////////////////////////////////
// Allow a user to be edited after added //
///////////////////////////////////////////
const char *CceUser::Edit(int socket, int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype,
	string firstname, string lastname, string email, string cell, string address, string city, string state, string zip)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "user::edit error", "A database connection needs to be made first");
	//if (usertype.size() == 0)
	//	usertype = "1"; // Default to affiliate //
	if (is_userid(user_id) == false)
		return SetError(400, "API", "user::edit error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_userid(parent_id) == false)
		return SetError(400, "API", "user::edit error", "The parentid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_userid(sponsor_id) == false)
		return SetError(400, "API", "user::edit error", "The sponsorid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_date(signup_date) == false)
		return SetError(400, "API", "user::edit error", "The signupdate is not in correct date format YYYY-MM-DD");
	if (is_number(usertype) == false)
		return SetError(400, "API", "user::edit error", "The usertype is not numeric");
	if ((atoi(usertype.c_str()) != 1) && (atoi(usertype.c_str()) != 2))
		return SetError(400, "API", "user::edit error", "The usertype can only be 1 (affiliate) or 2 (customer)");

	// Validate optional values //
	if (firstname.size() != 0)
	{
		if (is_alphanum(firstname) == false)
			return SetError(400, "API", "user::edit error", "The firstname can only be alpha characters");
	}
	if (lastname.size() != 0)
	{
		if (is_alphanum(lastname) == false)
			return SetError(400, "API", "user::edit error", "The lastname can only be alpha characters");
	}
	if (email.size() != 0)
	{
		if (is_email(email) == false)
			return SetError(400, "API", "user::edit error", "The email is invalid");
	}
	if (cell.size() != 0)
	{
		// Scrub and rebuild the cell properly //

		if (is_number(cell) == false)
			return SetError(400, "API", "user::edit error", "The cell is invalid");
	}

	////////////////////////////////////
	// Handle optional address fields //
	////////////////////////////////////
	if (address.size() != 0)
	{
		if (is_alphanum(address) == false)
			return SetError(400, "API", "user::edit error", "The address has invalid characters");
	}	
	if (city.size() != 0)
	{
		if (is_alpha(city) == false)
			return SetError(400, "API", "user::edit error", "The city has invalid characters");
	}
	if (state.size() != 0)
	{
		if (state.size() != 2)
			return SetError(400, "API", "user::edit error", "Invalid size limit. Only 2 characters are allowed for state");
		if (is_alpha(state) == false)
			return SetError(400, "API", "user::edit error", "The state has invalid characters");
	}
	if (zip.size() != 0)
	{
		if (zip.size() >= 20)
			return SetError(400, "API", "user::edit error", "Invalid size limit. Zip must be less than 20 characters long");
		if (is_zipcode(zip) == false)
			return SetError(400, "API", "user::edit error", "The zip has invalid characters");
	}

#ifndef COMPILE_UNITED
	// Make sure sponsor is in the database for their system //
	if (sponsor_id != "0")
	{
		CceUser ceuser(m_pDB, CezJson::m_Origin);
		if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", sponsor_id) == false)
			return SetError(400, "API", "user::edit error", "The sponsor_id is not in the system");
	}

	// Make sure sponsor is in the database for their system //
	if (parent_id != "0")
	{
		CceUser ceuser(m_pDB, CezJson::m_Origin);
		if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", parent_id) == false)
			return SetError(400, "API", "user::edit error", "The parent_id is not in the system");
	}
#endif

	signup_date = FixDate(signup_date);

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	//columns["user_id"] = user_id;
	columns["parent_id"] = parent_id;
	columns["sponsor_id"] = sponsor_id;
	columns["signup_date"] = signup_date;
	columns["usertype"] = usertype;
	columns["upline_parent"] = BuildUplineDB(socket, system_id, parent_id, UPLINE_PARENT_ID);
	columns["upline_sponsor"] = BuildUplineDB(socket, system_id, sponsor_id, UPLINE_SPONSOR_ID);
	columns["firstname"] = firstname;
	columns["lastname"] = lastname;
	columns["email"] = email;
	columns["cell"] = cell;
	columns["address"] = address;
	columns["city"] = city;
	columns["state"] = state;
	columns["zip"] = zip;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, user_id, "user_id", unique, columns, mask);
}

/////////////////////////////
// Update the user address //
/////////////////////////////
const char *CceUser::UpdateAddress(int socket, int system_id, string user_id, string address, string city, string state, string zip)
{	
	if (is_userid(user_id) == false)
		return SetError(400, "API", "user::edit error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_alphanum(address) == false)
		return SetError(400, "API", "user::edit error", "The address has invalid characters");
	if (is_alpha(city) == false)
		return SetError(400, "API", "user::edit error", "The city has invalid characters");
	if (state.size() != 2)
		return SetError(400, "API", "user::edit error", "Invalid size limit. Only 2 characters are allowed for state");
	if (is_alpha(state) == false)
		return SetError(400, "API", "user::edit error", "The state has invalid characters");
	if (zip.size() != 5)
		return SetError(400, "API", "user::edit error", "Invalid size limit. Zip must be 5 characters long");
	if (is_number(zip) == false)
		return SetError(400, "API", "user::edit error", "The zip has invalid characters");

	map <string, string> columns;
	list <string> unique; // Nothing //
	map <string, int> mask;

	columns["address"] = address;
	columns["city"] = city;
	columns["state"] = state;
	columns["zip"] = zip;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, user_id, "user_id", unique, columns, mask);
}

/////////////////////////////////
// Handle pagination for query //
/////////////////////////////////
const char *CceUser::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "user::query error", "A database connection needs to be made first");
	
	map <string, int> mask;

	list<string> columns;
	columns.push_back("user_id");
	columns.push_back("parent_id");
	columns.push_back("sponsor_id");
	columns.push_back("advisor_id");
	columns.push_back("signup_date");
	columns.push_back("usertype");
	columns.push_back("upline_parent");
	columns.push_back("upline_sponsor");
	columns.push_back("firstname");
	columns.push_back("lastname");
	columns.push_back("email");
	columns.push_back("cell");
	columns.push_back("address");
	columns.push_back("city");
	columns.push_back("state");
	columns.push_back("zip");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////////////////////////////////////
// Remove a user out. Maybe they discontinued memebership //
////////////////////////////////////////////////////////////
const char *CceUser::Disable(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "user::edit error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "user::disable error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, user_id, "user_id");
}

///////////////////////////////////////
// Re-enable a user to be accessable //
///////////////////////////////////////
const char *CceUser::Enable(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "user::enable error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "user::enable error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, user_id, "user_id");
}

///////////////////////////
// Get a commission rule //
//////////////////
const char *CceUser::Get(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "user::get error", "A database connection needs to be made first");
	if (is_alphanum(user_id) == false)
		return SetError(400, "API", "user::get error", "The userid is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("user_id");
	columns.push_back("parent_id");
	columns.push_back("sponsor_id");
	columns.push_back("signup_date");
	columns.push_back("usertype");
	columns.push_back("upline_parent");
	columns.push_back("upline_sponsor");
	columns.push_back("firstname");
	columns.push_back("lastname");
	columns.push_back("email");
	columns.push_back("cell");
	columns.push_back("address");
	columns.push_back("city");
	columns.push_back("state");
	columns.push_back("zip");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, user_id, "user_id", columns, mask);
}

///////////////////////////
// Build the upline text //
///////////////////////////
string CceUser::BuildUplineDB(int socket, int system_id, string parent_id, int upline_type)
{
	if (parent_id == "0")
		return " 0 ";

	string upline;
	stringstream ss2;
	if (upline_type == UPLINE_PARENT_ID)
		upline = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT upline_parent FROM ce_users WHERE system_id='" << system_id << "' AND user_id='" << parent_id << "'");
	if (upline_type == UPLINE_SPONSOR_ID)
		upline = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT upline_sponsor FROM ce_users WHERE system_id='" << system_id << "' AND user_id='" << parent_id << "'");
	
	upline += "/ "+parent_id+" ";

	//Debug(DEBUG_WARN, "CceUser::BuildUplineDB - parent_id", parent_id.c_str());
	//Debug(DEBUG_WARN, "CceUser::BuildUplineDB - upline", upline.c_str());

	return upline;
}

/////////////////////////////
// Build the upline string //
/////////////////////////////
string CceUser::BuildUplineLocal(int socket, string user_id, int upline_type)
{
	if (strlen(user_id.c_str()) == 0)
		return "";

	string parent_id;

	if (upline_type == UPLINE_PARENT_ID)
		parent_id = m_UsersMap[user_id].m_ParentID;
	else if (upline_type == UPLINE_SPONSOR_ID)
		parent_id = m_UsersMap[user_id].m_SponsorID;
	else if (upline_type == UPLINE_ADVISOR_ID)
		parent_id = m_UsersMap[user_id].m_AdvisorID;

	if (parent_id.size() == 0)
		parent_id = "0";

	int count = 0;
	string upline = parent_id+" ";
	while ((parent_id != "0") && (parent_id.size() != 0))
	{
		if (upline_type == UPLINE_PARENT_ID)
			parent_id = m_UsersMap[parent_id].m_ParentID;
		else if (upline_type == UPLINE_SPONSOR_ID)
			parent_id = m_UsersMap[parent_id].m_SponsorID;
		else if (upline_type == UPLINE_ADVISOR_ID)
			parent_id = m_UsersMap[parent_id].m_AdvisorID;

		if (parent_id.size() == 0)
			parent_id = "0";

		if (parent_id != "0")
			upline = parent_id+" / "+upline;

		if (count > 10000) // Stop at 10,000 cycles //
		{
			Debug(DEBUG_ERROR, "CceUser::BuildUplineLocal - 10,000 hit - parent_id", parent_id.c_str());
			exit(1);
		}
		count++;
	}
 
	upline = " "+upline;

	//Debug(DEBUG_ERROR, "CceUser::BuildUplineLocal - upline", upline.c_str());

	return upline;
}

////////////////////////////////////////////////////
// Rebuild upline entries for all users in system //
////////////////////////////////////////////////////
bool CceUser::RebuildAllUpline(CDb *pDB, int socket, int system_id)
{
	if (pDB == NULL)
		return Debug(DEBUG_ERROR, "CceUser::RebuildAllUpline - A database connection needs to be made first");

	// Date doesn't really matter in this case //
	string start_date = "2017-1-1";
	string end_date = "2017-1-31";

	// Grab all users ref ParentID //
	if (pDB->GetUsers(socket, system_id, true, m_UsersMap, UPLINE_PARENT_ID, start_date.c_str(), end_date.c_str()) == false)
		return Debug(DEBUG_ERROR, "CceUser::RebuildAllUpline - Problems with GetUsers ParentID");

	Debug(DEBUG_TRACE, "CceUser::RebuildAllUpline - Users are loaded");

	int usercount = m_UsersMap.size();
	Debug(DEBUG_DEBUG, "CceUser::RebuildAllUpline - usercount", usercount);

	// Connection pool //
/*	std::string ipaddress;
	ipaddress = "127.0.0.1";
	std::stringstream ss;
	std::string conninfo;
	Debug(DEBUG_DEBUG, "CDb::Connect - db_name", pDB->m_pSettings->m_DatabaseName);
	ss << " user=" << pDB->m_pSettings->m_Username << " password=" << pDB->m_pSettings->m_Password << " dbname=" << pDB->m_pSettings->m_DatabaseName << " hostaddr=" << ipaddress << " port=5432";
	conninfo = ss.str();
	CConnPool pooltest;
	if (pooltest.ConnectPool(conninfo, 98) == false)
		Debug(DEBUG_ERROR, "CCommissionEngine::Test - Connect Error");
*/
	TimeStart();

	// 458.227441 //

	// Loop through all users rebuilding user_upline //
	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //

		string upline_parent = BuildUplineLocal(socket, puser->m_UserID, UPLINE_PARENT_ID);
		string upline_sponsor = BuildUplineLocal(socket, puser->m_UserID, UPLINE_SPONSOR_ID);
		//string upline_advisor = BuildUplineLocal(socket, puser->m_UserID, UPLINE_ADVISOR_ID);

		if (upline_parent != " 0 ")
			upline_parent = " 0 /"+upline_parent;
		if (upline_sponsor != " 0 ")
			upline_sponsor = " 0 /"+upline_sponsor;
		//if (upline_advisor != " 0 ")
		//	upline_advisor = " 0 /"+upline_advisor;

		if ((upline_parent.size() != 0) && (upline_sponsor.size() != 0))
		{
			stringstream ss;
			if (pDB->ExecDB(socket, ss << "UPDATE ce_users SET upline_parent='" << upline_parent << "', upline_sponsor='" << upline_sponsor << "' WHERE system_id=" << system_id << " AND user_id='" << puser->m_UserID << "'") == NULL)
				return Debug(DEBUG_ERROR, "CceUser::RebuildAllUpline - Problems with UPDATE");
			//if (pDB->ExecDB(socket, ss << "UPDATE ce_users SET upline_parent='" << upline_parent << "', upline_sponsor='" << upline_sponsor << "', upline_advisor='" << upline_advisor << "' WHERE system_id=" << system_id << " AND user_id='" << puser->m_UserID << "'") == false)
			//	return Debug(DEBUG_ERROR, "CceUser::RebuildAllUpline - Problems with UPDATE");
//			ss << "UPDATE ce_users SET upline_parent='" << upline_parent << "', upline_sponsor='" << upline_sponsor << "' WHERE system_id=" << system_id << " AND user_id='" << puser->m_UserID << "'";
//			if (pooltest.Exec(ss.str().c_str()) == false)
//				return Debug(DEBUG_ERROR, "CceUser::RebuildAllUpline - Problems with UPDATE");
		}
	}

	TimeEnd();

	Debug(DEBUG_TRACE, "CceUser::RebuildAllUpline - BOTTOM");

	return true;
}

string CceUser::BuildUplineAdvisor(int socket, string user_id, int upline_type, map <string, CUser> &UsersMap)
{
	if (strlen(user_id.c_str()) == 0)
		return "";

	string parent_id;

	if (upline_type == UPLINE_ADVISOR_ID)
		parent_id = UsersMap[user_id].m_AdvisorID;

	if (parent_id.size() == 0)
		parent_id = "0";

	int count = 0;
	string upline = parent_id+" ";
	while ((parent_id != "0") && (parent_id.size() != 0))
	{
		if (upline_type == UPLINE_ADVISOR_ID)
			parent_id = UsersMap[parent_id].m_AdvisorID;

		if (parent_id.size() == 0)
			parent_id = "0";

		if (parent_id != "0")
			upline = parent_id+" / "+upline;

		if (count > 10000) // Stop at 10,000 cycles //
		{
			Debug(DEBUG_ERROR, "CceUser::BuildUplineLocal - 10,000 hit - parent_id", parent_id.c_str());
			exit(1);
		}
		count++;
	}
 
	upline = " "+upline;

	//Debug(DEBUG_ERROR, "CceUser::BuildUplineLocal - upline", upline.c_str());

	return upline;
}

//////////////////////////////////////////////////////////////////
// upline_advisor needs to compress and updated in the database //
//////////////////////////////////////////////////////////////////
bool CceUser::RebuildAdvisorUpline(CDb *pDB, int socket, int system_id, map <string, CUser> &UsersMap)
{
	if (pDB == NULL)
		return Debug(DEBUG_ERROR, "CceUser::RebuildAdvisorUpline - A database connection needs to be made first");

//	TimeStart();

	// Loop through all users rebuilding user_upline //
	map <string, CUser>::iterator j;
	for (j=UsersMap.begin(); j != UsersMap.end(); ++j) 
	{
		CUser *puser = &UsersMap[j->first]; // This seems to be more accurate //

		//Debug(DEBUG_TRACE, "CceUser::RebuildAdvisorUpline - LOOP - userid", puser->m_UserID);

		string upline_advisor = BuildUplineAdvisor(socket, puser->m_UserID, UPLINE_ADVISOR_ID, UsersMap);

		if (upline_advisor != " 0 ")
			upline_advisor = " 0 /"+upline_advisor;

		// Needs to be stored for later math use //
		puser->m_UplineAdvisor = upline_advisor;

		if (m_pDB->m_pSettings->m_DisableAdvisorSQL == false)
		{
			stringstream ss;
			if (pDB->ExecDB(socket, ss << "UPDATE ce_users SET upline_advisor='" << upline_advisor << "' WHERE system_id=" << system_id << " AND user_id='" << puser->m_UserID << "'") == NULL)
				return Debug(DEBUG_ERROR, "CceUser::RebuildAllUpline - Problems with UPDATE");
		}
	}

//	TimeEnd();

//	Debug(DEBUG_TRACE, "CceUser::RebuildAdvisorUpline - BOTTOM");

	return true;
}