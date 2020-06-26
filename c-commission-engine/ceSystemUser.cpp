#include "ceSystemUser.h"
#include "db.h"
#include "ezRecv.h"
#include "ezCrypt.h"

#include <stdlib.h>

#include <openssl/rand.h> // RAND_pseudo_bytes, RAND_bytes

/////////////////
// Constructor //
/////////////////
CceSystemUser::CceSystemUser(CDb *pDB, string origin)
{
	m_pDB = pDB;
	CezJson::SetOrigin(origin);
}

/////////////////////////////////////
// Create new session key for user //
/////////////////////////////////////
const char *CceSystemUser::AuthSessionUser(int socket, const char *email, const char *authpass, const char *ipaddress, string remoteaddress)
{
	//Debug(DEBUG_TRACE, socket, "CceSystemUser::AuthSessionUser - ipaddress", ipaddress);

	if (strlen(email) == 0)
		return SetError(401, "API", "authemail is missing", "The authemail needs to be defined so authenication can be performed");
	if (strlen(email) > API_EMAIL_LENGTH) // Make sure email isn't too long //
		return SetError(401, "API", "authemail to long", "The authemail is too long in character length");
	if (is_email(email) == false)	
		return SetError(401, "API", "Invalid email", "The email has invalid characters");
	if (is_password(authpass) == false)
		return SetError(401, "API", "Invalid password", "The password has invalid characters");
	if (is_ipaddress(ipaddress) == false)
		return SetError(401, "API", "Invalid ipaddress", "The ipaddress has invalid characters");

	std::stringstream ss0;
	if (m_pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'") == 0)
		return SetError(401, "API", "Invalid login", "Invalid email address/password");

	CezCrypt crypt;
	std::stringstream ss;
	std::string salt = m_pDB->GetFirstCharDB(socket, ss << "SELECT salt FROM ce_systemusers WHERE email ILIKE '" << email << "'");
	std::string authpass_hash = crypt.GenPBKDF2(m_pDB->m_pSettings->m_HashPass.c_str(), salt.c_str(), authpass);

	std::stringstream ss2;
	int count = m_pDB->GetFirstDB(socket, ss2 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "' AND password_hash='" << authpass_hash.c_str() << "'");
	if (count == 0) // Unable to login //
		return SetError(503, "API", "authsessionuser error", "Could not authenticate with given authemail and authpass");
	else if (count == 1) // Login successful //
	{
		std::stringstream ss3;
		int sysuser_id = m_pDB->GetFirstDB(socket, ss3 << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "' AND password_hash='" << authpass_hash.c_str() << "'");

		// Create new sesson key //
		CezCrypt crypt;
		std::string sessionkey = crypt.GenSha256(); // user_id = 1 for master account //
		std::string hash_session = crypt.GenPBKDF2(m_pDB->m_pSettings->m_HashPass.c_str(), salt.c_str(), sessionkey.c_str());

		// Create new session entry //
		std::stringstream ss4;
		if (m_pDB->ExecDB(socket, ss4 << "INSERT INTO ce_sessions (sysuser_id, sessionkey, ipaddress, hit_count, created_at) VALUES (" << sysuser_id << ",'" << hash_session.c_str() << "','" << ipaddress << "', 1, 'now()')") == NULL)
		{
			Debug(DEBUG_ERROR, "CDb::AuthSessionUser - error inserting new session record entry");
			return SetError(503, "API", "authsessionuser error", "Database Error. Could not INSERT into database");
		}

		// Make a log record of the login //
		if (LoginLog(socket, email, remoteaddress) == false)
			return SetError(503, "API", "authsessionuser error", "LoginLog == false");

		// Return the session key to user //
		std::stringstream ss5;
		return SetJson(200, ss5 << ",\"authuser\":{\"sessionkey\":\"" << sessionkey.c_str() << "\"}");
	}
	else // ??? //
	{
		Debug(DEBUG_ERROR, "CDb::AuthSessionUser - count > 1? Theoretically we should never get here");
		return SetError(503, "API", "authsessionuser error", "Could not authenticate with given authemail and authpass");
	}
}

/////////////////////////////
// Check the password hash //
/////////////////////////////
const char *CceSystemUser::UserValidCheck(int socket, string email)
{
	// Scrub inputs //
	
	if (is_email(email) == false)
			return SetError(400, "API", "systemuser::uservalidcheck error", "The email is invalid");

	stringstream ss1;
	ss1 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'";
	int retval = m_pDB->GetFirstDB(socket, ss1);
	if (retval == 0)
	{
		stringstream ss2;
		ss2 << "CceSystemUser::UserValidCheck - Systemuser not found with email=" << email;
		Debug(DEBUG_TRACE, ss2.str().c_str());
		return SetError(400, "API", "systemuser::uservalidcheck error", "No systemuser found in system for given systemid and email");
	}
	else if (retval > 1)
	{
		stringstream ss2;
		ss2 << "CceSystemUser::UserValidCheck - Multiple systemusers found with email=" << email;
		Debug(DEBUG_TRACE, ss2.str().c_str());
		return SetError(400, "API", "systemuser::uservalidcheck error", "Multiple systemusers found for given email");
	}

	return SetJson(200, "");
}

/////////////////////////////
// Password Hash Generator //
/////////////////////////////
const char *CceSystemUser::PasswordHashGen(int socket, string email, string remoteaddress)
{
	// Scrub inputs //
	if (is_ipaddress(remoteaddress) == false)
	{
		Debug(DEBUG_WARN, "CceSystemUser::PasswordHashGen - remoteaddress", remoteaddress.c_str());
		return SetError(400, "API", "systemuser::passwordhashgen error", "The remoteaddress has invalid characters");
	}

	stringstream ss0;
	ss0 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'";
	if (m_pDB->GetFirstDB(socket, ss0) == 0)
		return SetError(400, "API", "systemuser::passwordhashgen error", "The systemuser cannot be found in ref to the email");

	stringstream ss1;
	ss1 << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "'";
	int sysuser_id = m_pDB->GetFirstDB(socket, ss1);

	string hash;
	stringstream ss2;
	ss2 << "SELECT count(*) FROM ces_sysuser_passreset WHERE substr((created_at::TIME-(now()-interval '30 minutes')::TIME)::TEXT,1,1) != '-' AND sysuser_id='" << sysuser_id << "' AND used='false'";
    int retval = m_pDB->GetFirstDB(socket, ss2);
	if (retval != 0)
	{
		stringstream ss3;
		ss3 << "SELECT hash FROM ces_sysuser_passreset WHERE substr((created_at::TIME-(now()-interval '30 minutes')::TIME)::TEXT,1,1) != '-' AND sysuser_id='" << sysuser_id << "' AND used='false'";
		hash = m_pDB->GetFirstCharDB(socket, ss3);
	}

	// Create new hash if not found //
    if (hash.size() == 0)
    {
    	char bytes[256];
        memset(bytes, 0, 256);
        if (RAND_bytes((unsigned char *)bytes, 32) == 0)
        {
        	Debug(DEBUG_ERROR, "CceSystemUser::PasswordHashGen - ERROR RAND_bytes == 0");
			return SetError(400, "API", "systemuser::passwordhashgen error", "ERROR RAND_bytes returned 0");
        }

        // Convert binary to hex //
        string tmpstr = bytes;
        hash = bin2hex(tmpstr);
   
    	stringstream ss4;
		ss4 << "INSERT INTO ces_sysuser_passreset(sysuser_id, hash, ipaddress) VALUES ('" << sysuser_id << "', '" << hash << "', '" << remoteaddress << "')";
		if (m_pDB->ExecDB(socket, ss4) == NULL)
		{
			Debug(DEBUG_ERROR, "CceSystemUser::PasswordHashGen - Problems with ExecDB query");
			return SetError(400, "API", "systemuser::passwordhashgen error", "Multiple users found in system for given email");
		}
    }   

    // Send back hash in Json format //
    stringstream ss5;
    return SetJson(200, ss5 << ",\"hashgen\":{\"hash\":\"" << hash << "\"}");
}

///////////////////////////////////
// Verify that it's a valid hash //
///////////////////////////////////
const char *CceSystemUser::PasswordHashValid(int socket, string hash)
{
	// Make sure a record exists first //
	stringstream ss1;
	ss1 << "SELECT count(*) FROM ces_sysuser_passreset WHERE hash='" << hash << "' AND used=false";
	if (m_pDB->GetFirstDB(socket, ss1) == 0)
	{
		Debug(DEBUG_DEBUG, "CceAffiliate::PasswordHashUpdate - No records found related to hash");
		return SetError(400, "API", "affiliate::passwordhashupdate error", "No records found related to hash");
	}

	// Grab that given record //
	stringstream ss2;
	ss2 << "SELECT created_at::TIME-(now()- interval '30 minutes')::TIME FROM ces_sysuser_passreset WHERE hash='" << hash << "' AND used=false";
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
const char *CceSystemUser::PasswordHashUpdate(int socket, string hash)
{
	if (is_alphanum(hash) == false)
		return SetError(400, "API", "systemuser::passwordhashupdate error", "The hash can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	// Make sure a record exists first //
	stringstream ss1;
	ss1 << "SELECT count(*) FROM ces_sysuser_passreset WHERE hash='" << hash << "' AND used=false";
	if (m_pDB->GetFirstDB(socket, ss1) == 0)
	{
		Debug(DEBUG_DEBUG, "CceSystemUser::PasswordHashUpdate - No records found related to hash");
		return SetError(400, "API", "systemuser::passwordhashupdate error", "No records found related to hash");
	}

	// Grab the userid //
	stringstream ss2;
	ss2 << "SELECT sysuser_id FROM ces_sysuser_passreset WHERE hash='" << hash << "' AND used=false";
	string sysuserid = m_pDB->GetFirstCharDB(socket, ss2);

	// Grab that given record //
	stringstream ss3;
	ss3 << "SELECT created_at::TIME-(now()- interval '30 minutes')::TIME FROM ces_sysuser_passreset WHERE hash='" << hash << "' AND used=false";
	string timeval = m_pDB->GetFirstCharDB(socket, ss3);
	if (timeval == "-") // Expired //
	{
		Debug(DEBUG_DEBUG, "CceSystemUser::PasswordHashUpdate - The hash record has expired");
		return SetError(400, "API", "systemuser::passwordhashupdate error", "The hash record has expired");
	}

	// Update the has record for used=true //
	stringstream ss4;
	ss4 << "UPDATE ces_sysuser_passreset SET used=true WHERE sysuser_id IN (SELECT sysuser_id FROM ces_sysuser_passreset WHERE hash='" << hash << "')";
	if (m_pDB->ExecDB(socket, ss4) == NULL)
	{
		Debug(DEBUG_ERROR, "CceSystemUser::PasswordHashUpdate - Problems with ExecDB query");
		return SetError(400, "API", "systemuser::passwordhashupdate error", "Problems with SQL UPDATE command");
	}

	stringstream ss5;
	return SetJson(200, ss5 << ",\"hashupdate\":{\"sysuserid\":\"" << sysuserid << "\"}");
}

//////////////////////////////
// Make a log for the login //
//////////////////////////////
bool CceSystemUser::LoginLog(int socket, string email, string remoteaddress)
{
	if (is_email(email) == false)
		return Debug(DEBUG_DEBUG, "CceSystemUser::LoginLog - The email is invalid");

	stringstream ss1;
	ss1 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'";
	if (m_pDB->GetFirstDB(socket, ss1) == 0)
		return Debug(DEBUG_DEBUG, "CceSystemUser::LoginLog - Sysuser email not found");

	stringstream ss2;
	ss2 << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "'";
	int sysuser_id = m_pDB->GetFirstDB(socket, ss2);

	// Make login entry //
	stringstream ss3;
	ss3 << "INSERT INTO ces_sysuser_login (sysuser_id, ipaddress) VALUES (" << sysuser_id << ", '" << remoteaddress << "')";
  	if (m_pDB->ExecDB(socket, ss3) == NULL)
  		return Debug(DEBUG_ERROR, "CceSystemUser::LoginLog - There was an error with the SQL INSERT");
  	
  	return true;
}

///////////////////////////////
// Make a log for the logout //
///////////////////////////////
const char *CceSystemUser::LogoutLog(int socket, string email)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser::userlogoutlog error", "A database connection needs to be made first");
	if (is_email(email) == false)
		return SetError(400, "API", "systemuser::userlogoutlog error", "The email is invalid");

	stringstream ss1;
	ss1 << "UPDATE ces_sysuser_login SET logout_at='now()' WHERE id IN (SELECT id FROM ces_sysuser_login WHERE sysuser_id IN (SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "') ORDER BY id DESC LIMIT 1)";
	if (m_pDB->ExecDB(socket, ss1) == NULL)
	{
		Debug(DEBUG_ERROR, "CceSystemUser::LoginLogout - Problems with ExecDB query");
		return SetError(400, "API", "systemuser::userlogoutlog error", "Problems with SQL UPDATE command");
	}

	return SetJson(200, "");
}

//////////////////////////////////////////////////////////////////
// An initial system user need to be added before anything else //
//////////////////////////////////////////////////////////////////
const char *CceSystemUser::Add(int socket, string firstname, string lastname, string email, string password, string remoteaddress, string serveraddress)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser::add error", "A database connection needs to be made first");
	if (is_alpha(firstname) == false)
		return SetError(409, "API", "systemuser::add error", "The firstname is invalid");
	if (is_alpha(lastname) == false)
		return SetError(409, "API", "systemuser::add error", "The lastname is invalid");
	if (is_email(email) == false)
		return SetError(409, "API", "systemuser::add error", "The email is invalid");
	if (password.length() < 8)
		return SetError(409, "API", "systemuser::add error", "The password need to be at least 8 characters long");
	if (is_password(password) == false)
		return SetError(409, "API", "systemuser::add error", "The password is invalid");
		
	if (remoteaddress.size() != 0)
	{
		if (is_ipaddress(remoteaddress) == false)
		{
			return SetError(409, "API", "systemuser.add error", "The remoteaddress is invalid");
		}
	}

	if (serveraddress.size() != 0)
	{
		if (is_ipaddress(serveraddress) == false)
		{
			return SetError(409, "API", "systemuser.add error", "The serveraddress is invalid");
		}
	}

	//if (LoginLog(email, remoteaddress) == false)
	//	return SetError(400, "API", "systemuser.add error", "Error with logging");

	// Create the system user in database and get an ID //
	return m_pDB->AddSystemUser(socket, firstname.c_str(), lastname.c_str(), email.c_str(), password.c_str(), serveraddress.c_str());
}

////////////////////////////////////////////////////
// United Override Function for default 127.0.0.1 //
////////////////////////////////////////////////////
const char *CceSystemUser::AddRuby(int socket, string email, string password)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser.add error", "A database connection needs to be made first");
	return Add(socket, "rubyfirst", "rubylast", email, password, "127.0.0.1", "127.0.0.1");
}

//////////////////////////
// Edit the system_user //
//////////////////////////
const char *CceSystemUser::Edit(int socket, int coresysuser_id, int sysuser_id, string email, string password, string ipaddress)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser.edit error", "A database connection needs to be made first");
	if (coresysuser_id != sysuser_id)
		return SetError(409, "API", "systemuser.edit error", "Invalid access to sysuserid");
	if (is_email(email) == false)
		return SetError(409, "API", "systemuser.edit error", "The email is invalid");
	if (is_password(password) == false)
		return SetError(409, "API", "systemuser.edit error", "The password is invalid");
	if (is_ipaddress(ipaddress) == false)
		return SetError(409, "API", "systemuser.edit error", "The ipaddress is invalid");
	//if (atoi(sysuser_id.c_str()) < 0)
	if (sysuser_id <= 1)
		return SetError(409, "API", "systemuser.edit error", "The sysuserid is not within acceptable parameters");

	return m_pDB->EditSystemUser(socket, sysuser_id, email.c_str(), password.c_str(), ipaddress.c_str());
}

////////////////////////////////
// Query list of system users //
////////////////////////////////
const char *CceSystemUser::Query(int socket)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser.query error", "A database connection needs to be made first");
	return m_pDB->QuerySystemUsers(socket);
}

////////////////////////////////////////////
// Allow disabling of system user account //
////////////////////////////////////////////
const char *CceSystemUser::Disable(int socket, int coresysuser_id, int sysuser_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser.disable error", "A database connection needs to be made first");
	if (coresysuser_id != 1)
		return SetError(409, "API", "systemuser.disable error", "Invalid access to sysuserid");
	if (sysuser_id <= 1)
		return SetError(409, "API", "systemuser.disable error", "The sysuserid is not within acceptable parameters");

	return m_pDB->DisableSystemUser(socket, sysuser_id);
}

///////////////////////////////////////////
// Allow enabling of system user account //
///////////////////////////////////////////
const char *CceSystemUser::Enable(int socket, int coresysuser_id, int sysuser_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser.enable error", "A database connection needs to be made first");
	if (coresysuser_id != 1)
		return SetError(409, "API", "systemuser.enable error", "Invalid access to sysuserid");
	if (sysuser_id <= 1)
		return SetError(409, "API", "systemuser.enable error", "The sysuserid is not within acceptable parameters");

	return m_pDB->EnableSystemUser(socket, sysuser_id);
}

//////////////////////////////////
// Reissue system users API key //
//////////////////////////////////
const char *CceSystemUser::ReissueApiKey(int socket, int sysuser_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser::reissueapikey error", "A database connection needs to be made first");
	//if (is_number(sysuser_id) == false)
	//	return SetError(409, "API", "systemuser::reissueapikey error", "The sysuserid is not numeric");
	//if (coresysuser_id != 1)
	//	return SetError(409, "API", "systemuser::reissueapikey error", "Invalid access to sysuserid");
	if (sysuser_id <= 1)
		return SetError(409, "API", "systemuser::reissueapikey error", "The sysuserid is not within acceptable parameters");

	return m_pDB->ReissueApiKey(socket, sysuser_id);
}

////////////////////////
// Reset the password //
////////////////////////
const char *CceSystemUser::ResetPassword(int socket, int coresysuser_id, string sysuser_id, string password)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "systemuser::reissueapikey error", "A database connection needs to be made first");
	if (is_number(sysuser_id) == false)
		return SetError(409, "API", "systemuser::reissueapikey error", "The sysuserid is not numeric");
	if (coresysuser_id != 1)
		return SetError(409, "API", "systemuser::reissueapikey error", "Invalid access to sysuserid");
	if (atoi(sysuser_id.c_str()) <= 1)
		return SetError(409, "API", "systemuser::reissueapikey error", "The sysuserid is not within acceptable parameters");
	if (is_password(password) == false)
		return SetError(409, "API", "systemuser::reissueapikey error", "The password is invalid");

	if (m_pDB->ResetSysUserPassword(socket, sysuser_id, password) == false)
		return SetError(409, "API", "systemuser::resetpassword error", "There was a problem resetting the systemuser password");
	
	return SetJson(200, "");
}
