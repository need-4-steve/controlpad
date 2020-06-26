#include "Simulations.h"
#include "ConnPool.h"

#include <stdlib.h>
#include <sys/stat.h>
#include "commissions.h"

/////////////////
// Constructor //
/////////////////
CSimulations::CSimulations()
{
	m_pSimDB = NULL;
}

/////////////////////////////////////////////////////
// This needed if using Copy Specific tables below //
/////////////////////////////////////////////////////
bool CSimulations::SetSimDbPtr(CDb *pSimDb)
{
	m_pSimDB = pSimDb;
}

////////////////////////////////////////////////
// Handle how to seed the simulation database //
////////////////////////////////////////////////
bool CSimulations::CopySeed(CDb *pLiveDB, CDb *pSimDB, int socket, int system_id, int seed_type, int copyseedoption, int users_max, 
	int receipts_max, int min_price, int max_price, string start_date, string end_date)
{
	if (pSimDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - pSimDB == NULL");

	m_pSimDB = pSimDB; // Retain Pointer for later use //

	// Handle defaults if not given //
	if (min_price == 0)
		min_price = SIM_MIN_PRICE;
	if (max_price == 0)
		max_price = SIM_RECEIPT_MAX;
	if (users_max == 0)
		users_max = SIM_USER_MAX;

	// Handle deep or wide downlines //
	int lvl_one_min;
	int lvl_one_max;
	if (seed_type == SIM_SEED_WIDE)
	{
		lvl_one_min = SIM_WIDE_LVL1_MIN;
		lvl_one_max = SIM_WIDE_LVL1_MAX;
	}
	else if (seed_type == SIM_SEED_DEEP)
	{
		lvl_one_min = SIM_DEEP_LVL1_MIN;
		lvl_one_max = SIM_DEEP_LVL1_MAX;
	}

	CDbPlus::Debug(DEBUG_DEBUG, "CSimulations::CopySeed - Before GetFirstDB sysuser_id");

	stringstream ss;
	int sysuser_id = pLiveDB->GetFirstDB(socket, ss << "SELECT sysuser_id FROM ce_systems WHERE id='" << system_id << "'");

	CDbPlus::Debug(DEBUG_DEBUG, "CSimulations::CopySeed - After Get sysuser_id", sysuser_id);

	// Copy systemuser with sysuser_id //
	if (PurgeTable(socket, sysuser_id, "ce_systemusers", "id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems Purging Table ce_systemusers");
	if (PurgeTable(socket, system_id, "ce_systems", "id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems Purging Table ce_systems");
	if (PurgeTable(socket, system_id, "ce_users", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems Purging Table ce_users");
	if (PurgeTable(socket, system_id, "ce_receipts", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems Purging Table ce_receipts");

	// Copy from live //
	if (CopySystemUser(pLiveDB, socket, sysuser_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems copying ce_systemusers");
	if (CopySystem(pLiveDB, socket, system_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems copying ce_systems");

	// Prevent possible United altcore bleed over //
	stringstream ss2;
	if (pSimDB->ExecDB(socket, ss2 << "UPDATE ce_systems SET altcore='0' WHERE id=" << system_id) == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems UPDATE altcore"); 

	// Handle copying users and receipts //
	if ((copyseedoption == COPY_ONLY_USERS) || (copyseedoption == COPY_USERS_RECEIPTS))
	{
		if (PurgeTable(socket, system_id, "ce_users", "system_id") == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems Purging Table ce_users");
		if (CopyUsers(pLiveDB, socket, system_id) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems copying ce_users");
	}
	if ((copyseedoption == COPY_ONLY_RECEIPTS) || (copyseedoption == COPY_USERS_RECEIPTS))
	{
		if (PurgeTable(socket, system_id, "ce_receipts", "system_id") == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems Purging Table ce_receipts");
		if (CopyReceipts(pLiveDB, socket, system_id, start_date, end_date) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems copying ce_receipts");
	}

	// Seed users if not copied //
	if ((copyseedoption == COPY_ONLY_RECEIPTS) || (copyseedoption == SEED_BOTH))
	{
		if (SeedUsers(pSimDB, socket, system_id, users_max, lvl_one_min, lvl_one_max) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems SeedUsers");
	}

	// Seed receipts if not copied //
	if ((copyseedoption == COPY_ONLY_USERS) || (copyseedoption == SEED_BOTH))
	{
		if (SeedReceipts(pSimDB, socket, system_id, users_max, receipts_max, min_price, max_price, start_date) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopySeed - Problems SeedReceipts");

		// Seed some fraud receipts high on receipt count //
		srand(time(NULL)); // Seed random with time //
		int user_id = rand() % users_max + 1;
		if (SeedFraudReceipts(pSimDB, socket, system_id, user_id, 932, min_price, max_price, start_date) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Start - Problems SeedFraudReceipts");

		// Seed some fraud receipts high on receipt amount //
		srand(time(NULL)); // Seed random with time //
		user_id = rand() % users_max + 1;
		if (SeedFraudReceipts(pSimDB, socket, system_id, user_id, 5, min_price*10, max_price*10, start_date) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Start - Problems SeedFraudReceipts");
	}

	return true;
}

//////////////////////////
// Initate a simulation //
//////////////////////////
const char *CSimulations::Run(CDb *pLiveDB, CDb *pSimDB, int socket, int system_id, string start_date, string end_date)
{
	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::Run - TOP");

	if (pSimDB == NULL)
	{ 
		CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Simulation database not connected");
		return SetError(400, "API", "simulations::run error", "Simulation database not connected");
	}

	m_pSimDB = pSimDB; // Retain Pointer for later use //

	if (CopyFromLive(pLiveDB, socket, system_id, start_date, end_date) == false)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Start - Problems CopyFromLive");
		return SetError(400, "API", "simulations::run error", "Problems CopyFromLive");
	}

	// Prevent possible United altcore bleed over //
	stringstream ss;
	if (pSimDB->ExecDB(socket, ss << "UPDATE ce_systems SET altcore='0' WHERE id=" << system_id) == NULL)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Start - Problems UPDATE altcore"); 
		return SetError(400, "API", "simulations::run error", "Problems UPDATE altcore");
	}

	// Prevent bonus re-run problems //
	stringstream ss2;
	if (pSimDB->ExecDB(socket, ss2 << "UPDATE ce_bonus SET batch_id='0' WHERE system_id=" << system_id << " AND bonus_date >= '" << start_date << "' AND bonus_date <= '" << end_date << "'") == NULL)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Start - Problems UPDATE bonuses"); 
		return SetError(400, "API", "simulations::run error", "Problems UPDATE bonuses");
	}

	// Call commission class to do calculations //
	int commtype = pSimDB->GetSystemCommType(socket, system_id);
	
	string compression_str = pSimDB->GetSystemCompression(socket, system_id);
	bool compression = true;
	if (compression_str == "false")
		compression = false;
	else if (compression_str == "true")
		compression = true;

	CCommissions comm;
	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::Start - Right before comm.Run()"); 
	m_Json = comm.Run(pSimDB, socket, system_id, commtype, false, true, start_date.c_str(), end_date.c_str(), "", compression);
	return m_Json.c_str();
}

//////////////////////////////////////////////
// Copy certain data over from given system //
//////////////////////////////////////////////
bool CSimulations::CopyFromLive(CDb *pLiveDB, int socket, int system_id, string start_date, string end_date)
{
	// select last_value from ce_rankrules_id_seq;

	if (m_pSimDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - m_pSimDB == NULL");
	if (pLiveDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - pLiveDB == NULL");
	
	stringstream ss;
	int sysuser_id = pLiveDB->GetFirstDB(socket, ss << "SELECT sysuser_id FROM ce_systems WHERE id='" << system_id << "'");

	if (PurgeTable(socket, sysuser_id, "ce_systemusers", "id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_systemusers");
	if (PurgeTable(socket, system_id, "ce_systems", "id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_systems");
	if (PurgeTable(socket, system_id, "ce_rankrules", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_rankrules");
	if (PurgeTable(socket, system_id, "ce_commrules", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_commrules");	
	if (PurgeTable(socket, system_id, "ce_batches", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_batches");
	if (PurgeTable(socket, system_id, "ce_breakdown", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_breakdown");
	if (PurgeTable(socket, system_id, "ce_checkpoint", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_checkpoint");
	if (PurgeTable(socket, system_id, "ce_commissions", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_commissions");
	if (PurgeTable(socket, system_id, "ce_achvbonus", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_achvbonus");
	if (PurgeTable(socket, system_id, "ce_grandtotals", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_grandtotals");
	if (PurgeTable(socket, system_id, "ce_ledger", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_ledger");
	if (PurgeTable(socket, system_id, "ce_ranks", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_ranks");
	if (PurgeTable(socket, system_id, "ce_signupbonus", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_signupbonus");
	if (PurgeTable(socket, system_id, "ce_userstats_month", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_userstats_month");
	if (PurgeTable(socket, system_id, "ce_userstats_month_legs", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_userstats_month_legs");
	if (PurgeTable(socket, system_id, "ce_userstats_month_lvl1", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_userstats_month_lvl1");
	if (PurgeTable(socket, system_id, "ce_userstats_total", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_userstats_total");
	if (PurgeTable(socket, system_id, "ce_userstats_total_legs", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_userstats_total_legs");
	if (PurgeTable(socket, system_id, "ce_userstats_total_lvl1", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_userstats_total_lvl1");
	if (PurgeTable(socket, system_id, "ce_audit_users", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_audit_users");
	if (PurgeTable(socket, system_id, "ce_receipt_totals", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_receipt_totals");

	// United //
	if (PurgeTable(socket, system_id, "ce_checkmatch", "system_id") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems Purging Table ce_checkmatch");

	// Copy from live //
	if (CopySystemUser(pLiveDB, socket, sysuser_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems copying ce_systemusers");
	if (CopySystem(pLiveDB, socket, system_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems copying ce_systems");
	if (CopyRankRules(pLiveDB, socket, system_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems copying ce_rankrules");
	if (CopyCommRules(pLiveDB, socket, system_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyFromLive - Problems copying ce_commrules");

	return true;
}

/////////////////////
// Copy systemuser //
/////////////////////
bool CSimulations::CopySystemUser(CDb *pLiveDB, int socket, int sysuser_id)
{
	list <string> columns;
	columns.push_back("id");
	columns.push_back("email");
	columns.push_back("password_hash");
	columns.push_back("salt");
	columns.push_back("apikey_hash");
	columns.push_back("disabled");
	columns.push_back("updated_at");
	columns.push_back("firstname");
	columns.push_back("lastname");
	
	if (CopyTable(pLiveDB, socket, sysuser_id, "ce_systemusers", "ce_systemusers_id_seq", "id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - Problems with CopyTable ", "ce_systems");

	return true;
}

///////////////////////////////////////////
// Copy a system from live to simulation //
///////////////////////////////////////////
bool CSimulations::CopySystem(CDb *pLiveDB, int socket, int system_id)
{
	list <string> columns;
	columns.push_back("id");
	columns.push_back("sysuser_id");
	columns.push_back("system_name");
	columns.push_back("commtype");
	columns.push_back("altcore");
	columns.push_back("payout_type");
	columns.push_back("payout_monthday");
	columns.push_back("payout_weekday");
	columns.push_back("autoauthgrand");
	columns.push_back("infinitycap");
	columns.push_back("signupbonus");
	columns.push_back("updated_url");
	columns.push_back("updated_username");
	columns.push_back("updated_password");
	columns.push_back("disabled");
	columns.push_back("minpay");
	columns.push_back("teamgenmax");
	if (CopyTable(pLiveDB, socket, system_id, "ce_systems", "ce_systems_id_seq", "id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - Problems with CopyTable ", "ce_systems");

	return true;
}

////////////////////////
// Copy the rankrules //
////////////////////////
bool CSimulations::CopyRankRules(CDb *pLiveDB, int socket, int system_id)
{
	list <string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("achvbonus");
	columns.push_back("rulegroup");
	columns.push_back("maxdacleg");
	columns.push_back("disabled");
	if (CopyTable(pLiveDB, socket, system_id, "ce_rankrules", "ce_rankrules_id_seq", "system_id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - Problems with CopyTable ", "ce_rankrules");

	return true;
}

///////////////////////////////
// Copy the commission rules //
///////////////////////////////
bool CSimulations::CopyCommRules(CDb *pLiveDB, int socket, int system_id)
{
	list <string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("rank");
	columns.push_back("generation");
	columns.push_back("percent");
	columns.push_back("inv_type");
	columns.push_back("event");
	columns.push_back("infinitybonus");
	columns.push_back("disabled");
	if (CopyTable(pLiveDB, socket, system_id, "ce_commrules", "ce_commrules_id_seq", "system_id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - Problems with CopyTable ", "ce_commrules");

	return true;
}

////////////////////
// Copy the users //
////////////////////
bool CSimulations::CopyUsers(CDb *pLiveDB, int socket, int system_id)
{
	if (m_pSimDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - m_pSimDB == NULL");

	list <string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("usertype");
	columns.push_back("parent_id");
	columns.push_back("sponsor_id");
	columns.push_back("signup_date");
	columns.push_back("upline_parent");
	columns.push_back("upline_sponsor");
	columns.push_back("password_hash");
	columns.push_back("disabled");
	if (CopyTable(pLiveDB, socket, system_id, "ce_users", "ce_users_id_seq", "system_id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - Problems with CopyTable ", "ce_users");

	return true;
}

///////////////////////
// Copy the receipts //
///////////////////////
bool CSimulations::CopyReceipts(CDb *pLiveDB, int socket, int system_id, string start_date, string end_date)
{
	if (m_pSimDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::Copy - m_pSimDB == NULL");

	if (start_date.length() == 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyReceipts - Empty start_date ", "ce_receipts");
	if (end_date.length() == 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyReceipts - Empty end_date ", "ce_receipts");

	stringstream ssEndSQL;
	ssEndSQL << " AND ((wholesale_date >= '" << start_date << "' AND wholesale_date <= '" << end_date << "') OR (retail_date >= '" << start_date << "' AND retail_date <= '" << end_date << "'))";

	list <string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("receipt_id");
	columns.push_back("user_id");
	columns.push_back("usertype");
	columns.push_back("inv_type");
	columns.push_back("wholesale_price");
	columns.push_back("wholesale_date");
	//columns.push_back("retail_price");
	//columns.push_back("retail_date");
	columns.push_back("commissionable");
	columns.push_back("metadata_onadd");
	columns.push_back("metadata_onupdate");
	columns.push_back("disabled");
	if (CopyTable(pLiveDB, socket, system_id, "ce_receipts", "ce_receipts_id_seq", "system_id", columns, ssEndSQL.str()) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyReceipts - Problems with CopyTable ", "ce_receipts");

	stringstream ss;
	if (m_pSimDB->ExecDB(socket, ss << "UPDATE ce_receipts SET retail_date='" << start_date << "', wholesale_date='" << start_date << "' WHERE system_id=" << system_id) == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyReceipts - Problems with ExecDB UPDATE");

	return true;
}

///////////////////////////////////
// Prepare the simulations table //
///////////////////////////////////
bool CSimulations::PurgeTable(int socket, int system_id, string tablename, string id_column)
{
	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::PurgeTable - TOP");

	// Delete from simulation database //
	CConn *conn;
	stringstream ss;
	if ((conn = m_pSimDB->ExecDB(socket, ss << "DELETE FROM " << tablename << " WHERE " << id_column << "=" << system_id)) == NULL)
	{
		stringstream ss2;
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CleanTable - Problems with DELETE on simulation table ", tablename);
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::PurgeTable - ThreadReleaseConn == false");

	return true;
}

/////////////////////////////////////////////
// Copy the System from live to simulation //
/////////////////////////////////////////////
bool CSimulations::CopyTable(CDb *pLiveDB, int socket, int system_id, string tablename, string seqname, string system_id_column, list <string> columns, string endsql)
{
	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::CopyTable - TOP - tablename", tablename);

	if (m_pSimDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - m_pSimDB == NULL");
	if (pLiveDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - pLiveDB == NULL");

	// Build a string from column names //
	stringstream ss1;
	list <string>::iterator c;
	for (c=columns.begin(); c != columns.end(); ++c) 
	{
		ss1 << (*c) << ", ";
	}
	stringstream ssCols;
	ssCols << ss1.str().substr(0, ss1.str().size()-2); // Remove last comma //

	CConn *conn;
	stringstream ss2;
	if ((conn = pLiveDB->ExecDB(socket, ss2 << "SELECT " << ssCols.str() << " FROM " << tablename << " WHERE " << system_id_column << "=" << system_id << " " << endsql)) == NULL)
	{
		stringstream ss3;
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - SELECT Error ", ss3 << " from " << tablename << " failed");
	}

	// Grab the values //
	list <map <string, string> > allrecords;
	while (pLiveDB->FetchRow(conn) == true)
	{	
		map <string, string> values;
		int index = 0;
		for (c=columns.begin(); c != columns.end(); ++c) 
		{
			//values[(*c)] = pLiveDB->RowMap(index);
			values[(*c)] = conn->m_RowMap[index];
			index++;
		}

		allrecords.push_back(values);
	}

	//CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - allrecords.size()", allrecords.size());

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - ThreadReleaseConn == false");

	string strInsertSQL;
	int recordcount = 0;
	list <map <string, string> >::iterator i;
	for (i=allrecords.begin(); i != allrecords.end(); ++i) 
	{
		if ((recordcount = BulkAdd(m_pSimDB, socket, tablename, (*i), &strInsertSQL, recordcount)) == -1)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - BulkAdd == -1");
	}

	if (BulkFinish(m_pSimDB, socket, &strInsertSQL) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - BulkFinish == false");

	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::CopyTable - Towards End #1");

	// Set the SEQUENCE count to prevent errors //
	stringstream ss4;
	int seqcount;
	if ((seqcount = pLiveDB->GetFirstDB(socket, ss4 << "SELECT id+1 FROM " << tablename << " ORDER BY id DESC LIMIT 1")) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - Grab SEQUENCE count == false");

	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::CopyTable - Towards End #2");

	CConn *conn2;
	stringstream ss5;
	if ((conn2 = m_pSimDB->ExecDB(socket, ss5 << "ALTER SEQUENCE " << seqname << " RESTART WITH " << seqcount)) == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - ALTER SEQUENCE == false");

	if (ThreadReleaseConn(conn2->m_Resource) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - #2 ThreadReleaseConn == false");

	CDbPlus::Debug(DEBUG_TRACE, "CSimulations::CopyTable - END");

	return true;
}

////////////////
// Seed Users //
////////////////
bool CSimulations::SeedUsers(CDb *pDB, int socket, int system_id, int usersmax, int min_lvl_one, int max_lvl_one)
{
	string strSQL;
	int user_count = 0;
	int insert_count = 0;
	int parent_id;
	int user_id = 2;

	// Add the top affiliate. Usually the main company //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = IntToStr(1);
	columns["usertype"] = IntToStr(USERTYPE_RESELLER);
	columns["parent_id"] = IntToStr(0);
	columns["sponsor_id"] = IntToStr(0);
	columns["signup_date"] = "now()";
	columns["breakage"] = "false";
	columns["upline_parent"] = " ";
	columns["upline_sponsor"] = " ";
	if ((insert_count = BulkAdd(pDB, socket, "ce_users", columns, &strSQL, insert_count)) == -1)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedUsers - BulkAdd Error");
	user_count = 1;

	// Make sure the lookups are added //
	m_ParentLookup["1"] = "0"; 
	m_SponsorLookup["1"] = "0";

	// Add all other affiliates //
	for (parent_id=1; parent_id < usersmax; parent_id++)
	{
		int lvl_one_count = 0;
		int max_lvl = rand() % max_lvl_one + min_lvl_one;
		while (lvl_one_count < max_lvl)
		{
			m_ParentLookup[IntToStr(user_id)] = IntToStr(parent_id);
			m_SponsorLookup[IntToStr(user_id)] = IntToStr(parent_id);

			map <string, string> columns;
			columns["system_id"] = IntToStr(system_id);
			columns["user_id"] = IntToStr(user_id);
			columns["usertype"] = IntToStr(USERTYPE_RESELLER);
			columns["parent_id"] = IntToStr(parent_id);
			columns["sponsor_id"] = IntToStr(parent_id);
			columns["signup_date"] = "now()";
			columns["breakage"] = "false";
			columns["upline_parent"] = BuildUpline(IntToStr(user_id), UPLINE_PARENT_ID);
			columns["upline_sponsor"] = BuildUpline(IntToStr(user_id), UPLINE_SPONSOR_ID);

			if ((insert_count = BulkAdd(pDB, socket, "ce_users", columns, &strSQL, insert_count)) == -1)
				return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedUsers - BulkAdd Error");

			lvl_one_count++;			
			user_id++;
			user_count++;
			if (user_count >= usersmax)
			{
				if (BulkFinish(pDB, socket, &strSQL) == false)
					return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedUsers - BulkFinish Error");

				return true;
			}
		}
	}

	return true;
}

///////////////////
// Seed Receipts //
///////////////////
bool CSimulations::SeedReceipts(CDb *pDB, int socket, int system_id, int users_max, int receipts_max, int min_price, int max_price, string start_date)
{
	string strSQL;
	int insert_count = 0;
	int index;
	for (index=1; index <= receipts_max; index++)
	{
		int amount = rand() % (max_price-1) + min_price;
		int amount_cents = rand() % 99 + 1;
		int user_id = rand() % users_max + 1;
		string price = IntToStr(amount)+"."+IntToStr(amount_cents);
		
		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["user_id"] = IntToStr(user_id);
		columns["receipt_id"] = IntToStr(index);
		columns["usertype"] = IntToStr(USERTYPE_RESELLER);
		columns["commissionable"] = "true";
		columns["wholesale_price"] = price;
		columns["wholesale_date"] = start_date;
		columns["retail_date"] = start_date;
		columns["inv_type"] = INV_WHOLESALE;

		if ((insert_count = BulkAdd(pDB, socket, "ce_receipts", columns, &strSQL, insert_count)) == -1)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedReceipts - BulkAdd Error");

		// Finish up //
		if (index >= receipts_max)
		{
			if (BulkFinish(pDB, socket, &strSQL) == false)
				return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedUsers - BulkFinish Error");
			
			return true;
		}
	}

	return true;
}

///////////////////////////////////
// Seed some fraudulent receipts //
///////////////////////////////////
bool CSimulations::SeedFraudReceipts(CDb *pDB, int socket, int system_id, int user_id, int receipts_max, int min_price, int max_price, string start_date)
{
	string strSQL;
	int insert_count = 0;
	int index;
	for (index=1; index <= receipts_max; index++)
	{
		int amount = rand() % max_price + min_price;
		int amount_cents = rand() % 99 + 1;
		string price = IntToStr(amount)+"."+IntToStr(amount_cents);
		
		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["user_id"] = IntToStr(user_id);
		columns["receipt_id"] = IntToStr(index);
		columns["usertype"] = IntToStr(USERTYPE_RESELLER);
		columns["commissionable"] = "true";
		columns["wholesale_price"] = price;
		columns["wholesale_date"] = start_date;
		columns["retail_date"] = start_date;

		if ((insert_count = BulkAdd(pDB, socket, "ce_receipts", columns, &strSQL, insert_count)) == -1)
			return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedReceipts - BulkAdd Error");

		// Finish up //
		if (index >= receipts_max)
		{
			if (BulkFinish(pDB, socket, &strSQL) == false)
				return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::SeedUsers - BulkFinish Error");
			
			return true;
		}
	}

	return true;
}

/////////////////////////////
// Build the upline string //
/////////////////////////////
string CSimulations::BuildUpline(string user_id, int upline_type)
{
	string parent_id;

	if (upline_type == UPLINE_PARENT_ID)
		parent_id = m_ParentLookup[user_id];
	if (upline_type == UPLINE_SPONSOR_ID)
		parent_id = m_SponsorLookup[user_id];

	string upline = parent_id+" ";
	while (parent_id != "0")
	{
		if (upline_type == UPLINE_PARENT_ID)
			parent_id = m_ParentLookup[parent_id];
		if (upline_type == UPLINE_SPONSOR_ID)
			parent_id = m_SponsorLookup[parent_id];

		if (parent_id != "0")
			upline = parent_id+" / "+upline;
	}

	upline = " "+upline;
	return upline;
}

///////////////////////////
// Is the file available //
///////////////////////////
bool CSimulations::IsFile(string filename)
{
	struct stat buffer;   
	return (stat (filename.c_str(), &buffer) == 0);
}

/*
inline bool exists_test3 (const std::string& name) {
  struct stat buffer;   
  return (stat (name.c_str(), &buffer) == 0); 
}
*/