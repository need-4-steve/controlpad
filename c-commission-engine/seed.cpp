#include "seed.h"
#include "commissions.h"
#include "user.h"
#include "migrations.h"

#include <stdlib.h>
//#include <jsoncpp/json/json.h>
#include <sstream>
//////////////////////////
// Random sort function //
//////////////////////////
bool sort_random(const CUser first, const CUser second)
{
	int user_random = rand() % 100 + 1;
	
	if (user_random <= 50)
		return true;

	return false;
}

/////////////////
// Constructor //
/////////////////
CSeed::CSeed()
{
	m_RankRuleCount = 0;
	m_CommRuleCount = 0;
	m_AltUserCount = 0;
}

//////////////////////////////////////////////////////////
// The United League Rules are universal to each system //
//////////////////////////////////////////////////////////
void CSeed::UnitedLeagueRules(CDb *pDB, int system_id)
{
	/*
	// Rank 1 - Be an active affiliate //
	pDB->AddRankRule(system_id, 1, QLFY_GROUP_SALESUSED, 0, 0, "false", 0, 0);

	// Rank 2 //
	pDB->AddRankRule(system_id, 2, QLFY_AFFILVLONE_COUNT, 5, 0, "false", 1, 0);
	pDB->AddRankRule(system_id, 2, QLFY_GROUP_SALESUSED, 25000, 0, "false", 1, 12500);
	// pDB->AddRankRule(start_system_id+1, 2, 2, 35000, 0, "false", 1, 17500);

	// Rank 3 - Nataline wants to see test data before //
	//AddRankRule(1, 3, QLFY_AFFILIATE_COUNT, 10, 0, "false", 0, 0);
	pDB->AddRankRule(system_id, 3, QLFY_AFFILVLONE_COUNT, 5, 0, "false", 2, 0);
	pDB->AddRankRule(system_id, 3, QLFY_GROUP_SALESUSED, 75000, 0, "false", 2, 37500);

	// Rank 4 - Nataline wants to see test data before //
	//AddRankRule(1, 4, QLFY_AFFILIATE_COUNT, 20, 0, "false", 0, 0);
	pDB->AddRankRule(system_id, 4, QLFY_AFFILVLONE_COUNT, 5, 0, "false", 3, 0);
	pDB->AddRankRule(system_id, 4, QLFY_GROUP_SALESUSED, 225000, 0, "false", 3, 112500);

	//const char *AddRankRule(int system_id, int rank, int qualify_type, double qualify_threshold, double achvbonus, const char *breakage, int rulegroup, int maxdacleg);
	
	// Commission rules //

	// Zone 1 //
	pDB->AddCommRule(system_id, 1, 1, 1, 0, 0, "false", 10);
	pDB->AddCommRule(system_id, 1, 2, 2, 0, 0, "false", 5);

	// Zone 2 //
	pDB->AddCommRule(system_id, 2, 1, 1, 0, 0, "false", 10);
	pDB->AddCommRule(system_id, 2, 2, 2, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 2, 3, 3, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 2, 4, 4, 0, 0, "false", 2);

	// Zone 3 // 
	pDB->AddCommRule(system_id, 3, 1, 1, 0, 0, "false", 10);
	pDB->AddCommRule(system_id, 3, 2, 2, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 3, 3, 3, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 3, 4, 4, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 3, 5, 5, 0, 0, "false", 1);
	pDB->AddCommRule(system_id, 3, 6, 6, 0, 0, "false", 1);

	// Zone 4 //
	pDB->AddCommRule(system_id, 4, 1, 1, 0, 0, "false", 10);
	pDB->AddCommRule(system_id, 4, 2, 2, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 4, 3, 3, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 4, 4, 4, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 4, 5, 5, 0, 0, "false", 1);
	pDB->AddCommRule(system_id, 4, 6, 6, 0, 0, "false", 1);
	pDB->AddCommRule(system_id, 4, 7, 7, 0, 0, "false", 1);
	pDB->AddCommRule(system_id, 4, 8, 8, 0, 0, "false", 0.5);
	pDB->AddCommRule(system_id, 4, 9, 9, 0, 0, "false", 0.5);

	// Add CheckMatch Commission Rule //
	pDB->AddCMCommRule(system_id, 1, 1, 1, 20);

	pDB->AddCMCommRule(system_id, 2, 1, 1, 20);
	pDB->AddCMCommRule(system_id, 2, 2, 2, 10);

	pDB->AddCMCommRule(system_id, 3, 1, 1, 20);
	pDB->AddCMCommRule(system_id, 3, 2, 2, 10);
	pDB->AddCMCommRule(system_id, 3, 3, 3, 5);

	pDB->AddCMCommRule(system_id, 4, 1, 1, 20);
	pDB->AddCMCommRule(system_id, 4, 2, 2, 10);
	pDB->AddCMCommRule(system_id, 4, 3, 3, 5);
	pDB->AddCMCommRule(system_id, 4, 4, 4, 5);
	*/
}

//////////////////////////////////////////////////////////
// The United League Rules are universal to each system //
//////////////////////////////////////////////////////////
void CSeed::UnitedLeagueGameRules(CDb *pDB, int system_id)
{
	int socket = 0;

	// Rank 1 - Be an active affiliate //
/*	pDB->AddRankRule(system_id, 1, QLFY_GROUP_SALES, 0, 0, "false", 0, 0);

	// Rank 2 //
	pDB->AddRankRule(system_id, 2, QLFY_AFFILVLONE_COUNT, 5, 0, "false", 1, 0);
	pDB->AddRankRule(system_id, 2, QLFY_GROUP_SALES, 25000, 0, "false", 1, 12500);
	// pDB->AddRankRule(start_system_id+1, 2, 2, 35000, 0, "false", 1, 17500);

	// Rank 3 - Nataline wants to see test data before //
	//AddRankRule(1, 3, QLFY_AFFILIATE_COUNT, 10, 0, "false", 0, 0);
	pDB->AddRankRule(system_id, 3, QLFY_AFFILVLONE_COUNT, 5, 0, "false", 2, 0);
	pDB->AddRankRule(system_id, 3, QLFY_GROUP_SALES, 75000, 0, "false", 2, 37500);

	// Rank 4 - Nataline wants to see test data before //
	//AddRankRule(1, 4, QLFY_AFFILIATE_COUNT, 20, 0, "false", 0, 0);
	pDB->AddRankRule(system_id, 4, QLFY_AFFILVLONE_COUNT, 5, 0, "false", 3, 0);
	pDB->AddRankRule(system_id, 4, QLFY_GROUP_SALES, 225000, 0, "false", 3, 112500);
*/
	if (m_CommRuleCount == 0)
	{
		m_CommRulesSS << "INSERT INTO ce_commrules (system_id, rank, start_gen, end_gen, percent, infinitybonus) VALUES";
		m_CommRulesSS << " (" << system_id << ", 1, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 2, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 2, 2, 2, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 3, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 3, 2, 2, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 3, 3, 3, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 2, 2, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 3, 3, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 4, 4, 1, 'false')";
		m_CommRuleCount+=7;
	}
	else
	{
		m_CommRulesSS << ",(" << system_id << ", 1, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 2, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 2, 2, 2, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 3, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 3, 2, 2, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 3, 3, 3, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 1, 1, 5, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 2, 2, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 3, 3, 2, 'false')";
		m_CommRulesSS << ",(" << system_id << ", 4, 4, 4, 1, 'false')";
		m_CommRuleCount+=7;
	}

	if (m_CommRuleCount > MAX_SQL_APPEND)
	{
		std::string tmpstr; 
		tmpstr = m_CommRulesSS.str();
		pDB->ExecDB(socket, tmpstr.c_str());
		m_CommRulesSS.str("");
		m_CommRuleCount = 0;
	}
}

/////////////////////////////////////
// Generate base for United League //
/////////////////////////////////////
void CSeed::UnitedLeagueGenBase(CDb *pDB, int start_system_id)
{
/*
	int socket = -1;

	// Build the commission plan //
	pDB->AddSystemUser(socket, "unitedfirst", "unitedlast", "master@unitedleague.com", "This.is.a.test.2345677800", "127.0.0.1");

	/////////////////////
	// Tokens Acquired //
	/////////////////////
	pDB->AddSystem(socket, "UNITED_CORE", 1, ALTCORE_UNITED_MAIN, 1, 1, 10, 0, "false", 0, "0", "", "", ""); // 1 - monthly, 2 - weekly, 3 - daily
	UnitedLeagueRules(pDB, 1);
*/
	/////////////////////////////////////
	// Recognition Level qualificatons //
	/////////////////////////////////////
	// Needs tweak //

	// Level, 	Purchased token volume, Spent token volume, combined token volume //
	// Amateur - ---, ---, ---
	// Semi-Pro - 2000, 2000, 3000
	// Professional - 5000, 5000, 75000
	// Asst. Coach - 10000, 10000, 15000
	// Head Coach - 25000, 25000, 37500
	// Manager - TBA
	// General Manager - TBA
	// Owner - TBA
	// Commissioner - TBA

	// Personal sponsorship does not compress //
} 

/////////////////////////////////////
// Generate data for United League //
/////////////////////////////////////
bool CSeed::UnitedLeagueGenData(CDb *pDB, int start_system_id)
{
	/*
	int socket = -1;

	Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - TOP");

	///////////////////////////////////////
	// Tokens Played // Inline GAME play //
	///////////////////////////////////////
	pDB->AddSystem(socket, "GAME.1", 1, ALTCORE_UNITED_GAME, 1, 1, 10, 0, "false", 0, "0", "", "", ""); // 1 - monthly, 2 - weekly, 3 - daily
	UnitedLeagueGameRules(pDB, 2);

	// Drop the INDEXES before we do anything //
	CMigrations migrate(pDB);
	migrate.DropIndexes();

//	pDB->AddPoolPot(start_system_id, 2, 5000, "2016-7-1", "2016-7-31");
	// const char *AddPoolPot(int system_id, int qualify_type, int amount, const char *startdate, const char *enddate);

//	pDB->AddPoolRule(start_system_id, 1, 1, 1, 50);
	// const char *AddPoolRule(int system_id, int poolpotid, int startrank, int endrank, int qualifythreshold);

	// Load system 1 and 2 from latest data dump //
	Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - system 9 load users");
	if (system("psql ce < dump/united-users-9.sql") != 0)
		Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - error psql ce < dump/united-users-9.sql");
	Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - system 10 load users");
	if (system("psql ce < dump/united-users-10.sql") != 0)
		Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - error psql ce < dump/united-users-10.sql");

	// Add the random affiliate with 1 million users underneath 5 users //	
//	Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - 5 mill customers");
	//if (system("psql ce < dump/5-mill-sim.sql") != 0)
	//	Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - error psql ce < dump/5-mill-sim.sql");
*/	
	/*
	std::map <std::string, CUser> UsersMap;
	pDB->GetUsers(start_system_id, true, UsersMap, UPLINE_PARENT_ID, "2016-1-1", "2026-1-1");

	std::map <std::string, CUser>::iterator q;
	for (q=UsersMap.begin(); q != UsersMap.end(); ++q) 
	{
		CUser *puser = &UsersMap[q->first]; // This seems to be more accurate //

		// Level 1 - 7 preload //
		UnitedPreload(pDB, puser->m_UserType, puser->m_UserID, "2016-8-15"); // The first 7 preload //

		// Level 2 - 7 preload //
		int index;
		for (index=1; index <= 7; index++)
		{
			//string user_id_lvl2 = puser->m_UserID+"-1-1";
			std::stringstream userid_lvl2;
			userid_lvl2 << puser->m_UserID << "-" << index;
			UnitedPreload(pDB, puser->m_UserType, userid_lvl2.str(), "2016-8-15"); // The first 7 preload //

			// Level 3 - 7 preload //
			//int count;
			//for (count=1; count <= 7; count++)
			//{
			//	std::stringstream userid_lvl3;
			//	userid_lvl3 << puser->m_UserID << "-" << index << "-" << count;
			//	UnitedPreload(pDB, puser->m_UserType, userid_lvl3.str(), "2016-8-15"); // The first 7 preload //
			//}
		}
	}

	if (m_AltUserCount > 0)
	{
		std::string tmpstr;
		tmpstr = m_AddUsersStr.str();
		pDB->ExecDB(tmpstr.c_str());
		m_AltUserCount = 0;
		m_AddUsersStr.str("");
	}
	*/

	// Rebuild the levels table cause we took a short cut on importing user accounts //
//    pDB->RebuildAllLevels(start_system_id); // This takes 15 minutes to generate. Only rebuild if needed for now //
/*
    // Add in receipts for August //
    Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - system 1 load receipts");
    pDB->ExecDB("INSERT INTO ce_receipts(system_id, receipt_id, user_id, amount, purchase_date, commissionable, usertype) SELECT system_id, id, user_id, '10', now(), true, usertype FROM ce_users WHERE usertype='1' AND system_id=1"); //" AND id < 1000000");
    pDB->ExecDB("INSERT INTO ce_receipts(system_id, receipt_id, user_id, amount, purchase_date, commissionable, usertype) SELECT system_id, id, user_id, '7', now(), true, usertype FROM ce_users WHERE usertype='2' AND system_id=1"); // AND id < 1000000");

    Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - system 2 load receipts");
    pDB->ExecDB("INSERT INTO ce_receipts(system_id, receipt_id, user_id, amount, purchase_date, commissionable, usertype) SELECT system_id, id, user_id, '3.5', now(), true, usertype FROM ce_users WHERE usertype='1' AND system_id=2"); //" AND id < 1000000");
    pDB->ExecDB("INSERT INTO ce_receipts(system_id, receipt_id, user_id, amount, purchase_date, commissionable, usertype) SELECT system_id, id, user_id, '2.45', now(), true, usertype FROM ce_users WHERE usertype='2' AND system_id=2"); // AND id < 1000000");

    //////////////////////////
    // Build full user list //
    //////////////////////////
    std::stringstream ss;
    if (pDB->ExecDB(ss << "SELECT user_id, sponsor_id, usertype FROM ce_users WHERE system_id='1' ORDER BY random() < 0.01") == false)
    	Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB Error - Grab All Users");
    Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - Build random games");
    std::list <CUser> FullUsersList;
    while (pDB->FetchRow() != false)
    {
    	std::string user_id = pDB->RowMap(0);
    	int usertype = atoi(pDB->RowMap(2));

    	CUser newuser;
		newuser.m_UserID = user_id;
		newuser.m_UserType = usertype;
		FullUsersList.push_back(newuser);
    }

    ////////////////////////////////////////////
    // Build each random system AND add users //
    ////////////////////////////////////////////
    std::string strInsertSystemsSQL;
    std::string strInsertUsersSQL;
    std::string strInsertReceiptsSQL;
    int InsertSystemCount = 0;
    int InsertUserCount = 0;
    int InsertReceiptCount = 0;
    int member_count = 0;
    std::string top_player;
    std::string game_name;
    int game_count = 2;
    std::list <CUser> SysUsersList;
    std::list <CUser>::iterator i;
    int receiptcount = 6275608+1;
	for (i=FullUsersList.begin(); i != FullUsersList.end(); ++i) 
	{
    	std::string user_id = (*i).m_UserID;
    	int usertype = (*i).m_UserType;
    	std::string sponsor_id;

    	if (member_count == 0)
    	{
    		SysUsersList.clear();
    		CUser newuser;
			newuser.m_UserID = user_id;
			newuser.m_UserType = usertype;
			SysUsersList.push_back(newuser);
    		top_player = user_id;
    		member_count = rand() % 2000 + 5; // 5 to 2000 people in a game //
    		std::stringstream ss9;
    		ss9 << "game." << game_count;
    		game_name = ss9.str();
    		game_count++;

    		// INSERT systems //
    		std::stringstream ssSystem;
    		if (strInsertSystemsSQL.size() == 0)
				ssSystem << "INSERT INTO ce_systems (system_name, commtype, altcore, sysuser_id, payout_type, payout_monthday, payout_weekday, autoauthgrand, infinitycap, updated_url, updated_username, updated_password) VALUES ('" << game_name.c_str() << "', '1', '" << ALTCORE_UNITED_GAME << "', '1', '1', '10', '0', 'false', '0', '', '', '') ";
			else
    			ssSystem << ", ('" << game_name.c_str() << "', '1', '" << ALTCORE_UNITED_GAME << "', '1', '1', '10', '0', 'false', '0', '', '', '') ";
    		strInsertSystemsSQL += ssSystem.str();
    		InsertSystemCount++;

			// INSERT users //
			std::stringstream ssUser;
			if (strInsertUsersSQL.size() == 0)
				ssUser << "INSERT INTO ce_users (system_id, user_id, usertype, parent_id, sponsor_id, signup_date) VALUES (" << game_count << ", '" << user_id << "', " << usertype << ", '0', '0', '2016-8-15')";
			else
    			ssUser << ", (" << game_count << ", '" << user_id << "', '" << usertype << "', '0', '0', '2016-8-15')";
    		strInsertUsersSQL += ssUser.str();
    		InsertUserCount++;

    		// INSERT receipts //
    		double amount;
			if (usertype == 1)
				amount = 3.50; //3.5;
			else
				amount = 2.45; //2.45;

    		std::stringstream ssReceipts;
			if (strInsertReceiptsSQL.size() == 0)
				ssReceipts << "INSERT INTO ce_receipts (system_id, receipt_id, user_id, usertype, amount, purchase_date, commissionable) VALUES (" << game_count << ", '" << receiptcount << "', '" << user_id << "', " << usertype << ", '" << amount << "', 'now()', 'true')";
			else
    			ssReceipts << ", (" << game_count << ", '" << receiptcount << "', '" << user_id << "', " << usertype << ", '" << amount << "', 'now()', 'true')";
    		strInsertReceiptsSQL += ssReceipts.str();
    		InsertReceiptCount++;

     		sponsor_id = "0";
    	}
    	else
    	{
    		// Build random INSERT into receipts and users into all different game systems //
    		std::list<CUser>::iterator k;
    		unsigned long userlistsize = SysUsersList.size();
    		k=SysUsersList.begin();
    		if (userlistsize > 1)
    		{
    			int random = rand() % userlistsize;
    			if (random != 1)
    			{
    				int index;
    				for (index=0; index < random; index++)
    					k++;
    			}
    		}

			// INSERT users  //
			std::stringstream ssUser;
			if (strInsertUsersSQL.size() == 0)
				ssUser << "INSERT INTO ce_users (system_id, user_id, usertype, parent_id, sponsor_id, signup_date) VALUES (" << game_count << ", '" << user_id << "', " << usertype << ", '" << (*k).m_UserID.c_str() << "', '" << (*k).m_UserID.c_str() << "', 'now()')";
			else
    			ssUser << ",(" << game_count << ", '" << user_id << "', " << usertype << ", '" << (*k).m_UserID.c_str() << "', '" << (*k).m_UserID.c_str() << "', 'now()')";
    		
    		strInsertUsersSQL += ssUser.str();
    		InsertUserCount++;

    		CUser newuser;
			newuser.m_UserID = user_id;
			newuser.m_UserType = usertype;
			SysUsersList.push_back(newuser);

			sponsor_id = (*k).m_UserID.c_str();

			// INSERT receipts //
    		double amount;
			if (atoi((*k).m_UserID.c_str()) == 1)
				amount = 3.50; //3.5;
			else 
				amount = 2.45; //2.45;

    		std::stringstream ssReceipts;
    		if (strInsertReceiptsSQL.size() == 0)
				ssReceipts << "INSERT INTO ce_receipts (system_id, receipt_id, user_id, usertype, amount, purchase_date, commissionable) VALUES (" << game_count << ", " << receiptcount << ", '" << user_id << "', " << usertype << ", '" << amount << "', 'now()', 'true')";
			else
				ssReceipts << ", (" << game_count << ", " << receiptcount << ", '" << user_id << "', " << usertype << ", '" << amount << "', 'now()', 'true')";
    		strInsertReceiptsSQL += ssReceipts.str();
    		InsertReceiptCount++;
    	}

    	// Add User to LL //
    	//CUser newuser;
    	//newuser.m_UserID = user_id;
    	//newuser.m_UserType = usertype;
    	//newuser.m_SponsorID = sponsor_id;
    	//newuser.m_ParentID = sponsor_id;
    	//newuser.m_SignupDate = "2016-8-15";
    	//m_SystemMap[game_count].m_UsersLL.push_back(newuser);

	    if (InsertSystemCount >= MAX_SQL_APPEND)
		{
			Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - InsertSystemCount >= MAX_SQL_APPEND");
			if (pDB->ExecDB(strInsertSystemsSQL.c_str()) == false)
				return Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB Systems Error");
			strInsertSystemsSQL.clear();
			InsertSystemCount = 0;
		}

		if (InsertUserCount >= MAX_SQL_APPEND)
		{
			Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - InsertUserCount >= MAX_SQL_APPEND");
			if (pDB->ExecDB(strInsertUsersSQL.c_str()) == false)
				return Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB User Error");
			strInsertUsersSQL.clear();
			InsertUserCount = 0;
		}

		if (InsertReceiptCount >= MAX_SQL_APPEND)
		{
			Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - InsertReceiptCount >= MAX_SQL_APPEND");
			if (pDB->ExecDB(strInsertReceiptsSQL.c_str()) == false)
				return Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB User Error");
			strInsertReceiptsSQL.clear();
			InsertReceiptCount = 0;
		}

    	member_count--;
		receiptcount++;
    }

    //////////////////////
    // Do final cleanup //
    //////////////////////
	if (pDB->ExecDB(strInsertSystemsSQL.c_str()) == false)
		return Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB Systems Error");
	strInsertSystemsSQL.clear();
	InsertSystemCount = 0;

	if (pDB->ExecDB(strInsertUsersSQL.c_str()) == false)
		return Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB User Error");
	strInsertUsersSQL.clear();
	InsertUserCount = 0;

	if (pDB->ExecDB(strInsertReceiptsSQL.c_str()) == false)
		return Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB User Error");
	strInsertReceiptsSQL.clear();
	InsertReceiptCount = 0;
	
    Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - Done seeding data");
    if (pDB->ExecDB("SELECT id FROM ce_systems WHERE id!=1 AND id!=2 ORDER BY id") == false)
    	Debug(DEBUG_ERROR, "CSeed::UnitedLeagueGenData - ExecDB - SELECT FROM systems");

    std::list <CSystem> SystemsList;
    while (pDB->FetchRow() == true)
    {
    	int system_id = atoi(pDB->RowMap(0));

    	CSystem newsystem;
		newsystem.m_SystemID = system_id;
    	SystemsList.push_back(newsystem);
    }

    // Finish building games //
    std::stringstream ssReceipt;
    std::list<CSystem>::iterator j;
	for (j=SystemsList.begin(); j != SystemsList.end(); ++j) 
	{
		UnitedLeagueGameRules(pDB, (*j).m_SystemID); // Add the rules here. It's easier here than above //
    }

    if (m_CommRuleCount > 0)
	{
		std::string tmpstr; 
		tmpstr = m_CommRulesSS.str();
		pDB->ExecDB(tmpstr.c_str());
		m_CommRulesSS.str("");
		m_CommRuleCount = 0;
	}

	// Make sure memory is free'd up //
	SysUsersList.clear();
    //m_SystemMap.clear();

    // Fix sponsor_id recursion problem //
    //pDB->ExecDB("UPDATE ce_users set sponsor_id=parent_id WHERE system_id<=2 AND user_id='287310'");

    // RE-add the indexes //
    migrate.AddIndexes();

    //pDB->ExecDB("INSERT INTO ce_bonus (system_id, user_id, amount, bonus_date) VALUES (1, '88', 88.88, 'now()'), (1, '99', 99.99, 'now()')");

    //Debug(DEBUG_DEBUG, "CSeed::UnitedLeagueGenData - CronCommissions");
    //int retval2 = system("psql ce < dump/ce.united.sql");
    //pDB->CronCommissions();
*/
    return true;
}

//////////////////////////////
// Preload multiple players //
//////////////////////////////
void CSeed::UnitedPreload(CDb *pDB, int usertype, std::string user_id, std::string signup_date)
{
	int socket = -1;

	if (0 == 0) // All users //
	//if (puser->m_UserType == 1)
	{
		// Everyone gets 7 players //
		if (m_AltUserCount == 0)
		{
			m_AddUsersStr << "INSERT INTO ce_users (system_id, user_id, sponsor_id, parent_id, usertype, signup_date) VALUES (1, '" << user_id << "-1', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		}
		else
		{
			m_AddUsersStr << ",(1, '" << user_id << "-1', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		}
		m_AddUsersStr << ",(1, '" << user_id << "-2', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(1, '" << user_id << "-3', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(1, '" << user_id << "-4', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(1, '" << user_id << "-5', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(1, '" << user_id << "-6', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(1, '" << user_id << "-7', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";

		m_AddUsersStr << ",(2, '" << user_id << "-1', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(2, '" << user_id << "-2', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(2, '" << user_id << "-3', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(2, '" << user_id << "-4', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(2, '" << user_id << "-5', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(2, '" << user_id << "-6', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";
		m_AddUsersStr << ",(2, '" << user_id << "-7', '" << user_id << "', '" << user_id << "', '2', '" << signup_date << "')";

		// '2016-8-15' //

		m_AltUserCount += 20;
		if (m_AltUserCount > 5000)
		{
			std::string tmpstr;
			tmpstr = m_AddUsersStr.str();
			pDB->ExecDB(socket, tmpstr.c_str());
			m_AltUserCount = 0;
			m_AddUsersStr.str("");
		}
	}
}

/////////////////////////////////////
// Generate base plan for Hope5000 //
/////////////////////////////////////
void CSeed::Hope5000GenBase(CDb *pDB, int system_id)
{
	/*
	pDB->AddSystemUser("testuser@hope5000.com", "This.is.a.test.2345677800", "127.0.0.1");
	pDB->AddSystem("Hope5000", 1, 0, 1, 1, 15, 6, "true", 2, "0", "", "", "");
	
		// Rank 0 // Only AchvBonus //
	pDB->AddRankRule(system_id, 0, 4, 0, 40, "false", 1, 0);
	pDB->AddRankRule(system_id, 0, 5, 10, 40, "false", 1, 0);
	
	// Rank 1 //
	pDB->AddRankRule(system_id, 1, 8, 3, 0, "false", 2, 10);
	pDB->AddRankRule(system_id, 1, 7, 10, 0, "false", 2, 10);
	pDB->AddRankRule(system_id, 1, 5, 5, 0, "false", 2, 10);
	pDB->AddRankRule(system_id, -1, 4, 1, 40, "false", 3, 10);
	pDB->AddRankRule(system_id, -1, 5, 5, 40, "false", 3, 10);

	// Rank 2 //
	pDB->AddRankRule(system_id, 2, 8, 4, 0, "false", 4, 25);
	pDB->AddRankRule(system_id, 2, 7, 25, 0, "false", 4, 25);
	pDB->AddRankRule(system_id, 2, 5, 13, 0, "false", 4, 25);
	pDB->AddRankRule(system_id, -2, 4, 2, 40, "false", 5, 25);
	pDB->AddRankRule(system_id, -2, 5, 30, 40, "false", 5, 25);

	// Rank 3 //
	pDB->AddRankRule(system_id, 3, 8, 5, 0, "false", 6, 63);
	pDB->AddRankRule(system_id, 3, 7, 75, 0, "false", 6, 63);
	pDB->AddRankRule(system_id, 3, 5, 38, 0, "false", 6, 63);
	pDB->AddRankRule(system_id, -3, 4, 3, 40, "false", 7, 63);
	pDB->AddRankRule(system_id, -3, 5, 40, 40, "false", 7, 63);

	// Rank 4 //
	pDB->AddRankRule(system_id, 4, 8, 6, 0, "false", 8, 125);
	pDB->AddRankRule(system_id, 4, 7, 200, 0, "false", 8, 125);
	pDB->AddRankRule(system_id, 4, 5, 100, 0, "false", 8, 125);
	pDB->AddRankRule(system_id, -4, 4, 4, 40, "false", 9, 125);
	pDB->AddRankRule(system_id, -4, 5, 50, 40, "false", 9, 125);

	// Rank 5 //
	pDB->AddRankRule(system_id, 5, 8, 8, 0, "false", 10, 250);
	pDB->AddRankRule(system_id, 5, 7, 500, 0, "false", 10, 250);
	pDB->AddRankRule(system_id, 5, 5, 250, 0, "false", 10, 250);
	pDB->AddRankRule(system_id, -5, 4, 5, 40, "false", 11, 250);
	pDB->AddRankRule(system_id, -5, 5, 60, 40, "false", 11, 250);

	// Rank 6 //
	pDB->AddRankRule(system_id, 6, 8, 8, 0, "false", 12, 500);
	pDB->AddRankRule(system_id, 6, 7, 1000, 0, "false", 12, 500);
	pDB->AddRankRule(system_id, 6, 5, 500, 0, "false", 12, 500);
	pDB->AddRankRule(system_id, -6, 4, 6, 40, "false", 13, 500);
	pDB->AddRankRule(system_id, -6, 5, 70, 40, "false", 13, 500);

	// Rank 7 //
	pDB->AddRankRule(system_id, 7, 8, 8, 0, "false", 14, 1250);
	pDB->AddRankRule(system_id, 7, 7, 2500, 0, "false", 14, 1250);
	pDB->AddRankRule(system_id, 7, 5, 1250, 0, "false", 14, 1250);
	pDB->AddRankRule(system_id, -7, 4, 7, 40, "false", 15, 1250);
	pDB->AddRankRule(system_id, -7, 5, 80, 40, "false", 15, 1250);

	// Commission rules //
	pDB->AddCommRule(system_id, 0, 1, 1, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 1, 1, 1, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 2, 1, 1, 0, 0, "false", 6);
	pDB->AddCommRule(system_id, 2, 2, 2, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 3, 1, 1, 0, 0, "false", 7);
	pDB->AddCommRule(system_id, 3, 2, 2, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 3, 3, 3, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 4, 1, 1, 0, 0, "false", 8);
	pDB->AddCommRule(system_id, 4, 2, 2, 0, 0, "false", 4);
	pDB->AddCommRule(system_id, 4, 3, 3, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 5, 1, 1, 0, 0, "false", 8);
	pDB->AddCommRule(system_id, 5, 2, 2, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 5, 3, 3, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 5, 4, 4, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 6, 1, 1, 0, 0, "false", 8);
	pDB->AddCommRule(system_id, 6, 2, 2, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 6, 3, 3, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 6, 4, 4, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 6, 5, 5, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 6, 6, 6, 0, 0, "true", 0.5);
	pDB->AddCommRule(system_id, 7, 1, 1, 0, 0, "false", 8);
	pDB->AddCommRule(system_id, 7, 2, 2, 0, 0, "false", 5);
	pDB->AddCommRule(system_id, 7, 3, 3, 0, 0, "false", 3);
	pDB->AddCommRule(system_id, 7, 4, 4, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 7, 5, 5, 0, 0, "false", 2);
	pDB->AddCommRule(system_id, 7, 6, 6, 0, 0, "true", 1);

	pDB->AddPoolPot(system_id, 2, 5000, "2016-7-1", "2016-7-31");
	pDB->AddPoolRule(system_id, 1, 1, 1, 50);
	*/
}

////////////////////////////////
// Generate data for Hope5000 //
////////////////////////////////
void CSeed::Hope5000GenData(CDb *pDB, int system_id)
{
	/*
	// Add the users //
	int count;
	int sponsor_id = 0;
	for (count=1; count <= 100; count++)
	{
		// User_id //
		std::stringstream ss;
		ss << count;
		std::string user_str = ss.str();

		// Prepare the sponsor_id //
		if (count == 1)
			sponsor_id = 0;
		else if ((count >=2) && (count <=11))
			sponsor_id = 1;
		else if ((count >=12) && (count <=21))
			sponsor_id = 2;
		else if ((count >=22) && (count <=31))
			sponsor_id = 3;
		else if (count <=39)
			sponsor_id = 12;
		else if ((count >= 40) && (count < 50))
			sponsor_id = 1;
		else if ((count >= 50) && (count < 60))
			sponsor_id = 2;
		else if ((count >= 60) && (count < 70))
			sponsor_id = 3;
		else
			sponsor_id = 12;

		// Sponsor_id //
		std::stringstream ss2;
		ss2 << sponsor_id;
		std::string spon_str = ss2.str();

		if (count <= 40)
		{
			std::string retstr = pDB->AddUser(system_id, user_str.c_str(), spon_str.c_str(), spon_str.c_str(), "2016-7-30", 1);
		}
		else
		{
			std::string retstr = pDB->AddUser(system_id, user_str.c_str(), spon_str.c_str(), spon_str.c_str(), "2016-7-30", 2);
		}
	}

	// Add the receipts //
	int index;
	for (index=1; index <= 100; index++)
	{
		std::stringstream ss;
		ss << index;
		std::string user_str = ss.str();
		double amount = index;

		pDB->AddReceipt(system_id, index, user_str.c_str(), amount, "2016-7-30", "true");
	}

	// Do Commission Calculations //
	CCommissions comm;

	// This should be the only extrnal function we run //
	comm.Run(pDB, system_id, 1, false, true, "2016-7-1", "2016-7-31");

	//int AddBatch(int system_id, const char *start_date, const char *end_date);
	//comm.RunPool(this, 4, 1, 2, 50, "2016-7-1", "2016-7-31");

	*/
}
