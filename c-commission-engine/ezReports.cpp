#include "ezReports.h"
#include <stdlib.h>

/////////////////
// Constructor //
/////////////////
CezReports::CezReports(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CezJson::SetOrigin(origin);
}

//////////////////////////////////////////////////
// Calculate the data for all the audit reports //
//////////////////////////////////////////////////
bool CezReports::CalcAll(int socket, int system_id, int batch_id)
{
	if (CalcRankAudit(socket, system_id, batch_id) == false)
		return Debug(DEBUG_WARN, "CezReports::CalcAll - CalcRankAudit failed");
	if (CalcUsersAudit(socket, system_id, batch_id) == false)
		return Debug(DEBUG_WARN, "CezReports::CalcAll - CalcUsersAudit failed");
	if (CalcGenerationAudit(socket, system_id, batch_id) == false)
		return Debug(DEBUG_WARN, "CezReports::CalcAll - CalcGenerationAudit failed");

	return true;
}

///////////////////////////////////////
// Pre-calculate the rank audit data //
///////////////////////////////////////
bool CezReports::CalcRankAudit(int socket, int system_id, int batch_id)
{
	stringstream ss0;
	if (m_pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_rankrules WHERE system_id=" << system_id << " AND disabled=false") == 0)
		return Debug(DEBUG_INFO, "CezReports::CalcRankAudit - ce_rankrules count(*) == 0");

	// Grab max number of ranks from ce_rankrules //
	stringstream ss1;
	string maxrankstr = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT rank FROM ce_rankrules WHERE system_id=" << system_id << " AND disabled=false ORDER BY rank::INT4 DESC");

	// Handle values differently depending on plan //
	bool breakdownflag = false;
	stringstream ssBreakdown;
	if (m_pDB->GetFirstDB(socket, ssBreakdown << "SELECT count(*) FROM ce_breakdown WHERE batch_id='" << batch_id << "'") != 0)
		breakdownflag = true;

	string sum;
	int maxrank = atoi(maxrankstr.c_str());
	int index = 1;
	for (index=1; index <= maxrank; index++)
	{	
		// Loop through each rank SUM(amount) on breakdown for each rank //
		if (breakdownflag == true)
		{
			stringstream ss2;
			sum = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT SUM(amount) FROM ce_breakdown WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND rank='" << index << "')");
			if (sum.length() == 0)
				sum = "0";
		}
		else
		{
			stringstream ss2;
			sum = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT SUM(amount) FROM ce_ledger WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND user_id IN (SELECT user_id FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND rank='" << index << "')");
			if (sum.length() == 0)
				sum = "0";
		}

		stringstream ss3;
		string usercount = m_pDB->GetFirstCharDB(socket, ss3 << "SELECT count(*) FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND rank='" << index << "'");
		if (usercount.length() == 0)
			usercount = "0";

		stringstream ss4;
		if (m_pDB->ExecDB(socket, ss4 << "INSERT INTO ce_audit_ranks(system_id, batch_id, rank, total, usercount) VALUES (" << system_id << ", " << batch_id << ", '" << index << "', '" << sum << "', '" << usercount << "')") == NULL)
		{
			return Debug(DEBUG_ERROR, "CezReports::CalcRankAudit - Problems with ExecDB INSERT");
		}
	}

	return true;
}

///////////////////////////////////////
// Pre-calculate the user audit data //
///////////////////////////////////////
bool CezReports::CalcUsersAudit(int socket, int system_id, int batch_id)
{
	m_pDB->m_ConnPool.WaitForThreads(socket);

	// If there isn't a count, then there is a problem //
	stringstream ssCount;
	int totalcount = 0;
	ssCount << "SELECT count(*) FROM ce_ledger_totals WHERE system_id=" << system_id;
	if ((totalcount = m_pDB->GetFirstDB(socket, ssCount)) == 0)
		return Debug(DEBUG_ERROR, "CezReports::CalcUsersAudit - There are no records in the ce_ledger_totals for system_id", system_id);

	//Debug(DEBUG_ERROR, "CezReports::CalcUsersAudit - totalcount", totalcount);

	// Get top 3 users //
	list <string> topusers;
/*	if (m_pDB->m_pSettings->m_ReportsUser1.size() != 0)
	{
		CConn *conn;
		stringstream ss;
		ss << "SELECT user_id FROM ce_ledger_totals WHERE system_id=" << system_id << " ORDER BY amount DESC LIMIT 3";
		if ((conn = m_pDB->ExecDB(socket, ss)) == NULL)
			return Debug(DEBUG_ERROR, "CezReports::UsersReport - Problems with SELECT statement");

		// Grab sum for each generation //
		while (m_pDB->FetchRow(conn) == true)
		{
			string user_id = conn->m_RowMap[0];
			topusers.push_back(user_id);
		}

		if (ThreadReleaseConn(conn->m_Resource) == false)
		{
			Debug(DEBUG_ERROR, "CezReports::CalcUsersAudit - ThreadReleaseConn == false");
			return SetError(400, "API", "user::queryaudituser error", "Could not release the database connection (1)");
		}
	}
	else // Handle defined users //
	{
*/
		// Grab 6 defined users //
		if (m_pDB->m_pSettings->m_ReportsUser1.size() != 0)
			topusers.push_back(m_pDB->m_pSettings->m_ReportsUser1);
		if (m_pDB->m_pSettings->m_ReportsUser2.size() != 0)
			topusers.push_back(m_pDB->m_pSettings->m_ReportsUser2);
		if (m_pDB->m_pSettings->m_ReportsUser3.size() != 0)
			topusers.push_back(m_pDB->m_pSettings->m_ReportsUser3);
		if (m_pDB->m_pSettings->m_ReportsUser4.size() != 0)
			topusers.push_back(m_pDB->m_pSettings->m_ReportsUser4);
		if (m_pDB->m_pSettings->m_ReportsUser5.size() != 0)
			topusers.push_back(m_pDB->m_pSettings->m_ReportsUser5);
		if (m_pDB->m_pSettings->m_ReportsUser6.size() != 0)
			topusers.push_back(m_pDB->m_pSettings->m_ReportsUser6);
//	}

	list <string>::iterator q;
	for (q=topusers.begin(); q != topusers.end(); ++q) 
	{
		if (UsersCalcAllGen(socket, system_id, batch_id, (*q)) == false)
			return Debug(DEBUG_WARN, "CezReports::UsersReport - Something is wrong with UsersCalcAllGen");
	}

	return true;
}

/////////////////////////////////////////////
// Pre-calculate the generation audit data //
/////////////////////////////////////////////
bool CezReports::CalcGenerationAudit(int socket, int system_id, int batch_id)
{
	// Grab max number of ranks from ce_rankrules //
	stringstream ss0;
	if (m_pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_commrules WHERE system_id=" << system_id) == 0)
	{
		return Debug(DEBUG_INFO, "CezReports::CalcGenerationAudit - count(*) FROM ce_commrules == 0");
	}

	// Grab max number of ranks from ce_rankrules //
	stringstream ss1;
	string maxgenstr = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT generation FROM ce_breakdown WHERE system_id=" << system_id << " AND batch_id='" << batch_id << "' ORDER BY generation::INT4 DESC");

	int maxgen = atoi(maxgenstr.c_str());
	int index = 1;
	for (index=1; index <= maxgen; index++)
	{	
		// Loop through each rank SUM(amount) on breakdown for each rank //
		stringstream ss2;
		string sum = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT SUM(amount) FROM ce_breakdown WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND generation='" << index << "'");

		if (sum.length() == 0)
			sum = "0";

		stringstream ss3;
		if (m_pDB->ExecDB(socket, ss3 << "INSERT INTO ce_audit_generations(system_id, batch_id, generation, total) VALUES (" << system_id << ", " << batch_id << ", '" << index << "', '" << sum << "')") == NULL)
		{
			return Debug(DEBUG_ERROR, "CezReports::CalcGenerationAudit - Problems with ExecDB INSERT");
		}
	}

	return true;
}

/////////////////////////////////////
// Allow query of repcompiled data //
///////////////////////////////////// 
const char *CezReports::QueryAuditRanks(int socket, int system_id, string batch_id)
{
	CDbPlus::Setup("auditranks", "ce_audit_ranks");

	if (is_number(batch_id) == false)
		return SetError(400, "API", "ezreport::queryauditranks error", "The batchid must be a number");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("rank");
	columns.push_back("total");

	stringstream ssearch;
	ssearch << "systemid=" << system_id << "&batchid=" << batch_id;
	string sort = "orderby=rank&orderdir=asc&offset=0&limit=10000";

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, ssearch.str(), sort);
}

/////////////////////////////////////
// Allow query of repcompiled data //
///////////////////////////////////// 
const char *CezReports::QueryAuditUsers(int socket, int system_id, string batch_id)
{
	if (is_number(batch_id) == false)
		return SetError(400, "API", "user::queryaudituser error", "The batchid is not numeric");

	// Make sure there are records in the batch //
	stringstream ss;
	int count = m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_audit_users WHERE system_id=" << system_id << " AND batch_id=" << batch_id);
	if (count == 0)
		return SetError(400, "API", "user::queryaudituser error", "There are no records to build the report data");

	// Build a list of generations //
	CConn *conn;
	list <string> MapGen;
	stringstream ss1;
	if ((conn = m_pDB->ExecDB(socket, ss1 << "SELECT DISTINCT generation FROM ce_audit_users WHERE system_id=" << system_id << " AND batch_id='" << batch_id << "' ORDER BY generation")) == NULL)
		return SetError(400, "API", "user::queryaudituser error", "There was a problem with the SELECT DISTINCT");
	while (m_pDB->FetchRow(conn) == true)
	{
		string genstr = conn->m_RowMap[0];
		MapGen.push_back(genstr);
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDbPlus::QueryAuditUsers - #1 ThreadReleaseConn == false");
		return SetError(400, "API", "user::queryaudituser error", "Could not release the database connection (1)");
	}

	// Return a list of userid's //
	stringstream ssJson;
	ssJson << ",\"userids\":[";
	stringstream ss2;
	if ((conn = m_pDB->ExecDB(socket, ss2 << "SELECT DISTINCT user_id FROM ce_audit_users WHERE system_id=" << system_id << " AND batch_id='" << batch_id << "' ORDER BY user_id")) == NULL)
		return SetError(400, "API", "user::queryaudituser error", "There was a problem with the SELECT DISTINCT");
	while (m_pDB->FetchRow(conn) == true)
	{
		string userid = conn->m_RowMap[0];
		ssJson << "{\"userid\":\"" << userid << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDbPlus::QueryAuditUsers - #2 ThreadReleaseConn == false");
		return SetError(400, "API", "user::queryaudituser error", "Could not release the database connection (2)");
	}

	stringstream ssTrim;
	ssTrim << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
	ssJson.str("");
	ssJson << ssTrim.str();
	ssJson << "]";

	// Loop through each generation building each json set //
	ssJson << ",\"auditusers\":[";
	list <string>::iterator i;
	for (i=MapGen.begin(); i != MapGen.end(); ++i) 
	{
		stringstream ss3;
		if ((conn = m_pDB->ExecDB(socket, ss3 << "SELECT user_id, total FROM ce_audit_users WHERE system_id=" << system_id << " AND generation='" << (*i) << "' AND batch_id='" << batch_id << "' ORDER BY user_id")) == NULL)
			return SetError(400, "API", "user::queryaudituser error", "There was a problem with totals SELECT");

		ssJson << "{\"data\":[";
		while (m_pDB->FetchRow(conn) == true)
		{
			string userid = conn->m_RowMap[0];
			string total = conn->m_RowMap[1];
			ssJson << "{\"userid\":\"" << userid << "\",\"total\":\"" << total << "\"},";
		}

		if (ThreadReleaseConn(conn->m_Resource) == false)
		{
			Debug(DEBUG_ERROR, "CDbPlus::QueryAuditUsers - #3 ThreadReleaseConn == false");
			return SetError(400, "API", "user::queryaudituser error", "Could not release the database connection (3)");
		}

		stringstream ssTrim;
		ssTrim << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
		ssJson.str("");
		ssJson << ssTrim.str();
		ssJson << "]},";
	}
	stringstream ssTrim2;
	ssTrim2 << ssJson.str().substr(0, ssJson.str().size()-1); // Remove last comma //
	ssJson.str("");
	ssJson << ssTrim2.str();
	ssJson << "]";

	return SetJson(200, ssJson.str().c_str());
}

/////////////////////////////////////
// Allow query of repcompiled data //
///////////////////////////////////// 
const char *CezReports::QueryAuditGen(int socket, int system_id, string batch_id)
{
	CDbPlus::Setup("auditgen", "ce_audit_generations");

	if (is_number(batch_id) == false)
		return SetError(400, "API", "ezreport::queryauditgen error", "The batchid must be a number");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("generation");
	columns.push_back("total");

	stringstream ssearch;
	ssearch << "systemid=" << system_id << "&batchid=" << batch_id;
	string sort = "orderby=generation&orderdir=asc&offset=0&limit=10000";

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, ssearch.str(), sort);
}

///////////////////
// Query batches //
///////////////////
const char *CezReports::QueryBatches(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("batches", "ce_batches");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("start_date");
	columns.push_back("end_date");
	columns.push_back("receipts_wholesale");
	columns.push_back("commissions");
	columns.push_back("achv_bonuses");
	columns.push_back("pools");
	columns.push_back("bonuses");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

/////////////////
// Query ranks //
/////////////////
const char *CezReports::QueryRanks(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("ranks", "ce_ranks");
	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("rank");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

/////////////////////////////
// Query achievement bonus //
/////////////////////////////
const char *CezReports::QueryAchvBonus(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("achvbonus", "ce_achvbonus");

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

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////
// Query Commissions //
///////////////////////
const char *CezReports::QueryCommissions(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("commissions", "ce_commissions");

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

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

/////////////////////
// Query Userstats //
/////////////////////
const char *CezReports::QueryUserStats(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("userstats", "ce_userstats_month");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("group_wholesale_sales");
	columns.push_back("group_used");
	columns.push_back("customer_wholesale_sales");
	columns.push_back("affiliate_wholesale_sales");
	columns.push_back("reseller_wholesale_sales");
	columns.push_back("customer_retail_sales");
	columns.push_back("affiliate_retail_sales");
	columns.push_back("reseller_retail_sales");
	columns.push_back("signup_count");
	columns.push_back("affiliate_count");
	columns.push_back("customer_count");
	columns.push_back("reseller_count");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////
// Query Userstats Lvl1 //
//////////////////////////
const char *CezReports::QueryUserStatsLvl1(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("userstatslvl1", "ce_userstats_month_lvl1");

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

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////////////////////
// Do pre calculation of ce_pre_legrankgen table //
///////////////////////////////////////////////////
bool CezReports::CalcPreLegRankGen(int socket, int system_id, int batch_id, int rankmax, map <string, CUser> &UsersMap)
{
	// Don't allow over rankmax 50. Database doesn't support it //
	if (rankmax > 50)
		rankmax = 50;

	int recordcount = 0;
	string PreSQL;
	CDbBulk bulk;

	map < int, map <string, int> > StatsLvlRank;
	map < int, map <string, int> > StatsLegRank;

	int index;
	for (index=1; index <= 50; index++)
	{
		if (m_pDB->GetStatLvl1Rank(socket, system_id, batch_id, index, StatsLvlRank[index]) == false)
			Debug(DEBUG_DEBUG, "CezReports::CalcPreLegRankGen - m_pDB->GetStatLvl1Rank == false");

		int generation = 1;
		if (m_pDB->GetStatLegRank(socket, system_id, batch_id, generation, index, StatsLegRank[index]) == false)
			Debug(DEBUG_DEBUG, "CezReports::CalcPreLegRankGen - m_pDB->GetStatLegRank == false");
	}
 
	// Loop through all users //
	// Finding records from Lvl1 and Leg Rank //
	std::map <std::string, CUser>::iterator u;
	for (u=UsersMap.begin(); u != UsersMap.end(); ++u) 
	{
		CUser *puser = &UsersMap[u->first]; // This seems to be more accurate //

		//Debug(DEBUG_ERROR, "CezReports::CalcPreLegRankGen - puser->m_UserID", puser->m_UserID.c_str());

		// Add Pre Stat Record //
		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["batch_id"] = IntToStr(batch_id);
		columns["user_id"] = puser->m_UserID; //puser->m_UserID;

		int index;
		for (index=1; index <= rankmax; index++)
		{
			stringstream ssColumnLvl;
			ssColumnLvl << "lvl1_rank_" << index;
			int lvl1total = StatsLvlRank[index][puser->m_UserID];
			columns[ssColumnLvl.str().c_str()] = IntToStr(lvl1total); // This needed for Chalkatour //

			stringstream ssColumnLeg;
			ssColumnLeg << "gen1_rank_" << index;
			int legtotal = StatsLegRank[index][puser->m_UserID];
			columns[ssColumnLeg.str().c_str()] = IntToStr(legtotal); // This needed for Chalkatour //
		}

		if ((recordcount = bulk.BulkAdd(m_pDB, socket, "ce_pre_legrankgen", columns, &PreSQL, recordcount)) == -1)
			return Debug(DEBUG_ERROR, "CezReports::CalcPreLegRankGen - Error BulkAdd SQL", PreSQL.c_str());
	}

	if (bulk.BulkFinish(m_pDB, socket, &PreSQL) == false)
		return Debug(DEBUG_ERROR, "CezReports::CalcPreLegRankGen - Error BulkFinish");
}

////////////////////////////////
// Calc all generation totals //
////////////////////////////////
bool CezReports::UsersCalcAllGen(int socket, int system_id, int batch_id, string user_id)
{
	if (user_id.size() == 0)
		return false;

	// Add select count(*) for protection //
	stringstream ss;
	if (m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_rankrules WHERE system_id=" << system_id << " AND rank::INT4 >= 1") == 0)
		return Debug(DEBUG_INFO, "CezReports::CalcAllGen - No records for ce_rankrules");

	// Handle values differently depending on plan //
	bool breakdownflag = false;
	stringstream ssBreakdown;
	if (m_pDB->GetFirstDB(socket, ssBreakdown << "SELECT count(*) FROM ce_breakdown WHERE batch_id='" << batch_id << "'") != 0)
		breakdownflag = true;

	string total;
	stringstream ss1;
	int maxrank = m_pDB->GetFirstDB(socket, ss1 << "SELECT DISTINCT generation::INT4 FROM ce_commrules ORDER BY generation::INT4 DESC");
	int generation;
	for (generation=1; generation <= maxrank; generation++)
	{
		if (breakdownflag == true)
		{
			stringstream ss2;
			ss2 << "SELECT SUM(amount) FROM ce_breakdown WHERE system_id=" << system_id << " AND batch_id='" << batch_id << "' AND user_id='" << user_id << "' AND generation=" << generation;
			total = m_pDB->GetFirstCharDB(socket, ss2);
		}
		else
		{
			stringstream ss2;
			ss2 << "SELECT SUM(amount) FROM ce_ledger WHERE system_id=" << system_id << " AND batch_id='" << batch_id << "' AND user_id='" << user_id << "' AND generation=" << generation;
			total = m_pDB->GetFirstCharDB(socket, ss2);
		}

		if (total.length() == 0)
			total = "0";

		// INSERT entry into database //
		stringstream ss3;
		ss3 << "INSERT INTO ce_audit_users (system_id, batch_id, user_id, generation, total) VALUES (" << system_id << ", " << batch_id << ", " << user_id << ", " << generation << ", '" << total << "')"; 
		if (m_pDB->ExecDB(socket, ss3) == NULL)
			return Debug(DEBUG_ERROR, "CezReports::CalcAllGen - Error running record INSERT");
	}

	return true;
}

///////////////////////////////////////////
// Get a random user that has final data //
///////////////////////////////////////////
string CezReports::GetRandomUser(int socket, int system_id, int batch_id)
{
	stringstream ss1;
	int count = m_pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_grandtotals WHERE system_id=" << system_id << " AND batch_id=" << batch_id);
	if (count == 0)
	{
		Debug(DEBUG_ERROR, "CezReports::GetRandomUser - count", count);
		return "";
	}

	stringstream ss2;
	string user_id = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT user_id FROM ce_grandtotals WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " OFFSET floor(random()*" << count << ") LIMIT 1");

	return user_id;
}
