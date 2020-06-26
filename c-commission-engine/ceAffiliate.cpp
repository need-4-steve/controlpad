#include "ceAffiliate.h"
#include "ezCrypt.h"
#include "commissions.h"
#include "rulesrank.h"

#include <sstream>
#include <stdlib.h> // atoi //

#include <openssl/rand.h> // RAND_pseudo_bytes, RAND_bytes

/////////////////
// Constructor //
/////////////////
CceAffiliate::CceAffiliate(CDb *pDB, string origin)
{
	m_pDB = pDB;
	CezJson::SetOrigin(origin);

	//Debug(DEBUG_WARN, "CceAffiliate::CceAffiliate - origin", origin);
}

/////////////////////////////
// Check the password hash //
/////////////////////////////
const char *CceAffiliate::UserValidCheck(int socket, int system_id, string email)
{
	// Scrub inputs //
	if (is_email(email) == false)
			return SetError(400, "API", "affiliate::uservalidcheck error", "The email is invalid");

	stringstream ss1;
	ss1 << "SELECT count(*) FROM ce_users WHERE disabled=false AND system_id=" << system_id << " AND email ILIKE '" << email << "'";
	int retval = m_pDB->GetFirstDB(socket, ss1);
	if (retval == 0)
	{
		stringstream ss2;
		ss2 << "CceAffiliate::UserValidCheck - User not found in system_id=" << system_id << " AND email ILIKE " << email;
		Debug(DEBUG_TRACE, ss2.str().c_str());
		return SetError(400, "API", "affiliate::uservalidcheck error", "No users found in system for given systemid and email");
	}
	else if (retval > 1)
	{
		stringstream ss2;
		ss2 << "CceAffiliate::UserValidCheck - Multiple users found in system_id=" << system_id << " AND email ILIKE " << email;
		Debug(DEBUG_TRACE, ss2.str().c_str());
		return SetError(400, "API", "affiliate::uservalidcheck error", "Multiple users found in system for given systemid and email");
	}

	return SetJson(200, "");

	// Return the userid //
	//stringstream ss3;
	//ss3 << "SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND email ILIKE '" << email << "'";
	//string userid = m_pDB->GetFirstCharDB(ss3);

	//stringstream ss4;
	//return SetJson(200, ss4 << ",\"usercheck\":{\"userid\":\"" << userid << "\"}");
}

/////////////////////////////
// Password Hash Generator //
/////////////////////////////
const char *CceAffiliate::PasswordHashGen(int socket, int system_id, string email, string remoteaddress)
{
	// Scrub inputs //
	if (is_email(email) == false)
		return SetError(400, "API", "affiliate::passwordhashgen error", "The email can only be a-z, A-Z, 1-9, +(plus), _(underscore), -(minus) @, and .(period)");
	if (remoteaddress.size() != 0)
	{
		if (is_ipaddress(remoteaddress) == false)
		{
			Debug(DEBUG_DEBUG, "CceAffiliate::PasswordHashGen - remoteaddress", remoteaddress.c_str());
			return SetError(400, "API", "affiliate::passwordhashgen error", "The remoteaddress has invalid characters");
		}
	}

	stringstream ss0;
	ss0 << "SELECT count(*) FROM ce_users WHERE system_id=" << system_id << " AND email ILIKE '" << email << "'";
	if (m_pDB->GetFirstDB(socket, ss0) == 0)
		return SetError(400, "API", "affiliate::passwordhashgen error", "The user cannot be found in ref to the email");

	stringstream ss1;
	ss1 << "SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND email ILIKE '" << email << "'";
	string user_id = m_pDB->GetFirstCharDB(socket, ss1);

	string hash; // Allow lookup for creation //
	stringstream ss2;
	ss2 << "SELECT count(*) FROM ces_user_passreset WHERE substr((created_at::TIME-(now()-interval '30 minutes')::TIME)::TEXT,1,1) != '-' AND user_id='" << user_id << "' AND used='false'";
    int retval = m_pDB->GetFirstDB(socket, ss2);
	if (retval != 0)
	{
		stringstream ss3;
		ss3 << "SELECT hash FROM ces_user_passreset WHERE substr((created_at::TIME-(now()-interval '30 minutes')::TIME)::TEXT,1,1) != '-' AND user_id='" << user_id << "' AND used='false'";
		hash = m_pDB->GetFirstCharDB(socket, ss3);
	}

	// Create new hash if not found //
    if (hash.size() == 0)
    {
    	char bytes[256];
        memset(bytes, 0, 256);
        if (RAND_bytes((unsigned char *)bytes, 32) == 0)
        {
        	Debug(DEBUG_ERROR, "CceAffiliate::PasswordHashGen #1 - ERROR RAND_bytes == 0");
			return SetError(400, "API", "affiliate::passwordhashgen #1 error", "ERROR RAND_bytes returned 0");
        }

        // Recursive function needed for retrying again and again 1000 times //
        hash = GetRandHash64(1);

    	stringstream ss4;
		ss4 << "INSERT INTO ces_user_passreset(system_id, user_id, hash, ipaddress) VALUES (" << system_id << ", '" << user_id << "', '" << hash << "', '" << remoteaddress << "')";
		if (m_pDB->ExecDB(socket, ss4) == NULL)
		{
			Debug(DEBUG_ERROR, "CceAffiliate::PasswordHashGen - Problems with ExecDB query");
			return SetError(400, "API", "affiliate::passwordhashgen error", "Multiple users found in system for given systemid and email");
		}
    }   

    // Send back hash in Json format //
    stringstream ss5;
    return SetJson(200, ss5 << ",\"hashgen\":{\"hash\":\"" << hash << "\"}");
}

////////////////////////////////
// Get a random 6 length hash //
////////////////////////////////
string CceAffiliate::GetRandHash64(int deep)
{
	if (deep == 100)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::GetRandHash64 - deep == 1000. Reverting to backup default hash");
		return "ASDFASDFasdfasdf1234567890987654321qwertyuiopmnbvcxzasdfghjklIOU"; // Maybe return a generic temp? //
	}

	string hash;
	char bytes[256];
    memset(bytes, 0, 256);
    if (RAND_bytes((unsigned char *)bytes, 32) == 0)
    {
     	Debug(DEBUG_ERROR, "CceAffiliate::GetRandHash64 - ERROR RAND_bytes == 0");
		return SetError(400, "API", "affiliate::GetRandHash64 #1 error", "ERROR RAND_bytes returned 0");
    }

    // Convert binary to hex //
    string tmpstr = bytes;
    hash = bin2hex(tmpstr);

    if (hash.size() < 64)
    {
    	//Debug(DEBUG_ERROR, "CceAffiliate::GetRandHash64 #1 - deep", deep);
    	//Debug(DEBUG_ERROR, "CceAffiliate::GetRandHash64 #1 - hash.size()", hash.size());
    	return GetRandHash64(deep+1);
    }
    else
    {
    	return hash;
    }
}

///////////////////////////////////////
// Check to see if it's a valid hash //
///////////////////////////////////////
const char *CceAffiliate::PasswordHashValid(int socket, string hash)
{
	// Make sure a record exists first //
	stringstream ss1;
	ss1 << "SELECT count(*) FROM ces_user_passreset WHERE hash='" << hash << "' AND used=false";
	if (m_pDB->GetFirstDB(socket, ss1) == 0)
	{
		Debug(DEBUG_DEBUG, "CceAffiliate::PasswordHashUpdate - No records found related to hash");
		return SetError(400, "API", "affiliate::passwordhashupdate error", "No records found related to hash");
	}

	// Grab that given record //
	stringstream ss2;
	ss2 << "SELECT created_at::TIME-(now()- interval '30 minutes')::TIME FROM ces_user_passreset WHERE hash='" << hash << "' AND used=false";
	string timeval = m_pDB->GetFirstCharDB(socket, ss2);
	if (timeval == "-") // Expired //
	{
		Debug(DEBUG_DEBUG, "CceAffiliate::PasswordHashUpdate - The hash record has expired");
		return SetError(400, "API", "affiliate::passwordhashupdate error", "The hash record has expired");
	}

	return SetJson(200, "");	
}

//////////////////////////////
// Update the password hash //
//////////////////////////////
const char *CceAffiliate::PasswordHashUpdate(int socket, string hash)
{
	if (is_alphanum(hash) == false)
		return SetError(400, "API", "affiliate::passwordhashupdate error", "The hash can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	// Make sure a record exists first //
	stringstream ss1;
	ss1 << "SELECT count(*) FROM ces_user_passreset WHERE hash='" << hash << "' AND used=false";
	if (m_pDB->GetFirstDB(socket, ss1) == 0)
	{
		Debug(DEBUG_DEBUG, "CceAffiliate::PasswordHashUpdate - No records found related to hash");
		return SetError(400, "API", "affiliate::passwordhashupdate error", "No records found related to hash");
	}

	// Grab the userid //
	stringstream ss2;
	ss2 << "SELECT user_id FROM ces_user_passreset WHERE hash='" << hash << "' AND used=false";
	string userid = m_pDB->GetFirstCharDB(socket, ss2);

	// Grab that given record //
	stringstream ss3;
	ss3 << "SELECT created_at::TIME-(now()- interval '30 minutes')::TIME FROM ces_user_passreset WHERE hash='" << hash << "' AND used=false";
	string timeval = m_pDB->GetFirstCharDB(socket, ss3);
	if (timeval == "-") // Expired //
	{
		Debug(DEBUG_DEBUG, "CceAffiliate::PasswordHashUpdate - The hash record has expired");
		return SetError(400, "API", "affiliate::passwordhashupdate error", "The hash record has expired");
	}

	// Update the has record for used=true //
	stringstream ss4;
	ss4 << "UPDATE ces_user_passreset SET used=true WHERE user_id IN (SELECT user_id FROM ces_user_passreset WHERE hash='" << hash << "')";
	if (m_pDB->ExecDB(socket, ss4) == NULL)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::PasswordHashUpdate - Problems with ExecDB query");
		return SetError(400, "API", "affiliate::passwordhashupdate error", "Problems with SQL UPDATE command");
	}
       
    stringstream ss5;
	return SetJson(200, ss5 << ",\"hashupdate\":{\"userid\":\"" << userid << "\"}");
}

//////////////////////////////
// Make a log for the login //
//////////////////////////////
const char *CceAffiliate::LoginLog(int socket, int system_id, string email, string remoteaddress)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::userloginlog error", "A database connection needs to be made first");
	if (is_email(email) == false)
		return SetError(400, "API", "affiliate::userloginlog error", "The email is invalid");
	if (remoteaddress.size() != 0)
	{
		if (is_ipaddress(remoteaddress) == false)
			return SetError(400, "API", "affiliate::userloginlog error", "The remoteaddress is invalid");
	}

	stringstream ss1;
	ss1 << "SELECT count(*) FROM ce_users WHERE system_id=" << system_id << " AND email ILIKE '" << email << "'";
	if (m_pDB->GetFirstDB(socket, ss1) == 0)
		return SetError(400, "API", "affiliate::userloginlog error", "User email not found in that system_id");

	stringstream ss2;
	ss2 << "SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND email ILIKE '" << email << "'";
	string userid = m_pDB->GetFirstCharDB(socket, ss2);

	// Make login entry //
	stringstream ss3;
	ss3 << "INSERT INTO ces_user_login (user_id, system_id, ipaddress) VALUES ('" << userid << "', " << system_id << ", '" << remoteaddress << "')";
  	if (m_pDB->ExecDB(socket, ss3) == NULL)
  	{
  		Debug(DEBUG_ERROR, "CceAffiliate::LoginLog - There was an error with the SQL INSERT");
  		return SetError(409, "API", "affiliate::userlloginlog error", "There was an error with the SQL INSERT");
  	}

  	stringstream ss5;
	return SetJson(200, ss5 << ",\"user\":{\"userid\":\"" << userid << "\"}");
}

///////////////////////////////
// Make a log for the logout //
///////////////////////////////
const char *CceAffiliate::LogoutLog(int socket, int system_id, string email)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::userlogoutlog error", "A database connection needs to be made first");
	if (is_email(email) == false)
		return SetError(400, "API", "affiliate::userlogoutlog error", "The email is invalid");

	stringstream ss1;
	ss1 << "UPDATE ces_user_login SET logout_at='now()' WHERE id IN (SELECT id FROM ces_user_login WHERE user_id IN (SELECT user_id FROM ce_users WHERE email ILIKE '" << email << "' AND system_id=" << system_id << ") ORDER BY id DESC LIMIT 1)";
	if (m_pDB->ExecDB(socket, ss1) == NULL)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::LoginLogout - Problems with ExecDB query");
		return SetError(400, "API", "affiliate::userlogoutlog error", "Problems with SQL UPDATE command");
	}

	return SetJson(200, "");
}

/////////////////////////////
// Reset the user password //
/////////////////////////////
const char *CceAffiliate::PasswordReset(int socket, int system_id, string user_id, string password)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::passwordreset error", "A database connection needs to be made first");
	if (password.length() < 8)
		return SetError(409, "API", "affiliate::passwordreset error", "The password need to be at least 8 characters long");
	if (is_password(password) == false)
		return SetError(409, "API", "affiliate::passwordreset error", "The password is invalid");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "affiliate::passwordreset error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	CezCrypt crypt;
	stringstream ss;
	string salt = crypt.GenSalt();

	string password_hash = crypt.GenPBKDF2(m_pDB->m_pSettings->m_HashPass.c_str(), salt.c_str(), password.c_str());

	stringstream ss2;
	ss2 << "UPDATE ce_users SET password_hash='" << password_hash << "', salt='" << salt.c_str() << "' WHERE system_id='" << system_id << "' AND user_id='" << user_id << "'";
	if (m_pDB->ExecDB(socket, ss2) == NULL)
	{
		Debug(DEBUG_ERROR, "");
		return SetError(400, "API", "affiliate::passwordreset error", "There was a Exec UPDATE error");
	}

	return SetJson(200, "");
}	

//////////////////////////////
// Verifu login for enduser //
//////////////////////////////
bool CceAffiliate::Login(int socket, int system_id, string enduseremail, string enduserpass)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::login error", "A database connection needs to be made first");

	stringstream ss1;
	if (m_pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_users WHERE disabled=false AND system_id=" << system_id << " AND email ILIKE '" << enduseremail << "'") != 1)
		return false;

	stringstream ss2;
	string salt = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT salt FROM ce_users WHERE disabled=false AND system_id=" << system_id << " AND email ILIKE '" << enduseremail << "'");
	CezCrypt crypt;
	string password_hash = crypt.GenPBKDF2(m_pDB->m_pSettings->m_HashPass.c_str(), salt.c_str(), enduserpass.c_str());

	stringstream ss3;
	int count = m_pDB->GetFirstDB(socket, ss3 << "SELECT count(*) FROM ce_users WHERE disabled=false AND system_id=" << system_id << " AND email ILIKE '" << enduseremail << "' AND password_hash='" << password_hash << "'");
	if (count == 1)
		return true;

	return false;
}

///////////////////////////////////
// Get the UserID of the EndUser //
///////////////////////////////////
const char *CceAffiliate::GetUserID(int socket, int system_id, string enduseremail)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::getuserid error", "A database connection needs to be made first");

	stringstream ss1;
	string user_id = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT user_id FROM ce_users WHERE disabled=false AND system_id='" << system_id << "' AND email ILIKE '" << enduseremail << "'");
	return user_id.c_str();
}

//////////////////////////
// Get User Projections //
//////////////////////////
const char *CceAffiliate::MyProjections(int socket, int system_id, string userid, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myprojections error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::myprojections error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_date(startdate) == false)
		return SetError(409, "API", "affiliate::myprojections error", "The startdate is invalid");
	if (is_date(enddate) == false)
		return SetError(409, "API", "affiliate::myprojections error", "The enddate is invalid");

	// Run a projection for given user at top of upline //
	startdate = FixDate(startdate);
	enddate = FixDate(enddate);

	// Call commission class to do calculations //
	int commtype = m_pDB->GetSystemCommType(socket, system_id);
	string compression_str = m_pDB->GetSystemCompression(socket, system_id);
	bool compression = true;
	if (compression_str == "false")
		compression = false;
	else if (compression_str == "true")
		compression = true;

	CCommissions comm;
	m_Json = comm.Run(m_pDB, socket, system_id, commtype, true, false, startdate.c_str(), enddate.c_str(), userid, compression);
	return m_Json.c_str();
}
			
///////////////////////////
// Get Users Commissions //
///////////////////////////
const char *CceAffiliate::MyCommissions(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("commissions", "ce_commissions");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mycommissions error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mycommissions error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	// Only grab records in ref to userid //
	search = "userid="+userid+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}
			
///////////////////////////////////
// Get Users Achievement Bonuses //
///////////////////////////////////
const char *CceAffiliate::MyAchvBonus(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("achvbonus", "ce_achvbonus");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myachvbonus error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(409, "API", "affiliate::myachvbonus error", "The userid is invalid");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("rankrule_id");
	columns.push_back("rank");
	columns.push_back("amount");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	// Only grab records in ref to userid //
	search = "userid="+userid+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}
		
//////////////////////
// Get User Bonuses //
//////////////////////	
const char *CceAffiliate::MyBonus(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("bonus", "ce_bonus");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mybonus error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mybonus error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("bonus_date");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	search = "userid="+userid+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}
	
////////////////////////
// Get Rank Gen Bonus //
////////////////////////	
const char *CceAffiliate::MyRankGenBonus(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("rankgenbonus", "ce_rankgenbonus");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myrankgenbonus error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::myrankgenbonus error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("event_date");
	columns.push_back("my_rank");
	columns.push_back("user_rank");
	columns.push_back("generation");
	columns.push_back("userdata");
	columns.push_back("rule_id");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	search = "userid="+userid+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////
// Get Users Ledger //
//////////////////////		
const char *CceAffiliate::MyLedger(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("ledger", "ce_ledger");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myledger error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::myledger error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("ref_id");
	columns.push_back("user_id");
	columns.push_back("ledger_type");
	columns.push_back("amount");
	columns.push_back("from_system_id");
	columns.push_back("from_user_id");
	columns.push_back("event_date");
	columns.push_back("generation");
	columns.push_back("authorized");
	columns.push_back("transaction_id");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	search = "userid="+userid+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}
				
///////////////////////////////////////
// Get All breakdown related to user //
///////////////////////////////////////
const char *CceAffiliate::MyBreakdown(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("breakdown", "ce_breakdown");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mybreakdown error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mybreakdown error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("receipt_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("commrule_id");
	columns.push_back("generation");
	columns.push_back("percent");
	columns.push_back("infinitybonus");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	search = "userid="+userid+"&"+search;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////////
// Get All breakdown related to user //
///////////////////////////////////////
const char *CceAffiliate::MyBreakdownGen(int socket, int system_id, string batch_id, string parentid)
{
	CDbPlus::Setup("breakdowngen", "ce_breakdown_gen");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mybreakdowngen error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "affiliate::mybreakdowngen error", "The batchid must be a number");
	if (is_userid(parentid) == false)
		return SetError(400, "API", "affiliate::mybreakdowngen error", "The parentid can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("parent_id");
	columns.push_back("generation");
	columns.push_back("amount");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	string search = "batchid="+batch_id+"&"+"parentid="+parentid;
	string sort = "orderby=generation&orderdir=asc&offset=0&limit=10";

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////////
// Get All breakdown related to user //
///////////////////////////////////////
const char *CceAffiliate::MyBreakdownUsers(int socket, int system_id, string batch_id, string parentid, string generation)
{
	CDbPlus::Setup("breakdownusers", "ce_breakdown_users");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mybreakdownusers error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "affiliate::mybreakdownusers error", "The batchid must be a number");
	if (is_userid(parentid) == false)
		return SetError(400, "API", "affiliate::mybreakdownusers error", "The parentid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(generation) == false)
		return SetError(400, "API", "affiliate::mybreakdownusers error", "The generation needs to be a number");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("parent_id");
	columns.push_back("user_id");
	columns.push_back("generation");
	columns.push_back("amount");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	string search = "batchid="+batch_id+"&parentid="+parentid+"&generation="+generation;
	string sort = "orderby=amount&orderdir=desc&offset=0&limit=1000000";

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////////
// Get All breakdown related to user //
///////////////////////////////////////
const char *CceAffiliate::MyBreakdownOrders(int socket, int system_id, string batch_id, string parentid, string userid)
{
	CDbPlus::Setup("breakdownorders", "ce_breakdown_orders");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mybreakdownorders error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "affiliate::mybreakdownorders error", "The batchid must be a number");
	if (is_userid(parentid) == false)
		return SetError(400, "API", "affiliate::mybreakdownorders error", "The parentid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mybreakdownorders error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("parent_id");
	columns.push_back("user_id");
	columns.push_back("ordernum");
	columns.push_back("generation");
	columns.push_back("amount");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	string search = "batchid="+batch_id+"&parentid="+parentid+"&userid="+userid;
	string sort = "orderby=amount&orderdir=asc&offset=0&limit=1000000";

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////////////////////////
// Get downline information related to user //
//////////////////////////////////////////////
const char *CceAffiliate::MyDownlineLvl1(int socket, int system_id, string userid)
{
	CDbPlus::Setup("users", "ce_users");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownline error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownline error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
/*
	map <string, int> mask;

	list<string> columns;
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("firstname");
	columns.push_back("lastname");
	columns.push_back("parent_id");
	columns.push_back("usertype");
	columns.push_back("disabled");

	string search = "parentid="+userid+"&usertype=1&disabled=false";
	string sort = "orderby=user_id&orderdir=asc&offset=0&limit=100000"; // No one could ever physically click 10,000 directly beneath them //

	m_Json = CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
	m_Json = StrReplace(m_Json, "user_id", "id");
	return m_Json.c_str();
*/
	stringstream ss0;
	int count = m_pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_users WHERE system_id='" << system_id << "' AND disabled='false' AND advisor_id='" << userid << "' AND usertype='1'");

	// Grab the count data //
	stringstream ss1;
	CConn *conn;
	if ((conn = m_pDB->ExecDB(socket, ss1 << "SELECT advisor_id, count(*) FROM ce_users WHERE system_id='" << system_id << "' AND disabled='false' GROUP BY advisor_id")) == NULL)
		return SetError(503, "API", "CceAffiliate::MyDownlineLvl1 error", "Database error. Could not SELECT from database");

	map <string, int> MapCount;
	while (m_pDB->FetchRow(conn) == true)
	{
		string user_id = conn->m_RowMap[0]; 
		MapCount[user_id] = atoi(conn->m_RowMap[1].c_str());
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryLedger - ThreadReleaseConn == false");
		return SetError(503, "API", "cdb::queryledger error", "The database connection could not be released");
	}

	// Grab user data //
	stringstream ss2;
	CConn *conn2;
	if ((conn2 = m_pDB->ExecDB(socket, ss2 << "SELECT system_id, user_id, firstname, lastname, advisor_id, usertype, disabled FROM ce_users WHERE system_id=" << system_id << " AND advisor_id='" << userid << "' AND usertype='1' AND disabled=false ORDER BY firstname, lastname")) == NULL)
		return SetError(503, "API", "CceAffiliate::MyDownlineLvl1 error", "Database error. Could not SELECT from database");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << count << "\"";
	ss3 << ",\"users\":[";
	while (m_pDB->FetchRow(conn2) == true)
	{
		string user_id = conn->m_RowMap[1]; 
		ss3 << "{\"systemid\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"userid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"firstname\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"lastname\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"parentid\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"usertype\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss3 << "\"disabled\":\"" << conn->m_RowMap[6].c_str() << "\",";
		ss3 << "\"count\":\"" << MapCount[user_id] << "\"},";
	}

	string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	if (ThreadReleaseConn(conn2->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryLedger - ThreadReleaseConn == false");
		return SetError(503, "API", "cdb::queryledger error", "The database connection could not be released");
	}

	return SetJson(200, json.c_str());

}
	
//////////////////////////////
// Let user grab their info //
//////////////////////////////
const char *CceAffiliate::MyUpline(int socket, int system_id, string userid)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myupline error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::myupline error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	stringstream ss;
	if (m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << userid << "'") == 0)
		return SetError(400, "API", "affiliate::myupline error", "parent_id not found in system");

	stringstream ss1;
	string upline_parent = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT upline_parent FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << userid << "'");
	stringstream ss2;
	string upline_sponsor = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT upline_sponsor FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << userid << "'");

	//Debug(DEBUG_TRACE, "CceAffiliate::MyUpline - upline_parent", upline_parent.c_str());
	//Debug(DEBUG_TRACE, "CceAffiliate::MyUpline - upline_sponsor", upline_sponsor.c_str());
	
	// Loop through //
	//1 / 1205373 / 40//

	stringstream ss3;
	ss3 << ",\"upline\":[";
	size_t last = 0; 
	size_t next = 0; 
	while ((next = upline_parent.find(" / ", last)) != string::npos) 
	{ 
		string parent_id = upline_parent.substr(last, next-last);
		parent_id = StrReplace(parent_id, " ", "");
		parent_id = StrReplace(parent_id, "/", "");

		string up_parent_id;
		string firstname;
		string lastname;
		stringstream ssCount;
		if (m_pDB->GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'") != 0)
		{
			stringstream ss4;
			firstname = m_pDB->GetFirstCharDB(socket, ss4 << "SELECT firstname FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'");
			stringstream ss5;
			lastname = m_pDB->GetFirstCharDB(socket, ss5 << "SELECT lastname FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'");
			stringstream ss6;
			up_parent_id = m_pDB->GetFirstCharDB(socket, ss6 << "SELECT parent_id FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'");
		}

		ss3 << "{\"userid\":\"" << parent_id << "\",";
		ss3 << "\"parentid\":\"" << up_parent_id << "\",";
		ss3 << "\"firstname\":\"" << firstname << "\",";
		ss3 << "\"lastname\":\"" << lastname << "\"},";
		last = next + 1; 
	} 

	// Handle the very last one //
	string fullkey = upline_parent.substr(last);
	int length = fullkey.size();
	if (length != 0)
	{
		string parent_id = fullkey;
		parent_id = StrReplace(parent_id, " ", "");
		parent_id = StrReplace(parent_id, "/", "");
		
		string up_parent_id;
		string firstname;
		string lastname;
		stringstream ssCount;
		if (m_pDB->GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'") != 0)
		{
			stringstream ss4;
			firstname = m_pDB->GetFirstCharDB(socket, ss4 << "SELECT firstname FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'");
			stringstream ss5;
			lastname = m_pDB->GetFirstCharDB(socket, ss5 << "SELECT lastname FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'");
			stringstream ss6;
			up_parent_id = m_pDB->GetFirstCharDB(socket, ss6 << "SELECT parent_id FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << parent_id << "'");
		}

		ss3 << "{\"userid\":\"" << parent_id << "\",";
		ss3 << "\"parentid\":\"" << up_parent_id << "\",";
		ss3 << "\"firstname\":\"" << firstname << "\",";
		ss3 << "\"lastname\":\"" << lastname << "\"},";
	}
	
    m_Json = ss3.str();
    m_Json = m_Json.substr(0, m_Json.size()-1);
    m_Json += "]";
	return SetJson(200, m_Json.c_str());
}		

//////////////////////////////////
// Grab top users to close rank //
//////////////////////////////////
const char *CceAffiliate::MyTopClose(int socket, int system_id, string userid)
{
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mytopclose error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	// Grab the most recent batchid //
	stringstream ssTop;
	ssTop << "SELECT id FROM ce_batches WHERE system_id=" << system_id << " ORDER BY id DESC";
	int batchid = m_pDB->GetFirstDB(socket, ssTop);

	// Make sure there are records available //
	stringstream ssMissedCount;
	ssMissedCount << "SELECT count(*) FROM ce_rankrules_missed m LEFT JOIN ce_users u ON u.user_id=m.user_id WHERE m.system_id=" << system_id << " AND u.system_id=" << system_id << " AND m.batch_id=" << batchid << " AND m.user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE  '% " << userid << " %')";
	if ((m_pDB->GetFirstDB(socket, ssMissedCount)) == 0)
		return SetError(503, "API", "ceaffiliate::mytopclose", "There are no records available");

	CConn *conn;
	stringstream ssMissed;
	ssMissed << "SELECT m.id, m.user_id, m.rule_id, m.rank, m.qualify_type, m.qualify_threshold, m.actual_value, m.diff, u.firstname, u.lastname, u.email FROM ce_rankrules_missed m LEFT JOIN ce_users u ON u.user_id=m.user_id WHERE m.system_id=" << system_id << " AND u.system_id=" << system_id << " AND m.batch_id=" << batchid << " AND m.user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE  '% " << userid << " %') ORDER BY m.diff ASC LIMIT 10";
	if ((conn = m_pDB->ExecDB(socket, ssMissed)) == NULL)
		return SetError(503, "API", "ceaffiliate::mytopclose", "There was an internal error that prevented a SELECT in the database");

	stringstream ssJson;
	ssJson << ",\"rankmissed\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ssJson << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ssJson << "\"userid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ssJson << "\"ruleid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ssJson << "\"rank\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ssJson << "\"qualifytype\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ssJson << "\"qualifythreshold\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ssJson << "\"actualvalue\":\"" << conn->m_RowMap[6].c_str() << "\",";
		ssJson << "\"diff\":\"" << conn->m_RowMap[7].c_str() << "\",";
		ssJson << "\"firstname\":\"" << conn->m_RowMap[8].c_str() << "\",";
		ssJson << "\"lastname\":\"" << conn->m_RowMap[9].c_str() << "\",";
		ssJson << "\"email\":\"" << conn->m_RowMap[10].c_str() << "\"},";
	}
	string json;
    json = ssJson.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyTopClose - ThreadReleaseConn == false");
		return SetError(503, "API", "cceaffiliate::mytopclose error", "The database connection could not be released");
	}

	return SetJson(200, json.c_str());
}

/////////////////////////
// My RankRules Missed //
/////////////////////////
const char *CceAffiliate::MyRankRulesMissed(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("rankrulesmissed", "ce_rankrules_missed");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myrankrulesmissed error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::myrankrulesmissed error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	// Handle pagination values //
	if (search.length() != 0)
	{
		if (is_qstring(search) == false)
			return SetError(400, "API", "rankrulesmissed::myrankrulesmissed error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}
	if (sort.length() != 0) 
	{
		if (is_qstring(sort) == false)
			return SetError(400, "API", "rankrulesmissed::myrankrulesmissed error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("rule_id");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("actual_value");
	columns.push_back("diff");
	columns.push_back("created_at");

	search = search+"&userid="+userid;

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////
// Get Users Statistics //
//////////////////////////
const char *CceAffiliate::MyStats(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("userstats", "ce_userstats_month");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mystats error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mystats error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	//columns.push_back("group_sales"); group_wholesale_sales
	columns.push_back("team_and_my_wholesale");
	columns.push_back("group_wholesale_sales");
	columns.push_back("group_used");
	//columns.push_back("customer_sales");
	columns.push_back("customer_wholesale_sales");
	//columns.push_back("affiliate_sales"); customer_wholesale_sales
	columns.push_back("affiliate_wholesale_sales");
	columns.push_back("reseller_wholesale_sales");
	columns.push_back("signup_count");
	columns.push_back("affiliate_count");
	columns.push_back("customer_count");
	columns.push_back("reseller_count");
	columns.push_back("team_wholesale_sales");
	columns.push_back("team_retail_sales");

	columns.push_back("group_retail_sales");
	columns.push_back("item_count_retail");
	columns.push_back("item_count_retail_ev");

	columns.push_back("item_count_wholesale");
	columns.push_back("item_count_wholesale_ev");

	columns.push_back("affiliate_retail_sales");
	columns.push_back("team_wholesale_sales");

	columns.push_back("corp_wholesale_price");
	columns.push_back("corp_retail_price");

	columns.push_back("created_at");
	columns.push_back("updated_at");

	search = "userid="+userid+"&"+search;

	
	//SELECT id, system_id, batch_id, user_id, personal_sales, signup_count, customer_count, affiliate_count, created_at, updated_at FROM ce_userstats_month_lvl1 WHERE system_id='1' AND user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE '% 3 %') ORDER BY id desc OFFSET 0 LIMIT 10;

	//WHERE system_id='1' AND user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE '% 3 %');
	

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////
// Display my statistics level 1 //
///////////////////////////////////	
const char *CceAffiliate::MyStatsLvl1(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("userstatslvl1", "ce_userstats_month_lvl1");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mystatslvl1 error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mystatslvl1 error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("personal_sales");
	columns.push_back("my_wholesale_sales");
	columns.push_back("my_retail_sales");
	columns.push_back("reseller_count");
	columns.push_back("signup_count");
	columns.push_back("customer_count");
	columns.push_back("affiliate_count");
	columns.push_back("reseller_count");
	columns.push_back("psq");

	columns.push_back("created_at");
	columns.push_back("updated_at");

	search = "userid="+userid+"&"+search;
	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);

	//stringstream searchraw;
	//searchraw << " WHERE t.system_id='" << system_id << "' AND t.user_id='" << userid << "' ";
	//return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);

	//return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////////////////////////////
// Be able to query rank sum of defined rank lvl1 //
////////////////////////////////////////////////////
const char *CceAffiliate::MyDownlineRankSumLvl1(int socket, int system_id, string batch_id, string userid)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::MyDownlineRankSumLvl1 error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::MyDownlineRankSumLvl1 error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "affiliate::MyDownlineRankSumLvl1 error", "The batchid must be a number");
	
	stringstream ss0;
	int count = m_pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_userstats_month_lvl1_rank WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id='" << userid << "'");

	CConn *conn;
	stringstream ss1;
	if ((conn = m_pDB->ExecDB(socket, ss1 << "SELECT rank, total FROM ce_userstats_month_lvl1_rank WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id='" << userid << "'")) == NULL)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownlineRankSumLvl1 - Problem with SELECT");
		return SetError(501, "API", "affiliate::MyDownlineRankSumLvl1 error", "There was a problem with the database");
	}

	std::stringstream ssJson;
	ssJson << ",\"count\":\"" << count << "\"";
	ssJson << ",\"ranksumlvl1\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ssJson << "{\"rank\":\"" << conn->m_RowMap[0] << "\",";
		ssJson << "\"total\":\"" << conn->m_RowMap[1] << "\"},";
	}
		
	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownlineRankSumLvl1 - ThreadReleaseConn == false");
		return SetError(503, "API", "affiliate::MyDownlineRankSumLvl1 error", "Could not release the database connection");
	}

	stringstream json;
    json << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
    json << "]";
	m_Json = SetJson(200, json.str().c_str());
	return m_Json.c_str();

	//stringstream ss1;
	//ss1 << "SELECT count(*) FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND rank='" << rank << "' AND user_id IN (SELECT user_id FROM ce_users WHERE parent_id='" << userid << "')";
	//int ranksum = m_pDB->GetFirstDB(socket, ss1);

	// Send back hash in Json format //
    //stringstream ss2;
    //m_Json = SetJson(200, ss2 << ",\"ranksumlvl1\":{\"total\":\"" << ranksum << "\"}");
    //return m_Json.c_str();
}

/////////////////////////////////////////////////////////////
// Be able to query rank sum of defined rank full downline //
/////////////////////////////////////////////////////////////
const char *CceAffiliate::MyDownlineRankSum(int socket, int system_id, string batch_id, string userid, string generation)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::MyDownlineRankSum error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::MyDownlineRankSum error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "affiliate::MyDownlineRankSum error", "The batchid must be a number");
	if (is_number(generation) == false)
		return SetError(400, "API", "affiliate::MyDownlineRankSum error", "The generation must be a number");
	if ((atoi(generation.c_str()) <= 0) || (atoi(generation.c_str()) >= 4))
		return SetError(400, "API", "affiliate::MyDownlineRankSum error", "The generation must be a 1-3");

	string tablename;
	tablename = "ce_userstats_month_leg_rank";
	//if (generation == "1")
	//	tablename = "ce_userstats_month_leg_rank_gen1";
	//if (generation == "2")
	//	tablename = "ce_userstats_month_leg_rank_gen2";
	//if (generation == "3")
	//	tablename = "ce_userstats_month_leg_rank_gen3";

	////////////////////////////////////////////////////
	// We need to do a count(*) of records here first //
	// A SQL error can ocassionally happen if we don't //
	// I'd fix now, but currently working on something more important //
	// Come back to this when I have more time //
	/////////////////////////////////////////////

	stringstream ss0;
	int count = m_pDB->GetFirstDB(socket, ss0 << "SELECT DISTINCT rank FROM " << tablename << " WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id='" << userid << "' AND generation='" << generation << "' ORDER BY rank DESC");

	CConn *conn;
	stringstream ss1;
	if ((conn = m_pDB->ExecDB(socket, ss1 << "SELECT rank, SUM(total), array_to_string(array_agg(userdata), ', ') FROM " << tablename << " WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id='" << userid << "' AND total!=0  AND generation='" << generation << "' GROUP BY rank ORDER BY rank")) == NULL)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownlineRankSum - Problem with SELECT");
		return SetError(501, "API", "affiliate::MyDownlineRankSum error", "There was a problem with the database");
	}	

	std::stringstream ssJson;
	ssJson << ",\"count\":\"" << count << "\"";
	ssJson << ",\"ranksum\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ssJson << "{\"rank\":\"" << conn->m_RowMap[0] << "\",";
		ssJson << "\"total\":\"" << conn->m_RowMap[1] << "\",";
		ssJson << "\"userdata\":\"" << conn->m_RowMap[2] << "\"},";
	}
		
	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownlineRankSum - ThreadReleaseConn == false");
		return SetError(503, "API", "affiliate::MyDownlineRankSum error", "Could not release the database connection");
	}

	stringstream json;
    json << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
    json << "]";
	m_Json = SetJson(200, json.str().c_str());
	return m_Json.c_str();

	//stringstream ss1;
	//ss1 << "SELECT count(*) FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND rank='" << rank << "' AND user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE '% " << userid << " %')";
	//int ranksum = m_pDB->GetFirstDB(socket, ss1);

	// Send back hash in Json format //
    //stringstream ss2;
    //return SetJson(200, ss2 << ",\"ranksum\":{\"total\":\"" << ranksum << "\"}");
}

///////////////////////////////////////////////////////
// Get Current Title and Carrer Title. Rank included //
///////////////////////////////////////////////////////
const char *CceAffiliate::MyTitle(int socket, int system_id, string batch_id, string userid)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::MyTitle error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::MyTitle error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "affiliate::MyTitle error", "The batchid must be a number");

	stringstream ss1;
	ss1 << "SELECT rank FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id='" << userid << "'";
	int current_rank = m_pDB->GetFirstDB(socket, ss1);

	string current_title;
	stringstream ss1b;
	if (m_pDB->GetFirstDB(socket, ss1b << "SELECT count(*) FROM ce_rankrules WHERE rank='" << current_rank << "' AND system_id=" << system_id) != 0)
	{
		stringstream ss2;
		current_title = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT label FROM ce_rankrules WHERE rank='" << current_rank << "' AND system_id=" << system_id);
	}
	else
	{
		current_title = "Unranked";
	}

	stringstream ss3;
	ss3 << "SELECT rank FROM ce_ranks WHERE system_id=" << system_id << " AND user_id='" << userid << "' ORDER BY rank DESC";
	int carrer_rank = m_pDB->GetFirstDB(socket, ss3);

	stringstream ss4;
	string carrer_title = m_pDB->GetFirstCharDB(socket, ss4 << "SELECT label FROM ce_rankrules WHERE rank='" << carrer_rank << "' AND system_id=" << system_id);

	// Send back hash in Json format //
    stringstream ss5;
    return SetJson(200, ss5 << ",\"mytitle\":{\"currentrank\":\"" << current_rank << "\", \"currenttitle\":\"" << current_title << "\", \"carrerrank\":\"" << carrer_rank << "\", \"carrertitle\":\"" << carrer_title << "\"}");
}

///////////////////////////////////
// Get Users Downline statistics //
///////////////////////////////////
const char *CceAffiliate::MyDownLineStats(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("userstats", "ce_userstats_month");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownstats error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownstats error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("team_and_my_wholesale");
	columns.push_back("group_wholesale_sales");
	columns.push_back("group_used");
	columns.push_back("customer_wholesale_sales");
	columns.push_back("affiliate_wholesale_sales");
	columns.push_back("reseller_wholesale_sales");
	columns.push_back("signup_count");
	columns.push_back("affiliate_count");
	columns.push_back("customer_count");
	columns.push_back("reseller_count");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	stringstream searchraw;
	searchraw << " WHERE t.system_id='" << system_id << "' AND t.user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE '% " << userid << " %' AND system_id='" << system_id << "') ";
	if (search.size() != 0)
	{
		string retval = CDbPlus::PagBuildSearch(0, 0, columns, search);
		retval = StrReplace(retval, " WHERE ", " ");
		searchraw << " AND " << retval << " "; 
	}

	return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

///////////////////////////////////
// Get Users Downline stats lvl1 //
///////////////////////////////////
const char *CceAffiliate::MyDownLineStatsLvl1(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("userstatslvl1", "ce_userstats_month_lvl1");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownlinestatslvl1 error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownlinestatslvl1 error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("personal_sales");
	columns.push_back("signup_count");
	columns.push_back("customer_count");
	columns.push_back("affiliate_count");
	columns.push_back("reseller_count");
	columns.push_back("my_wholesale_sales");
	columns.push_back("my_retail_sales");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	stringstream searchraw;
	searchraw << " WHERE t.system_id='" << system_id << "' AND t.user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE '% " << userid << " %' AND system_id='" << system_id << "') ";
	if (search.size() != 0)
	{
		string retval = CDbPlus::PagBuildSearch(0, 0, columns, search);
		retval = StrReplace(retval, " WHERE ", " ");
		searchraw << " AND " << retval << " "; 
	}

	return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

////////////////////////////////////////////////////////////
// Handle phasing out the old way of doing downline stats //
////////////////////////////////////////////////////////////
const char *CceAffiliate::MyDownLineStatsFull(int socket, int system_id, string userid, string batchid, string search, string sort)
{
	if ((m_pDB->m_pSettings->m_DatabaseName == "chalk-live") || (m_pDB->m_pSettings->m_DatabaseName == "chalk-sim1") || (m_pDB->m_pSettings->m_DatabaseName == "chalk-sim2"))
	{
		if (atoi(batchid.c_str()) <= 43)
			return MyDownLineStatsFullOld(socket, system_id, userid, batchid, search, sort);
		else
			return MyDownLineStatsFullNew(socket, system_id, userid, batchid, search, sort);
	}

	return MyDownLineStatsFullNew(socket, system_id, userid, batchid, search, sort);
}

///////////////////////////////////
// Get Users Downline stats lvl1 //
///////////////////////////////////
const char *CceAffiliate::MyDownLineStatsFullNew(int socket, int system_id, string userid, string batchid, string search, string sort)
{
	CDbPlus::Setup("userstatslvl1", "ce_userstats_month_lvl1");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownlinestatsfull error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(batchid) == false)
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "The batchid is not numeric");

	//////////////////////////////////////////////////////////////////////////////////////////////
	// Prevent SQL deadlock on mega query below resulting from empty records on commission runs //
	//////////////////////////////////////////////////////////////////////////////////////////////
/*	stringstream ssSQL1;
	if (m_pDB->GetFirstDB(socket, ssSQL1 << "SELECT count(*) FROM ce_ranks WHERE system_id='" << system_id << "' AND batch_id='" << batchid << "' AND user_id='" << userid << "'") == 0)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - Ranks empty set");
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "Commission Run. Potential SQL deadlock. Ranks empty set");
	}
	stringstream ssSQL2;
	if (m_pDB->GetFirstDB(socket, ssSQL2 << "SELECT count(*) FROM ce_userstats_month_lvl1_rank WHERE (rank='4' OR rank='5' OR rank='6' OR rank='7' OR rank='8' OR rank='9') AND system_id='" << system_id << "' AND batch_id='" << batchid << "' AND user_id='" << userid << "'") != 6)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - Stats Ranks(4) empty set");
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "Commission Run. Potential SQL deadlock. Stats Ranks(4) empty set");
	}

	stringstream ssSQL3;
	if (m_pDB->GetFirstDB(socket, ssSQL3 << "SELECT count(*) FROM ce_userstats_month_leg_rank WHERE (rank='6' OR rank='7' OR rank='8' OR rank='9') AND system_id='" << system_id << "' AND batch_id='" << batchid << "' AND user_id='" << userid << "' AND generation='1'") < 4)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - Leg Ranks empty set user_id", userid);
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "Commission Run. Potential SQL deadlock. Leg Ranks empty set");
	}
*/
	// Grab the rankrules //
	list <CRulesRank> RulesRankList;
	m_pDB->GetRankRules(socket, system_id, &RulesRankList, "ce_rankrules");

	map <string, int> mask;

	list<string> columns;

	// Stats LVL //
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("personal_sales");
	columns.push_back("signup_count");
	columns.push_back("customer_count");
	columns.push_back("affiliate_count");
	columns.push_back("reseller_count");
	columns.push_back("my_wholesale_sales");
	columns.push_back("my_retail_sales");
	columns.push_back("psq");

	// Stats month //
	columns.push_back("group_wholesale_sales");
	columns.push_back("group_used");
	columns.push_back("customer_wholesale_sales");
	columns.push_back("affiliate_wholesale_sales");
	columns.push_back("signup_count");
	columns.push_back("affiliate_count");
	columns.push_back("customer_count");
	columns.push_back("group_retail_sales");
	columns.push_back("customer_retail_sales");
	columns.push_back("affiliate_retail_sales");
	columns.push_back("reseller_wholesale_sales");
	columns.push_back("reseller_retail_sales");
	columns.push_back("reseller_count");
	columns.push_back("team_wholesale_sales");
	columns.push_back("team_retail_sales");
	columns.push_back("group_and_my_wholesale");
	columns.push_back("team_and_my_wholesale");

	// Users //
	columns.push_back("ufirstname");
	columns.push_back("ulastname");
	columns.push_back("uparent_id");
	columns.push_back("usponsor_id");
	columns.push_back("usignup_date");
	columns.push_back("uupline_parent");
	columns.push_back("uupline_sponsor");
	columns.push_back("uupline_advisor");
	columns.push_back("uemail");
	columns.push_back("ucell");
	columns.push_back("ucarrerrank");
	columns.push_back("udate_last_earned");
	//columns.push_back("address");
	//columns.push_back("city");
	//columns.push_back("state");
	//columns.push_back("zip");

	// Parent //
	columns.push_back("puser_id");
	columns.push_back("pfirstname");
	columns.push_back("plastname");
	columns.push_back("pemail");
	columns.push_back("pcell");

	// Sponsor //
	columns.push_back("suser_id");
	columns.push_back("sfirstname");
	columns.push_back("slastname");
	columns.push_back("semail");
	columns.push_back("scell");

	// Advisor //
	columns.push_back("auser_id");
	columns.push_back("afirstname");
	columns.push_back("alastname");
	columns.push_back("aemail");
	columns.push_back("acell");

	columns.push_back("rank");

	// ce_userstats_month_lvl1_rank //
	columns.push_back("lvl1-4");
	columns.push_back("lvl1-5");
	columns.push_back("lvl1-6");
	columns.push_back("lvl1-7");
	columns.push_back("lvl1-8");
	columns.push_back("lvl1-9");

	// ce_userstats_month_leg_rank
	columns.push_back("leg-6");
	columns.push_back("leg-7");
	columns.push_back("leg-8");
	columns.push_back("leg-9");

	columns.push_back("level");

	// Additional Stats: Maverick //
	columns.push_back("item_count_wholesale");
	columns.push_back("item_count_retail");
	columns.push_back("unique_users_receipts");
	columns.push_back("item_count_wholesale_ev");
	columns.push_back("item_count_retail_ev");
	columns.push_back("corp_wholesale_price");
	columns.push_back("corp_retail_price");
	
	string searchsql;
	if (search.size() != 0)
	{
		search = StrReplace(search, "*", "%");
		searchsql = CDbPlus::PagBuildSearch(0, 0, columns, search);
		//searchsql = StrReplace(searchsql, "system_id", "l.system_id");
		//searchsql = StrReplace(searchsql, "batch_id", "l.batch_id");

		Debug(DEBUG_WARN, "searchsql", searchsql.c_str());

		searchsql = StrReplace(searchsql, "ufirstname", "u.firstname");
		searchsql = StrReplace(searchsql, "afirstname", "a.firstname");
		searchsql = StrReplace(searchsql, "pfirstname", "p.firstname");
		searchsql = StrReplace(searchsql, "sfirstname", "s.firstname");
		searchsql = StrReplace(searchsql, "user_id", "l.user_id");
		searchsql = StrReplace(searchsql, "level", "array_length(string_to_array(a.upline_advisor, '/'), 1)");

		searchsql = StrReplace(searchsql, " WHERE ", " AND ");
	}

	sort = StrReplace(sort, "ufirstname", "u.firstname");
	sort = StrReplace(sort, "afirstname", "a.firstname");
	sort = StrReplace(sort, "pfirstname", "p.firstname");
	sort = StrReplace(sort, "sfirstname", "s.firstname");
	sort = StrReplace(sort, "careertitle", "u.carrer_rank");
	sort = StrReplace(sort, "currenttitle", "r.rank");

	sort = StrReplace(sort, "usignupdate", "u.signup_date");
	sort = StrReplace(sort, "ucell", "u.cell");
	sort = StrReplace(sort, "uemail", "u.email");

	// Do sorting by pre data //
	sort = StrReplace(sort, "teamwholesalesales", "m.team_wholesale_sales");
	//sort = StrReplace(sort, "level1mentors", "v4.total");
	sort = StrReplace(sort, "level1mentors", "lvl1_rank_4");
	//sort = StrReplace(sort, "mastermentorlegs", "leg6.total");
	sort = StrReplace(sort, "mastermentorlegs", "gen1_rank_6");
	//sort = StrReplace(sort, "executivecourtieurlegs", "leg8.total");
	sort = StrReplace(sort, "executivecouturierlegs", "gen1_rank_8");
	//sort = StrReplace(sort, "mastercouturierlegs", "leg9.total");
	sort = StrReplace(sort, "mastercouturierlegs", "gen1_rank_9");
	//sort = StrReplace(sort, "courtieurlegs", "leg7.total");
	sort = StrReplace(sort, "couturierlegs", "gen1_rank_7");
	
	
	string sqlend = BuildSQLEnd(sort);	
	sqlend = StrReplace(sqlend, "user_id", "l.user_id");

	// Handle sorting by level //
	sqlend = StrReplace(sqlend, "level", "array_length(string_to_array(a.upline_advisor, '/'), 1)-1");
	sqlend = StrReplace(sqlend, "group_wholesale_sales", "m.group_wholesale_sales+l.my_wholesale_sales");
	sqlend = StrReplace(sqlend, "datelastearned", "u.date_last_earned");

	//stringstream ssSqlEnd;
	//ssSqlEnd << searchsql << sqlend;

	// Build the SQL //
	stringstream ssBody;
	ssBody << " FROM ce_userstats_month_lvl1 l INNER JOIN ce_userstats_month m ON l.user_id=m.user_id ";
	ssBody << " INNER JOIN ce_users u ON u.user_id=l.user_id ";
	ssBody << " INNER JOIN ce_ranks r ON r.user_id=l.user_id ";
	ssBody << " INNER JOIN ce_users p ON p.user_id=u.parent_id ";
	ssBody << " INNER JOIN ce_users s ON s.user_id=u.sponsor_id ";
	ssBody << " INNER JOIN ce_users a ON a.user_id=u.advisor_id ";

	ssBody << " INNER JOIN ce_pre_legrankgen pre ON pre.user_id=u.user_id ";
	/*
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v4 ON v4.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v5 ON v5.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v6 ON v6.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v7 ON v7.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v8 ON v8.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v9 ON v9.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg6 ON leg6.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg7 ON leg7.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg8 ON leg8.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg9 ON leg9.user_id=u.user_id ";
	*/
	ssBody << " WHERE u.disabled='false' AND l.batch_id=" << batchid << " AND m.batch_id=" << batchid << " AND r.batch_id=" << batchid;
//	ssBody << " AND v4.batch_id=" << batchid << " AND v5.batch_id=" << batchid << " AND v6.batch_id=" << batchid << " AND v7.batch_id=" << batchid << " AND v8.batch_id=" << batchid << " AND v9.batch_id=" << batchid;
//	ssBody << " AND leg6.batch_id=" << batchid << " AND leg7.batch_id=" << batchid << " AND leg8.batch_id=" << batchid << " AND leg9.batch_id=" << batchid;
	ssBody << " AND pre.batch_id=" << batchid;

	ssBody << " AND l.system_id=" << system_id << " AND m.system_id=" << system_id << " AND u.system_id=" << system_id << " AND u.usertype!='2'";
	ssBody << " AND r.system_id=" << system_id << " AND p.system_id=" << system_id << " AND s.system_id=" << system_id << " AND a.system_id=" << system_id << " AND a.usertype!='2'";
//	ssBody << " AND v4.system_id=" << system_id << " AND v5.system_id=" << system_id << " AND v6.system_id=" << system_id << " AND v7.system_id=" << system_id << " AND v8.system_id=" << system_id << " AND v9.system_id=" << system_id;
//	ssBody << " AND leg6.system_id=" << system_id << " AND leg7.system_id=" << system_id << " AND leg8.system_id=" << system_id << " AND leg9.system_id=" << system_id;
	ssBody << " AND pre.system_id=" << system_id;

	ssBody << " AND l.user_id IN (SELECT user_id FROM ce_users WHERE upline_advisor LIKE '% " << userid << " %' AND system_id='" << system_id << "' AND usertype!='2') ";

/*	ssBody << " AND v4.rank='4' ";
	ssBody << " AND v5.rank='5' ";
	ssBody << " AND v6.rank='6' ";
	ssBody << " AND v7.rank='7' ";
	ssBody << " AND v8.rank='8' ";
	ssBody << " AND v9.rank='9' ";
	ssBody << " AND leg6.rank='6' ";
	ssBody << " AND leg7.rank='7' ";
	ssBody << " AND leg8.rank='8' ";
	ssBody << " AND leg9.rank='9' ";
	ssBody << " AND leg6.generation='1' ";
	ssBody << " AND leg7.generation='1' ";
	ssBody << " AND leg8.generation='1' ";
	ssBody << " AND leg9.generation='1' ";
*/
	int testcount = m_pDB->GetFirstDB(socket, "SELECT count(*) FROM ce_ranks");
	Debug(DEBUG_TRACE, "CceAffiliate::MyDownLineStatsFull - testcount", testcount);

	stringstream ssCount;
	ssCount << "SELECT count(*)";
	ssCount << ssBody.str() << searchsql;
	int count = m_pDB->GetFirstDB(socket, ssCount);
	if (count == 0)
	{
		return SetError(200, "API", "Affiliate::MyDownLineStatsFull", "No records found");
	}

	stringstream ssColumns;
	ssColumns << " l.id, l.system_id, l.batch_id, l.user_id, l.personal_sales, l.signup_count, l.customer_count, l.affiliate_count, l.reseller_count, l.my_wholesale_sales, l.my_retail_sales, l.psq, ";
	ssColumns << " m.group_wholesale_sales+l.my_wholesale_sales, m.group_used, m.customer_wholesale_sales, m.affiliate_wholesale_sales, m.signup_count, m.affiliate_count, m.customer_count, m.group_retail_sales, m.customer_retail_sales, m.affiliate_retail_sales, m.reseller_wholesale_sales, m.reseller_retail_sales, m.reseller_count, m.team_wholesale_sales, m.team_retail_sales, m.group_and_my_wholesale, m.team_and_my_wholesale, ";
	ssColumns << " u.firstname, u.lastname, u.parent_id, u.sponsor_id, u.signup_date, u.upline_parent, u.upline_sponsor, u.upline_advisor, u.email, u.cell, u.carrer_rank, u.date_last_earned, ";
	ssColumns << " p.user_id, p.firstname, p.lastname, p.email, p.cell, ";
	ssColumns << " s.user_id, s.firstname, s.lastname, s.email, s.cell, ";
	ssColumns << " a.user_id, a.firstname, a.lastname, a.email, a.cell, ";
	ssColumns << " r.rank, ";
//	ssColumns << " v4.total, v5.total, v6.total, v7.total, v8.total, v9.total, ";
	ssColumns << " lvl1_rank_4, lvl1_rank_5, lvl1_rank_6, lvl1_rank_7, lvl1_rank_8, lvl1_rank_9, ";
//	ssColumns << " leg6.total, leg7.total, leg8.total, leg9.total, ";
	ssColumns << " gen1_rank_6, gen1_rank_7, gen1_rank_8, gen1_rank_9, ";

	ssColumns << " array_length(string_to_array(a.upline_advisor, '/'), 1)-1 ";
	
	stringstream ssRecords;
	ssRecords << "SELECT DISTINCT " << ssColumns.str() << ssBody.str() << searchsql << sqlend;

	// Run the SQL and build json //
	CConn *conn;
	if ((conn = m_pDB->ExecDB(socket, ssRecords)) == NULL)
	{
		Debug(DEBUG_ERROR, "Affiliate::MyDownLineStatsFull", "There was an internal error that prevented a SELECT from the database");
		return SetError(503, "API", "Affiliate::MyDownLineStatsFull", "There was an internal database error");
	}

	std::stringstream ssJson;
	ssJson << ",\"count\":\"" << count << "\"";
	ssJson << ",\"userstatsfull\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ssJson << "{";
		int index = 0;
		std::list<string>::iterator i;
		for (i=columns.begin(); i != columns.end(); ++i)
		{
			if (index !=0)
				ssJson << ",";
			string second = conn->m_RowMap[index];

			//Debug(DEBUG_ERROR ,"second=", second);
			//Debug(DEBUG_ERROR ,"second=", second);
			//string tmpval = (*i)+" == "+second;
			//Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull", tmpval);

			if ((*i) == "rank")
			{
				second = m_pDB->MemLookupTitle(atoi(second.c_str()), &RulesRankList);
				ssJson << "\"currenttitle\":\"" << second << "\"";
			}
			else if ((*i) == "ucarrerrank")
			{
				second = m_pDB->MemLookupTitle(atoi(second.c_str()), &RulesRankList);
				ssJson << "\"carrertitle\":\"" << second << "\"";
			}
			else
			{
				// Write out json //
				ssJson << "\""<< RemoveUnderScore(*i) << "\":\"" << second << "\"";
			}
			index++;
		}

		ssJson << "},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - ThreadReleaseConn == false");
		return SetError(503, "API", "affiliate::mydownlinestatsfull error", "Could not release the database connection");
	}

	stringstream json;
    json << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
    json << "]";

	m_Json = SetJson(200, json.str().c_str());
	return m_Json.c_str();

	//return SetError(503, "API", "CceAffiliate::MyDownLineStatsFull error", "See Debug code on server");
	//return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

/////////////////////////////////////////////
// Get Users Downline stats lvl1 - OLD!!!! //
/////////////////////////////////////////////
const char *CceAffiliate::MyDownLineStatsFullOld(int socket, int system_id, string userid, string batchid, string search, string sort)
{
	CDbPlus::Setup("userstatslvl1", "ce_userstats_month_lvl1");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownlinestatsfull error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(batchid) == false)
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "The batchid is not numeric");

	//////////////////////////////////////////////////////////////////////////////////////////////
	// Prevent SQL deadlock on mega query below resulting from empty records on commission runs //
	//////////////////////////////////////////////////////////////////////////////////////////////
	stringstream ssSQL1;
	if (m_pDB->GetFirstDB(socket, ssSQL1 << "SELECT count(*) FROM ce_ranks WHERE system_id='" << system_id << "' AND batch_id='" << batchid << "' AND user_id='" << userid << "'") == 0)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - Ranks empty set");
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "Commission Run. Potential SQL deadlock. Ranks empty set");
	}
	stringstream ssSQL2;
	if (m_pDB->GetFirstDB(socket, ssSQL2 << "SELECT count(*) FROM ce_userstats_month_lvl1_rank WHERE (rank='4' OR rank='5' OR rank='6' OR rank='7' OR rank='8' OR rank='9') AND system_id='" << system_id << "' AND batch_id='" << batchid << "' AND user_id='" << userid << "'") != 6)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - Stats Ranks(4) empty set");
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "Commission Run. Potential SQL deadlock. Stats Ranks(4) empty set");
	}

	stringstream ssSQL3;
	if (m_pDB->GetFirstDB(socket, ssSQL3 << "SELECT count(*) FROM ce_userstats_month_leg_rank WHERE (rank='6' OR rank='7' OR rank='8' OR rank='9') AND system_id='" << system_id << "' AND batch_id='" << batchid << "' AND user_id='" << userid << "' AND generation='1'") < 4)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - Leg Ranks empty set user_id", userid);
		return SetError(400, "API", "affiliate::mydownlinestatsfull error", "Commission Run. Potential SQL deadlock. Leg Ranks empty set");
	}

	// Grab the rankrules //
	list <CRulesRank> RulesRankList;
	m_pDB->GetRankRules(socket, system_id, &RulesRankList, "ce_rankrules");

	map <string, int> mask;

	list<string> columns;

	// Stats LVL //
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("personal_sales");
	columns.push_back("signup_count");
	columns.push_back("customer_count");
	columns.push_back("affiliate_count");
	columns.push_back("reseller_count");
	columns.push_back("my_wholesale_sales");
	columns.push_back("my_retail_sales");
	columns.push_back("psq");

	// Stats month //
	columns.push_back("group_wholesale_sales");
	columns.push_back("group_used");
	columns.push_back("customer_wholesale_sales");
	columns.push_back("affiliate_wholesale_sales");
	columns.push_back("signup_count");
	columns.push_back("affiliate_count");
	columns.push_back("customer_count");
	columns.push_back("group_retail_sales");
	columns.push_back("customer_retail_sales");
	columns.push_back("affiliate_retail_sales");
	columns.push_back("reseller_wholesale_sales");
	columns.push_back("reseller_retail_sales");
	columns.push_back("reseller_count");
	columns.push_back("team_wholesale_sales");
	columns.push_back("team_retail_sales");
	columns.push_back("group_and_my_wholesale");
	columns.push_back("team_and_my_wholesale");

	// Users //
	columns.push_back("ufirstname");
	columns.push_back("ulastname");
	columns.push_back("uparent_id");
	columns.push_back("usponsor_id");
	columns.push_back("usignup_date");
	columns.push_back("uupline_parent");
	columns.push_back("uupline_sponsor");
	columns.push_back("uupline_advisor");
	columns.push_back("uemail");
	columns.push_back("ucell");
	columns.push_back("ucarrerrank");
	columns.push_back("udate_last_earned");
	//columns.push_back("address");
	//columns.push_back("city");
	//columns.push_back("state");
	//columns.push_back("zip");

	// Parent //
	columns.push_back("puser_id");
	columns.push_back("pfirstname");
	columns.push_back("plastname");
	columns.push_back("pemail");
	columns.push_back("pcell");

	// Sponsor //
	columns.push_back("suser_id");
	columns.push_back("sfirstname");
	columns.push_back("slastname");
	columns.push_back("semail");
	columns.push_back("scell");

	// Advisor //
	columns.push_back("auser_id");
	columns.push_back("afirstname");
	columns.push_back("alastname");
	columns.push_back("aemail");
	columns.push_back("acell");

	columns.push_back("rank");

	// ce_userstats_month_lvl1_rank //
	columns.push_back("lvl1-4");
	columns.push_back("lvl1-5");
	columns.push_back("lvl1-6");
	columns.push_back("lvl1-7");
	columns.push_back("lvl1-8");
	columns.push_back("lvl1-9");

	// ce_userstats_month_leg_rank
	columns.push_back("leg-6");
	columns.push_back("leg-7");
	columns.push_back("leg-8");
	columns.push_back("leg-9");

	columns.push_back("level");
	
	string searchsql;
	if (search.size() != 0)
	{
		search = StrReplace(search, "*", "%");
		searchsql = CDbPlus::PagBuildSearch(0, 0, columns, search);
		//searchsql = StrReplace(searchsql, "system_id", "l.system_id");
		//searchsql = StrReplace(searchsql, "batch_id", "l.batch_id");

		Debug(DEBUG_TRACE, "searchsql", searchsql.c_str());

		searchsql = StrReplace(searchsql, "ufirstname", "u.firstname");
		searchsql = StrReplace(searchsql, "afirstname", "a.firstname");
		searchsql = StrReplace(searchsql, "pfirstname", "p.firstname");
		searchsql = StrReplace(searchsql, "sfirstname", "s.firstname");
		searchsql = StrReplace(searchsql, "user_id", "l.user_id");
		searchsql = StrReplace(searchsql, "level", "array_length(string_to_array(a.upline_advisor, '/'), 1)");

		searchsql = StrReplace(searchsql, " WHERE ", " AND ");
	}

	sort = StrReplace(sort, "ufirstname", "u.firstname");
	sort = StrReplace(sort, "afirstname", "a.firstname");
	sort = StrReplace(sort, "pfirstname", "p.firstname");
	sort = StrReplace(sort, "sfirstname", "s.firstname");
	sort = StrReplace(sort, "careertitle", "u.carrer_rank");
	sort = StrReplace(sort, "currenttitle", "r.rank");

	sort = StrReplace(sort, "teamwholesalesales", "m.team_wholesale_sales");
	sort = StrReplace(sort, "level1mentors", "v4.total");
	sort = StrReplace(sort, "mastermentorlegs", "leg6.total");
	sort = StrReplace(sort, "executivecourtieurlegs", "leg8.total");
	sort = StrReplace(sort, "mastercouturierlegs", "leg9.total");
	sort = StrReplace(sort, "courtieurlegs", "leg7.total");
	
	string sqlend = BuildSQLEnd(sort);	
	sqlend = StrReplace(sqlend, "user_id", "l.user_id");

	// Handle sorting by level //
	sqlend = StrReplace(sqlend, "level", "array_length(string_to_array(a.upline_advisor, '/'), 1)-1");
	sqlend = StrReplace(sqlend, "group_wholesale_sales", "m.group_wholesale_sales+l.my_wholesale_sales");
	sqlend = StrReplace(sqlend, "datelastearned", "u.date_last_earned");

	//stringstream ssSqlEnd;
	//ssSqlEnd << searchsql << sqlend;

	// Build the SQL //
	stringstream ssBody;
	ssBody << " FROM ce_userstats_month_lvl1 l INNER JOIN ce_userstats_month m ON l.user_id=m.user_id ";
	ssBody << " INNER JOIN ce_users u ON u.user_id=l.user_id ";
	ssBody << " INNER JOIN ce_ranks r ON r.user_id=l.user_id ";
	ssBody << " INNER JOIN ce_users p ON p.user_id=u.parent_id ";
	ssBody << " INNER JOIN ce_users s ON s.user_id=u.sponsor_id ";
	ssBody << " INNER JOIN ce_users a ON a.user_id=u.advisor_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v4 ON v4.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v5 ON v5.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v6 ON v6.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v7 ON v7.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v8 ON v8.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_lvl1_rank v9 ON v9.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg6 ON leg6.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg7 ON leg7.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg8 ON leg8.user_id=u.user_id ";
	ssBody << " INNER JOIN ce_userstats_month_leg_rank leg9 ON leg9.user_id=u.user_id ";

	ssBody << " WHERE u.disabled='false' AND l.batch_id=" << batchid << " AND m.batch_id=" << batchid << " AND r.batch_id=" << batchid;
	ssBody << " AND v4.batch_id=" << batchid << " AND v5.batch_id=" << batchid << " AND v6.batch_id=" << batchid << " AND v7.batch_id=" << batchid << " AND v8.batch_id=" << batchid << " AND v9.batch_id=" << batchid;
	ssBody << " AND leg6.batch_id=" << batchid << " AND leg7.batch_id=" << batchid << " AND leg8.batch_id=" << batchid << " AND leg9.batch_id=" << batchid;
	ssBody << " AND l.system_id=" << system_id << " AND m.system_id=" << system_id << " AND u.system_id=" << system_id;
	ssBody << " AND r.system_id=" << system_id << " AND p.system_id=" << system_id << " AND s.system_id=" << system_id << " AND a.system_id=" << system_id;
	ssBody << " AND v4.system_id=" << system_id << " AND v5.system_id=" << system_id << " AND v6.system_id=" << system_id << " AND v7.system_id=" << system_id << " AND v8.system_id=" << system_id << " AND v9.system_id=" << system_id;
	ssBody << " AND leg6.system_id=" << system_id << " AND leg7.system_id=" << system_id << " AND leg8.system_id=" << system_id << " AND leg9.system_id=" << system_id;
	ssBody << " AND l.user_id IN (SELECT user_id FROM ce_users WHERE upline_advisor LIKE '% " << userid << " %' AND system_id='" << system_id << "') ";
	ssBody << " AND v4.rank='4' ";
	ssBody << " AND v5.rank='5' ";
	ssBody << " AND v6.rank='6' ";
	ssBody << " AND v7.rank='7' ";
	ssBody << " AND v8.rank='8' ";
	ssBody << " AND v9.rank='9' ";
	ssBody << " AND leg6.rank='6' ";
	ssBody << " AND leg7.rank='7' ";
	ssBody << " AND leg8.rank='8' ";
	ssBody << " AND leg9.rank='9' ";
	ssBody << " AND leg6.generation='1' ";
	ssBody << " AND leg7.generation='1' ";
	ssBody << " AND leg8.generation='1' ";
	ssBody << " AND leg9.generation='1' ";

	int testcount = m_pDB->GetFirstDB(socket, "SELECT count(*) FROM ce_ranks");
	Debug(DEBUG_TRACE, "CceAffiliate::MyDownLineStatsFull - testcount", testcount);

	stringstream ssCount;
	ssCount << "SELECT count(*)";
	ssCount << ssBody.str() << searchsql;
	int count = m_pDB->GetFirstDB(socket, ssCount);

	stringstream ssColumns;
	ssColumns << " l.id, l.system_id, l.batch_id, l.user_id, l.personal_sales, l.signup_count, l.customer_count, l.affiliate_count, l.reseller_count, l.my_wholesale_sales, l.my_retail_sales, l.psq, ";
	ssColumns << " m.group_wholesale_sales+l.my_wholesale_sales, m.group_used, m.customer_wholesale_sales, m.affiliate_wholesale_sales, m.signup_count, m.affiliate_count, m.customer_count, m.group_retail_sales, m.customer_retail_sales, m.affiliate_retail_sales, m.reseller_wholesale_sales, m.reseller_retail_sales, m.reseller_count, m.team_wholesale_sales, m.team_retail_sales, m.group_and_my_wholesale, m.team_and_my_wholesale, ";
	ssColumns << " u.firstname, u.lastname, u.parent_id, u.sponsor_id, u.signup_date, u.upline_parent, u.upline_sponsor, u.upline_advisor, u.email, u.cell, u.carrer_rank, u.date_last_earned, ";
	ssColumns << " p.user_id, p.firstname, p.lastname, p.email, p.cell, ";
	ssColumns << " s.user_id, s.firstname, s.lastname, s.email, s.cell, ";
	ssColumns << " a.user_id, a.firstname, a.lastname, a.email, a.cell, ";
	ssColumns << " r.rank, ";
	ssColumns << " v4.total, v5.total, v6.total, v7.total, v8.total, v9.total, ";
	ssColumns << " leg6.total, leg7.total, leg8.total, leg9.total, ";
	ssColumns << " array_length(string_to_array(a.upline_advisor, '/'), 1)-1 ";
	
	stringstream ssRecords;
	ssRecords << "SELECT DISTINCT " << ssColumns.str() << ssBody.str() << searchsql << sqlend;

	// Run the SQL and build json //
	CConn *conn;
	if ((conn = m_pDB->ExecDB(socket, ssRecords)) == NULL)
	{
		Debug(DEBUG_ERROR, "Affiliate::MyDownLineStatsFull", "There was an internal error that prevented a SELECT from the database");
		return SetError(503, "API", "Affiliate::MyDownLineStatsFull", "There was an internal database error");
	}

	std::stringstream ssJson;
	ssJson << ",\"count\":\"" << count << "\"";
	ssJson << ",\"userstatsfull\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ssJson << "{";
		int index = 0;
		std::list<string>::iterator i;
		for (i=columns.begin(); i != columns.end(); ++i)
		{
			if (index !=0)
				ssJson << ",";
			string second = conn->m_RowMap[index];

			//Debug(DEBUG_ERROR ,"second=", second);
			//Debug(DEBUG_ERROR ,"second=", second);
			//string tmpval = (*i)+" == "+second;
			//Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull", tmpval);

			if ((*i) == "rank")
			{
				second = m_pDB->MemLookupTitle(atoi(second.c_str()), &RulesRankList);
				ssJson << "\"currenttitle\":\"" << second << "\"";
			}
			else if ((*i) == "ucarrerrank")
			{
				second = m_pDB->MemLookupTitle(atoi(second.c_str()), &RulesRankList);
				ssJson << "\"carrertitle\":\"" << second << "\"";
			}
			else
			{
				// Write out json //
				ssJson << "\""<< RemoveUnderScore(*i) << "\":\"" << second << "\"";
			}
			index++;
		}

		ssJson << "},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CceAffiliate::MyDownLineStatsFull - ThreadReleaseConn == false");
		return SetError(503, "API", "affiliate::mydownlinestatsfull error", "Could not release the database connection");
	}

	stringstream json;
    json << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
    json << "]";
	m_Json = SetJson(200, json.str().c_str());
	return m_Json.c_str();

	//return SetError(503, "API", "CceAffiliate::MyDownLineStatsFull error", "See Debug code on server");
	//return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

///////////////////////////////////
// Get Users Downline statistics //
///////////////////////////////////
const char *CceAffiliate::MySponsoredStats(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("userstats", "ce_userstats_month");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownstats error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownstats error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("team_and_my_wholesale");
	columns.push_back("group_wholesale_sales");
	columns.push_back("group_used");
	columns.push_back("customer_wholesale_sales");
	columns.push_back("affiliate_wholesale_sales");
	columns.push_back("reseller_wholesale_sales");
	columns.push_back("signup_count");
	columns.push_back("affiliate_count");
	columns.push_back("customer_count");
	columns.push_back("reseller_count");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	stringstream searchraw;
	searchraw << " WHERE t.system_id='" << system_id << "' AND t.user_id IN (SELECT user_id FROM ce_users WHERE sponsor_id='" << userid << "' AND system_id='" << system_id << "') ";
	if (search.size() != 0)
	{
		string retval = CDbPlus::PagBuildSearch(0, 0, columns, search);
		retval = StrReplace(retval, " WHERE ", " ");
		searchraw << " AND " << retval << " "; 
	}

	return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

///////////////////////////////////
// Get Users Downline stats lvl1 //
///////////////////////////////////
const char *CceAffiliate::MySponsoredStatsLvl1(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("userstatslvl1", "ce_userstats_month_lvl1");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownlinestatslvl1 error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownlinestatslvl1 error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("personal_sales");
	columns.push_back("signup_count");
	columns.push_back("customer_count");
	columns.push_back("affiliate_count");
	columns.push_back("reseller_count");
	columns.push_back("my_wholesale_sales");
	columns.push_back("my_retail_sales");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	stringstream searchraw;
	searchraw << " WHERE t.system_id='" << system_id << "' AND t.user_id IN (SELECT user_id FROM ce_users WHERE sponsor_id='" << userid << "' AND system_id='" << system_id << "') ";
	if (search.size() != 0)
	{
		string retval = CDbPlus::PagBuildSearch(0, 0, columns, search);
		retval = StrReplace(retval, " WHERE ", " ");
		searchraw << " AND " << retval << " "; 
	}

	return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}


/////////////////////////////////////
// My downline of rankrules missed //
/////////////////////////////////////
const char *CceAffiliate::MyDownlineRankRulesMissed(int socket, int system_id, string userid, string search, string sort)
{
	CDbPlus::Setup("rankrulesmissed", "ce_rankrules_missed");

	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::mydownlinerankrulesmissed error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::mydownlinerankrulesmissed error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	// Handle pagination values //
	if (search.length() != 0)
	{
		if (is_qstring(search) == false)
			return SetError(400, "API", "affiliate::mydownlinerankrulesmissed error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}
	if (sort.length() != 0) 
	{
		if (is_qstring(sort) == false)
			return SetError(400, "API", "affiliate::mydownlinerankrulesmissed error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("rule_id");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("actual_value");
	columns.push_back("diff");
	columns.push_back("created_at");

	//Debug(DEBUG_TRACE, "CceAffiliate::MyDownlineRankRulesMissed - sort", sort.c_str());

	stringstream searchraw;
	searchraw << " WHERE t.system_id='" << system_id << "' AND t.user_id IN (SELECT user_id FROM ce_users WHERE upline_parent LIKE '% " << userid << " %' AND system_id='" << system_id << "') ";
	if (search.size() != 0)
	{
		string retval = CDbPlus::PagBuildSearch(0, 0, columns, search);
		retval = StrReplace(retval, " WHERE ", " ");
		searchraw << " AND " << retval << " "; 
	}

	Debug(DEBUG_TRACE, "CceAffiliate::MyDownlineRankRulesMissed - searchraw", searchraw.str().c_str());

	return CDbPlus::QuerySearchRawDB(m_pDB, socket, 0, system_id, columns, mask, searchraw.str(), sort);
}

/////////////////////////////////////////////////
// My Receipt Sum. Needed for Chalk Site Sales //
/////////////////////////////////////////////////
const char *CceAffiliate::MyReceiptSum(int socket, int system_id, string userid, string inv_type, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "affiliate::myreceiptsum error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "affiliate::myreceiptsum error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(inv_type) == false)
		return SetError(409, "API", "affiliate::myreceiptsum error", "The invtype is not a number");
	if (is_date(startdate) == false)
		return SetError(409, "API", "affiliate::myreceiptsum error", "The startdate is invalid");
	if (is_date(enddate) == false)
		return SetError(409, "API", "affiliate::myreceiptsum error", "The enddate is invalid");

	stringstream ss;
	string receiptsum = m_pDB->GetFirstCharDB(socket, ss << "SELECT sum(retail_price) FROM ce_receipts WHERE system_id='" << system_id << "' AND inv_type='" << inv_type << "' AND user_id='" << userid << "' AND retail_date >= '" << startdate << "'  AND retail_date <= '" << enddate << "'");

	stringstream ssJson;
	ssJson << ",\"receiptsum\":\"" << receiptsum << "\"";

	m_Json = SetJson(200, ssJson.str().c_str());
	return m_Json.c_str();
}