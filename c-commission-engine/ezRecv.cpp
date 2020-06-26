#include "Compile.h"
#include "CommissionEngine.h"
#include "migrations.h"
#include "seed.h"
#include "convert.h"

#include "ezCrypt.h"
#include "ezRecv.h"
#include "ezReports.h"

#include "ceAffiliate.h"
#include "ceApiKey.h"
#include "ceBankAccount.h"
#include "ceBasicCommRule.h"
#include "ceBonus.h"
#include "ceCMRankRule.h"
#include "ceCMCommRule.h"
#include "ceCommRule.h"
#include "ceCommissions.h"
#include "ceExtQualify.h"
#include "ceFastStart.h"
#include "ceLedgerTotals.h"
#include "cePayments.h"
#include "cePayout.h"
#include "cePools.h"
#include "cePoolRule.h"
#include "ceReceiptsFilter.h"
#include "ceReceiptTotals.h"
#include "ceRankBonusRules.h"
#include "ceRankGenBonusRules.h"
#include "ceRankRule.h"
#include "ceRankRuleMissed.h"
#include "ceReceipt.h"
#include "ceSettings.h"
#include "ceSimulation.h"
#include "ceSignupBonus.h"
#include "ceSystem.h"
#include "ceSystemUser.h"
#include "ceUser.h"
#include "ceLedger.h"

#include <stdlib.h>
#include <algorithm>
#include <string> 

//#include "josepp/hmac.cpp" 
//#include <openssl/hmac.h>
//#include <crypto++/sha.h>
//#include <crypto++/hmac.h>

extern CDb *g_pDB; 
CDb *g_pSimDB = NULL; 

// Make cacheing global //
map <string, int> g_SysUserIDCache;
map <string, string> g_SysUserPassCache;
map <int, int> g_SystemRights; // Speed up lookup of systemid-to-systemuser rights //

////////////////////////
// Set initial values //
////////////////////////
CezRecv::CezRecv()
{
	//g_pDB = &m_DB;
}

/////////////////////
// Set the logfile //
/////////////////////
void CezRecv::SetLogFile(string logfile)
{
	CDebug::SetLogFile(logfile.c_str());
}

////////////////////////////////////
// Process incoming communication //
////////////////////////////////////
string CezRecv::Process(int socket, CDb *pDB, CezVars *pVars)
{
	CDebug debug;

	if (g_pDB == NULL)
	{
		debug.Debug(DEBUG_TRACE, socket, "CezRecv::Process - g_pDB == NULL");
		return SetError(401, "API", "No Database Connection", "Unable to proceed due to NULL database connection");
	}
	if (pDB == NULL)
	{
		debug.Debug(DEBUG_TRACE, socket, "CezRecv::Process - pDB == NULL");
		return SetError(401, "API", "Empty Variables", "The database class is empty"); 
	}
	if (pVars == NULL)
	{
		debug.Debug(DEBUG_TRACE, socket, "CezRecv::Process - pVars == NULL");
		return SetError(401, "API", "Empty Variables", "The variables class is empty"); 
	}

	CezJson::SetOrigin(pVars->m_Origin);

	int SystemUserID = 0; // Keep track of system sysuser_id after authentication //
	string AffiliateID; // Keep track of system user_id after authentication //

	// Article said if we only use POST then we won't be as vulnerable to Javascript cross site attacks //
	//std::string request_method = GetHeadVar("request_method");
	//if (strcmp(request_method.c_str(), "post") != 0)
	//	return SetError(401, "API", "method error", "Only POST allowed");

	string command_str = pVars->Get("command");
	string authemail = pVars->Get("authemail");
	string authpass = pVars->Get("authpass");
	string sessionkey;// = pVars->Get("sessionkey"); // Going away //
	string api_key = pVars->Get("apikey");

	string affiliateemail = pVars->Get("affiliateemail");
	string affiliatepass = pVars->Get("affiliatepass");

	string authorization = pVars->Get("authorization"); // This will handle cors //

	Debug(DEBUG_INFO, socket, "CezRecv::Process - command_str", command_str.c_str());

	unsigned int commsize = command_str.size();
	Debug(DEBUG_INFO, socket, "CezRecv::Process - command.size()", (int)commsize);

	//if (authemail != MASTER_ACCOUNT);
	//	api_key = toupper(api_key);

/////////////////////////////////////////
// Handle skipping auth stuff for ruby //
/////////////////////////////////////////
#ifndef COMPILE_RUBYRICE
		
	bool jwtverify = false;

	if (authemail.size() > API_EMAIL_LENGTH) // Check API keys for too long in length //
		return SetError(401, "API", "authemail to long", "The authemail is too long in character length");
	if (affiliateemail.size() > API_EMAIL_LENGTH) // Check API keys for too long in length //
		return SetError(401, "API", "authemail to long", "The authemail is too long in character length");
	if ((affiliateemail.size() != 0) && (affiliatepass.size() != 0))
	{
		string system_id_str = pVars->Get("systemid");
		if (system_id_str.size() == 0) // Make sure value is passed in //
			return SetError(400, "API", "systemid missing", "A systemid needs to be defined for other commands to work properly");
		if (is_number(system_id_str) == false)
			return SetError(400, "API", "systemid error", "The systemid is not numeric");
		int system_id = atoi(system_id_str.c_str());

		// Allow admin override //
		if ((authemail.size() != 0) && (authpass.size() != 0))
		{
			// Admin Login with username and password //
			string ipaddress = pVars->m_RemoteAddr;
			int tmpsysuserid = pDB->AuthSysUser(socket, authemail.c_str(), authpass.c_str(), ipaddress.c_str());
			if (tmpsysuserid < 1)
				return SetError(401, "API", "authentication error", "Could not authenticate");

			CceAffiliate affiliate(pDB, pVars->m_Origin);
			AffiliateID = affiliate.GetUserID(socket, system_id, affiliateemail);
		}
		else // Normal Affiliate //
		{
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			if (affiliate.Login(socket, system_id, affiliateemail, affiliatepass) == false)
				return SetError(401, "API", "authentication error", "Could not authenticate");

			AffiliateID = affiliate.GetUserID(socket, system_id, affiliateemail);
		}
	}
	else if ((authemail.size() != 0) && (authpass.size() != 0)) // && (command_str == POST_AUTHSESSIONUSER))
	{
		Debug(DEBUG_TRACE, "CezRecv::Process - authemail", authemail);

		if (strlen(authemail.c_str()) > API_EMAIL_LENGTH) // Make sure email isn't too long //
			return SetError(401, "API", "authentication error", "authemail is longer than 128");
		else if (is_email(authemail) == false)
			return SetError(401, "API", "authentication error", "authemail is invalid");
		else if (is_password(authpass) == false)
			return SetError(401, "API", "authentication error", "authpass is invalid");

		// Check cache first //
		if (g_SysUserPassCache[authemail] == authpass)
		{
			SystemUserID = g_SysUserIDCache[authemail];
		}
		else // Then check database //
		{
			// Login with username and password //
			string ipaddress = pVars->m_RemoteAddr;
			SystemUserID = pDB->AuthSysUser(socket, authemail.c_str(), authpass.c_str(), ipaddress.c_str());
			if (SystemUserID < 1)
				return SetError(401, "API", "authentication error", "Could not authenticate");

			// Cache credential for faster API speed //
			g_SysUserPassCache[authemail] = authpass;
			g_SysUserIDCache[authemail] = SystemUserID;
		}
	}
	else if ((authorization.size() != 0) && (pDB->m_pSettings->m_JwtSecret.size() != 0)) // Handle authorization for Cors //
	{ 
		Debug(DEBUG_DEBUG, socket, "CezRecv::Process - JWT Auth Hit");

		CezCrypt crypt;

		string claimsToVerify;
		claimsToVerify = "Where Do We Get Claims From???"; // Claims is in the secret // Delete this //

		//stringstream data;
		//data << "{\"tenant_id\":\"2\",\"role\":\"Superadmin\",\"orgId\":\"foo123\",\"sub\":109,\"iss\":\"http://core.local/api/external/authenticate\",\"iat\":1523635367,\"exp\":1526227367,\"nbf\":1523635367,\"jti\":\"bAsEP7zoiCsbtVHt\"}";
		//string test = crypt.HMAC_Generate(pDB->m_pSettings->m_JwtSecret, data.str().c_str());

		//Debug(DEBUG_DEBUG, socket, "CezRecv::Process - test", test.c_str());

		AffiliateID = crypt.HMAC_Verify(pDB->m_pSettings->m_JwtSecret, authorization, claimsToVerify);
		if (AffiliateID == "")
			return SetError(401, "API", "authentication error", "JWT authorization did not verify");

		Debug(DEBUG_DEBUG, socket, "CezRecv::Process - authorization PASSED");
		jwtverify = true;
	}
	// This type of authentication is going away... I think //
	//else if (sessionkey.size() != 0) // Do limited session default 24 minute authentication //
	//{
	//	if (authemail.size() == 0) // Make sure value is passed in //
	//		return SetError(401, "API", "authemail is missing", "The authemail needs to be defined so authenication can be performed (#1)");
	//	if (authemail.size() > API_EMAIL_LENGTH) // Make sure value is passed in //
	//		return SetError(401, "API", "authemail to long", "The authemail is too long in character length");
	//	
	//	// Do database lookup for authentication //
	//	SystemUserID = pDB->CheckSessionUser(socket, authemail.c_str(), sessionkey.c_str());
	//		
	//	Debug(DEBUG_DEBUG, socket, "CezRecv::Process - SystemUserID =", SystemUserID);
	//
	//	if (SystemUserID == -1)
	//		return SetError(401, "API", "authemail and/or sessionkey mismatch", "The given authemail and/or sessionkey were unable to authenticate");
	//}
	else if (sessionkey.size() == 0) // Handle longterm API authetication //
	{
		if (api_key.size() == 0) // Make sure value is passed in //
			return SetError(401, "API", "apikey is missing", "The apikey needs to be defined so authenication can be performed");
		if (api_key.size() > API_KEY_LENGTH) // Make sure value is passed in //
			return SetError(401, "API", "apikey to long", "The apikey is too long in character length");
		if (authemail.size() == 0)
			return SetError(401, "API", "authemail is missing", "The authemail needs to be defined so authenication can be performed (#2)");
		if (authemail.size() > API_EMAIL_LENGTH) // Make sure email isn't too long //
			return SetError(401, "API", "authemail to long", "The authemail is too long in character length");

		// Do database lookup for authentication //
		SystemUserID = pDB->AuthAPIUser(socket, authemail.c_str(), api_key.c_str());
		if (SystemUserID == -1)
			return SetError(401, "API", "authemail and/or apikey mismatch", "The given authemail and/or apikey were unable to authenticate");
	}
	else if (authemail.size() == 0) // Check API keys for missing //
		return SetError(401, "API", "authemail is missing", "The authemail needs to be defined so authenication can be performed (#3)");

#endif

#ifdef COMPILE_RUBYRICE
	SystemUserID = 1; // SystemUserID will always be 1 for United League
#endif

	// On future speed up release, keep all authentication stuff in memory //

	// Handle the command //
	if (command_str.size() == 0) // Make sure value is passed in //
		return SetError(401, "API", "command is missing", "The command is need for the API to know how to process clients request");

	// Do core commands first //
	int command = CheckCommands(command_str.c_str());
	if (command == -1)
	{
		std::string error_msg;
		error_msg = "The command (";
		error_msg += command_str.c_str();
		error_msg += ") is not a recognized command";
		return SetError(401, "API", "invalid command", error_msg.c_str());
	}

	Debug(DEBUG_DEBUG, socket, "CezRecv::Process - command", command);
	Debug(DEBUG_DEBUG, socket, "CezRecv::Process - AffiliateID", AffiliateID.c_str());
	Debug(DEBUG_DEBUG, socket, "CezRecv::Process - SystemUserID", SystemUserID);

	///////////////////////////////
	// Handle Affiliate Commands //
	///////////////////////////////
	if (AffiliateID.size() > 0)
	{	
		// The system ID is required for the next set of commands //
		string system_id_str = pVars->Get("systemid");
		if (system_id_str.size() == 0) // Make sure value is passed in //
			return SetError(400, "API", "systemid missing", "A systemid needs to be defined for other commands to work properly");
		if (is_number(system_id_str) == false)
			return SetError(400, "API", "systemid error", "The systemid is not numeric");
		int system_id = atoi(system_id_str.c_str());

		// Check to see if sysuser_id owns system_id //
		if (pDB->IsUserRightsSystem(socket, system_id, AffiliateID) == false)
			return SetError(403, "API", "system rights error", "You do not have rights to this system");
 
		CceAffiliate affiliate(pDB, pVars->m_Origin);
		string userid = pVars->Get("userid");
		string search = pVars->Get("search");
		string sort = pVars->Get("sort");
		switch (command)
		{
			case CMD_MYJWTVERIFY:
			{
				if (jwtverify == false)
					return SetError(401, "API", "jwtverify error", "There was a problem with jwtverify");
				else
				{
					stringstream ssRet;
					ssRet << ",\"userid\":\"" << AffiliateID << "\"";
					return SetJson(200, ssRet.str().c_str());
				}

				return SetError(401, "API", "jwtverify error", "It should never get here");
			}
			case CMD_MYLOGIN:
			{	
				string affiliateemail = pVars->Get("affiliateemail");
				string remoteaddress = pVars->Get("remoteaddress");
				return affiliate.LoginLog(socket, system_id, affiliateemail, remoteaddress);
			}
			case CMD_MYPROJECTIONS:
			{
				string startdate = pVars->Get("startdate");
				string enddate = pVars->Get("enddate");
				return affiliate.MyProjections(socket, system_id, userid, startdate, enddate);
			}
			case CMD_MYCOMMISSIONS:
			{
				return affiliate.MyCommissions(socket, system_id, userid, search, sort);
			}
			case CMD_MYACHVBONUS:
			{
				//Debug(DEBUG_TRACE, "CMD_MYACHVBONUS");
				return affiliate.MyAchvBonus(socket, system_id, userid, search, sort);
			}
			case CMD_MYBONUS:
			{
				return affiliate.MyBonus(socket, system_id, userid, search, sort);
			}
			case CMD_MYRANKGENBONUS:
			{
				return affiliate.MyRankGenBonus(socket, system_id, userid, search, sort);
			}
			case CMD_MYLEDGER:
			{
				return affiliate.MyLedger(socket, system_id, userid, search, sort);
			}
			case CMD_MYSTATS:
			{
				return affiliate.MyStats(socket, system_id, userid, search, sort);
			}
			case CMD_MYSTATS_LVL1:
			{
				return affiliate.MyStatsLvl1(socket, system_id, userid, search, sort);
			}
			case CMD_MYDOWNSTATS:
			{
				return affiliate.MyDownLineStats(socket, system_id, userid, search, sort);
			}
			case CMD_MYDOWNSTATSLVL1:
			{
				return affiliate.MyDownLineStatsLvl1(socket, system_id, userid, search, sort);
			}
			case CMD_MYDOWNSTATSFULL:
			{
				string batchid = pVars->Get("batchid");
				return affiliate.MyDownLineStatsFull(socket, system_id, userid, batchid, search, sort);
			}
			case CMD_MYSPONSOREDSTATS:
			{
				return affiliate.MySponsoredStats(socket, system_id, userid, search, sort);
			}
			case CMD_MYSPONSOREDSTATSLVL1:
			{
				return affiliate.MySponsoredStatsLvl1(socket, system_id, userid, search, sort);
			}
			case CMD_MYBREAKDOWN:
			{
				return affiliate.MyBreakdown(socket, system_id, userid, search, sort);
			}
			case CMD_MYBREAKDOWNGEN:
			{
				string batchid = pVars->Get("batchid");
				string parentid = pVars->Get("parentid");
				return affiliate.MyBreakdownGen(socket, system_id, batchid, parentid);
			}
			case CMD_MYBREAKDOWNUSERS:
			{
				string batchid = pVars->Get("batchid");
				string parentid = pVars->Get("parentid");
				string generation = pVars->Get("generation");
				return affiliate.MyBreakdownUsers(socket, system_id, batchid, parentid, generation);
			}
			case CMD_MYBREAKDOWNORDERS:
			{
				string batchid = pVars->Get("batchid");
				string parentid = pVars->Get("parentid");
				string userid = pVars->Get("userid");
				return affiliate.MyBreakdownOrders(socket, system_id, batchid, parentid, userid);
			}
			case CMD_MYDOWNLINE_LVL1:
			{
				return affiliate.MyDownlineLvl1(socket, system_id, userid);
			}
			case CMD_MYUPLINE:
			{
				return affiliate.MyUpline(socket, system_id, userid);
			}
			case CMD_MYTOPCLOSE:
			{
				return affiliate.MyTopClose(socket, system_id, userid);
			}
			case CMD_MYRANKRULESMISSED:
			{
				return affiliate.MyRankRulesMissed(socket, system_id, userid, search, sort);
			}
			case CMD_MYDOWNRANKSUMLVL1:
			{
				string batchid = pVars->Get("batchid");
				return affiliate.MyDownlineRankSumLvl1(socket, system_id, batchid, userid); 
			}
			case CMD_MYDOWNRANKSUM:
			{
				string batchid = pVars->Get("batchid");
				string generation = pVars->Get("generation");
				return affiliate.MyDownlineRankSum(socket, system_id, batchid, userid, generation);
			}
			case CMD_MYTITLE:
			{	
				string batchid = pVars->Get("batchid");
				return affiliate.MyTitle(socket, system_id, batchid, userid);
			}
			case CMD_MYRECEIPTSUM:
			{	
				string invtype = pVars->Get("invtype");
				string startdate = pVars->Get("startdate");
				string enddate = pVars->Get("enddate");
				return affiliate.MyReceiptSum(socket, system_id, userid, invtype, startdate, enddate);
			}	

			// Downline commands //
			case CMD_DOWNRANKRULESMISSED:
			{
				return affiliate.MyDownlineRankRulesMissed(socket, system_id, userid, search, sort);
			}

			// These the affiliates needs access to //
			case CMD_QUERYSYSTEMS:
			{
				stringstream ssQuery;
				int tmpsysuser = pDB->GetFirstDB(socket, ssQuery << "SELECT id FROM ce_systemusers WHERE id IN (SELECT sysuser_id FROM ce_systems WHERE id=" << system_id << ")");
				
				CceSystem ceSystem(pDB, pVars->m_Origin);
				string search = pVars->Get("search");
				string sort = pVars->Get("sort");
				return ceSystem.Query(socket, tmpsysuser, search, sort);
			}
			case CMD_QUERYBATCHES:
			{
				string search = pVars->Get("search");
				string sort = pVars->Get("sort");
				CezReports report(pDB, pVars->m_Origin);
				return report.QueryBatches(socket, system_id, search, sort);
			}
			case CMD_GETUSER:
			{
				CceUser ceUser(pDB, pVars->m_Origin);
				string user_id = pVars->Get("userid");
				return ceUser.Get(socket, system_id, user_id);
			}
			case CMD_SETTINGS_GET:
			{
				CceSettings settings(pDB, pVars->m_Origin);
				string webpage = pVars->Get("webpage");
				string userid = pVars->Get("userid");
				string varname = pVars->Get("varname");
				return settings.Get(socket, 0, webpage, userid, varname);
			}
		}

		// Bark invalid command if not found in EndUser commands //
		return SetError(401, "API", "invalid command", "invalid command");
	}

	// Make sure there is a sysuserid //
	if (SystemUserID < 1)
		return SetError(401, "API", "sysuserid is missing", "No sysuserid found");

	/////////////////////////////////////////////////
	// Handle first set of commands. Non-system_id //
	/////////////////////////////////////////////////
	switch (command)
	{
		case CMD_SETTINGS_GET:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string webpage = pVars->Get("webpage");
			string userid = pVars->Get("userid");
			string varname = pVars->Get("varname");
			return settings.Get(socket, 0, webpage, userid, varname);
		}
		case CMD_SETTINGS_SET:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string webpage = pVars->Get("webpage");
			string userid = pVars->Get("userid");
			string varname = pVars->Get("varname");
			string value = pVars->Get("value");
			return settings.Set(socket, webpage, userid, varname, value);
		}
		case CMD_SETTINGS_DISABLE:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string varname = pVars->Get("varname");
			return settings.Disable(socket, varname);
		}
		case CMD_SETTINGS_ENABLE:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string varname = pVars->Get("varname");
			return settings.Enable(socket, varname);
		}
		case CMD_SETTINGS_QUERY:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return settings.Query(socket, search, sort);
		}
		case CMD_SETTINGS_GET_TZ:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string sort = pVars->Get("sort");
			return settings.GetTimeZones(socket, sort);
		}
		case CMD_MYPASSHASHVALID:
		{	
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			string hash = pVars->Get("hash");
			return affiliate.PasswordHashValid(socket, hash);
		}
		case CMD_MYPASSHASHUPDATE:
		{
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			string hash = pVars->Get("hash");
			return affiliate.PasswordHashUpdate(socket, hash);
		}
		case CMD_AUTHSESSIONUSER: // Login a system user //
		{	
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string ipaddress = "0.0.0.0"; //pVars->Get("remote_addr");
			string remoteaddress = pVars->Get("remoteaddress");
			return ceSystemUser.AuthSessionUser(socket, authemail.c_str(), authpass.c_str(), ipaddress.c_str(), remoteaddress); // Return json sessionkey or error //
		}
		case CMD_SYSUSERVALIDCHECK:
		{	
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string email = pVars->Get("email");
			return ceSystemUser.UserValidCheck(socket, email);
		}
		case CMD_PASSHASHSYSUSERGEN:
		{
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string email = pVars->Get("email");
			string remoteaddress = pVars->Get("remoteaddress");
			return ceSystemUser.PasswordHashGen(socket, email, remoteaddress); 
		}
		case CMD_PASSHASHSYSUSERVALID:
		{	
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string hash = pVars->Get("hash");
			return ceSystemUser.PasswordHashValid(socket, hash);
		}
		case CMD_PASSHASHSYSUSERUPDATE:
		{
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string hash = pVars->Get("hash");
			return ceSystemUser.PasswordHashUpdate(socket, hash); 
		}
		case CMD_PASSRESETSYSUSER:
		{
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string sysuser_id = pVars->Get("sysuserid");
			string password = pVars->Get("password");
			return ceSystemUser.ResetPassword(socket, SystemUserID, sysuser_id, password);
		}
		case CMD_LOGOUTSYSUSERLOG:
		{
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string email = pVars->Get("email");
			return ceSystemUser.LogoutLog(socket, email);
		}
		case CMD_ADDSYSTEMUSER:
		{
			if (SystemUserID == 1) // Only allow master account //
			{
				CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
				string firstname = pVars->Get("firstname");
				string lastname = pVars->Get("lastname");
				string email = pVars->Get("email");
				string password = pVars->Get("password");
				string remoteaddress = pVars->Get("remoteaddress"); // Personal account Tracking //
				string serveraddress = pVars->m_RemoteAddr; // Server Tracking //
				return ceSystemUser.Add(socket, firstname, lastname, email, password, remoteaddress, serveraddress); // Add a system user //
			}
			else
				return SetError(401, "API", "The command addsystemuser error", "The command addsystemuser is not allowed");
		}
		case CMD_EDITSYSTEMUSER:
		{			
			CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
			string retsysuserid = pVars->Get("sysuserid");
			int sysuser_id = atoi(retsysuserid.c_str());
			string email = pVars->Get("email");
			string password = pVars->Get("password");
			string ipaddress = pVars->m_RemoteAddr;
			return ceSystemUser.Edit(socket, SystemUserID, sysuser_id, email, password, ipaddress);
		}
		case CMD_QUERYSYSTEMUSERS:
		{
			if (SystemUserID == 1) // Only allow master account //
			{
				CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
				return ceSystemUser.Query(socket);
			}
			else
				return SetError(401, "API", "The command querysystemusers error", "The command querysystemusers is not allowed");
		}
		case CMD_DISABLESYSTEMUSER:
		{
			if (SystemUserID == 1) // Only allow master account //
			{
				CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
				string sysuserid = pVars->Get("sysuserid");
				int sysuser_id = atoi(sysuserid.c_str());
				return ceSystemUser.Disable(socket, SystemUserID, sysuser_id);
			}
			else
				return SetError(401, "API", "The command disablesystemuser error", "The command disablesystemuser is not allowed");
		}
		case CMD_ENABLESYSTEMUSER:
		{
			if (SystemUserID == 1) // Only allow master account //
			{
				CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
				string sysuserid = pVars->Get("sysuserid");
				int sysuser_id = atoi(sysuserid.c_str());
				return ceSystemUser.Enable(socket, SystemUserID, sysuser_id);
			}
			else
				return SetError(401, "API", "The command enablesystemuser error", "The command enablesystemuser is not allowed");
		}
		case CMD_PASSRESETSYSTEMUSER:
		{
			if (SystemUserID == 1) // Only allow master account //
			{
				CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
				string sysuserid = pVars->Get("sysuserid");
				string password = pVars->Get("password");
				return ceSystemUser.ResetPassword(socket, SystemUserID, sysuserid, password);
			}
			else
				return SetError(401, "API", "The command reissueapikey error", "The command passresetsystemuser is not allowed");
		}
		case CMD_REISSUEAPIKEY:
		{
			//if (SystemUserID == 1) // Only allow master account //
			//{
				CceSystemUser ceSystemUser(pDB, pVars->m_Origin);
				//string sysuserid = pVars->Get("sysuserid");
				//return ceSystemUser.ReissueApiKey(SystemUserID, sysuserid);
				return ceSystemUser.ReissueApiKey(socket, SystemUserID);
			//}
			//else
			//	return SetError(401, "API", "The command reissueapikey error", "The command reissueapikey is not allowed");
		}
		case CMD_ADDSYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			string stacktype = pVars->Get("stacktype");
			string systemname = pVars->Get("systemname");
			string commtype = pVars->Get("commtype");
			string altcore = pVars->Get("altcore");
			string payout_type = pVars->Get("payouttype");
			string payout_monthday = pVars->Get("payoutmonthday");
			string payout_weekday = pVars->Get("payoutweekday");
			string autoauthgrand = pVars->Get("autoauthgrand");
			string infinitycap = pVars->Get("infinitycap");
			string minpay = pVars->Get("minpay");
			string updated_url = pVars->Get("updatedurl");
			string updated_username = pVars->Get("updatedusername");
			string updated_password = pVars->Get("updatedpassword");
			string signupbonus = pVars->Get("signupbonus");
			string teamgenmax = pVars->Get("teamgenmax");
			string piggyid = pVars->Get("piggyid");
			string psqlimit = pVars->Get("psqlimit");
			string compression = pVars->Get("compression");
			return ceSystem.Add(socket, SystemUserID, stacktype, systemname, commtype, altcore, payout_type, payout_monthday, payout_weekday, autoauthgrand, 
				infinitycap, minpay, updated_url, updated_username, updated_password, signupbonus, teamgenmax, piggyid, psqlimit, compression);
		}
		case CMD_QUERYSYSTEMS:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceSystem.Query(socket, SystemUserID, search, sort);
		}
		case CMD_COUNTSYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			return ceSystem.Count(socket, SystemUserID);
		}
		case CMD_EXIT:
		{
			Debug(DEBUG_WARN, "CezRecv::Process - CMD_EXIT hit");
			usleep(20000000); // 20 second sleep //
			return SetError(401, "API", "Command exit only delays", "Command exit only delays");
			//exit(0); // Only exit on gcov testing purposes // 
		}
	}

	// Future enhancement sockets //
	// If fork/spawn of processes happens here, then copy of heap and stack will keep copy of linked lists complete //
	// Or use static and extern types? //
	// Does this block incoming connections? 
	// Really think this one through //

	// The system ID is required for the next set of commands //
	std::string system_id_str = pVars->Get("systemid");
	if (system_id_str.size() == 0) // Make sure value is passed in //
		return SetError(400, "API", "systemid missing", "A systemid needs to be defined for other commands to work properly");
	if (is_number(system_id_str) == false)
		return SetError(400, "API", "systemid error", "The systemid is not numeric");
	int system_id = atoi(system_id_str.c_str());

	// If not equal, then lookup in database //
	if (g_SystemRights[system_id] != SystemUserID)
	{
		//Debug(DEBUG_ERROR, "system_id", system_id);
		//Debug(DEBUG_ERROR, "g_SystemRights[system_id]", g_SystemRights[system_id]);
		//Debug(DEBUG_ERROR, "SystemUserID", SystemUserID);

		// Check to see if sysuser_id owns system_id //
		if (pDB->IsRightsSystem(socket, system_id, SystemUserID) == false)
			return SetError(403, "API", "system rights error", "You do not have rights to this system");
		else
		{
			g_SystemRights[system_id] = SystemUserID;
		}
	}

	//////////////////////////////////////////////
	// Handle second set of commands. system_id //
	//////////////////////////////////////////////
	switch (command)
	{
		case CMD_MYUSERVALIDCHECK:
		{
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			string email = pVars->Get("email");
			return affiliate.UserValidCheck(socket, system_id, email);
		}
		case CMD_MYPASSHASHGEN:
		{
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			string email = pVars->Get("email");
			string remoteaddress = pVars->Get("remoteaddress");
			return affiliate.PasswordHashGen(socket, system_id, email, remoteaddress);
		}
		case CMD_MYLOGOUTLOG:
		{
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			string email = pVars->Get("email");
			return affiliate.LogoutLog(socket, system_id, email);
		}
		case CMD_MYPASSRESET:
		{
			CceAffiliate affiliate(pDB, pVars->m_Origin);
			string userid = pVars->Get("userid");
			string password = pVars->Get("password");
			return affiliate.PasswordReset(socket, system_id, userid, password);
		}
		case CMD_SETTINGS_SETSYSTEM:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string webpage = pVars->Get("webpage");
			string userid = pVars->Get("userid");
			string varname = pVars->Get("varname");
			string value = pVars->Get("value");
			return settings.SetSystem(socket, system_id, webpage, userid, varname, value);
		}
		case CMD_SETTINGS_QUERYSYSTEM:
		{
			CceSettings settings(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return settings.QuerySystem(socket, system_id, search, sort);
		}
		case CMD_EDITSYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			string stacktype = pVars->Get("stacktype");
			string systemname = pVars->Get("systemname");
			string commtype = pVars->Get("commtype");
			string altcore = pVars->Get("altcore");
			string payout_type = pVars->Get("payouttype");
			string payout_monthday = pVars->Get("payoutmonthday");
			string payout_weekday = pVars->Get("payoutweekday");
			string autoauthgrand = pVars->Get("autoauthgrand");
			string infinitycap = pVars->Get("infinitycap");
			string minpay = pVars->Get("minpay");
			string updated_url = pVars->Get("updatedurl");
			string updated_username = pVars->Get("updatedusername");
			string updated_password = pVars->Get("updatedpassword");
			string signupbonus = pVars->Get("signupbonus");
			string teamgenmax = pVars->Get("teamgenmax");
			string piggyid = pVars->Get("piggyid");
			string psqlimit = pVars->Get("psqlimit");
			string compression = pVars->Get("compression");
			return ceSystem.Edit(socket, SystemUserID, system_id, stacktype, systemname, commtype, altcore, payout_type, payout_monthday, payout_weekday, autoauthgrand,
				infinitycap, minpay, updated_url, updated_username, updated_password, signupbonus, teamgenmax, piggyid, psqlimit, compression);
		}
		case CMD_DISABLESYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			return ceSystem.Disable(socket, system_id);
		}
		case CMD_ENABLESYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			return ceSystem.Enable(socket, system_id);
		}
		case CMD_GETSYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			return ceSystem.Get(socket, system_id);
		}
		case CMD_STATSSYSTEM:
		{
			CceSystem ceSystem(pDB, pVars->m_Origin);
			return ceSystem.Stats(socket, system_id);
		}
/*		
////////////////////////////////////
// Disable for now 				  //
// This way will slow things down //
// Enable if users request or we  //
// Find a faster way 			  //
////////////////////////////////////
		case CMD_ADDAPIKEY:
		{
			CceApiKey ceApiKey;
			string label = pVars->Get("label");
			return ceApiKey.Add(socket, SystemUserID, system_id, label);
		}
		case CMD_EDITAPIKEY:
		{
			CceApiKey ceApiKey;
			string id = pVars->Get("id");
			string notes = pVars->Get("notes");
			return ceApiKey.Edit(socket, SystemUserID, system_id, id, notes);
		}
		case CMD_QUERYAPIKEYS:
		{	
			CceApiKey ceApiKey;
			string search = pVars->Get("search");
			string orderby = pVars->Get("orderby");
			string orderdir = pVars->Get("orderdir");
			string offset = pVars->Get("offset");
			string limit = pVars->Get("limit");
			return ceApiKey.Query(socket, SystemUserID, system_id, search, orderby, orderdir, offset, limit);
		}
		case CMD_DISABLEAPIKEY:
		{
			CceApiKey ceApiKey;
			string id = pVars->Get("id");
			return ceApiKey.Disable(socket, SystemUserID, system_id, id);
		}
		case CMD_ENABLEAPIKEY:
		{
			CceApiKey ceApiKey;
			string id = pVars->Get("id");
			return ceApiKey.Enable(socket, SystemUserID, system_id, id);
		}
*/
		case CMD_ADDUSER:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string parent_id = pVars->Get("parentid");
			string sponsor_id = pVars->Get("sponsorid");
			string signup_date = pVars->Get("signupdate");
			string usertype = pVars->Get("usertype");
			string firstname = pVars->Get("firstname");
			string lastname = pVars->Get("lastname");
			string email = pVars->Get("email");
			string cell = pVars->Get("cell");
			string address = pVars->Get("address");
			string city = pVars->Get("city");
			string state = pVars->Get("state");
			string zip = pVars->Get("zip");
			return ceUser.Add(socket, system_id, user_id, parent_id, sponsor_id, signup_date, usertype, firstname, lastname, email, cell, address, city, state, zip);
		}
		case CMD_EDITUSER:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string parent_id = pVars->Get("parentid");
			string sponsor_id = pVars->Get("sponsorid");
			string signup_date = pVars->Get("signupdate");
			string usertype = pVars->Get("usertype");
			string firstname = pVars->Get("firstname");
			string lastname = pVars->Get("lastname");
			string email = pVars->Get("email");
			string cell = pVars->Get("cell");
			string address = pVars->Get("address");
			string city = pVars->Get("city");
			string state = pVars->Get("state");
			string zip = pVars->Get("zip");
			return ceUser.Edit(socket, system_id, user_id, parent_id, sponsor_id, signup_date, usertype, firstname, lastname, email, cell, address, city, state, zip);
		}
		case CMD_UPDATEUSERADDR:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string address = pVars->Get("address");
			string city = pVars->Get("city");
			string state = pVars->Get("state");
			string zip = pVars->Get("zip");
			return ceUser.UpdateAddress(socket, system_id, user_id, address, city, state, zip);
		}
		case CMD_QUERYUSERS:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceUser.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLEUSER:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceUser.Disable(socket, system_id, user_id);
		}
		case CMD_ENABLEUSER:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceUser.Enable(socket, system_id, user_id);
		}
		case CMD_GETUSER:
		{
			CceUser ceUser(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceUser.Get(socket, system_id, user_id);
		}
		case CMD_ADDRECEIPT:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string receipt_id = pVars->Get("receiptid");
			string user_id = pVars->Get("userid");
			string wholesale_price = pVars->Get("wholesaleprice");
			string retail_price = pVars->Get("retailprice");
			string wholesale_date = pVars->Get("wholesaledate");
			string retail_date = pVars->Get("retaildate");
			string invtype = pVars->Get("invtype");
			string commissionable = pVars->Get("commissionable");
			string metadata_onadd = pVars->Get("metadataonadd");
			string producttype = pVars->Get("producttype");
			return ceReceipt.Add(socket, system_id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, invtype, commissionable, metadata_onadd, producttype);
		}
		case CMD_EDITRECEIPT:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string receipt_id = pVars->Get("receiptid");
			string user_id = pVars->Get("userid");
			string wholesale_price = pVars->Get("wholesaleprice");
			string retail_price = pVars->Get("retailprice");
			string wholesale_date = pVars->Get("wholesaledate");
			string retail_date = pVars->Get("retaildate");
			string invtype = pVars->Get("invtype");
			string commissionable = pVars->Get("commissionable");
			string metadata_onadd = pVars->Get("metadataonadd");
			string producttype = pVars->Get("producttype");
			return ceReceipt.Edit(socket, system_id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, invtype, commissionable, metadata_onadd, producttype);
		}
		case CMD_EDITRECEIPTWID:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string receipt_id = pVars->Get("receiptid");
			string user_id = pVars->Get("userid");
			string wholesale_price = pVars->Get("wholesaleprice");
			string retail_price = pVars->Get("retailprice");
			string wholesale_date = pVars->Get("wholesaledate");
			string retail_date = pVars->Get("retaildate");
			string invtype = pVars->Get("invtype");
			string commissionable = pVars->Get("commissionable");
			string metadata_onadd = pVars->Get("metadataonadd");
			string producttype = pVars->Get("producttype");
			return ceReceipt.EditWID(socket, system_id, id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, invtype, commissionable, metadata_onadd, producttype);
		}
		case CMD_QUERYRECEIPTS:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceReceipt.Query(socket, system_id, search, sort);
		}
		case CMD_QUERYRECEIPTSUM:
		{
			CceReceiptTotals ceReceiptTotals(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceReceiptTotals.QuerySum(socket, system_id, search, sort);
		}
		case CMD_DISABLERECEIPT:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceReceipt.Disable(socket, system_id, id);
		}
		case CMD_ENABLERECEIPT:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceReceipt.Enable(socket, system_id, id);
		}
		case CMD_GETRECEIPT:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceReceipt.Get(socket, system_id, id);
		}
		case CMD_QUERYBREAKDOWN:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceReceipt.QueryBreakdown(socket, system_id, search, sort);
		}
		case CMD_ADDRECEIPTBULK:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string qty = pVars->Get("qty");
			string receipt_id = pVars->Get("receiptid");
			string user_id = pVars->Get("userid");
			string wholesale_price = pVars->Get("wholesaleprice");
			string wholesale_date = pVars->Get("wholesaledate");
			string retail_price = pVars->Get("retailprice");
			string retail_date = pVars->Get("retaildate");
			string inv_type = pVars->Get("invtype");
			string commissionable = pVars->Get("commissionable");
			string metadata = pVars->Get("metadata");
			string producttype = pVars->Get("producttype");
			return ceReceipt.AddBulk(socket, system_id, qty, receipt_id, user_id, wholesale_price, wholesale_date, retail_price, retail_date, inv_type, commissionable, metadata, producttype);
		}
		case CMD_UPDATERECEIPTBULK:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string qty = pVars->Get("qty");
			string receipt_id = pVars->Get("receiptid");
			string user_id = pVars->Get("userid");
			string wholesale_price = pVars->Get("wholesaleprice");
			string wholesale_date = pVars->Get("wholesaledate");
			string retail_price = pVars->Get("retailprice");
			string retail_date = pVars->Get("retaildate");
			string metadata = pVars->Get("metadata");
			string producttype = pVars->Get("producttype");
			return ceReceipt.UpdateBulk(socket, system_id, qty, receipt_id, user_id, wholesale_price, wholesale_date, retail_price, retail_date, metadata, producttype);
		}
		case CMD_COMMISSIONABLERECEIPTBULK:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string userid = pVars->Get("userid");
			string startdate = pVars->Get("startdate");
			string enddate = pVars->Get("enddate");
			string commissionable = pVars->Get("commissionable");
			return ceReceipt.CommissionableBulk(socket, system_id, userid, startdate, enddate, commissionable);
		}
		case CMD_ORDERSUMRECEIPTWHOLE:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string batch_id = pVars->Get("batchid");
			string user_id = pVars->Get("userid");
			return ceReceipt.OrderSumWholesale(socket, system_id, batch_id, user_id);
		}
		case CMD_CANCELRECEIPT:
		{
			CceReceipt ceReceipt(pDB, pVars->m_Origin);
			string receipt_id = pVars->Get("receiptid");
			string metadata_onadd = pVars->Get("metadataonadd");
			return ceReceipt.CancelReceipt(socket, system_id, receipt_id, metadata_onadd);
		}

		case CMD_ADDRECEIPTFILTER:
		{
			CceReceiptsFilter ceReceiptsFilter(pDB, pVars->m_Origin);
			string invtype = pVars->Get("invtype");
			string producttype = pVars->Get("producttype");
			return ceReceiptsFilter.Add(socket, system_id, invtype, producttype);
		}
		case CMD_EDITRECEIPTFILTER:
		{
			CceReceiptsFilter ceReceiptsFilter(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string invtype = pVars->Get("invtype");
			string producttype = pVars->Get("producttype");
			return ceReceiptsFilter.Edit(socket, system_id, id, invtype, producttype);
		}
		case CMD_QUERYRECEIPTFILTER:
		{
			CceReceiptsFilter ceReceiptsFilter(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceReceiptsFilter.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLERECEIPTFILTER:
		{
			CceReceiptsFilter ceReceiptsFilter(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceReceiptsFilter.Disable(socket, system_id, id);
		}
		case CMD_ENABLERECEIPTFILTER:
		{
			CceReceiptsFilter ceReceiptsFilter(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceReceiptsFilter.Enable(socket, system_id, id);
		}
		case CMD_GETRECEIPTFILTER:
		{
			CceReceiptsFilter ceReceiptsFilter(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceReceiptsFilter.Get(socket, system_id, id);
		}

		case CMD_ADDRANKRULE:
		{
			CceRankRule ceRankRule(pDB, pVars->m_Origin);
			string label = pVars->Get("label");
			string rank = pVars->Get("rank");
			string qualify_type = pVars->Get("qualifytype");
			string qualify_threshold = pVars->Get("qualifythreshold");
			string achvbonus = pVars->Get("achvbonus");
			string breakage = pVars->Get("breakage");
			string rulegroup = pVars->Get("rulegroup");
			string maxdacleg = pVars->Get("maxdacleg");
			string sumrankstart = pVars->Get("sumrankstart");
			string sumrankend = pVars->Get("sumrankend");
			string varid = pVars->Get("varid");
			return ceRankRule.Add(socket, system_id, label, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg, sumrankstart, sumrankend, varid);
		}
		case CMD_EDITRANKRULE:
		{
			CceRankRule ceRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string label = pVars->Get("label");
			string rank = pVars->Get("rank");
			string qualify_type = pVars->Get("qualifytype");
			string qualify_threshold = pVars->Get("qualifythreshold");
			string achvbonus = pVars->Get("achvbonus");
			string breakage = pVars->Get("breakage");
			string rulegroup = pVars->Get("rulegroup");
			string maxdacleg = pVars->Get("maxdacleg");
			string sumrankstart = pVars->Get("sumrankstart");
			string sumrankend = pVars->Get("sumrankend");
			string varid = pVars->Get("varid");
			return ceRankRule.Edit(socket, system_id, id, label, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg, sumrankstart, sumrankend, varid);
		}
		case CMD_QUERYRANKRULES:
		{
			CceRankRule ceRankRule(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceRankRule.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLERANKRULE:
		{
			CceRankRule ceRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankRule.Disable(socket, system_id, id);
		}
		case CMD_ENABLERANKRULE:
		{
			CceRankRule ceRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankRule.Enable(socket, system_id, id);
		}
		case CMD_GETRANKRULE:
		{
			CceRankRule ceRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankRule.Get(socket, system_id, id);
		}
		case CMD_QUERYRANKRULESMISSED:
		{
			CceRankRuleMissed ceRankRuleMissed(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceRankRuleMissed.Query(socket, system_id, search, sort);
		}

		case CMD_ADDEXTQUALIFY:
		{
			CceExtQualify extqualify(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string varid = pVars->Get("varid");
			string value = pVars->Get("value");
			string event_date = pVars->Get("eventdate");
			return extqualify.Add(socket, system_id, user_id, varid, value, event_date);
		}
		case CMD_EDITEXTQUALIFY:
		{
			CceExtQualify extqualify(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string id = pVars->Get("id");
			string varid = pVars->Get("varid");
			string value = pVars->Get("value");
			string event_date = pVars->Get("eventdate");
			return extqualify.Edit(socket, system_id, id, user_id, varid, value, event_date);
		}
		case CMD_QUERYEXTQUALIFY:
		{
			CceExtQualify extqualify(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return extqualify.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLEEXTQUALIFY:
		{
			CceExtQualify extqualify(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return extqualify.Disable(socket, system_id, id);
		}
		case CMD_ENABLEEXTQUALIFY:
		{
			CceExtQualify extqualify(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return extqualify.Enable(socket, system_id, id);
		}
		case CMD_GETEXTQUALIFY:
		{
			CceExtQualify extqualify(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return extqualify.Get(socket, system_id, id);
		}

		case CMD_ADDBASICCOMMRULE:
		{
			CceBasicCommRule ceBasicCommRule(pDB, pVars->m_Origin);
			string generation = pVars->Get("generation");
			string qualifytype = pVars->Get("qualifytype");
			string startthreshold = pVars->Get("startthreshold");
			string endthreshold = pVars->Get("endthreshold");
			string invtype = pVars->Get("invtype");
			string event = pVars->Get("event");
			string percent = pVars->Get("percent");
			string modulus = pVars->Get("modulus");
			string paylimit = pVars->Get("paylimit");
			string pv_override = pVars->Get("pvoverride");
			string paytype = pVars->Get("paytype");
			string rank = pVars->Get("rank");
			return ceBasicCommRule.Add(socket, system_id, generation, qualifytype, startthreshold, endthreshold, invtype, event, percent, modulus, paylimit, pv_override, paytype, rank);
		}
		case CMD_EDITBASICCOMMRULE:
		{
			CceBasicCommRule ceBasicCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string generation = pVars->Get("generation");
			string qualifytype = pVars->Get("qualifytype");
			string startthreshold = pVars->Get("startthreshold");
			string endthreshold = pVars->Get("endthreshold");
			string invtype = pVars->Get("invtype");
			string event = pVars->Get("event");
			string percent = pVars->Get("percent");
			string modulus = pVars->Get("modulus");
			string paylimit = pVars->Get("paylimit");
			string pv_override = pVars->Get("pvoverride");
			string paytype = pVars->Get("paytype");
			string rank = pVars->Get("rank");
			return ceBasicCommRule.Edit(socket, system_id, id, generation, qualifytype, startthreshold, endthreshold, invtype, event, percent, modulus, paylimit, pv_override, paytype, rank);
		}
		case CMD_QUERYBASICCOMMRULES:
		{
			CceBasicCommRule ceBasicCommRule(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceBasicCommRule.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLEBASICCOMMRULE:
		{
			CceBasicCommRule ceBasicCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceBasicCommRule.Disable(socket, system_id, id);
		}
		case CMD_ENABLEBASICCOMMRULE:
		{
			CceBasicCommRule ceBasicCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceBasicCommRule.Enable(socket, system_id, id);
		}
		case CMD_GETBASICCOMMRULE:
		{
			CceBasicCommRule ceBasicCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceBasicCommRule.Get(socket, system_id, id);
		}
		case CMD_ADDCOMMRULE:
		{
			CceCommRule ceCommRule(pDB, pVars->m_Origin);
			string rank = pVars->Get("rank");
			string generation = pVars->Get("generation");
			string infinitybonus = pVars->Get("infinitybonus");
			string percent = pVars->Get("percent");
			string dollar = pVars->Get("dollar");
			string invtype = pVars->Get("invtype");
			string event = pVars->Get("event");
			string paytype = pVars->Get("paytype");
			return ceCommRule.Add(socket, system_id, rank, generation, infinitybonus, percent, dollar, invtype, event, paytype);
		}
		case CMD_EDITCOMMRULE:
		{
			CceCommRule ceCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string rank = pVars->Get("rank");
			string generation = pVars->Get("generation");
			string infinitybonus = pVars->Get("infinitybonus");
			string percent = pVars->Get("percent");
			string dollar = pVars->Get("dollar");
			string invtype = pVars->Get("invtype");
			string event = pVars->Get("event");
			string paytype = pVars->Get("paytype");
			return ceCommRule.Edit(socket, system_id, id, rank, generation, infinitybonus, percent, dollar, invtype, event, paytype);
		}
		case CMD_QUERYCOMMRULES:
		{
			CceCommRule ceCommRule(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceCommRule.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLECOMMRULE:
		{
			CceCommRule ceCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceCommRule.Disable(socket, system_id, id);
		}
		case CMD_ENABLECOMMRULE:
		{
			CceCommRule ceCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceCommRule.Enable(socket, system_id, id);
		}
		case CMD_GETCOMMRULE:
		{
			CceCommRule ceCommRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceCommRule.Get(socket, system_id, id);
		}

		case CMD_ADDCMRANKRULE:
		{
			CceCMRankRule ceCMRankRule(pDB, pVars->m_Origin);
			string label = pVars->Get("label");
			string rank = pVars->Get("rank");
			string qualify_type = pVars->Get("qualifytype");
			string qualify_threshold = pVars->Get("qualifythreshold");
			string achvbonus = pVars->Get("achvbonus");
			string breakage = pVars->Get("breakage");
			string rulegroup = pVars->Get("rulegroup");
			string maxdacleg = pVars->Get("maxdacleg");
			string sumrankstart = pVars->Get("sumrankstart");
			string sumrankend = pVars->Get("sumrankend");
			return ceCMRankRule.Add(socket, system_id, label, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg, sumrankstart, sumrankend);
		}
		case CMD_EDITCMRANKRULE:
		{
			CceCMRankRule ceCMRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string label = pVars->Get("label");
			string rank = pVars->Get("rank");
			string qualify_type = pVars->Get("qualifytype");
			string qualify_threshold = pVars->Get("qualifythreshold");
			string achvbonus = pVars->Get("achvbonus");
			string breakage = pVars->Get("breakage");
			string rulegroup = pVars->Get("rulegroup");
			string maxdacleg = pVars->Get("maxdacleg");
			string sumrankstart = pVars->Get("sumrankstart");
			string sumrankend = pVars->Get("sumrankend");
			return ceCMRankRule.Edit(socket, system_id, id, label, rank, qualify_type, qualify_threshold, achvbonus, breakage, rulegroup, maxdacleg, sumrankstart, sumrankend);
		}
		case CMD_QUERYCMRANKRULES:
		{
			CceCMRankRule ceCMRankRule(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceCMRankRule.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLECMRANKRULE:
		{
			CceCMRankRule ceCMRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceCMRankRule.Disable(socket, system_id, id);
		}
		case CMD_ENABLECMRANKRULE:
		{
			CceCMRankRule ceCMRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceCMRankRule.Enable(socket, system_id, id);
		}
		case CMD_GETCMRANKRULE:
		{
			CceCMRankRule ceCMRankRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceCMRankRule.Get(socket, system_id, id);
		}


		case CMD_ADDCMCOMMRULE:
		{
			CceCMCommRule ceCMCommRule(pDB, pVars->m_Origin);
			string rank = pVars->Get("rank");
			string generation = pVars->Get("generation");
			string percent = pVars->Get("percent");
			return ceCMCommRule.Add(socket, system_id, rank, generation, percent);
		}
		case CMD_EDITCMCOMMRULE:
		{
			CceCMCommRule ceCMCommRule(pDB, pVars->m_Origin);
			string cmcommrule_id = pVars->Get("id");
			string rank = pVars->Get("rank");
			string generation = pVars->Get("generation");
			string percent = pVars->Get("percent");
			return ceCMCommRule.Edit(socket, system_id, cmcommrule_id, rank, generation, percent);
		}
		case CMD_QUERYCMCOMMRULES:
		{
			CceCMCommRule ceCMCommRule(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceCMCommRule.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLECMCOMMRULE:
		{
			CceCMCommRule ceCMCommRule(pDB, pVars->m_Origin);
			string cmcommrule_id = pVars->Get("id");
			return ceCMCommRule.Disable(socket, system_id, cmcommrule_id);
		}
		case CMD_ENABLECMCOMMRULE:
		{
			CceCMCommRule ceCMCommRule(pDB, pVars->m_Origin);
			string cmcommrule_id = pVars->Get("id");
			return ceCMCommRule.Enable(socket, system_id, cmcommrule_id);
		}
		case CMD_ADDPOOL:
		{
			CcePools cePools(pDB, pVars->m_Origin);
			string amount = pVars->Get("amount");
			string startdate = pVars->Get("startdate");
			string enddate = pVars->Get("enddate");
			return cePools.Add(socket, system_id, amount, startdate, enddate);
		}
		case CMD_EDITPOOL:
		{
			CcePools cePools(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string amount = pVars->Get("amount");
			string startdate = pVars->Get("startdate");
			string enddate = pVars->Get("enddate");
			return cePools.Edit(socket, system_id, id, amount, startdate, enddate);
		}
		case CMD_QUERYPOOLS:
		{
			CcePools cePools(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return cePools.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLEPOOL:
		{
			CcePools cePools(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePools.Disable(socket, system_id, id);
		}
		case CMD_ENABLEPOOL:
		{
			CcePools cePools(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePools.Enable(socket, system_id, id);
		}
		case CMD_GETPOOL:
		{
			CcePools cePools(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePools.Get(socket, system_id, id);
		}
		case CMD_ADDPOOLRULE:
		{
			CcePoolRule cePoolRule(pDB, pVars->m_Origin);
			string poolid = pVars->Get("poolid");
			string startrank = pVars->Get("startrank");
			string endrank = pVars->Get("endrank");
			string qualifytype = pVars->Get("qualifytype");
			string qualifythreshold = pVars->Get("qualifythreshold");
			return cePoolRule.Add(socket, system_id, poolid, startrank, endrank, qualifytype, qualifythreshold);
		}
		case CMD_EDITPOOLRULE:
		{
			CcePoolRule cePoolRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string startrank = pVars->Get("startrank");
			string endrank = pVars->Get("endrank");
			string qualifytype = pVars->Get("qualifytype");
			string qualifythreshold = pVars->Get("qualifythreshold");
			return cePoolRule.Edit(socket, system_id, id, startrank, endrank, qualifytype, qualifythreshold);
		}
		case CMD_QUERYPOOLRULES:
		{
			CcePoolRule cePoolRule(pDB, pVars->m_Origin);
			string poolid = pVars->Get("poolid");
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return cePoolRule.Query(socket, system_id, poolid, search, sort);
		}
		case CMD_DISABLEPOOLRULE:
		{
			CcePoolRule cePoolRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePoolRule.Disable(socket, system_id, id);
		}
		case CMD_ENABLEPOOLRULE:
		{
			CcePoolRule cePoolRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePoolRule.Enable(socket, system_id, id);
		}
		case CMD_GETPOOLRULE:
		{
			CcePoolRule cePoolRule(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePoolRule.Get(socket, system_id, id);
		}
		case CMD_ADDBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string amount = pVars->Get("amount");
			string bonus_date = pVars->Get("bonusdate");
			return ceBonus.Add(socket, system_id, user_id, amount, bonus_date);
		}
		case CMD_EDITBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string user_id = pVars->Get("userid");
			string amount = pVars->Get("amount");
			string bonus_date = pVars->Get("bonusdate");
			return ceBonus.Edit(socket, system_id, id, user_id, amount, bonus_date);
		}
		case CMD_QUERYBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceBonus.Query(socket, system_id, search, sort);
		}
		case CMD_QUERYUSERBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceBonus.QueryUser(socket, system_id, user_id);
		}
		case CMD_DISABLEBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceBonus.Disable(socket, system_id, id);
		}
		case CMD_ENABLEBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceBonus.Enable(socket, system_id, id);
		}
		case CMD_GETBONUS:
		{
			CceBonus ceBonus(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceBonus.Get(socket, system_id, id);
		}

		case CMD_ADDFASTSTART:
		{
			CceFastStart ceFastStart(pDB, pVars->m_Origin);
			string rank = pVars->Get("rank");
			string qualify_type = pVars->Get("qualifytype");
			string qualify_threshold = pVars->Get("qualifythreshold");
			string days_count = pVars->Get("dayscount");
			string bonus = pVars->Get("bonus");
			string rulegroup = pVars->Get("rulegroup");
			return ceFastStart.Add(socket, system_id, rank, qualify_type, qualify_threshold, days_count, bonus, rulegroup);
		}
		case CMD_EDITFASTSTART:
		{
			CceFastStart ceFastStart(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string rank = pVars->Get("rank");
			string qualify_type = pVars->Get("qualifytype");
			string qualify_threshold = pVars->Get("qualifythreshold");
			string days_count = pVars->Get("dayscount");
			string bonus = pVars->Get("bonus");
			string rulegroup = pVars->Get("rulegroup");
			return ceFastStart.Edit(socket, system_id, id, rank, qualify_type, qualify_threshold, days_count, bonus, rulegroup);
		}
		case CMD_QUERYFASTSTART:
		{
			CceFastStart ceFastStart(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceFastStart.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLEFASTSTART:
		{
			CceFastStart ceFastStart(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceFastStart.Disable(socket, system_id, id);
		}
		case CMD_ENABLEFASTSTART:
		{
			CceFastStart ceFastStart(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceFastStart.Enable(socket, system_id, id);
		}
		case CMD_GETFASTSTART:
		{
			CceFastStart ceFastStart(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceFastStart.Get(socket, system_id, id);
		}

		case CMD_ADDRANKBONUSRULE:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string rank = pVars->Get("rank");
			string bonus = pVars->Get("bonus");
			return ceRankBonusRules.Add(socket, system_id, rank, bonus);
		}
		case CMD_EDITRANKBONUSRULE:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string rank = pVars->Get("rank");
			string bonus = pVars->Get("bonus");
			return ceRankBonusRules.Edit(socket, system_id, id, rank, bonus);
		}
		case CMD_QUERYRANKBONUSRULE:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceRankBonusRules.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLERANKBONUSRULE:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankBonusRules.Disable(socket, system_id, id);
		}
		case CMD_ENABLERANKBONUSRULE:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankBonusRules.Enable(socket, system_id, id);
		}
		case CMD_GETRANKBONUSRULE:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankBonusRules.Get(socket, system_id, id);
		}
		case CMD_QUERYRANKBONUS:
		{
			CceRankBonusRules ceRankBonusRules(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceRankBonusRules.QueryBonus(socket, system_id, search, sort);
		}

		case CMD_ADDRANKGENBONUSRULE:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string myrank = pVars->Get("myrank");
			string userrank = pVars->Get("userrank");
			string generation = pVars->Get("generation");
			string bonus = pVars->Get("bonus");
			return ceRankGenBonusRules.Add(socket, system_id, myrank, userrank, generation, bonus);
		}
		case CMD_EDITRANKGENBONUSRULE:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string myrank = pVars->Get("myrank");
			string userrank = pVars->Get("userrank");
			string generation = pVars->Get("generation");
			string bonus = pVars->Get("bonus");
			return ceRankGenBonusRules.Edit(socket, system_id, id, myrank, userrank, generation, bonus);
		}
		case CMD_QUERYRANKGENBONUSRULE:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceRankGenBonusRules.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLERANKGENBONUSRULE:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankGenBonusRules.Disable(socket, system_id, id);
		}
		case CMD_ENABLERANKGENBONUSRULE:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankGenBonusRules.Enable(socket, system_id, id);
		}
		case CMD_GETRANKGENBONUSRULE:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceRankGenBonusRules.Get(socket, system_id, id);
		}
		case CMD_QUERYRANKGENBONUS:
		{
			CceRankGenBonusRules ceRankGenBonusRules(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceRankGenBonusRules.QueryBonus(socket, system_id, search, sort);
		}




		case CMD_QUERYSIGNUPBONUS:
		{
			CceSignupBonus ceSignupBonus(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceSignupBonus.Query(socket, system_id, search, sort);
		}
		case CMD_PREDICTCOMMISSIONS:
		{
			CceCommissions ceCommissions(pDB, pVars->m_Origin);
			string startdate = pVars->Get("startdate");
			string enddate = pVars->Get("enddate");
			return ceCommissions.Predict(socket, system_id, startdate, enddate);
		}
		case CMD_PREDICTGRANDTOTAL:
		{
			CceCommissions ceCommissions(pDB, pVars->m_Origin);
			string startdate = pVars->Get("startdate");
			string enddate = pVars->Get("enddate");
			return ceCommissions.PredictGrandTotal(socket, system_id, startdate, enddate);
		}
		case CMD_CALCCOMMISSIONS:
		{
			CceCommissions ceCommissions(pDB, pVars->m_Origin);
			string startdate = pVars->Get("startdate");
			string enddate = pVars->Get("enddate");
			return ceCommissions.Calc(socket, system_id, startdate, enddate);
		}
		case CMD_QUERYBATCHES:
		{
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryBatches(socket, system_id, search, sort);
		}
		case CMD_QUERYUSERCOMM:
		{
			CceCommissions ceCommissions(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceCommissions.QueryUser(socket, system_id, user_id);
		}
		case CMD_QUERYBATCHCOMM:
		{
			CceCommissions ceCommissions(pDB, pVars->m_Origin);
			string batch_id = pVars->Get("batchid");
			return ceCommissions.QueryComm(socket, system_id, batch_id);
		}
		case CMD_QUERYGRANDPAYOUT:
		{
			CcePayout cePayout(pDB, pVars->m_Origin);
			string authorized = pVars->Get("authorized");
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return cePayout.Query(socket, system_id, authorized, search, sort);
		}
		case CMD_AUTHGRANDPAYOUT:
		{
			CcePayout cePayout(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string authorized = pVars->Get("authorized");
			return cePayout.Auth(socket, system_id, id, authorized);
		}
		case CMD_AUTHGRANDBULK:
		{
			CcePayout cePayout(pDB, pVars->m_Origin);
			return cePayout.AuthBulk(socket, system_id);
		}
		case CMD_DISABLEGRANDPAYOUT:
		{
			CcePayout cePayout(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePayout.Disable(socket, system_id, id);
		}
		case CMD_ENABLEGRANDPAYOUT:
		{
			CcePayout cePayout(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return cePayout.Enable(socket, system_id, id);
		}
		case CMD_QUERYAUDITRANKS:
		{
			string batchid = pVars->Get("batchid");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryAuditRanks(socket, system_id, batchid);
		}
		case CMD_QUERYAUDITUSERS:
		{
			string batchid = pVars->Get("batchid");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryAuditUsers(socket, system_id, batchid);
		}
		case CMD_QUERYAUDITGEN:
		{
			string batchid = pVars->Get("batchid");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryAuditGen(socket, system_id, batchid);
		}
		case CMD_QUERYRANKS:
		{
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryRanks(socket, system_id, search, sort); 
		}
		case CMD_QUERYACHVBONUS:
		{
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryAchvBonus(socket, system_id, search, sort); 
		}

		case CMD_QUERYCOMMISSIONS:
		{
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryCommissions(socket, system_id, search, sort); 
		}
		case CMD_QUERYUSERSTATS:
		{
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryUserStats(socket, system_id, search, sort);
		}
		case CMD_QUERYUSERSTATSLVL1:
		{
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			CezReports report(pDB, pVars->m_Origin);
			return report.QueryUserStatsLvl1(socket, system_id, search, sort);
		}
		case CMD_ADDBANKACCOUNT:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string account_type = pVars->Get("accounttype");
			string routing_number = pVars->Get("routingnumber");
			string account_number = pVars->Get("accountnumber");
			string holder_name = pVars->Get("holdername");
			return ceBankAccount.Add(socket, system_id, user_id, account_type, routing_number, account_number, holder_name);
		}
		case CMD_EDITBANKACCOUNT:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string account_type = pVars->Get("accounttype");
			string routing_number = pVars->Get("routingnumber");
			string account_number = pVars->Get("accountnumber");
			string holder_name = pVars->Get("holdername");
			return ceBankAccount.Edit(socket, system_id, user_id, account_type, routing_number, account_number, holder_name);
		}
		case CMD_QUERYBANKACCOUNTS:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceBankAccount.Query(socket, system_id, search, sort);
		}
		case CMD_DISABLEBANKACCOUNT:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceBankAccount.Disable(socket, system_id, user_id);
		}
		case CMD_ENABLEBANKACCOUNT:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceBankAccount.Enable(socket, system_id, user_id);
		}
		case CMD_GETBANKACCOUNT:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string userid = pVars->Get("userid");
			return ceBankAccount.Get(socket, system_id, userid);
		}
		case CMD_INITIATEVALIDATION:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return ceBankAccount.InitiateValidation(socket, system_id, user_id);
		}
		case CMD_VALIDATEACCOUNT:
		{
			CceBankAccount ceBankAccount(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			string amount1 = pVars->Get("amount1");
			string amount2 = pVars->Get("amount2");
			return ceBankAccount.Validate(socket, system_id, user_id, amount1, amount2);
		}
		case CMD_PROCESSPAYMENTS:
		{
			CcePayments cePayments(pDB, pVars->m_Origin);
			string batch_id = pVars->Get("batchid");
			return cePayments.Process(socket, system_id, batch_id);
		}
		case CMD_QUERYUSERPAYMENTS:
		{
			CcePayments cePayments(pDB, pVars->m_Origin);
			string user_id = pVars->Get("userid");
			return cePayments.QueryUser(socket, system_id, user_id);
		}
		case CMD_QUERYBATCHPAYMENTS:
		{
			CcePayments cePayments(pDB, pVars->m_Origin);
			string batch_id = pVars->Get("batchid");
			return cePayments.QueryBatch(socket, system_id, batch_id);
		}
		case CMD_QUERYNOPAYUSERS:
		{
			CcePayments cePayments(pDB, pVars->m_Origin);
			string batch_id = pVars->Get("batchid");
			return cePayments.QueryNoPay(socket, system_id, batch_id);
		}
		case CMD_QUERYPAYMENTSTOTAL:
		{
			CcePayments cePayments(pDB, pVars->m_Origin);
			return cePayments.QueryPaymentsTotal(socket, system_id);
		}
		case CMD_QUERYPAYMENTS:
		{
			CcePayments cePayments(pDB, pVars->m_Origin);
			return cePayments.QueryPayments(socket, system_id);
		}	
		case CMD_ADDLEDGER:
		{
			CceLedger ceLedger(pDB, pVars->m_Origin);
			string batch_id = pVars->Get("batchid");
			string user_id = pVars->Get("userid");
			string ledger_type = pVars->Get("ledgertype");
			string amount = pVars->Get("amount");
			string event_date = pVars->Get("eventdate");
			return ceLedger.Add(socket, system_id, batch_id, user_id, ledger_type, amount, event_date);
		}
		case CMD_EDITLEDGER:
		{
			CceLedger ceLedger(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			string batch_id = pVars->Get("batchid");
			string user_id = pVars->Get("userid");
			string ledger_type = pVars->Get("ledgertype");
			string amount = pVars->Get("amount");
			string event_date = pVars->Get("eventdate");
			return ceLedger.Edit(socket, system_id, id, batch_id, user_id, ledger_type, amount, event_date);
		}
		case CMD_GETLEDGER:
		{
			CceLedger ceLedger(pDB, pVars->m_Origin);
			string id = pVars->Get("id");
			return ceLedger.Get(socket, system_id, id);
		}
		case CMD_QUERYLEDGER:
		{	
			CceLedger ceLedger(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceLedger.QueryLedger(socket, system_id, search, sort);
		}
		case CMD_QUERYLEDGERUSER:
		{
			CceLedger ceLedger(pDB, pVars->m_Origin);
			string userid = pVars->Get("userid");
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceLedger.QueryLedgerUser(socket, system_id, userid, search, sort);
		}
		case CMD_QUERYLEDGERBATCH:
		{
			CceLedger ceLedger(pDB, pVars->m_Origin);
			string batchid = pVars->Get("batchid");
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceLedger.QueryLedgerBatch(socket, system_id, batchid, search, sort);
		}
		case CMD_QUERYLEDGERBALANCE:
		{
			CceLedgerTotals ceLedgerTotals(pDB, pVars->m_Origin);
			string search = pVars->Get("search");
			string sort = pVars->Get("sort");
			return ceLedgerTotals.QueryBalance(socket, system_id, search, sort);
		}
		case CMD_SIM_COPYSEED:
		{
			CceSimulation ceSimulation;
			string simini = pVars->Get("sim");
			string copyseedoption = pVars->Get("copyseedoption");
			string seed_type = pVars->Get("seedtype");
			string users_max = pVars->Get("usersmax");
			string receipts_max = pVars->Get("receiptsmax");
			string min_price = pVars->Get("minprice");
			string max_price = pVars->Get("maxprice");
			string start_date = pVars->Get("startdate");
			string end_date = pVars->Get("enddate");
			return ceSimulation.CopySeed(socket, simini, system_id, copyseedoption, seed_type, users_max, receipts_max, min_price, max_price, start_date, end_date);
		}
		case CMD_RUNSIM:
		{
			CceSimulation ceSimulation;
			string simini = pVars->Get("sim");
			string start_date = pVars->Get("startdate");
			string end_date = pVars->Get("enddate");
			return ceSimulation.Run(socket, simini, system_id, start_date, end_date);
		}
	}

	return SetError(401, "API", "process", "The end of command processing should never be reached");
}

//////////////////////////////////////
// Check all command for comparison //
//////////////////////////////////////
int CezRecv::CheckCommands(const char *string)
{
	if (strcmp(string, POST_AUTHSESSIONUSER) == 0)
		return CMD_AUTHSESSIONUSER;
	if (strcmp(string, POST_MYUSERVALIDCHECK) == 0)
		return CMD_MYUSERVALIDCHECK;
	if (strcmp(string, POST_MYPASSHASHGEN) == 0)
		return CMD_MYPASSHASHGEN;
	if (strcmp(string, POST_MYPASSHASHVALID) == 0)
		return CMD_MYPASSHASHVALID;
	if (strcmp(string, POST_MYPASSHASHUPDATE) == 0)
		return CMD_MYPASSHASHUPDATE;
	if (strcmp(string, POST_MYPASSRESET) == 0)
		return CMD_MYPASSRESET;
	if (strcmp(string, POST_MYLOGOUTLOG) == 0)
		return CMD_MYLOGOUTLOG;
	if (strcmp(string, POST_MYLOGIN) == 0)
		return CMD_MYLOGIN;
	if (strcmp(string, POST_MYJWTVERIFY) == 0)
		return CMD_MYJWTVERIFY;
	if (strcmp(string, POST_MYPROJECTIONS) == 0)
		return CMD_MYPROJECTIONS;
	if (strcmp(string, POST_MYCOMMISSIONS) == 0)
		return CMD_MYCOMMISSIONS;
	if (strcmp(string, POST_MYACHVBONUS) == 0)
		return CMD_MYACHVBONUS;
	if (strcmp(string, POST_MYBONUS) == 0)
		return CMD_MYBONUS;
	if (strcmp(string, POST_MYRANKGENBONUS) == 0)
		return CMD_MYRANKGENBONUS;
	if (strcmp(string, POST_MYLEDGER) == 0)
		return CMD_MYLEDGER;
	if (strcmp(string, POST_MYSTATS) == 0)
		return CMD_MYSTATS;
	if (strcmp(string, POST_MYSTATS_LVL1) == 0)
		return CMD_MYSTATS_LVL1;
	if (strcmp(string, POST_MYDOWNSTATS) == 0)
		return CMD_MYDOWNSTATS;
	if (strcmp(string, POST_MYDOWNSTATSLVL1) == 0)
		return CMD_MYDOWNSTATSLVL1;
	if (strcmp(string, POST_MYDOWNSTATSFULL) == 0)
		return CMD_MYDOWNSTATSFULL;
	if (strcmp(string, POST_MYSPONSOREDSTATS) == 0)
		return CMD_MYSPONSOREDSTATS;
	if (strcmp(string, POST_MYSPONSOREDSTATSLVL1) == 0)
		return CMD_MYSPONSOREDSTATSLVL1;
	if (strcmp(string, POST_MYBREAKDOWN) == 0)
		return CMD_MYBREAKDOWN;
	if (strcmp(string, POST_MYBREAKDOWNGEN) == 0)
		return CMD_MYBREAKDOWNGEN;
	if (strcmp(string, POST_MYBREAKDOWNUSERS) == 0)
		return CMD_MYBREAKDOWNUSERS;
	if (strcmp(string, POST_MYBREAKDOWNORDERS) == 0)
		return CMD_MYBREAKDOWNORDERS;
	if (strcmp(string, POST_MYDOWNLINE_LVL1) == 0)
		return CMD_MYDOWNLINE_LVL1;
	if (strcmp(string, POST_MYUPLINE) == 0)
		return CMD_MYUPLINE;
	if (strcmp(string, POST_MYTOPCLOSE) == 0)
		return CMD_MYTOPCLOSE;
	if (strcmp(string, POST_MYRANKRULESMISSED) == 0)
		return CMD_MYRANKRULESMISSED;
	if (strcmp(string, POST_MYDOWNRANKSUMLVL1) == 0)
		return CMD_MYDOWNRANKSUMLVL1;
	if (strcmp(string, POST_MYDOWNRANKSUM) == 0)
		return CMD_MYDOWNRANKSUM;
	if (strcmp(string, POST_MYTITLE) == 0)
		return CMD_MYTITLE;
	if (strcmp(string, POST_MYRECEIPTSUM) == 0)
		return CMD_MYRECEIPTSUM;
	if (strcmp(string, POST_SETTINGS_GET) == 0)
		return CMD_SETTINGS_GET;
	if (strcmp(string, POST_SETTINGS_QUERY) == 0)
		return CMD_SETTINGS_QUERY;
	if (strcmp(string, POST_SETTINGS_QUERYSYSTEM) == 0)
		return CMD_SETTINGS_QUERYSYSTEM;
	if (strcmp(string, POST_SETTINGS_SET) == 0)
		return CMD_SETTINGS_SET;
	if (strcmp(string, POST_SETTINGS_DISABLE) == 0)
		return CMD_SETTINGS_DISABLE;
	if (strcmp(string, POST_SETTINGS_ENABLE) == 0)
		return CMD_SETTINGS_ENABLE;
	if (strcmp(string, POST_SETTINGS_SETSYSTEM) == 0)
		return CMD_SETTINGS_SETSYSTEM;
	if (strcmp(string, POST_SETTINGS_GET_TZ) == 0)
		return CMD_SETTINGS_GET_TZ;
	if (strcmp(string, POST_DOWNRANKRULESMISSED) == 0)
		return CMD_DOWNRANKRULESMISSED;
	if (strcmp(string, POST_SYSUSERVALIDCHECK) == 0)
		return CMD_SYSUSERVALIDCHECK;
	if (strcmp(string, POST_PASSHASHSYSUSERGEN) == 0)
		return CMD_PASSHASHSYSUSERGEN;
	if (strcmp(string, POST_PASSHASHSYSUSERVALID) == 0)
		return CMD_PASSHASHSYSUSERVALID;
	if (strcmp(string, POST_PASSHASHSYSUSERUPDATE) == 0)
		return CMD_PASSHASHSYSUSERUPDATE;
	if (strcmp(string, POST_PASSRESETSYSUSER) == 0)
		return CMD_PASSRESETSYSUSER;
	if (strcmp(string, POST_LOGOUTSYSUSERLOG) == 0)
		return CMD_LOGOUTSYSUSERLOG;
	if (strcmp(string, POST_ADDSYSTEMUSER) == 0)
		return CMD_ADDSYSTEMUSER;
	if (strcmp(string, POST_EDITSYSTEMUSER) == 0)
		return CMD_EDITSYSTEMUSER;
	if (strcmp(string, POST_QUERYSYSTEMUSERS) == 0)
		return CMD_QUERYSYSTEMUSERS;
	if (strcmp(string, POST_DISABLESYSTEMUSER) == 0)
		return CMD_DISABLESYSTEMUSER;
	if (strcmp(string, POST_ENABLESYSTEMUSER) == 0)
		return CMD_ENABLESYSTEMUSER;
	if (strcmp(string, POST_PASSRESETSYSTEMUSER) == 0)
		return CMD_PASSRESETSYSTEMUSER;
	if (strcmp(string, POST_ADDSYSTEM) == 0)
		return CMD_ADDSYSTEM;
	if (strcmp(string, POST_EDITSYSTEM) == 0)
		return CMD_EDITSYSTEM;
	if (strcmp(string, POST_QUERYSYSTEMS) == 0)
		return CMD_QUERYSYSTEMS;
	if (strcmp(string, POST_DISABLESYSTEM) == 0)
		return CMD_DISABLESYSTEM;
	if (strcmp(string, POST_ENABLESYSTEM) == 0)
		return CMD_ENABLESYSTEM;
	if (strcmp(string, POST_GETSYSTEM) == 0)
		return CMD_GETSYSTEM;
	if (strcmp(string, POST_COUNTSYSTEM) == 0)
		return CMD_COUNTSYSTEM;
	if (strcmp(string, POST_STATSSYSTEM) == 0)
		return CMD_STATSSYSTEM;
	if (strcmp(string, POST_REISSUEAPIKEY) == 0)
		return CMD_REISSUEAPIKEY;
	if (strcmp(string, POST_ADDAPIKEY) == 0)
		return CMD_ADDAPIKEY;
	if (strcmp(string, POST_EDITAPIKEY) == 0)
		return CMD_EDITAPIKEY;
	if (strcmp(string, POST_QUERYAPIKEY) == 0)
		return CMD_QUERYAPIKEYS;
	if (strcmp(string, POST_DISABLEAPIKEY) == 0)
		return CMD_DISABLEAPIKEY;
	if (strcmp(string, POST_ENABLEAPIKEY) == 0)
		return CMD_ENABLEAPIKEY;
	if (strcmp(string, POST_ADDUSER) == 0)
		return CMD_ADDUSER;
	if (strcmp(string, POST_EDITUSER) == 0)
		return CMD_EDITUSER;
	if (strcmp(string, POST_UPDATEUSERADDR) == 0)
		return CMD_UPDATEUSERADDR;
	if (strcmp(string, POST_QUERYUSERS) == 0)
		return CMD_QUERYUSERS;
	if (strcmp(string, POST_DISABLEUSER) == 0)
		return CMD_DISABLEUSER;
	if (strcmp(string, POST_ENABLEUSER) == 0)
		return CMD_ENABLEUSER;
	if (strcmp(string, POST_GETUSER) == 0)
		return CMD_GETUSER;
	if (strcmp(string, POST_ADDRECEIPT) == 0)
		return CMD_ADDRECEIPT;
	if (strcmp(string, POST_EDITRECEIPT) == 0)
		return CMD_EDITRECEIPT;
	if (strcmp(string, POST_EDITRECEIPTWID) == 0)
		return CMD_EDITRECEIPTWID;
	if (strcmp(string, POST_QUERYRECEIPTS) == 0)
		return CMD_QUERYRECEIPTS;
	if (strcmp(string, POST_QUERYRECEIPTSUM) == 0)
		return CMD_QUERYRECEIPTSUM;
	if (strcmp(string, POST_DISABLERECEIPT) == 0)
		return CMD_DISABLERECEIPT;
	if (strcmp(string, POST_ENABLERECEIPT) == 0)
		return CMD_ENABLERECEIPT;
	if (strcmp(string, POST_GETRECEIPT) == 0)
		return CMD_GETRECEIPT;

	if (strcmp(string, POST_ADDRECEIPTFILTER) == 0)
		return CMD_ADDRECEIPTFILTER;
	if (strcmp(string, POST_EDITRECEIPTFILTER) == 0)
		return CMD_EDITRECEIPTFILTER;
	if (strcmp(string, POST_QUERYRECEIPTFILTER) == 0)
		return CMD_QUERYRECEIPTFILTER;
	if (strcmp(string, POST_DISABLERECEIPTFILTER) == 0)
		return CMD_DISABLERECEIPTFILTER;
	if (strcmp(string, POST_ENABLERECEIPTFILTER) == 0)
		return CMD_ENABLERECEIPTFILTER;
	if (strcmp(string, POST_GETRECEIPTFILTER) == 0)
		return CMD_GETRECEIPTFILTER;

	if (strcmp(string, POST_QUERYBREAKDOWN) == 0)
		return CMD_QUERYBREAKDOWN;
	if (strcmp(string, POST_ADDRECEIPTBULK) == 0)
		return CMD_ADDRECEIPTBULK;
	if (strcmp(string, POST_UPDATERECEIPTBULK) == 0)
		return CMD_UPDATERECEIPTBULK;
	if (strcmp(string, POST_COMMISSIONABLERECEIPTBULK) == 0)
		return CMD_COMMISSIONABLERECEIPTBULK;
	if (strcmp(string, POST_ORDERSUMRECEIPTWHOLE) == 0)
		return CMD_ORDERSUMRECEIPTWHOLE;
	if (strcmp(string, POST_CANCELRECEIPT) == 0)
		return CMD_CANCELRECEIPT;
	if (strcmp(string, POST_ADDRANKRULE) == 0)
		return CMD_ADDRANKRULE;
	if (strcmp(string, POST_EDITRANKRULE) == 0)
		return CMD_EDITRANKRULE;
	if (strcmp(string, POST_QUERYRANKRULES) == 0)
		return CMD_QUERYRANKRULES;
	if (strcmp(string, POST_DISABLERANKRULE) == 0)
		return CMD_DISABLERANKRULE;
	if (strcmp(string, POST_ENABLERANKRULE) == 0)
		return CMD_ENABLERANKRULE;
	if (strcmp(string, POST_GETRANKRULE) == 0)
		return CMD_GETRANKRULE;
	if (strcmp(string, POST_QUERYRANKRULESMISSED) == 0)
		return CMD_QUERYRANKRULESMISSED;

	if (strcmp(string, POST_ADDEXTQUALIFY) == 0)
		return CMD_ADDEXTQUALIFY;
	if (strcmp(string, POST_EDITEXTQUALIFY) == 0)
		return CMD_EDITEXTQUALIFY;
	if (strcmp(string, POST_QUERYEXTQUALIFY) == 0)
		return CMD_QUERYEXTQUALIFY;
	if (strcmp(string, POST_DISABLEEXTQUALIFY) == 0)
		return CMD_DISABLEEXTQUALIFY;
	if (strcmp(string, POST_ENABLEEXTQUALIFY) == 0)
		return CMD_ENABLEEXTQUALIFY;
	if (strcmp(string, POST_GETEXTQUALIFY) == 0)
		return CMD_GETEXTQUALIFY;

	if (strcmp(string, POST_ADDBASICCOMMRULE) == 0)
		return CMD_ADDBASICCOMMRULE;
	if (strcmp(string, POST_EDITBASICCOMMRULE) == 0)
		return CMD_EDITBASICCOMMRULE;
	if (strcmp(string, POST_QUERYBASICCOMMRULES) == 0)
		return CMD_QUERYBASICCOMMRULES;
	if (strcmp(string, POST_DISABLEBASICCOMMRULE) == 0)
		return CMD_DISABLEBASICCOMMRULE;
	if (strcmp(string, POST_ENABLEBASICCOMMRULE) == 0)
		return CMD_ENABLEBASICCOMMRULE;
	if (strcmp(string, POST_GETBASICCOMMRULE) == 0)
		return CMD_GETBASICCOMMRULE;
	if (strcmp(string, POST_ADDCOMMRULE) == 0)
		return CMD_ADDCOMMRULE;
	if (strcmp(string, POST_EDITCOMMRULE) == 0)
		return CMD_EDITCOMMRULE;
	if (strcmp(string, POST_QUERYCOMMRULES) == 0)
		return CMD_QUERYCOMMRULES;
	if (strcmp(string, POST_DISABLECOMMRULE) == 0)
		return CMD_DISABLECOMMRULE;
	if (strcmp(string, POST_ENABLECOMMRULE) == 0)
		return CMD_ENABLECOMMRULE;
	if (strcmp(string, POST_GETCOMMRULE) == 0)
		return CMD_GETCOMMRULE;
	if (strcmp(string, POST_ADDCMRANKRULE) == 0)
		return CMD_ADDCMRANKRULE;
	if (strcmp(string, POST_EDITCMRANKRULE) == 0)
		return CMD_EDITCMRANKRULE;
	if (strcmp(string, POST_QUERYCMRANKRULES) == 0)
		return CMD_QUERYCMRANKRULES;
	if (strcmp(string, POST_DISABLECMRANKRULE) == 0)
		return CMD_DISABLECMRANKRULE;
	if (strcmp(string, POST_ENABLECMRANKRULE) == 0)
		return CMD_ENABLECMRANKRULE;
	if (strcmp(string, POST_GETCMRANKRULE) == 0)
		return CMD_GETCMRANKRULE;
	if (strcmp(string, POST_ADDCMCOMMRULE) == 0)
		return CMD_ADDCMCOMMRULE;
	if (strcmp(string, POST_EDITCMCOMMRULE) == 0)
		return CMD_EDITCMCOMMRULE;
	if (strcmp(string, POST_QUERYCMCOMMRULES) == 0)
		return CMD_QUERYCMCOMMRULES;
	if (strcmp(string, POST_DISABLECMCOMMRULE) == 0)
		return CMD_DISABLECMCOMMRULE;
	if (strcmp(string, POST_ENABLECMCOMMRULE) == 0)
		return CMD_ENABLECMCOMMRULE;
	if (strcmp(string, POST_ADDPOOL) == 0)
		return CMD_ADDPOOL;
	if (strcmp(string, POST_EDITPOOL) == 0)
		return CMD_EDITPOOL;
	if (strcmp(string, POST_QUERYPOOLS) == 0)
		return CMD_QUERYPOOLS;
	if (strcmp(string, POST_DISABLEPOOL) == 0)
		return CMD_DISABLEPOOL;
	if (strcmp(string, POST_ENABLEPOOL) == 0)
		return CMD_ENABLEPOOL;
	if (strcmp(string, POST_GETPOOL) == 0)
		return CMD_GETPOOL;
	if (strcmp(string, POST_ADDPOOLRULE) == 0)
		return CMD_ADDPOOLRULE;
	if (strcmp(string, POST_EDITPOOLRULE) == 0)
		return CMD_EDITPOOLRULE;
	if (strcmp(string, POST_QUERYPOOLRULES) == 0)
		return CMD_QUERYPOOLRULES;
	if (strcmp(string, POST_DISABLEPOOLRULE) == 0)
		return CMD_DISABLEPOOLRULE;
	if (strcmp(string, POST_ENABLEPOOLRULE) == 0)
		return CMD_ENABLEPOOLRULE;
	if (strcmp(string, POST_GETPOOLRULE) == 0)
		return CMD_GETPOOLRULE;
	if (strcmp(string, POST_ADDBONUS) == 0)
		return CMD_ADDBONUS;
	if (strcmp(string, POST_EDITBONUS) == 0)
		return CMD_EDITBONUS;
	if (strcmp(string, POST_QUERYBONUS) == 0)
		return CMD_QUERYBONUS;
	if (strcmp(string, POST_QUERYUSERBONUS) == 0)
		return CMD_QUERYUSERBONUS;
	if (strcmp(string, POST_DISABLEBONUS) == 0)
		return CMD_DISABLEBONUS;
	if (strcmp(string, POST_ENABLEBONUS) == 0)
		return CMD_ENABLEBONUS;
	if (strcmp(string, POST_GETBONUS) == 0)
		return CMD_GETBONUS;

	if (strcmp(string, POST_ADDFASTSTART) == 0)
		return CMD_ADDFASTSTART;
	if (strcmp(string, POST_EDITFASTSTART) == 0)
		return CMD_EDITFASTSTART;
	if (strcmp(string, POST_QUERYFASTSTART) == 0)
		return CMD_QUERYFASTSTART;
	if (strcmp(string, POST_DISABLEFASTSTART) == 0)
		return CMD_DISABLEFASTSTART;
	if (strcmp(string, POST_ENABLEFASTSTART) == 0)
		return CMD_ENABLEFASTSTART;
	if (strcmp(string, POST_GETFASTSTART) == 0)
		return CMD_GETFASTSTART;

	if (strcmp(string, POST_ADDRANKBONUSRULE) == 0)
		return CMD_ADDRANKBONUSRULE;
	if (strcmp(string, POST_EDITRANKBONUSRULE) == 0)
		return CMD_EDITRANKBONUSRULE;
	if (strcmp(string, POST_QUERYRANKBONUSRULE) == 0)
		return CMD_QUERYRANKBONUSRULE;
	if (strcmp(string, POST_DISABLERANKBONUSRULE) == 0)
		return CMD_DISABLERANKBONUSRULE;
	if (strcmp(string, POST_ENABLERANKBONUSRULE) == 0)
		return CMD_ENABLERANKBONUSRULE;
	if (strcmp(string, POST_GETRANKBONUSRULE) == 0)
		return CMD_GETRANKBONUSRULE;
	if (strcmp(string, POST_QUERYRANKBONUS) == 0)
		return CMD_QUERYRANKBONUS;

	if (strcmp(string, POST_ADDRANKGENBONUSRULE) == 0)
		return CMD_ADDRANKGENBONUSRULE;
	if (strcmp(string, POST_EDITRANKGENBONUSRULE) == 0)
		return CMD_EDITRANKGENBONUSRULE;
	if (strcmp(string, POST_QUERYRANKGENBONUSRULE) == 0)
		return CMD_QUERYRANKGENBONUSRULE;
	if (strcmp(string, POST_DISABLERANKGENBONUSRULE) == 0)
		return CMD_DISABLERANKGENBONUSRULE;
	if (strcmp(string, POST_ENABLERANKGENBONUSRULE) == 0)
		return CMD_ENABLERANKGENBONUSRULE;
	if (strcmp(string, POST_GETRANKGENBONUSRULE) == 0)
		return CMD_GETRANKGENBONUSRULE;
	if (strcmp(string, POST_QUERYRANKGENBONUS) == 0)
		return CMD_QUERYRANKGENBONUS;

	if (strcmp(string, POST_QUERYSIGNUPBONUS) == 0)
		return CMD_QUERYSIGNUPBONUS;
	if (strcmp(string, POST_PREDICTCOMMISSIONS) == 0)
		return CMD_PREDICTCOMMISSIONS;
	if (strcmp(string, POST_PREDICTGRANDTOTAL) == 0)
		return CMD_PREDICTGRANDTOTAL;
	if (strcmp(string, POST_CALCCOMMISSIONS) == 0)
		return CMD_CALCCOMMISSIONS;
	if (strcmp(string, POST_QUERYGRANDPAYOUT) == 0)
		return CMD_QUERYGRANDPAYOUT;
	if (strcmp(string, POST_AUTHGRANDPAYOUT) == 0)
		return CMD_AUTHGRANDPAYOUT;
	if (strcmp(string, POST_AUTHGRANDBULK) == 0)
		return CMD_AUTHGRANDBULK;
	if (strcmp(string, POST_DISABLEGRANDPAYOUT) == 0)
		return CMD_DISABLEGRANDPAYOUT;
	if (strcmp(string, POST_ENABLEGRANDPAYOUT) == 0)
		return CMD_ENABLEGRANDPAYOUT;
	if (strcmp(string, POST_QUERYAUDITRANKS) == 0)
		return CMD_QUERYAUDITRANKS;
	if (strcmp(string, POST_QUERYAUDITUSERS) == 0)
		return CMD_QUERYAUDITUSERS;
	if (strcmp(string, POST_QUERYAUDITGEN) == 0)
		return CMD_QUERYAUDITGEN;
	if (strcmp(string, POST_QUERYRANKS) == 0)
		return CMD_QUERYRANKS;
	if (strcmp(string, POST_QUERYACHVBONUS) == 0)
		return CMD_QUERYACHVBONUS;
	if (strcmp(string, POST_QUERYCOMMISSIONS) == 0)
		return CMD_QUERYCOMMISSIONS;
	if (strcmp(string, POST_QUERYUSERSTATS) == 0)
		return CMD_QUERYUSERSTATS;
	if (strcmp(string, POST_QUERYUSERSTATSLVL1) == 0)
		return CMD_QUERYUSERSTATSLVL1;
	if (strcmp(string, POST_QUERYBATCHES) == 0)
		return CMD_QUERYBATCHES;
	if (strcmp(string, POST_QUERYUSERCOMM) == 0)
		return CMD_QUERYUSERCOMM;
	if (strcmp(string, POST_QUERYBATCHCOMM) == 0)
		return CMD_QUERYBATCHCOMM;
	if (strcmp(string, POST_ADDBANKACCOUNT) == 0)
		return CMD_ADDBANKACCOUNT;
	if (strcmp(string, POST_QUERYBANKACCOUNTS) == 0)
		return CMD_QUERYBANKACCOUNTS;
	if (strcmp(string, POST_EDITBANKACCOUNT) == 0)
		return CMD_EDITBANKACCOUNT;
	if (strcmp(string, POST_DISABLEBANKACCOUNT) == 0)
		return CMD_DISABLEBANKACCOUNT;
	if (strcmp(string, POST_ENABLEBANKACCOUNT) == 0)
		return CMD_ENABLEBANKACCOUNT;
	if (strcmp(string, POST_GETBANKACCOUNT) == 0)
		return CMD_GETBANKACCOUNT;
	if (strcmp(string, POST_INITIATEVALIDATION) == 0)
		return CMD_INITIATEVALIDATION;
	if (strcmp(string, POST_VALIDATEACCOUNT) == 0)
		return CMD_VALIDATEACCOUNT;
	if (strcmp(string, POST_PROCESSPAYMENTS) == 0)
		return CMD_PROCESSPAYMENTS;
	if (strcmp(string, POST_QUERYUSERPAYMENTS) == 0)
		return CMD_QUERYUSERPAYMENTS;
	if (strcmp(string, POST_QUERYBATCHPAYMENTS) == 0)
		return CMD_QUERYBATCHPAYMENTS;
	if (strcmp(string, POST_QUERYNOPAYUSERS) == 0)
		return CMD_QUERYNOPAYUSERS;
	if (strcmp(string, POST_QUERYPAYMENTSTOTAL) == 0)
		return CMD_QUERYPAYMENTSTOTAL;
	if (strcmp(string, POST_QUERYPAYMENTS) == 0)
		return CMD_QUERYPAYMENTS;
	if (strcmp(string, POST_ADDLEDGER) == 0)
		return CMD_ADDLEDGER;
	if (strcmp(string, POST_EDITLEDGER) == 0)
		return CMD_EDITLEDGER;
	if (strcmp(string, POST_GETLEDGER) == 0)
		return CMD_GETLEDGER;
	if (strcmp(string, POST_QUERYLEDGER) == 0)
		return CMD_QUERYLEDGER;
	if (strcmp(string, POST_QUERYLEDGERUSER) == 0)
		return CMD_QUERYLEDGERUSER;
	if (strcmp(string, POST_QUERYLEDGERBATCH) == 0)
		return CMD_QUERYLEDGERBATCH;
	if (strcmp(string, POST_QUERYLEDGERBALANCE) == 0)
		return CMD_QUERYLEDGERBALANCE;
	if (strcmp(string, POST_SIM_COPYSEED) == 0)
		return CMD_SIM_COPYSEED;
	if (strcmp(string, POST_RUNSIM) == 0)
		return CMD_RUNSIM;

	if (strcmp(string, POST_EXIT) == 0)
		return CMD_EXIT;

	return -1; // Error //
}