////////////
// db.cpp //
////////////

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

#include <sstream>

// IP address lookup //
#include <arpa/inet.h>
#include <netdb.h>

#include "db.h"
#include "receipts.h"
#include "date.h"
#include "ezCrypt.h"
#include "commissions.h"
#include "ezCurl.h"
#include "ezRecv.h"
#include "ezReports.h"
#include "commissions.h"

#include "dbplus.h"

#include "Compile.h"
#ifndef COMPILE_RUBYRICE
#include <jsoncpp/json/json.h>
#endif

extern int g_RankOverride;

/*
////////////////////////////////
// Stats Rank Lvl Constructor //
////////////////////////////////
CStatsLvlRank::CStatsLvlRank()
{
	m_Rank = 0;
	m_Total = 0;
}

///////////////////////////
// State Leg Constructor //
///////////////////////////
CStatsLegRank::CStatsLegRank()
{
	m_Rank = 0;
	m_Total = 0;
	m_Generation = 0;
}
*/
////////////////////////
// Set initial values //
////////////////////////
CReceiptTotal::CReceiptTotal()
{
	m_WholesaleTotal = 0;
	m_RetailTotal = 0;
};

/////////////////////////
// Run connection here //
/////////////////////////
CDb::CDb()
{
	m_myConn = NULL;
	m_myResult = NULL;
	
	m_BreakdownCount = 0;
	m_CommCount = 0;
	//m_UserStatMonthCount = 0;
	//m_UserStatMonthLVL1Count = 0;
	//m_UserStatMonthLegCount = 0;
	//Connect();

	m_RankCount = 0;
	m_RankBonusCount = 0;

	m_LedgerCount = 0;

	m_LevelsCount = 0;
	m_RecurrGen = 0;

	m_AchvCount = 0;

	//m_Generation = 0;
	m_ConnType = 0;

	m_pSettings = NULL;
}

///////////////////////////////////
// Disconnect from database here //
///////////////////////////////////
CDb::~CDb()
{
	Debug(DEBUG_TRACE, "CDb::~CDb - Destructor");

	Disconnect();

#ifdef COMPILE_MYSQL
	// Prevent memory leaks //
	if (m_myResult != 0)
	{
		mysql_free_result(m_myResult);
	}
#endif
}

/////////////////////////////////////////
// Lookup IP address based on hostname //
/////////////////////////////////////////
std::string CDb::LookupIP(const char *hostname)
{
	if (hostname == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::LookupIP - hostname is NULL");
		return ""; // return empty string on error //
	}

	struct sockaddr_in sa;

	// store this IP address in sa:
	int retval = inet_pton(AF_INET, hostname, &(sa.sin_addr));
	if (retval == 0) // Invalid IP address. Try hostname //
	{
		struct hostent *he;
	    struct in_addr **addr_list;

	    if ((he = gethostbyname(hostname)) == NULL)
	    {
	    	Debug(DEBUG_ERROR, "CDb::LookupIP - invalid hostname syntax problems. retval", retval);
	    	Debug(DEBUG_ERROR, "CDb::LookupIP - hostname", hostname);
	    	return ""; // return empty string on error //
	    }
	    else
	    {
	    	// Lookup list of ipaddresses //
		    addr_list = (struct in_addr **)he->h_addr_list;
	        
	        // Return first IP in list //
	        std::string ipaddress = inet_ntoa(*addr_list[0]);
	        return ipaddress;
	    }
	}
	else if (retval == -1) // Error //
	{
		Debug(DEBUG_ERROR, "CDb::LookupIP - IP address problems. retval", retval);
		Debug(DEBUG_ERROR, "CDb::LookupIP - hostname", hostname);
		return ""; 
	}
	else if (retval == 1) // IP address is valid //
	{
		// Return the IP address passed in //
		std::string ipaddress = hostname;
		return ipaddress;
	}

	Debug(DEBUG_ERROR, "CDb::LookupIP - End of function. Something went wrong. retval", retval);
	Debug(DEBUG_ERROR, "CDb::LookupIP - hostname", hostname);
	return ""; // return empty string on error //
}

/////////////////////////////
// Connect to the database //
/////////////////////////////
bool CDb::Connect(CezSettings *psettings)
{
	m_pSettings = psettings;
	CDebug::SetLogFile(m_pSettings->m_LogFile.c_str());

	if ((m_pSettings->m_DBType != 1) && (m_pSettings->m_DBType != 2))
		return Debug(DEBUG_ERROR, "CDb::Connect - Invalid systemtype");

	std::string ipaddress;
	ipaddress = LookupIP(m_pSettings->m_Hostname.c_str());
	if (ipaddress.size() == 0)
	{
		Debug(DEBUG_ERROR, "CDb::Connect - invalid hostame problems");
		return Debug(DEBUG_ERROR, "CDb::Connect - hostname", m_pSettings->m_Hostname.c_str());
	}

#ifdef COMPILE_MYSQL
	// Define mysql or postgres //
	if (m_pSettings->m_DBType == DB_MYSQL)
	{
		// That way we don't have to recompile if we change something like password //
		m_myConn = mysql_init(NULL);
	    if (m_myConn == NULL)
	    {
	    	Debug(DEBUG_SQL, mysql_error(m_myConn));

	        fprintf(stderr, "%s\n", mysql_error(m_myConn));
	        return false;
	    }

		//if (mysql_real_connect(m_myConn, MYSQL_HOST, MYSQL_USER, MYSQL_PASSWD, MYSQL_DATABASE, 0, NULL, 0) == NULL)
		if (mysql_real_connect(m_myConn, ipaddress.c_str(), m_pSettings->m_Username.c_str(), m_pSettings->m_Password.c_str(), m_pSettings->m_DatabaseName.c_str(), 0, NULL, 0) == NULL)
	    {
	    	Debug(DEBUG_SQL, mysql_error(m_myConn));

	        fprintf(stderr, "%s\n", mysql_error(m_myConn));
//	        mysql_close(m_myConn);
	        return false;
	    }
	    else
	    {
	    	Debug(DEBUG_INFO, "CDb::Connect - Mysql Connected Successfully!");
	    }

	    // Make sure to specify the database to use? //
	    // Error seems to go away //
	    std::string query;
	    query = "USE ";
	    query += m_pSettings->m_DatabaseName;
	    //Debug(DEBUG_ERROR, query);
	    ExecDB(query.c_str());
	}
#endif

#ifdef COMPILE_POSTGRES
	if (m_pSettings->m_DBType == DB_POSTGRES)
	{
		// user=postgres password=test123 dbname=testdb hostaddr=127.0.0.1 port=5432"
	    std::stringstream ss;
	    std::string conninfo;

	    //if (m_pSettings->m_SimEnabled == false)
		//    m_ConnType = CONNTYPE_LIVE;
		//else if (m_pSettings->m_SimEnabled == true)
		//    m_ConnType = CONNTYPE_SIM;
	    //else
	    //	return Debug(DEBUG_ERROR, "CDb::Connect - m_SimEnabled undefined");

	    Debug(DEBUG_DEBUG, "CDb::Connect - db_name", m_pSettings->m_DatabaseName);
		ss << " user=" << m_pSettings->m_Username << " password=" << m_pSettings->m_Password << " dbname=" << m_pSettings->m_DatabaseName << " hostaddr=" << ipaddress << " port=5432";
		conninfo = ss.str();

		//Debug(DEBUG_DEBUG, "conninfo", conninfo.c_str());
/*
		m_pgConn = PQconnectdb(conninfo.c_str());
		if (PQstatus(m_pgConn) != CONNECTION_OK)
		{
			Debug(DEBUG_ERROR, "CDb::Connect - Postgresql Connection Failed:");
			return Debug(DEBUG_ERROR, PQerrorMessage(m_pgConn));
		}
	  	else
	  	{
	  		Debug(DEBUG_DEBUG, "CDb::Connect - postgresql Connected Successfully!");
		}
*/		
		if (m_pSettings->m_ConnPoolCount == 0)
		{
			Debug(DEBUG_WARN, "CDb::Connect - m_pSettings->m_ConnPoolCount == 0");
			return true;
		}

		// Handle the connection pool //
		if (m_ConnPool.ConnectPool(m_pSettings->m_IniFile, conninfo, m_pSettings->m_ConnPoolCount, m_pSettings) == false)
			return false;
	}
#endif

	return true;
}

///////////////////////////////////////////
// Find out if the database is connected //
///////////////////////////////////////////
bool CDb::IsConnected()
{
	if (m_ConnPool.GetConnCount() == 0)
		return false;

	return true;
}

/////////////////////////
// Begin a transaction //
/////////////////////////
bool CDb::Begin(int socket)
{
	if (ExecDB(socket, "BEGIN") == NULL)
		return Debug(DEBUG_ERROR, "CDb::Begin - ExecDB error");

	return true;
}

//////////////////////////
// Commit a transaction //
//////////////////////////
bool CDb::Commit(int socket)
{
	if (ExecDB(socket, "COMMIT") == NULL)
		return Debug(DEBUG_ERROR, "CDb::Commit - ExecDB error");

	return true;
}

//////////////////////////////////////////////////////
// Standard entry with no autorelease of connection //
//////////////////////////////////////////////////////
CConn *CDb::ExecDB(int socket, const char *query)
{
	Debug(DEBUG_TRACE, socket, "CDb::ExecDB(int socket, const char *query) - TOP");

	return ExecDB(false, socket, query);
}

//////////////////////////////////////////////
// Allow stringstream passed as a parameter //
//////////////////////////////////////////////
//CConn *CDb::ExecDB(int socket, std::stringstream& query)
CConn *CDb::ExecDB(int socket, basic_ostream<char> &query)
{
	//std::string sql;
	//sql = query.str();
	stringstream sql;
	sql << query.rdbuf();
	return ExecDB(socket, sql.str().c_str());
}

//////////////////////////////////////////////
// Allow stringstream passed as a parameter //
//////////////////////////////////////////////
//CConn *CDb::ExecDB(bool autorelease, int socket, std::stringstream& query)
CConn *CDb::ExecDB(bool autorelease, int socket, basic_ostream<char> &query)
{
	//std::string sql;
	//sql = query.str();
	stringstream sql;
	sql << query.rdbuf();
	return ExecDB(autorelease, socket, sql.str().c_str());
}

////////////////////////////////////
// Pass the query to the database //
////////////////////////////////////
CConn *CDb::ExecDB(bool autorelease, int socket, const char *query)
{
	if (m_pSettings == NULL)
	{
		Debug(DEBUG_ERROR, socket, "CDb::ExecDB - m_pSettings == NULL");
		return NULL;
	}
	else if (m_pSettings != NULL)
	{
		Debug(DEBUG_TRACE, socket, "CDb::ExecDB - m_pSettings != NULL");
	}

	// Display all SQL in yellow //
	if (m_pSettings->m_FullSQL == "true")
	{
		Debug(DEBUG_SQL, socket, query);
	}
	else
	{
		// Only first 350ish characters //
		char ErrorBuffer[360];
		strncpy(ErrorBuffer, query, 350);
		ErrorBuffer[349] = 0; // Terminate the last of it //
		Debug(DEBUG_SQL, socket, ErrorBuffer);
	}

#ifdef COMPILE_MYSQL
	// Handle running query //
	if (m_pSettings->m_DBType == DB_MYSQL)
	{
		// Prevent memory leaks //
		if (m_myResult != 0)
		{
			//Debug(DEBUG_ERROR, "CDb::ExecDB - m_myResult != 0");
			mysql_free_result(m_myResult);
			m_myResult = 0;
		}

		// Run the query //
		if (mysql_query(m_myConn, query) != 0)
		{
			Debug(DEBUG_ERROR, "ExecDB - mysql_query:");
			Debug(DEBUG_ERROR, mysql_error(m_myConn));
//	        mysql_close(m_myConn);
//	        return false;
	    }

		m_myResult = mysql_store_result(m_myConn);
		if (m_myResult == 0)
		{
//			Debug(DEBUG_ERROR, "ExecDB - mysql_store_result:");
//			Debug(DEBUG_ERROR, mysql_error(m_myConn));
//	        mysql_close(m_myConn);
//	        return false;
	    }

	    return NULL;
	}
#endif

#ifdef COMPILE_POSTGRES
	if (m_pSettings->m_DBType == DB_POSTGRES) 
	{
		//int conncount = m_ConnPool.GetConnCount();
		//Debug(DEBUG_WARN, socket, "CDb::ExecDB - conncount", conncount);
		
		Debug(DEBUG_TRACE, socket, "CDb::ExecDB - Before m_ConnPool.GetConnCount() > 0");

		// if ((m_ConnPool.GetConnCount() > 0) && (m_ConnPool.IsEnabled() == true))
		//if (m_ConnPool.GetConnCount() > 0)
		//{
			CConn *pconn;
			if ((pconn = m_ConnPool.Exec(autorelease, socket, m_pSettings->m_IniFile, query)) == NULL)
				return NULL;

			return pconn;
		//}
	    //return NULL;
	}
#endif

	return NULL;
}

///////////////////////////////
// Allow reuse of connection //
///////////////////////////////
bool CDb::ExecDB(CConn *conn, bool autorelease, string sql)
{
	char ErrorBuffer[560];
	strncpy(ErrorBuffer, sql.c_str(), 550);
	ErrorBuffer[550] = 0; // Terminate the last of it //
	//if (m_pSettings->m_SimEnabled == false)
		Debug(DEBUG_SQL, ErrorBuffer);
	//else
	//{
	//	stringstream ss;
	//	ss << "(sim)" << ErrorBuffer;
	//	Debug(DEBUG_SQL, ss.str().c_str());
	//}

	// if ((m_ConnPool.GetConnCount() > 0) && (m_ConnPool.IsEnabled() == true))
	if (m_ConnPool.GetConnCount() > 0)
	{
		if (m_ConnPool.Exec(conn, autorelease, sql.c_str()) == false)
			return Debug(DEBUG_ERROR, "CDb::ExecDB - Reuse ExecDB conn had problems");

		return true;
	}
    return false;
}

//////////////////////////////
// Get the number of fields //
//////////////////////////////
int CDb::NumFields()
{
/*
	Debug(DEBUG_INFO, "CDb::NumFields");

#ifdef COMPILE_MYSQL
	if (m_pSettings->m_DBType == DB_MYSQL)
	{
		return mysql_num_fields(m_myResult);
	}
#endif

#ifdef COMPILE_POSTGRES
	if (m_pSettings->m_DBType == DB_POSTGRES)
	{
		return PQnfields(m_pgResult);
	}
#endif
*/
	Debug(DEBUG_ERROR, "CDb::NumFields Error Depricated");
	return 0;
}

/////////////////
// Fetch a row //
/////////////////
bool CDb::FetchRow(CConn *conn)
{
	conn->m_RowMap.clear(); // Clear values out before use //

#ifdef COMPILE_MYSQL
	if (m_pSettings->m_DBType == DB_MYSQL)
	{
		int i;
		int columnmax = mysql_num_fields(m_myResult);
		MYSQL_ROW row;
		row = mysql_fetch_row(m_myResult);
		if (row == NULL)
			return false;

		for (i = 0; i < columnmax; i++)
		{
			m_RowMap[i] = row[i];
		}

		return true;
	}
#endif

#ifdef COMPILE_POSTGRES
	if (m_pSettings->m_DBType == DB_POSTGRES)
	{
		// Make sure there is a row to return //
		if (PQntuples(conn->m_pgResult) == 0)
		{
			Debug(DEBUG_ERROR, "CDb::FetchRow - PQntuples == 0. query", conn->m_Query);
			return false;
		}

		// Make sure we don't read past the limit //
		if (conn->m_pgCurrentRow >= conn->m_pgRowMax)
		{
			//stringstream ssErr;
			//ssErr << "CDb::FetchRow - m_pgCurrentRow >= m_pgRowMax - m_pgRowMax=" << m_pgRowMax << ", m_pgCurrentRow=" << m_pgCurrentRow;
			//Debug(DEBUG_TRACE, ssErr.str().c_str());
			return false;
		}

		// Assign row values //
		int i;
		int columnmax = PQnfields(conn->m_pgResult);
		//Debug(DEBUG_TRACE, "CDb::FetchRow - columnmax", columnmax);
		for (i = 0; i < columnmax; i++)
		{
			conn->m_RowMap[i] = PQgetvalue(conn->m_pgResult, conn->m_pgCurrentRow, i);
			//Debug(DEBUG_TRACE, "CDb::FetchRow - m_RowMap[i]", conn->m_RowMap[i]);
		}

		// Go to the next row //
		conn->m_pgCurrentRow++;

		// Return our map //
		return true;
	}
#endif

	Debug(DEBUG_ERROR, "CDb::FetchRow - Error");
	return false;
}

/////////////////////////////////////
// Give external access to row map //
/////////////////////////////////////
const char *CDb::RowMap(CConn *conn, int row)
{
	return conn->m_RowMap[row].c_str();
}

/////////////////////////////////////
// Grab the first entry INT format //
/////////////////////////////////////
int CDb::GetFirstDB(int socket, const char *query)
{
	Debug(DEBUG_TRACE, "CDb::GetFirstDB #1 - TOP");

	CConn *conn;
	if ((conn = ExecDB(socket, query)) == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::GetFirstDB #1 - ExecDB Error");
		return -1;
	}

	Debug(DEBUG_TRACE, "CDb::GetFirstDB #1 - MID #1");

	if (FetchRow(conn) == false)
	{
		Debug(DEBUG_ERROR, "CDb::GetFirstDB #1 - FetchRow Error");
		return -1;
	} 

	Debug(DEBUG_TRACE, "CDb::GetFirstDB - MID #2");
	//printf("TESTING 1 2 3\n");

	int retval = StrToInt(conn->m_RowMap[0].c_str());
	if (ThreadReleaseConn(conn->m_Resource) == false)
		Debug(DEBUG_ERROR, "CDb::GetFirstDB - ThreadReleaseConn == false");

	Debug(DEBUG_TRACE, "CDb::GetFirstDB #1 - END");

	return retval;
}

/////////////////////////////////
// Less code to do it this way //
/////////////////////////////////
//int CDb::GetFirstDB(int socket, std::stringstream& query)
int CDb::GetFirstDB(int socket, basic_ostream<char> &query)
{ 
	Debug(DEBUG_TRACE, "CDb::GetFirstDB #2 - TOP - socket", socket);

	//string sql;
	stringstream sql;
	Debug(DEBUG_TRACE, socket, "CDb::GetFirstDB #2 - After string sql");
	//sql = query.str();
	sql << query.rdbuf();
	Debug(DEBUG_TRACE, socket, "CDb::GetFirstDB #2 - After sql = query.str()");
	CConn *conn;
	Debug(DEBUG_TRACE, socket, "CDb::GetFirstDB #2 - Before ExecDB");
	if ((conn = ExecDB(socket, sql.str().c_str())) == NULL)
	{
		Debug(DEBUG_ERROR, socket, "CDb::GetFirstDB #2 - ExecDB conn == NULL");
		return -1;
	}

	//Debug(DEBUG_TRACE, socket, "CDb::GetFirstDB #2 - MIDDLE #1");

	if (FetchRow(conn) == false)
	{
		// Release the connection or we get stuck with connection/thread loops //
		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, socket, "CDb::GetFirstDB #2 - ThreadReleaseConn #1 == false");

		Debug(DEBUG_ERROR, socket, "CDb::GetFirstDB #2 - FetchRow == false");
		return -1;
	}

	//Debug(DEBUG_TRACE, socket, "CDb::GetFirstDB #2 - MIDDLE #2");

	int retval = atoi(conn->m_RowMap[0].c_str());
	if (ThreadReleaseConn(conn->m_Resource) == false)
		Debug(DEBUG_ERROR, "CDb::GetFirstDB #2 - ThreadReleaseConn #2 == false");

	//Debug(DEBUG_TRACE, socket, "CDb::GetFirstDB #2 - END");

	return retval;
}

/////////////////////////////////////
// Grab the first entry INT format //
/////////////////////////////////////
//int CDb::GetFirstDB(CConn *conn, stringstream& query)
int CDb::GetFirstDB(CConn *conn, basic_ostream<char> &query)
{
	Debug(DEBUG_TRACE, "CDb::GetFirstDB #3 - TOP");

	//conn->m_Query = query.str();
	stringstream ssBuf;
	ssBuf << query.rdbuf();
	conn->m_Query = ssBuf.str();
	if (ThreadExec(conn) == false)
	{
		Debug(DEBUG_ERROR, "CDb::GetFirstDB #3 - ExecDB Error");
		return -1;
	}

	Debug(DEBUG_TRACE, "CDb::GetFirstDB - FetchRow MIDDLE");

	if (FetchRow(conn) == false)
	{
		Debug(DEBUG_ERROR, "CDb::GetFirstDB #3 - FetchRow Error");
		return -1;
	}

	Debug(DEBUG_TRACE, "CDb::GetFirstDB - FetchRow AFTER");

	int retval = StrToInt(conn->m_RowMap[0].c_str());

	Debug(DEBUG_TRACE, "CDb::GetFirstDB - retval", retval);

	if (ThreadReleaseConn(conn->m_Resource) == false)
		Debug(DEBUG_ERROR, "CDb::GetFirstDB #3 - ThreadReleaseConn == false");
		
	Debug(DEBUG_TRACE, "CDb::GetFirstDB #3 - END");

	return retval;
}

/////////////////////////////////////
// Grab the first entry INT format //
/////////////////////////////////////
string CDb::GetFirstCharDB(int socket, const char *query)
{
	CConn *conn;
	if ((conn = ExecDB(socket, query)) == NULL)
	{
		Debug(DEBUG_ERROR, socket, "CDb::GetFirstCharDB - ExecDB Error");
		return "";
		//conn->m_RowMap[0] = "";
		//return conn->m_RowMap[0].c_str();
	}

	//Debug(DEBUG_TRACE, socket, "CDb::GetFirstCharDB - Before FetchRow");

	if (FetchRow(conn) == false)
	{
		Debug(DEBUG_ERROR, socket, "CDb::GetFirstCharDB - FetchRow Error");
		conn->m_RowMap[0] = "";
		string retstr = conn->m_RowMap[0].c_str();

		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, socket, "CDb::GetFirstCharDB - ThreadReleaseConn == false");

		return retstr;
	}

	//Debug(DEBUG_TRACE, socket, "CDb::GetFirstCharDB - RowMap[0]", conn->m_RowMap[0].c_str());

	string retstr = conn->m_RowMap[0].c_str();
	if (ThreadReleaseConn(conn->m_Resource) == false)
		Debug(DEBUG_ERROR, socket, "CDb::GetFirstCharDB - ThreadReleaseConn == false");
	return retstr;
}

//////////////
// Shortcut //
//////////////
string CDb::GetFirstCharDB(int socket, basic_ostream<char> &query)
//string CDb::GetFirstCharDB(int socket, std::stringstream& query)
{
	//std::string qstring;
	//qstring = query.str();
	stringstream qstring;
	qstring << query.rdbuf();
	return GetFirstCharDB(socket, qstring.str().c_str());
}

//////////////////////////////
// Disconnect from database //
//////////////////////////////
bool CDb::Disconnect()
{
/*
#ifdef COMPILE_MYSQL
	if (m_myConn != NULL)
		mysql_close(m_myConn);
#endif

#ifdef COMPILE_POSTGRES
	if (m_pgConn != NULL)
		PQfinish(m_pgConn);
#endif
*/
	return true;
}

////////////////////////////
// Make json testing easy //
////////////////////////////
bool CDb::TestJson(const char *json1, const char *json2)
{
	int complen = strlen(json1);

	char json[2046];
	memset(json, 0, 2046);
	memcpy(json, json2, complen);

	int retval = 0;
	if ((retval = strcmp(json, json1)) == 0)
		return true;

	return false;
}

////////////////////////
// Get the user count //
////////////////////////
int CDb::GetUserCount(int socket)
{
	std::string query = "SELECT count(*) FROM users LIMIT 1";
	int usercount = GetFirstDB(socket, query.c_str());
	return usercount;
}

///////////////////////////////
// Grab the basic comm rules //
///////////////////////////////
bool CDb::GetBasicCommRules(int socket, int system_id, list <CRulesBasicComm> *pRulesBasicComm)
{
	stringstream ssCount;
	if (GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_basic_commrules WHERE system_id=" << system_id << " AND disabled=false") == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetBasicCommRules - count(*) FROM ce_basic_commrules == 0");

	CConn *conn;
	stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, generation, inv_type, event, qualify_type, start_threshold, end_threshold, percent, modulus, paylimit, pv_override, paytype, rank FROM ce_basic_commrules WHERE system_id=" << system_id << " AND disabled=false")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetBasicCommRules - ExecDB Error");

    while (FetchRow(conn) == true)
    {
    	CRulesBasicComm NewRuleComm;
    	NewRuleComm.m_ID = StrToInt(conn->m_RowMap[0].c_str());
		NewRuleComm.m_Generation = StrToInt(conn->m_RowMap[1].c_str());
		NewRuleComm.m_InvType = StrToInt(conn->m_RowMap[2].c_str());
		NewRuleComm.m_Event = StrToInt(conn->m_RowMap[3].c_str());
		NewRuleComm.m_QualifyType = StrToInt(conn->m_RowMap[4].c_str());
		NewRuleComm.m_StartThreshold = StrToInt(conn->m_RowMap[5].c_str());
		NewRuleComm.m_EndThreshold = StrToInt(conn->m_RowMap[6].c_str());
		NewRuleComm.m_Percent = StrToFloat(conn->m_RowMap[7].c_str());
		NewRuleComm.m_Modulus = StrToFloat(conn->m_RowMap[8].c_str());
		NewRuleComm.m_PayLimit = StrToInt(conn->m_RowMap[9].c_str());
		NewRuleComm.m_PVOverride = StrToBool(conn->m_RowMap[10].c_str());
		NewRuleComm.m_PayType = StrToInt(conn->m_RowMap[11].c_str());
		NewRuleComm.m_Rank = StrToInt(conn->m_RowMap[12].c_str());
		pRulesBasicComm->push_back(NewRuleComm);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetBasicCommRules - ThreadReleaseConn == false");

    return true;
}

/////////////////////////////////////
// Grab the rank rules to abide by //
/////////////////////////////////////
int CDb::GetRankRules(int socket, int system_id, std::list <CRulesRank> *pRulesRank, string tablename)
{
	// ce_rankrules //

	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM " << tablename << " WHERE system_id=" << system_id << " AND disabled=false") == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetRankRules - GetFirstDB Error - rankrules count(*) == 0");

	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, rank, qualify_type, qualify_threshold, achvbonus, breakage, maxdacleg, rulegroup, sumrankstart, sumrankend, label, varid FROM " << tablename << " WHERE system_id=" << system_id << " AND disabled=false ORDER BY rank::INT4, rulegroup::INT4, id")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetRankRules - ExecDB Error");

	int rankmax = 0;

	int count = 0;
	int prevrulegroup = 0;
	while (FetchRow(conn) == true)
    {
    	CRulesRank NewRuleRank;
    	NewRuleRank.m_SystemID = system_id; // Needed for debugging //
    	NewRuleRank.m_ID = StrToInt(conn->m_RowMap[0].c_str());
    	NewRuleRank.m_Rank = StrToInt(conn->m_RowMap[1].c_str());
    	NewRuleRank.m_QualifyType = StrToInt(conn->m_RowMap[2].c_str());
		NewRuleRank.m_QualifyThreshold = StrToFloat(conn->m_RowMap[3].c_str());
		NewRuleRank.m_AchvBonus = StrToFloat(conn->m_RowMap[4].c_str());
		NewRuleRank.m_Breakage = StrToInt(conn->m_RowMap[5].c_str());
		NewRuleRank.m_MaxDacLeg = StrToInt(conn->m_RowMap[6].c_str());
		NewRuleRank.m_RuleGroup = StrToInt(conn->m_RowMap[7].c_str());
		NewRuleRank.m_SumRankStart = StrToInt(conn->m_RowMap[8].c_str());
		NewRuleRank.m_SumRankEnd = StrToInt(conn->m_RowMap[9].c_str());
		sprintf(NewRuleRank.m_Label, "%s", conn->m_RowMap[10].c_str());
		NewRuleRank.m_VarID = conn->m_RowMap[11];

		// Set until last rule //
		if (NewRuleRank.m_Rank > rankmax)
			rankmax = NewRuleRank.m_Rank;

		// Handle End Rule Group Flag //
		if (NewRuleRank.m_RuleGroup == 0)
			NewRuleRank.m_EndFlag = true; // All RuleGroup=0 have endflag //
		if ((prevrulegroup != NewRuleRank.m_RuleGroup) && (count != 0))
		{
			CRulesRank endrule = pRulesRank->back();
			endrule.m_EndFlag = true; // Previous RuleGroup has endflag //
			pRulesRank->pop_back();
			pRulesRank->push_back(endrule);
		}

		prevrulegroup = NewRuleRank.m_RuleGroup; // Retain to compare next loop around //
		pRulesRank->push_back(NewRuleRank);
		count++;
    }

    if (pRulesRank->size() > 0)
    {
	    // Handle the last end flag //
	    CRulesRank endrule = pRulesRank->back();
		endrule.m_EndFlag = true; // Previous RuleGroup has endflag //
		pRulesRank->pop_back();
		pRulesRank->push_back(endrule);
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetRankRules - ThreadReleaseConn == false");
    
    // Return the max rank //
    return rankmax;
}

///////////////////////////////////////////
// Grab the commission rules to abide by //
///////////////////////////////////////////
bool CDb::GetCommRules(int socket, int system_id, std::list <CRulesComm> *pRulesComm)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_commrules WHERE system_id=" << system_id << " AND disabled=false") == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetCommRules - GetFirstDB Error - ce_commrules count(*) == 0");

	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, rank, generation, infinitybonus, percent, inv_type, event, paytype, forcepay, dollar FROM ce_commrules WHERE system_id=" << system_id << " AND disabled=false ORDER BY rank::int4")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetCommRules - ExecDB Error");

    while (FetchRow(conn) == true)
    {
    	CRulesComm NewRuleComm;
    	NewRuleComm.m_ID = StrToInt(conn->m_RowMap[0].c_str());
		NewRuleComm.m_Rank = StrToInt(conn->m_RowMap[1].c_str());
		NewRuleComm.m_Generation = StrToInt(conn->m_RowMap[2].c_str());
		NewRuleComm.m_InfinityBonus = StrToBool(conn->m_RowMap[3].c_str());
		NewRuleComm.m_Percent = StrToFloat(conn->m_RowMap[4].c_str());
		NewRuleComm.m_InvType = StrToInt(conn->m_RowMap[5].c_str());
		NewRuleComm.m_Event = StrToInt(conn->m_RowMap[6].c_str());
		NewRuleComm.m_PayType = StrToInt(conn->m_RowMap[7].c_str());
		NewRuleComm.m_ForcePay = StrToBool(conn->m_RowMap[8].c_str());
		NewRuleComm.m_Dollar = StrToFloat(conn->m_RowMap[9].c_str());
		pRulesComm->push_back(NewRuleComm);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetCommRules - ThreadReleaseConn == false");

    return true;
}
 
////////////////////
// Grab the users //
////////////////////
bool CDb::GetUsers(int socket, int system_id, bool include_disabled, std::map <std::string, CUser> &UsersMap, int upline, const char *start_date, const char *end_date)
{
	Debug(DEBUG_DEBUG, "CDb::GetUsers - BEGIN - system_id", system_id);

	if (system_id < 1)
		return Debug(DEBUG_ERROR, "CDb::GetUsers system_id < 1");
// Remove compile warning osx //
	//if (&UsersMap == NULL)
	//	return Debug(DEBUG_ERROR, "CDb::GetUsers &UsersMap == NULL");
	if (start_date == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetUsers start_date == NULL");
	if (end_date == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetUsers end_date == NULL");
	if (strlen(start_date) < 8)
		return Debug(DEBUG_ERROR, "CDb::GetUsers start_date < 8");
	if (strlen(end_date) < 8)
		return Debug(DEBUG_ERROR, "CDb::GetUsers end_date < 8", end_date);

	CConn *conn;
	std::stringstream ss;
	if (include_disabled == true)
	{
		if ((conn = ExecDB(socket, ss << "SELECT user_id, parent_id, sponsor_id, signup_date, usertype, carrer_rank, upline_advisor, disabled FROM ce_users WHERE system_id=" << system_id << " ORDER BY id")) == NULL)
			return Debug(DEBUG_ERROR, "CDb::GetUsers - ExecDB user Error #1");
	}
	else
	{
		if ((conn = ExecDB(socket, ss << "SELECT user_id, parent_id, sponsor_id, signup_date, usertype, carrer_rank, upline_advisor, disabled FROM ce_users WHERE system_id=" << system_id << " AND disabled=false ORDER BY id")) == NULL)
			return Debug(DEBUG_ERROR, "CDb::GetUsers - ExecDB user Error #2");
	}
 
	while (FetchRow(conn) == true)
    {
    	//Debug(DEBUG_DEBUG, "CDb::GetUsers - m_RowMap[0].c_str()", m_RowMap[0].c_str());

    	std::string user_id = conn->m_RowMap[0].c_str();
    	std::string parent_id = conn->m_RowMap[1].c_str();
    	std::string sponsor_id = conn->m_RowMap[2].c_str();
    	UsersMap[user_id].m_SignupDate = conn->m_RowMap[3].c_str();
		UsersMap[user_id].m_UserType = StrToInt(conn->m_RowMap[4].c_str()); // They need to requalify every pay period //

    	// Handle check on if sponsorcount get's credit //
    	UsersMap[user_id].m_UserID = user_id;
		UsersMap[user_id].m_SponsorID = sponsor_id;
		UsersMap[user_id].m_ParentID = parent_id;
		UsersMap[user_id].m_CarrerRank = StrToInt(conn->m_RowMap[5].c_str());
		UsersMap[user_id].m_UplineAdvisor = conn->m_RowMap[6];

    	if (conn->m_RowMap[7] == "f")
    		UsersMap[user_id].m_Disabled = false; // Track if user hasn't paid (United) //
    	else if (conn->m_RowMap[7] == "t")
    		UsersMap[user_id].m_Disabled = true; // Track if user hasn't paid (United) //
    }

    int usercount = UsersMap.size();
    Debug(DEBUG_DEBUG, "CDb::GetUsers - usercount", usercount);

    // Handle sponsor pointer after all users loaded, cause it can potentially point further down //
    std::map <std::string, CUser>::iterator j;
	for (j=UsersMap.begin(); j != UsersMap.end(); ++j)
	{
		CUser *puser = &j->second; //&m_UsersMap[j->first];

		// Point to the sponsor mem address to speed things up //
		if ((puser->m_SponsorID != "0") && (puser->m_SponsorID != ""))
		{
			// Add pointer to parent and sponsor //
			UsersMap[puser->m_UserID].m_pParent = &UsersMap[puser->m_ParentID];
			UsersMap[puser->m_UserID].m_pSponsor = &UsersMap[puser->m_SponsorID];

			// Add pointer to underling for binary legs //
			UsersMap[puser->m_SponsorID].m_CommLegsLL.push_back(&UsersMap[puser->m_UserID]);

			CDateCompare date_window(start_date, end_date);
			if (date_window.IsBetween(puser->m_SignupDate.c_str()) == true)
			{
				UsersMap[puser->m_SponsorID].m_SignupCount++;
			}
		}
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetUsers - ThreadReleaseConn == false");
    
    // Read in historical achivements bonuses to speed up reverse lookup //
	GetAchvBonuses(socket, system_id);

    return true;
}

///////////////////////////////////////////
// Get the ranks seperate from the users //
///////////////////////////////////////////
bool CDb::GetRanks(int socket, int system_id, int batch_id, std::map <std::string, CUser> &UsersMap)
{
	// Make sure there are ranks to pull from //
	stringstream ssCount;
	ssCount << "SELECT count(*) FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id;
	if (GetFirstDB(socket, ssCount) == 0)
		return true;

	std::stringstream ssRank;
//#ifdef COMPILE_UNITED // Always pull rank from system=1 //
//	if (ExecDB(ssRank << "SELECT DISTINCT user_id, rank FROM ce_ranks WHERE system_id=1 AND batch_id=" << batch_id << " AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=" << system_id << ")") == false)
//		return Debug(DEBUG_ERROR, "CDb::GetRanks - ExecDB rank (#1) Error");
//#else
	CConn *conn;
	if ((conn = ExecDB(socket, ssRank << "SELECT DISTINCT user_id, rank FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetRanks - ExecDB rank (#2) Error");
//#endif

	int count = 0;
	while (FetchRow(conn) == true) 
    {
    	std::string user_id = conn->m_RowMap[0].c_str();
    	int rank = atoi(conn->m_RowMap[1].c_str());
    	UsersMap[user_id].m_Rank = rank;

    	count++;
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetRanks - ThreadReleaseConn == false");

    return true;
}

///////////////////////////////////////////////////////////
// Get the users at the bottom of pyramid scheme. Lmao!! //
///////////////////////////////////////////////////////////
bool CDb::GetUserEnds(int socket, int system_id, std::map <std::string, CUser> &UsersMap, const char *start_date, const char *end_date)
{
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT user_id, sponsor_id, signup_date, usertype FROM ce_users WHERE system_id=" << system_id << " AND user_id NOT IN (SELECT DISTINCT sponsor_id FROM ce_users WHERE system_id=" << system_id << ")")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetUsers - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	std::string user_id = conn->m_RowMap[0].c_str();

    	// Handle check on if sponsorcount get's credit //
    	UsersMap[user_id].m_UserID = conn->m_RowMap[0].c_str();
		UsersMap[user_id].m_SponsorID = conn->m_RowMap[1].c_str();
		UsersMap[user_id].m_SignupDate = conn->m_RowMap[2].c_str();
		UsersMap[user_id].m_UserType = StrToInt(conn->m_RowMap[3].c_str()); // They need to requalify every pay period //
    }

    // Put here will speed things up //
    CDateCompare date_window(start_date, end_date);

    // Handle sponsor pointer after all users loaded, cause it can potentially point further down //
    std::map <std::string, CUser>::iterator j;
	for (j=UsersMap.begin(); j != UsersMap.end(); ++j)
	{
		CUser *puser = &j->second; //&m_UsersMap[j->first];

		// Point to the sponsor mem address to speed things up //
		if ((puser->m_SponsorID != "0") && (puser->m_SponsorID != ""))
		{
			// Add pointer to sponsor //
			UsersMap[puser->m_UserID].m_pSponsor = &UsersMap[puser->m_SponsorID];

			// Add pointer to underling for binary legs //
			UsersMap[puser->m_SponsorID].m_CommLegsLL.push_back(&UsersMap[puser->m_UserID]);

			if (date_window.IsBetween(puser->m_SignupDate.c_str()) == true)
			{
				UsersMap[puser->m_SponsorID].m_SignupCount++;
			}
		}
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetUserEnds - ThreadReleaseConn == false");
    return true;
}

////////////////////////////////////
// This needed to speed up lookup //
////////////////////////////////////
bool CDb::GetAchvBonuses(int socket, int system_id)
{
	// Make sure there are records //
	stringstream ss0;
	ss0 << "SELECT count(*) FROM ce_achvbonus WHERE system_id=" << system_id;
	if (GetFirstDB(socket, ss0) == 0)
		return true;

	//Debug(DEBUG_DEBUG, "CDb::GetAchvBonuses - After GetFirstDB");

	// Pull the records to memory //
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT user_id, rank, amount FROM ce_achvbonus WHERE system_id=" << system_id)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetAchvBonus - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	std::string user_id = conn->m_RowMap[0].c_str();
    	int rank = atoi(conn->m_RowMap[1].c_str());
    	m_AchvLookup[user_id].m_Amount[rank] = atof(conn->m_RowMap[2].c_str());
 	}

 	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetAchvBonuses - ThreadReleaseConn == false");
 	return true;
}

/////////////////////////////////////
// Handle Loading Rank Bonus Rules //
/////////////////////////////////////
bool CDb::GetRankBonusRules(int socket, int system_id, list <CRulesRankBonus> *pRulesRankBonus)
{
	// Make sure there are records //
	stringstream ss0;
	ss0 << "SELECT count(*) FROM ce_rankbonusrules WHERE system_id=" << system_id;
	if (GetFirstDB(socket, ss0) == 0)
		return true;

	//Debug(DEBUG_DEBUG, "CDb::GetRankBonusRules - After GetFirstDB");

	// Pull the records to memory //
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, rank, bonus FROM ce_rankbonusrules WHERE system_id=" << system_id)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetRankBonusRules - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	CRulesRankBonus NewBonusRule;
    	NewBonusRule.m_ID = atoi(conn->m_RowMap[0].c_str());
    	NewBonusRule.m_Rank = atoi(conn->m_RowMap[1].c_str());
    	NewBonusRule.m_Bonus = atof(conn->m_RowMap[2].c_str());

    	// Add to the linked list  //
    	pRulesRankBonus->push_back(NewBonusRule);
 	}

 	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetRankBonusRules - ThreadReleaseConn == false");
 	return true;

}

/////////////////////////////
// Get a count of receipts //
/////////////////////////////
int CDb::GetReceiptCount(int socket, int system_id, const char *start_date, const char *end_date)
{
	stringstream ssTZ;
	string timezone = GetFirstCharDB(socket, ssTZ << "SELECT value FROM ce_settings WHERE system_id=0 AND varname='timezone'");

	std::stringstream ss;
	//return GetFirstDB(socket, ss << "SELECT count(*) FROM ce_receipts WHERE system_id=" << system_id << " AND ((wholesale_date >='" << start_date << "' AND wholesale_date <='" << end_date << "') OR (retail_date >='" << start_date << "' AND retail_date <='" << end_date << "'))");

	// Accommodate for timezones //
	return GetFirstDB(socket, ss << "SELECT count(*) FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "') OR ((retail_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (retail_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "'))");
}

///////////////////////
// Grab all receipts //
///////////////////////
double CDb::GetReceipts(int socket, int system_id, std::map <std::string, CUser> &UsersMap, std::list <CReceipt> &ReceiptsLL, const char *start_date, const char *end_date, CReceiptTotal *preceipts)
{
	//Debug(DEBUG_TRACE, "CDb::GetReceipts - TOP - system_id", system_id);

	CConn *conn = NULL;
#ifdef COMPILE_UNITED
	
	if (system_id == -1) // Tokens Used = -1 //
	{
		std::stringstream ss;
		if ((conn = ExecDB(socket, ss << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, commissionable, usertype, inv_type FROM ce_receipts WHERE system_id!=1 AND ((wholesale_date >='" << start_date << "' AND wholesale_date <='" << end_date << "'))")) == NULL)
		{
			Debug(DEBUG_ERROR, "CDb::GetReceipts - ExecDB Error");
			return -1;
		}
	}
	else
	{
		std::stringstream ss;
		if ((conn = ExecDB(socket, ss << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, commissionable, usertype, inv_type FROM ce_receipts WHERE system_id=" << system_id << " AND ((wholesale_date >='" << start_date << "' AND wholesale_date <='" << end_date << "'))")) == NULL)
		{
			Debug(DEBUG_ERROR, "CDb::GetReceipts - ExecDB Error");
			return -1;
		}
	}

#else

	// Handle timezones so cutoff sales are at midnight //
	stringstream ssTZ;
	string timezone = "UTC"; //GetFirstCharDB(socket, ssTZ << "SELECT value FROM ce_settings WHERE system_id=0 AND varname='timezone'");
	//Debug(DEBUG_DEBUG, "CDb::GetReceipts - timezone", timezone.c_str());

	// Handle normal //
	if (system_id != -1)
	{
		// Original //
		//if ((conn = ExecDB(socket, ss << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, commissionable, usertype, inv_type FROM ce_receipts WHERE system_id=" << system_id << " AND ((wholesale_date >='" << start_date << "' AND wholesale_date <='" << end_date << "') OR (retail_date >='" << start_date << "' AND retail_date <='" << end_date << "'))")) == NULL)
		// With timezone UTC adjustment //
		//if ((conn = ExecDB(socket, ss << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, (wholesale_date AT TIME ZONE '" << timezone << "')::DATE, (retail_date AT TIME ZONE '" << timezone << "')::DATE, commissionable, usertype, inv_type, metadata_onadd FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "') OR ((retail_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (retail_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "'))")) == NULL)
		// Chalkcouture problem: Don't grab records with retaildate and invtype=1 (Wholesale) //
			
		/////////////////////////////////////////////////////////////////
		// THIS IS WHERE inv_type='1' FORCED UNTIL NEW RULESET WRITTEN // Ringbomb is 1 // Piphany is 5 //
		/////////////////////////////////////////////////////////////////
		//if ((conn = ExecDB(socket, ss << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, (wholesale_date AT TIME ZONE '" << timezone << "')::DATE, (retail_date AT TIME ZONE '" << timezone << "')::DATE, commissionable, usertype, inv_type, metadata_onadd FROM ce_receipts WHERE system_id=" << system_id << " AND inv_type='1' AND disabled=false AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "') OR (inv_type!=1 AND (retail_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (retail_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "'))")) == NULL)
		
		// No ce_receipt_filter needed //
		stringstream ssCount;
		std::stringstream ssRecords;
		if ((GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_receipts_filter WHERE system_id=" << system_id)) == 0)
		{
			if ((conn = ExecDB(socket, ssRecords << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, (wholesale_date AT TIME ZONE '" << timezone << "')::DATE, (retail_date AT TIME ZONE '" << timezone << "')::DATE, commissionable, usertype, inv_type, metadata_onadd FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "') OR (retail_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (retail_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "')")) == NULL)
			{
				Debug(DEBUG_ERROR, "CDb::GetReceipts - ExecDB Error #1");
				return -1;
			}
		}
		else // Use ce_receipt_filter if there are entries //
		{
			// RETAIL receipts - Piphany //
			std::stringstream ssQlfyCount;
			ssQlfyCount << "SELECT count(*) FROM ce_rankrules WHERE system_id=" << system_id << " AND qualify_type='13' OR qualify_type='15' OR qualify_type='25'";
			if (GetFirstDB(socket, ssQlfyCount.str().c_str()) > 0)
			{
				ssRecords << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, (wholesale_date AT TIME ZONE '" << timezone << "')::DATE, (retail_date AT TIME ZONE '" << timezone << "')::DATE, commissionable, usertype, inv_type, metadata_onadd FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND inv_type IN (SELECT DISTINCT inv_type FROM ce_receipts_filter WHERE disabled=false) AND ((retail_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (retail_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "')";
				if ((conn = ExecDB(socket, ssRecords.str().c_str())) == NULL)
				{
					Debug(DEBUG_ERROR, "CDb::GetReceipts - ExecDB Error #2");
					return -1;
				}
			}
			else // WHOLESALE receipts - Ringbomb and everyone else //
			{
				//////////////////////////////////////////////////////////
				// There is a problem mixing wholesale and retail dates //
				//////////////////////////////////////////////////////////
				//ssRecords << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, (wholesale_date AT TIME ZONE '" << timezone << "')::DATE, (retail_date AT TIME ZONE '" << timezone << "')::DATE, commissionable, usertype, inv_type, metadata_onadd FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND inv_type IN (SELECT DISTINCT inv_type FROM ce_receipts_filter WHERE disabled=false) AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "') OR (retail_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (retail_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "')";
				ssRecords << "SELECT id, receipt_id, user_id, wholesale_price, retail_price, (wholesale_date AT TIME ZONE '" << timezone << "')::DATE, (retail_date AT TIME ZONE '" << timezone << "')::DATE, commissionable, usertype, inv_type, metadata_onadd FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND inv_type IN (SELECT DISTINCT inv_type FROM ce_receipts_filter WHERE disabled=false) AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "'))";
				//Debug(DEBUG_ERROR, ssRecords.str().c_str());
				if ((conn = ExecDB(socket, ssRecords.str().c_str())) == NULL)
				{
					Debug(DEBUG_ERROR, "CDb::GetReceipts - ExecDB Error #2");
					return -1;
				}
			}
		}
	}
#endif

	if (conn == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::GetReceipts - conn == NULL");
		return -1;
	}

	CDateCompare date_window(start_date, end_date);

	while (FetchRow(conn) == true)
    {
    	CReceipt NewReceipt;
    	NewReceipt.m_ID = StrToInt(conn->m_RowMap[0].c_str());
    	NewReceipt.m_ReceiptID = StrToInt(conn->m_RowMap[1].c_str());
		NewReceipt.m_UserID = conn->m_RowMap[2];
		NewReceipt.m_WholesalePrice = StrToFloat(conn->m_RowMap[3].c_str());
		NewReceipt.m_RetailPrice = StrToFloat(conn->m_RowMap[4].c_str());
		NewReceipt.m_WholesaleDate = conn->m_RowMap[5].c_str();
		NewReceipt.m_RetailDate = conn->m_RowMap[6].c_str();
		if (conn->m_RowMap[7] == "t")
			NewReceipt.m_Commissionable = true;
		else if (conn->m_RowMap[7] == "f")
			NewReceipt.m_Commissionable = false;
		NewReceipt.m_UserType = atoi(conn->m_RowMap[8].c_str());
		NewReceipt.m_InvType = atoi(conn->m_RowMap[9].c_str());
		NewReceipt.m_MetaDataOnAdd = conn->m_RowMap[10].c_str();

		// Store the receipt information with the user //
		UsersMap[NewReceipt.m_UserID].m_PersonalPurchase += NewReceipt.m_WholesalePrice;

		if (NewReceipt.m_InvType == 5)
		{
			UsersMap[NewReceipt.m_UserID].m_CorpWholeSales += NewReceipt.m_WholesalePrice; // Is this wholesale or retail price? //
			UsersMap[NewReceipt.m_UserID].m_CorpRetailSales += NewReceipt.m_RetailPrice; // Is this wholesale or retail price? //
		}

		// Compare and store bool for later possible commission earned //
		if (date_window.IsBetween(NewReceipt.m_WholesaleDate.c_str()) == true)
			NewReceipt.m_EventWholesale = true;
		if (date_window.IsBetween(NewReceipt.m_RetailDate.c_str()) == true)
			NewReceipt.m_EventRetail = true;

		ReceiptsLL.push_back(NewReceipt); // Store in memory to prevent db hit x2 //

		// Do actual receipt ladder here //
//		CUser *puser = &UsersMap[NewReceipt.m_UserID];
		preceipts->m_WholesaleTotal = preceipts->m_WholesaleTotal + NewReceipt.m_WholesalePrice;
		preceipts->m_RetailTotal = preceipts->m_RetailTotal + NewReceipt.m_RetailPrice;

/*		if ((NewReceipt.m_UserType == USERTYPE_CUSTOMER) && (NewReceipt.m_UserID != puser->m_UserID))
		{
			puser->m_CustomerWholesaleSales += NewReceipt.m_WholesalePrice;
			puser->m_CustomerRetailSales += NewReceipt.m_RetailPrice;
		}
		if ((NewReceipt.m_UserType == USERTYPE_RESELLER) && (NewReceipt.m_UserID != puser->m_UserID))
		{
			puser->m_ResellerWholesaleSales += NewReceipt.m_WholesalePrice;
			puser->m_ResellerRetailSales += NewReceipt.m_RetailPrice;
		}
		if ((NewReceipt.m_UserType == USERTYPE_AFFILIATE) && (NewReceipt.m_UserID != puser->m_UserID))
		{
			puser->m_AffiliateWholesaleSales += NewReceipt.m_WholesalePrice;
			puser->m_AffiliateRetailSales += NewReceipt.m_RetailPrice;
		}
		if (NewReceipt.m_UserID == puser->m_UserID)
		{
			puser->m_MyWholesaleSales += NewReceipt.m_WholesalePrice;
			puser->m_MyRetailSales += NewReceipt.m_RetailPrice;
		}
*/
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetReceipts - ThreadReleaseConn == false");

    return preceipts->m_WholesaleTotal;
}

/////////////////////////////
// Grab the rank gen bonus //
/////////////////////////////  
bool CDb::GetRankGenBonus(int socket, int system_id, std::list <CRankGenBonus> *pRankGenBonus)
{
	CConn *conn = NULL;

	stringstream ss1;
	if ((GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_rankgenbonusrules WHERE system_id=" << system_id)) == 0)
		return Debug(DEBUG_INFO, "CDb::GetGenRankBonus - GetFirstDB Error");

	stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, my_rank, user_rank, generation, bonus FROM ce_rankgenbonusrules WHERE system_id=" << system_id)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetGenRankBonus - ExecDB Error");

	if (conn == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetGenRankBonus - conn == NULL");

	while (FetchRow(conn) == true)
    {
    	CRankGenBonus NewBonus;
    	NewBonus.m_ID = StrToInt(conn->m_RowMap[0].c_str());
    	NewBonus.m_MyRank = StrToInt(conn->m_RowMap[1].c_str());
    	NewBonus.m_UserRank = StrToInt(conn->m_RowMap[2].c_str());
		NewBonus.m_Generation = StrToInt(conn->m_RowMap[3].c_str());
		NewBonus.m_Bonus = StrToFloat(conn->m_RowMap[4].c_str());
		pRankGenBonus->push_back(NewBonus);
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetGenRankBonus - ThreadReleaseConn == false");

	return true;
}

///////////////////////////////
// Get records for lvl1 rank //
///////////////////////////////
bool CDb::GetStatLvl1Rank(int socket, int system_id, int batch_id, int rank, map <string, int> &pStatsLvlRank)
{
	CConn *conn = NULL;

	stringstream ss1;
	if ((GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_userstats_month_lvl1_rank WHERE system_id='" << system_id << "' AND batch_id='" << batch_id << "' AND rank='" << rank << "'")) == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetStatLvl1Rank - GetFirstDB Error");

	stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT user_id, total FROM ce_userstats_month_lvl1_rank WHERE system_id='" << system_id << "' AND batch_id='" << batch_id << "' AND rank='" << rank << "'")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetStatLvl1Rank - ExecDB Error");

	if (conn == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetStatLvl1Rank - conn == NULL");

	while (FetchRow(conn) == true)
    {
    	string userid = conn->m_RowMap[0].c_str();
		pStatsLvlRank[userid] = StrToInt(conn->m_RowMap[1].c_str());
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetStatLvl1Rank - ThreadReleaseConn == false");

	return true;
}

//////////////////////////////
// Get records for leg rank //
//////////////////////////////
bool CDb::GetStatLegRank(int socket, int system_id, int batch_id, int generation, int rank, map <string, int> &pStatsLegRank)
{
	CConn *conn = NULL;

	stringstream ss1;
	if ((GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_userstats_month_leg_rank WHERE system_id='" << system_id << "' AND batch_id='" << batch_id << "' AND generation='" << generation << "' AND rank='" << rank << "'")) == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetStatLegRank - GetFirstDB Error - rank", rank);

	stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT user_id, total FROM ce_userstats_month_leg_rank WHERE system_id='" << system_id << "' AND batch_id='" << batch_id << "' AND generation='" << generation << "' AND rank='" << rank << "'")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetStatLegRank - ExecDB Error - rank", rank);

	if (conn == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetStatLegRank - conn == NULL");

	while (FetchRow(conn) == true)
    {
    	//CStatsLegRank NewStatsLegRank;
    	//NewStatsLegRank.m_UserID = conn->m_RowMap[0].c_str();
    	//NewStatsLegRank.m_Total = StrToInt(conn->m_RowMap[1].c_str());
		//pStatsLegRank->push_back(NewStatsLegRank);

		string userid = conn->m_RowMap[0].c_str();
		pStatsLegRank[userid] = StrToInt(conn->m_RowMap[1].c_str());

		//if (userid == "949")
		//{
		//	stringstream ss;
		//	ss << "userid=949, rank=" << rank << ", total=" << conn->m_RowMap[1].c_str();
		//	Debug(DEBUG_ERROR, ss.str().c_str());
		//}
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetStatLegRank - ThreadReleaseConn == false");

	return true;
}

///////////////////////////////////
// Grab all the fast start rules //
///////////////////////////////////
bool CDb::GetFastStartRules(int socket, int system_id, list <CFastStartRules> *pFastStartRules)
{
	CConn *conn = NULL;

	stringstream ss1;
	if ((GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_faststart WHERE system_id='" << system_id << "'")) == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetFastStartRules - GetFirstDB Error - count(*) == 0");

	stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, rank, qualify_type, qualify_threshold, days_count, bonus, rulegroup FROM ce_faststart WHERE system_id='" << system_id << "'")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetFastStartRules - ExecDB SELECT Error");

	if (conn == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetFastStartRules - conn == NULL");

	while (FetchRow(conn) == true)
    {
    	CFastStartRules newfast;
    	newfast.m_ID = StrToInt(conn->m_RowMap[0].c_str());
    	newfast.m_Rank = StrToInt(conn->m_RowMap[1].c_str());
    	newfast.m_QualifyType = StrToInt(conn->m_RowMap[2].c_str());
    	newfast.m_QualifyThreshold = StrToInt(conn->m_RowMap[3].c_str());
    	newfast.m_DaysCount = StrToInt(conn->m_RowMap[4].c_str());
    	newfast.m_RuleGroup = StrToInt(conn->m_RowMap[5].c_str());

    	pFastStartRules->push_back(newfast);
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetFastStartRules - ThreadReleaseConn == false");

	return true;
}

/////////////////////////////////////
// Lookup the title from rankrules //
/////////////////////////////////////
string CDb::MemLookupTitle(int rank, list <CRulesRank> *pRulesRank)
{
	list<CRulesRank>::iterator i;
	for (i=pRulesRank->begin(); i != pRulesRank->end(); ++i) 
	{
		if ((*i).m_Rank == rank)
			return (*i).m_Label;
	}

	return "";
}

/////////////////////////
// Grab the pool rules //
/////////////////////////
bool CDb::GetPoolRules(int socket, int system_id, int poolpot_id, std::list <CRulesPool> *pRulesPool)
{
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT start_rank, end_rank, qualify_threshold FROM ce_poolrules WHERE system_id=" << system_id << " AND poolpot_id='" << poolpot_id << "' AND disabled=false")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetPoolRules - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	CRulesPool NewRule;
    	NewRule.m_StartRank = StrToInt(conn->m_RowMap[0].c_str());
		NewRule.m_EndRank = StrToInt(conn->m_RowMap[1].c_str());
		NewRule.m_QualifyThreshold = StrToInt(conn->m_RowMap[2].c_str());

		pRulesPool->push_back(NewRule);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetPoolRules - ThreadReleaseConn == false");

    return true;
}

//////////////////////////////
// Get list of systems used //
//////////////////////////////
bool CDb::GetSystemsUsed(int socket, int system_id, std::list <int> *pSystemsUsed)
{
	std::stringstream ss;
	int sysuser_id = GetFirstDB(socket, ss << "SELECT sysuser_id FROM ce_systems WHERE id='" << system_id << "'");

	CConn *conn;
	std::stringstream ss2;
#ifdef COMPILE_UNITED
	if ((conn = ExecDB(socket, ss2 << "SELECT id FROM ce_systems WHERE sysuser_id='" << sysuser_id << "' AND id!='" << system_id << "'")) == NULL)
#else
	if ((conn = ExecDB(socket, ss2 << "SELECT id FROM ce_systems WHERE sysuser_id='" << sysuser_id << "'")) == NULL)
#endif
		return Debug(DEBUG_ERROR, "CDb::GetSystemsUsed - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	pSystemsUsed->push_back(StrToInt(conn->m_RowMap[0].c_str()));
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetSystemsUsed - ThreadReleaseConn == false");

    return true;
}

//////////////////////////////
// Get list of systems used //
//////////////////////////////
bool CDb::GetSystemsSpeed(int socket, int system_id, int start_sys_id, int end_sys_id, std::list <CSystem> *pSystems)
{
	std::stringstream ss;
	int sysuser_id = GetFirstDB(socket, ss << "SELECT sysuser_id FROM ce_systems WHERE id='" << system_id << "'");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, commtype FROM ce_systems WHERE sysuser_id='" << sysuser_id << "' AND id!=" << system_id << " AND id >=" << start_sys_id << " AND id <=" << end_sys_id)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetSystemsUsed - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	CSystem system;
    	system.m_SystemID = StrToInt(conn->m_RowMap[0].c_str());
    	system.m_CommType = StrToInt(conn->m_RowMap[1].c_str());
    	pSystems->push_back(system);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetSystemsSpeed - ThreadReleaseConn == false");

    return true;
}

////////////////////
// Get group used //
////////////////////
bool CDb::GetGroupUsed(int socket, int system_id, std::map <std::string, CUser> &UsersMap)
{
	// Grab the system user_id //
	std::stringstream ss;
	int sysuser_id = GetFirstDB(socket, ss << "SELECT sysuser_id FROM ce_systems WHERE id='" << system_id << "'");

	// Grab all userstats_month records for ALTCORE_UNITED_GAME //
	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT user_id, group_sales FROM ce_userstats_month WHERE system_id!=" << system_id << " AND system_id IN (SELECT system_id FROM ce_systems WHERE sysuser_id='" << sysuser_id << "')")) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetSystemsUsed - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	std::string userid = conn->m_RowMap[0];
    	UsersMap[userid].m_GroupUsed += atof(conn->m_RowMap[1].c_str());
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetGroupUsed - ThreadReleaseConn == false");

    return true;
}

/////////////////////////////
// Get Grand Total Records //
/////////////////////////////
bool CDb::GetGrandTotals(int socket, int system_id, int batch_id, std::list <CGrandTotal> *pGrandTotals)
{
	// Make sure there are records //
	stringstream ssCount;
	ssCount << "SELECT count(*) FROM ce_grandtotals WHERE system_id=" << system_id << " AND batch_id=" << batch_id;
	if (GetFirstDB(socket, ssCount) == 0)
		return Debug(DEBUG_WARN, "CDb::GetGrandTotals - Problems with 0 ce_grandtotal records");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, user_id, amount FROM ce_grandtotals WHERE system_id=" << system_id << " AND batch_id=" << batch_id)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetGrandTotals - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	CGrandTotal grand;
    	grand.m_ID = atoi(conn->m_RowMap[0].c_str());
    	grand.m_UserID = conn->m_RowMap[1].c_str();
    	grand.m_Amount = atof(conn->m_RowMap[2].c_str());
    	pGrandTotals->push_back(grand);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetGrandTotals - ThreadReleaseConn == false");

    return true;
}

//////////////////////////////////////
// Get check match commission rules //
//////////////////////////////////////
bool CDb::GetCMCommRules(int socket, int system_id, std::list <CRulesComm> *pRulesComm)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_cmcommrules WHERE system_id=" << system_id << " AND disabled=false") == 0)
		return Debug(DEBUG_DEBUG, "CDb::GetCMCommRules - GetFirstDB Error - ce_cmcommrules count(*) == 0");

	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, rank, generation, percent FROM ce_cmcommrules WHERE system_id=" << system_id << " AND disabled=false")) == NULL)
		Debug(DEBUG_ERROR, "CDb::GetCMCommRules - ExecDB Error");

    while (FetchRow(conn) == true)
    {
    	CRulesComm NewRuleComm;
    	NewRuleComm.m_ID = StrToInt(conn->m_RowMap[0].c_str());
		NewRuleComm.m_Rank = StrToInt(conn->m_RowMap[1].c_str());
		NewRuleComm.m_Generation = StrToInt(conn->m_RowMap[2].c_str());
		NewRuleComm.m_Percent = StrToFloat(conn->m_RowMap[3].c_str());
		pRulesComm->push_back(NewRuleComm);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetCMCommRules - ThreadReleaseConn == false");

    return true;
}

////////////////////////
// Get Ledger Records //
////////////////////////
bool CDb::GetLedgerRecs(int socket, int system_id, int batch_id, int ledger_type, std::list <CGrandTotal> *pGrandTotals)
{
	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, user_id, amount FROM ce_ledger WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND ledger_type=" << ledger_type)) == NULL)
		return Debug(DEBUG_ERROR, "CDb::GetGrandTotals - ExecDB Error");

	while (FetchRow(conn) == true)
    {
    	CGrandTotal grand;
    	grand.m_ID = atoi(conn->m_RowMap[0].c_str());
    	grand.m_UserID = conn->m_RowMap[1].c_str();
    	grand.m_Amount = atof(conn->m_RowMap[2].c_str());
    	pGrandTotals->push_back(grand);
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::GetLedgerRecs - ThreadReleaseConn == false");

    return true;
}

//////////////////////////////////////////////
// Get the salt of the given system user id //
//////////////////////////////////////////////
string CDb::GetSysUserSalt(int socket, int sysuser_id)
{
	CConn *conn;
	std::stringstream ss1;
	if ((conn = ExecDB(socket, ss1 << "SELECT salt FROM ce_systemusers WHERE id='" << sysuser_id << "'")) == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::GetSysUserSalt - ExecDB Error");
		return "";
	}

	if (FetchRow(conn) == false)
	{
    	Debug(DEBUG_ERROR, "CDb::GetSysUserSalt - FetchRow Error");
    	return "";
	}

	string retstr = conn->m_RowMap[0];
	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::GetLedgerRecs - ThreadReleaseConn == false");
		return "";
	}

    return retstr;
}

//////////////////////////////////////////
// Update the apikey of the system user //
//////////////////////////////////////////
bool CDb::UpdateSysUserApiKey(int socket, int sysuser_id, const char *apikey)
{
	std::stringstream ss1;
	if (ExecDB(socket, ss1 << "UPDATE ce_systemusers SET apikey_hash='" << apikey << "' WHERE id='" << sysuser_id << "'") == NULL)
		return Debug(DEBUG_ERROR, "CDb::UpdateSysUserApiKey - ExecDB Error");

	return true;
}

/////////////////////////////////////////////////
// Is the QLFY_GROUP_USED rules used for rank? //
/////////////////////////////////////////////////
bool CDb::IsGroupUsedRank(int socket, int system_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_rankrules WHERE system_id='" << system_id << "' AND qualify_type='" << QLFY_GROUP_USED << "'") == 0)
		return false;

	return true;
}

/////////////////////////////////////////////
// Allow rank override to increase payouts //
/////////////////////////////////////////////
bool CDb::SetRankOverride(int rank)
{
	g_RankOverride = rank;
	return true;
}

//////////////////////////////
// UPDATE the new rank info //
//////////////////////////////
bool CDb::AddRank(int socket, int system_id, int batch_id, CUser *puser, int rank, bool breakage, double achvbonus, int rankrule_id)
{
	//Debug(DEBUG_TRACE, "CDb::AddRank - achvbonus", achvbonus);

	// Retain CarrerRank for later //
	if (rank > puser->m_CarrerRank)
		puser->m_CarrerRank = rank;

	// Create or Update the rank //
	map<string, map <string, string> >::iterator i = m_RankMap.find(puser->m_UserID);
	if (i == m_RankMap.end()) // Has it already been added? //
	{
		m_RankMap[puser->m_UserID]["system_id"] = IntToStr(system_id);
		m_RankMap[puser->m_UserID]["batch_id"] = IntToStr(batch_id);
		m_RankMap[puser->m_UserID]["user_id"] = puser->m_UserID;
		m_RankMap[puser->m_UserID]["rank"] = IntToStr(rank);
	}
	else if (rank > atoi(m_RankMap[puser->m_UserID]["rank"].c_str()))
	{
		m_RankMap[puser->m_UserID]["rank"] = IntToStr(rank);
	}

	// Add record of a bonus paid //
	std::stringstream ss3;
	if ((achvbonus > 0) && (m_AchvLookup[puser->m_UserID].m_Amount[rank] == 0))
	{
		// Store achvbonus in map for a faster lookup //
		m_AchvLookup[puser->m_UserID].m_Amount[rank] = achvbonus;

		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["batch_id"] = IntToStr(batch_id);
		columns["user_id"] = puser->m_UserID;
		columns["rankrule_id"] = IntToStr(rankrule_id);
		columns["rank"] = IntToStr(rank);
		columns["amount"] = IntToStr(achvbonus);
 
		if ((m_AchvCount = BulkAdd(this, socket, "ce_achvbonus", columns, &m_strAchvSQL, m_AchvCount)) == -1)
			return Debug(DEBUG_ERROR, "CDb::AddRank - dblus.BulkAdd achvbonus breakage Error");

		// Update to grand total for syncing payments //
		return UpdateGrandTotal(batch_id, system_id, puser->m_UserID.c_str(), achvbonus);
	}

	return true;
}

//////////////////////
// Add a rank bonus //
//////////////////////
bool CDb::AddRankBonus(int socket, int system_id, int batch_id, string user_id, int rank, double bonus)
{
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["batch_id"] = IntToStr(batch_id);
	columns["user_id"] = user_id;
	columns["rank"] = IntToStr(rank);
	columns["bonus"] = DoubleToStr(bonus);

	if ((m_RankBonusCount = BulkAdd(this, socket, "ce_rankbonus", columns, &m_strRankBonusSQL, m_RankBonusCount)) == -1)
		return Debug(DEBUG_ERROR, "CDb::AddRank - dblus.BulkAdd ce_rankbonus breakage Error");

	return true;
}	

/////////////////////////////////////////////////////////
// Verify if a user has been paid an achievement bonus //
/////////////////////////////////////////////////////////
bool CDb::IsAchvBonusPaid(int system_id, const char *user_id, int rank)
{
	std::string tmp_user_id = user_id;
	if (m_AchvLookup[tmp_user_id].m_Amount[rank] > 0) // Historically it's already been paid out //
		return true; // Continue processing //

	// I think this can be removed. Do testing to make sure //
	//std::stringstream ss;
	//if (GetFirstDB(ss << "SELECT count(*) FROM achvbonus WHERE system_id=" << system_id << " AND user_id='" << user_id << "' AND rank='" << rank << "'") == 0)
	//	return false;

	return false;
}

/////////////////////////////////////////
// Set sync flag for commission payout //
/////////////////////////////////////////
bool CDb::SetSyncGrand(int socket, int grand_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_grandtotals SET syncd_payman=true WHERE id=" << grand_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::SetSyncGrand - Error with ExecDB grandtotals");

	return true;
}

///////////////////////////////////////////////////
// Check to see if we have any check match rules //
///////////////////////////////////////////////////
bool CDb::IsCheckMatchRule(int socket, int system_id)
{
	std::stringstream ss;
	int cmcount = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_cmcommrules WHERE system_id=" << system_id);
	if (cmcount == 0)
		return false;

	return true;
}

///////////////////////////////////////////////////
// Reset checkmatch entries to resume processing //
///////////////////////////////////////////////////
bool CDb::ResetCheckmatch(int socket, int system_id, int batch_id)
{
	// There are some problems with this //
	// BUT we might not need to use it anymore //

	stringstream ss1;
	if (ExecDB(socket, ss1 << "DELETE FROM ce_ledger WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " AND (ledger_type=" << LEDGER_CM_PURCHASED << " OR ledger_type=" << LEDGER_CM_USED << ")") == NULL)
		return Debug(DEBUG_ERROR, "CDb::ResetCheckmatch - Error on DELETE command");

	return true;
}

///////////////////////
// Get AltCore value //
///////////////////////
int CDb::GetAltCore(int socket, int system_id)
{
	std::stringstream ss;
	return GetFirstDB(socket, ss << "SELECT altcore FROM ce_systems WHERE id=" << system_id);
}

/////////////////////////////
// Grab the base system ID //
/////////////////////////////
int CDb::GetBaseSystemID(int socket, int system_id)
{
	std::stringstream ss;
	int sysuser_id = GetFirstDB(socket, ss << "SELECT sysuser_id FROM ce_systems WHERE id=" << system_id);

	std::stringstream ss2;
	int basesys_id = GetFirstDB(socket, ss2 << "SELECT id FROM ce_systems WHERE sysuser_id=" << sysuser_id << " AND altcore='" << ALTCORE_UNITED_MAIN << "'");

	return basesys_id;
}

////////////////////////////
// Grab the base batch ID //
////////////////////////////
int CDb::GetBaseBatchID(int socket, int system_id)
{
	std::stringstream ss2;
	int basebatch_id = GetFirstDB(socket, ss2 << "SELECT id FROM ce_batches WHERE system_id=" << system_id << " ORDER BY id DESC");
	return basebatch_id;
}

//////////////////////////////////////////////
// Infinity cap needed for calc commissions //
//////////////////////////////////////////////
int CDb::GetInfinityCap(int socket, int system_id)
{
	std::stringstream ss;
	return GetFirstDB(socket, ss << "SELECT infinitycap FROM ce_systems WHERE id=" << system_id);
}

///////////////////////////////////
// Find the limit of generations //
///////////////////////////////////
int CDb::GetGenLimit(int socket, int system_id)
{
	std::stringstream ss;
	return GetFirstDB(socket, ss << "SELECT end_gen FROM ce_commrules WHERE system_id=" << system_id << " ORDER BY end_gen DESC LIMIT 1");
}

///////////////////////////////////////////////
// Add an entry for the breakdown of receipt //
///////////////////////////////////////////////
bool CDb::AddReceiptBreakdown(int socket, int system_id, int batch_id, int id, int receipt_id, const char *user_id, double amount, int commrule_id, int generation, double percent, bool infinitybonus, int comm_type, string metadata_onadd, int inv_type, double dollar)
{
//#ifdef TESTING_SPEEDUP
//	return true; // Speed up by preventing receipt breakdown records //
//#endif
	//if (system_id != 485)
	//	return false;

	std::string boolinfbonus;
	if (infinitybonus == true)
		boolinfbonus = "true";
	else if (infinitybonus == false)
		boolinfbonus = "false";

	// Run the query once MAX is met //
	if (m_BreakdownCount >= MAX_SQL_APPEND)
	{
		if (ExecDB(socket, m_strBreakdownSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::AddReceiptBreakdown - ExecDB Breakdown Error");
		m_strBreakdownSQL.clear();
		m_BreakdownCount = 0;
	}

	// Build the string to speed things up //
	stringstream query;
	if (m_strBreakdownSQL.size() == 0)
	{
		query << "INSERT INTO ce_breakdown (system_id, batch_id, receipt_id_internal, receipt_id, user_id, amount, percent, commrule_id, generation, infinitybonus, comm_type, metadata_onadd, inv_type, dollar) VALUES (" << system_id << "," << batch_id << "," << id << "," << receipt_id << ",'" << user_id << "','" << amount << "','" << percent << "','" << commrule_id << "','" << generation << "','" << infinitybonus << "', '" << comm_type << "', '" << metadata_onadd << "', '" << inv_type << "', '" << dollar << "')";
		m_strBreakdownSQL += query.str();
	}
	else
	{
		query << ", (" << system_id << "," << batch_id << "," << id << "," << receipt_id << ",'" << user_id << "','" << amount << "','" << percent << "','" << commrule_id << "','" << generation << "','" << infinitybonus << "', '" << comm_type << "', '" << metadata_onadd << "', '" << inv_type << "', '" << dollar << "')";
		m_strBreakdownSQL += query.str();
	}

	m_BreakdownCount++;
	return true;
}

//////////////////////////////////
// Start a batch for processing //
//////////////////////////////////
int CDb::AddBatch(bool pretend, int socket, int system_id, const char *start_date, const char *end_date)
{
	if (pretend == true)
		return -1;

#ifdef COMPILE_UNITED
	std::stringstream ssCount;
	if (GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_batches WHERE system_id=1 AND start_date::DATE='" << start_date << "' AND end_date::DATE='" << end_date << "'") == 1)
	{
		std::stringstream ssBatch;
		int batch_id = GetFirstDB(socket, ssBatch << "SELECT id FROM ce_batches WHERE system_id=1 AND start_date::DATE='" << start_date << "' AND end_date::DATE='" << end_date << "' ORDER BY id DESC");
		return batch_id;
	}
#endif

	// Grab a connection without spawning a thread //
	CConn *conn = m_ConnPool.GetConn(socket, m_pSettings->m_IniFile); 
	if (conn == NULL)
	{
		Debug(DEBUG_ERROR, socket, "CDb::AddBatch - conn == NULL");
		return -1;
	}

	std::stringstream query;
	query << "INSERT INTO ce_batches (system_id, start_date, end_date) VALUES (" << system_id << ", '" << start_date << "','" << end_date << "')";
	conn->m_Query = query.str(); // Make copy so memory isn't shared //
	if (ThreadExec(conn) == false)
	{
		Debug(DEBUG_ERROR, socket, "CDb::AddBatch - Insert into batches problem");
		return -1;
	}

	std::stringstream query2;
	int batch_id = GetFirstDB(conn, query2 << "SELECT id FROM ce_batches WHERE system_id=" << system_id << " AND start_date='" << start_date << "' AND end_date='" << end_date << "' ORDER BY id desc LIMIT 1");
	
	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::AddBatch - ThreadReleaseConn == false");

	return batch_id;
}

///////////////////////////////////////////////
// Update batch record after values computed //
///////////////////////////////////////////////
bool CDb::UpdateBatch(bool pretend, int socket, int system_id, int batch_id, double receipts_wholesale, double receipts_retail, double commissions, double achv_bonuses, double bonuses, double pools)
{
	if (pretend == true)
		return true;

	stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_batches SET receipts_wholesale=" << receipts_wholesale << ", receipts_retail=" << receipts_retail << ", commissions=" << commissions << ", achv_bonuses=" << achv_bonuses << ", bonuses=" << bonuses << ", pools=" << pools << " WHERE system_id=" << system_id << " AND id=" << batch_id) == NULL)
		Debug(DEBUG_ERROR, "CDb::UpdateBatch - ExecDB error on UPDATE");

	return true;
}

/////////////////////////////////
// Finally insert a commission //
/////////////////////////////////
bool CDb::AddCommission(int socket, int system_id, int batch_id, const char *user_id, double amount)
{
	// Make sure a valid amount //
	if (amount == 0)
		return false;

	if (m_CommCount >= MAX_SQL_APPEND)
	{
		if (ExecDB(socket, m_strCommSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::AddCommission - ExecDB Commissions Error");
		m_strCommSQL.clear();
		m_CommCount = 0;
	}

	// Build the string to speed things up //
	stringstream query;
	if (m_strCommSQL.size() == 0)
	{
		query << "INSERT INTO ce_commissions (system_id, batch_id, user_id, amount) VALUES (" << system_id << "," << batch_id << ",'" << user_id << "'," << amount << ")";
		m_strCommSQL += query.str();
	}
	else
	{
		query << ",(" << system_id << "," << batch_id << ",'" << user_id << "'," << amount << ")";
		m_strCommSQL += query.str();
	}
	m_CommCount++;

	// Update to grand total for syncing payments //
	return UpdateGrandTotal(batch_id, system_id, user_id, amount);
}

///////////////////////////////
// Add a binary ledger entry //
///////////////////////////////
bool CDb::AddBinaryLedger(int socket, int system_id, int batch_id, const char *user_id, double commission, double firstleg, double secondleg, double groupsales)
{
	if (m_strBinarySQL.size() == 0)
		m_strBinarySQL += "INSERT INTO ce_binaryledger (system_id, batch_id, user_id, commission, firstleg, secondleg, groupsales) VALUES";

	std::stringstream query;
	query << " (" << system_id << "," << batch_id << ",'" << user_id << "'," << commission << "," << firstleg << "," << secondleg << "," << groupsales << "), ";
	m_strBinarySQL += query.str();

	return UpdateGrandTotal(batch_id, system_id, user_id, commission);
}

/////////////////////////////
// Add a pool payout entry //
/////////////////////////////
bool CDb::AddPoolPayout(int socket, int system_id, int batch_id, int poolpot_id, const char *user_id, double amount)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "INSERT INTO ce_poolpayouts (system_id, poolpot_id, user_id, amount, batch_id) VALUES (" << system_id << "," << poolpot_id << ",'" << user_id << "','" << amount << "','" << batch_id << "')") == NULL)
		return Debug(DEBUG_ERROR, "CDb::AddPoolPayout - ExecDB error on INSERT");

	// Update to grand total for syncing payments //
	return UpdateGrandTotal(batch_id, system_id, user_id, amount);
}

/////////////////////////////////
// Reverse lookup the commtype //
/////////////////////////////////
int CDb::GetSystemCommType(int socket, int system_id)
{
	//Debug(DEBUG_TRACE, "CDb::GetSystemCommType - TOP");

	std::stringstream query;
	return GetFirstDB(socket, query << "SELECT commtype FROM ce_systems WHERE id=" << system_id);
}

/////////////////////////////////
// Reverse lookup the commtype //
/////////////////////////////////
string CDb::GetSystemCompression(int socket, int system_id)
{
	//Debug(DEBUG_TRACE, "CDb::GetSystemCompression - TOP");

	std::stringstream query;
	return GetFirstCharDB(socket, query << "SELECT compression FROM ce_systems WHERE id=" << system_id);
}

/////////////////////////////////////////////
// Get the signup bonus in ref to a system //
/////////////////////////////////////////////
string CDb::GetSignupBonus(int socket, int system_id)
{
	stringstream query;
	string signupbonus = GetFirstCharDB(socket, query << "SELECT signupbonus FROM ce_systems WHERE id=" << system_id);
	if (signupbonus.size() == 0)
		signupbonus = "0";
	
	return signupbonus;
}

////////////////////////////////////////////////////
// GrandID needed for finalization for processing //
////////////////////////////////////////////////////
bool CDb::UpdateGrandTotal(int batch_id, int system_id, const char *user_id, double amount)
{
	//Debug(DEBUG_ERROR, "CDb::UpdateGrandTotal - TOP");

	// Keep track of the grand amount //
	std::string user_str = user_id;
	m_GrandAmountMap[user_str] += amount;

	return true;
}

//////////////////////////////////////
// Handle extended breakdown values //
//////////////////////////////////////
bool CDb::DoExtBreakdown(int socket, int system_id, int batch_id)
{
	// Handle the breakdown for generation //
	stringstream ss1;
	if (ExecDB(socket, ss1 << "INSERT INTO ce_breakdown_gen(system_id, batch_id, parent_id, generation, amount) SELECT " << system_id << ", " << batch_id << ", b.user_id, b.generation, sum(b.amount) FROM ce_breakdown b WHERE b.system_id=" << system_id << " AND b.batch_id=" << batch_id << " AND b.system_id=" << system_id << " GROUP BY b.user_id, b.generation") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::DoExtBreakdown - ce_breakdown_gen", ss1.str().c_str());
	}

	// Handle the breakdown for users //
	stringstream ss2;
	if (ExecDB(socket, ss2 << "INSERT INTO ce_breakdown_users(system_id, batch_id, parent_id, user_id, generation, amount) SELECT " << system_id << ", " << batch_id << ", b.user_id, r.user_id, b.generation, sum(b.amount) FROM ce_breakdown b INNER JOIN ce_receipts r ON b.receipt_id_internal=r.id WHERE b.system_id=" << system_id << " AND b.batch_id=" << batch_id << " AND r.system_id=" << system_id << " GROUP BY b.user_id, r.user_id, b.generation") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::DoExtBreakdown - ce_breakdown_users", ss2.str().c_str());
	}

/*
	// Handle the breakdown for users //
	stringstream ss2;
	if (ExecDB(socket, ss2 << "INSERT INTO ce_breakdown_users(system_id, batch_id, parent_id, user_id, generation, amount) SELECT " << system_id << ", " << batch_id << ", u.parent_id, u.user_id, b.generation, sum(b.amount) FROM ce_breakdown b INNER JOIN ce_users u ON b.user_id=u.user_id WHERE b.system_id=" << system_id << " AND b.batch_id=" << batch_id << " AND u.system_id=" << system_id << " GROUP BY u.parent_id, u.user_id, b.generation") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::DoExtBreakdown - ce_breakdown_users", ss2.str().c_str());
	}
*/

	// Handle the breakdown for orders //
	stringstream ss3;
	if (ExecDB(socket, ss3 << "INSERT INTO ce_breakdown_orders(system_id, batch_id, parent_id, user_id, generation, ordernum, amount) SELECT " << system_id << ", " << batch_id << ", b.user_id, r.user_id, b.generation, b.metadata_onadd, sum(b.amount) FROM ce_breakdown b INNER JOIN ce_receipts r ON b.receipt_id_internal=r.id WHERE b.system_id=" << system_id << " AND b.batch_id=" << batch_id << " AND r.system_id=" << system_id << " GROUP BY b.user_id, r.user_id, b.generation, b.metadata_onadd") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::DoExtBreakdown - ce_breakdown_orders", ss3.str().c_str());
	}

/*
	// Handle the breakdown for orders //
	stringstream ss3;
	if (ExecDB(socket, ss3 << "INSERT INTO ce_breakdown_orders(system_id, batch_id, parent_id, user_id, generation, ordernum, amount) SELECT " << system_id << ", " << batch_id << ", u.parent_id, u.user_id, b.generation, b.metadata_onadd, sum(b.amount) FROM ce_breakdown b INNER JOIN ce_users u ON b.user_id=u.user_id WHERE b.system_id=" << system_id << " AND b.batch_id=" << batch_id << " AND u.system_id=" << system_id << " GROUP BY u.parent_id, u.user_id, b.generation, b.metadata_onadd") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::DoExtBreakdown - ce_breakdown_orders", ss3.str().c_str());
	}
*/
	return true;
}

////////////////////
// Add user stats //
////////////////////
/*
bool CDb::AddUserStat(bool month, int socket, int system_id, int batch_id, CUser *puser, string first_id, double firstsales, string second_id, double second_sales)
{
// Only handle stats for system_id = 1 for united //
#ifdef COMPILE_UNITED
	if (system_id != 1)
		return true; // Avoid errors on something like this //
#endif

	if (puser->m_UserID.size() == 0)
		return false;

	//////////////////////
	// User Stats Month //
	//////////////////////
	if ((puser->m_GroupWholesaleSales != 0) || 
		(puser->m_GroupRetailSales != 0) || 
		(puser->m_GroupUsed != 0) ||
		(puser->m_CustomerWholesaleSales != 0) ||
		(puser->m_CustomerRetailSales != 0) ||
		(puser->m_ResellerWholesaleSales != 0) ||
		(puser->m_ResellerRetailSales != 0) ||
		(puser->m_AffiliateWholesaleSales != 0) ||
		(puser->m_AffiliateRetailSales != 0) ||
		(puser->m_AllSignupCount != 0) ||
		(puser->m_AffiliateCount != 0) ||
		(puser->m_ResellerCount != 0) ||
		(puser->m_CustomerCount != 0))
	{
		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["batch_id"] = IntToStr(batch_id);
		columns["user_id"] = puser->m_UserID;
		columns["group_wholesale_sales"] = DoubleToStr(puser->m_GroupWholesaleSales);
		columns["group_retail_sales"] = DoubleToStr(puser->m_GroupRetailSales);
		columns["group_used"] = DoubleToStr(puser->m_GroupUsed);
		columns["customer_wholesale_sales"] = DoubleToStr(puser->m_CustomerWholesaleSales);
		columns["customer_retail_sales"] = DoubleToStr(puser->m_CustomerRetailSales);
		columns["reseller_wholesale_sales"] = DoubleToStr(puser->m_ResellerWholesaleSales);
		columns["reseller_retail_sales"] = DoubleToStr(puser->m_ResellerRetailSales);
		columns["affiliate_wholesale_sales"] = DoubleToStr(puser->m_AffiliateWholesaleSales);
		columns["affiliate_retail_sales"] = DoubleToStr(puser->m_TeamRetailSales);
		columns["team_wholesale_sales"] = DoubleToStr(puser->m_TeamWholesaleSales);
		columns["team_retail_sales"] = DoubleToStr(puser->m_AffiliateRetailSales);
		columns["signup_count"] = IntToStr(puser->m_AllSignupCount);
		columns["affiliate_count"] = IntToStr(puser->m_AffiliateCount);
		columns["reseller_count"] = IntToStr(puser->m_ResellerCount);
		columns["customer_count"] = IntToStr(puser->m_CustomerCount);
		if ((m_UserStatMonthCount = BulkAdd(this, socket, "ce_userstats_month", columns, &m_strStatMonthSQL, m_UserStatMonthCount)) == -1)
			return Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthSQL.c_str());
	}

	// Finish last remaining records //
	//if (BulkFinish(m_pDB, socket, &m_strStatMonthSQL) == false)
	//	return Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthSQL.c_str());

	///////////////////////////
	// User Stats Month LVL1 //
	///////////////////////////
	if ((puser->m_LvL1PersonalSales != 0) ||
		(puser->m_LvL1SignupCount != 0) ||
		(puser->m_LvL1AffiliateCount != 0) ||
		(puser->m_LvL1CustomerCount != 0) ||
		(puser->m_LvL1ResellerCount != 0) ||
		(puser->m_LvL1MyWholesaleSales != 0) ||
		(puser->m_LvL1MyRetailSales != 0))
	{
		map <string, string> columnslvl1;
		columnslvl1["system_id"] = IntToStr(system_id);
		columnslvl1["batch_id"] = IntToStr(batch_id);
		columnslvl1["user_id"] = puser->m_UserID;
		columnslvl1["personal_sales"] = DoubleToStr(puser->m_LvL1PersonalSales);
		columnslvl1["signup_count"] = IntToStr(puser->m_LvL1SignupCount);
		columnslvl1["affiliate_count"] = IntToStr(puser->m_LvL1AffiliateCount);
		columnslvl1["customer_count"] = IntToStr(puser->m_LvL1CustomerCount);
		columnslvl1["reseller_count"] = IntToStr(puser->m_LvL1ResellerCount);
		columnslvl1["my_wholesale_sales"] = DoubleToStr(puser->m_LvL1MyWholesaleSales);
		columnslvl1["my_retail_sales"] = DoubleToStr(puser->m_LvL1MyRetailSales);
		if ((m_UserStatMonthLVL1Count = BulkAdd(this, socket, "ce_userstats_month_lvl1", columnslvl1, &m_strStatMonthLVL1SQL, m_UserStatMonthLVL1Count)) == -1)
			return Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthLVL1SQL.c_str());
	}

	// Finish last remaining records //
	//if (BulkFinish(m_pDB, socket, &m_strStatMonthLVL1SQL) == false)
	//	return Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthLVL1SQL.c_str());

	///////////////////////////
	// User Stats Month Legs //
	///////////////////////////
	if ((firstsales != 0) || (second_sales != 0))
	{
		map <string, string> columnslegs;
		columnslegs["system_id"] = IntToStr(system_id);
		columnslegs["batch_id"] = IntToStr(batch_id);
		columnslegs["user_id"] = puser->m_UserID;
		columnslegs["firstbestleg_sales"] = DoubleToStr(firstsales);
		columnslegs["secondbestleg_sales"] = DoubleToStr(second_sales);
		columnslegs["firstbestleg_id"] = first_id;
		columnslegs["secondbestleg_id"] = second_id;
		if ((m_UserStatMonthLegCount = BulkAdd(this, socket, "ce_userstats_month_legs", columnslegs, &m_strStatMonthLegSQL, m_UserStatMonthLegCount)) == -1)
			return Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthLegSQL.c_str());
	}

	return true;
}
*/
////////////////////////
// Add a Ledger Entry //
////////////////////////
bool CDb::AddLedger(int socket, int system_id, int batch_id, const char *user_id, int ref_id, int ledger_type, int from_system_id, const char *from_user_id, double amount, int generation, const char *event_date)
{
	if (m_LedgerCount >= MAX_SQL_APPEND)
	{
		if (ExecDB(socket, m_strLedgerSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::AddLedger - ExecDB Commissions Error");
		m_strLedgerSQL.clear();
		m_LedgerCount = 0;
	}

	if (m_strLedgerSQL.size() == 0)
	{
		std::stringstream ss;
		ss << "INSERT INTO ce_ledger(system_id,batch_id,user_id,ref_id,ledger_type,from_system_id,from_user_id,amount,generation,event_date) VALUES (" << system_id << "," << batch_id << ",'" << user_id << "'," << ref_id << "," << ledger_type << "," << from_system_id << ",'" << from_user_id << "'," << amount << "," << generation << ",'" << event_date << "')";
		m_strLedgerSQL += ss.str();
	}
	else
	{
		std::stringstream ss;
		ss << ",(" << system_id << "," << batch_id << ",'" << user_id << "'," << ref_id << "," << ledger_type << "," << from_system_id << ",'" << from_user_id << "'," << amount << "," << generation << ",'" << event_date << "')";
		m_strLedgerSQL += ss.str();
	}
	m_LedgerCount++;

	return true;
}

///////////////////////////
// Rebuild Ledger Totals //
///////////////////////////
const char *CDb::RebuildLedgerTotals(bool pretend, int socket, int system_id)
{
	if (pretend == true)
		return SetJson(200, "");

	std::stringstream tmptable;
	tmptable << "ce_tmp_" << system_id << "_ledger";
	string tmp = tmptable.str();

	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(false, socket, ss << "SELECT DISTINCT system_id, user_id, sum(amount) INTO TEMP " << tmp << " FROM ce_ledger WHERE system_id=" << system_id << " AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND disabled=false) GROUP BY user_id, system_id")) == NULL)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not build TEMP TABLE in database");

	std::stringstream ss2;
	ss2 << "DELETE FROM ce_ledger_totals WHERE system_id=" << system_id;
	conn->m_Query = ss2.str();
	if (ThreadExec(conn) == false)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not DELETE previous records");

	// SELECT new totals into ledger_totals table //
	std::stringstream ss3;
	ss3 << "INSERT INTO ce_ledger_totals(system_id, user_id, amount) SELECT " << tmp << ".system_id, " << tmp << ".user_id, " << tmp << ".sum FROM " << tmp;
	conn->m_Query = ss3.str();
	if (ThreadExec(conn) == false)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. New record totals were not imported");

	// Drop the temp table //
	std::stringstream ss4;
	ss4 << "DROP TABLE IF EXISTS " << tmp;
	conn->m_Query = ss4.str();
	if (ThreadExec(conn) == false)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not DROP temp table");

	if (ThreadReleaseConn(conn->m_Resource) == false);
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not release database pool connection");

/*	m_ConnPool.Disable();

	std::stringstream tmptable;
	tmptable << "ce_tmp_" << system_id << "_ledger";
	string tmp = tmptable.str();

	std::stringstream ss;
	if (ExecDB(socket, ss << "(SELECT DISTINCT system_id, user_id, sum(amount) INTO TEMP " << tmp << " FROM ce_ledger WHERE system_id=" << system_id << " AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND disabled=false) GROUP BY user_id, system_id)") == NULL)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not build TEMP TABLE in database");

	// DELETE previous totals from before //
	std::stringstream ss2;
	if (ExecDB(socket, ss2 << "DELETE FROM ce_ledger_totals WHERE system_id=" << system_id) == NULL)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not DELETE previous records");

	// SELECT new totals into ledger_totals table //
	std::stringstream ss3;
	if (ExecDB(socket, ss3 << "INSERT INTO ce_ledger_totals(system_id, user_id, amount) SELECT " << tmp << ".system_id, " << tmp << ".user_id, " << tmp << ".sum FROM " << tmp) == NULL)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. New record totals were not imported");

	// Drop the temp table //
	std::stringstream ss4;
	if (ExecDB(socket, ss4 << "DROP TABLE IF EXISTS " << tmp) == NULL)
		return SetError(503, "API", "RebuildLedgerTotals error", "Database error. Could not DROP temp table");

	m_ConnPool.Enable();
*/
	return SetJson(200, "");
}

////////////////////////////////
// Rebuild the receipt totals //
////////////////////////////////
const char *CDb::RebuildReceiptTotals(bool pretend, int socket, int system_id)
{
	if (pretend == true)
		return SetJson(200, "");
	
//	m_ConnPool.Disable();

	std::stringstream tmptable;
	tmptable << "ce_tmp_" << system_id << "_receipts";
	string tmp = tmptable.str();

	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "(SELECT DISTINCT system_id, user_id, sum(wholesale_price), count(*) INTO TEMP " << tmp << " FROM ce_receipts where system_id=" << system_id << " AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND disabled=false) GROUP BY user_id, system_id)")) == NULL)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not build TEMP TABLE in database");

	std::stringstream ss2;
	ss2 << "DELETE FROM ce_receipt_totals WHERE system_id=" << system_id;
	conn->m_Query = ss2.str();
	if (ThreadExec(conn) == false)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not DELETE previous records");

	std::stringstream ss3;
	ss3 << "INSERT INTO ce_receipt_totals(system_id, user_id, amount, count) SELECT " << tmp << ".system_id, " << tmp << ".user_id, " << tmp << ".sum, " << tmp << ".count FROM " << tmp;
	conn->m_Query = ss3.str();
	if (ThreadExec(conn) == false)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. New record totals were not imported");

	std::stringstream ss4;
	ss4 << "DROP TABLE " << tmp;
	conn->m_Query = ss4.str();
	if (ThreadExec(conn) == false)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not DROP temp table");

	if (ThreadReleaseConn(conn->m_Resource) == false);
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not release database pool connection");
	/*
	std::stringstream ss;
	if (ExecDB(socket, ss << "(SELECT DISTINCT system_id, user_id, sum(wholesale_price), count(*) INTO TEMP " << tmp << " FROM ce_receipts where system_id=" << system_id << " AND user_id IN (SELECT user_id FROM ce_users WHERE system_id=" << system_id << " AND disabled=false) GROUP BY user_id, system_id)") == NULL)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not build TEMP TABLE in database");

	// DELETE previous totals from before //
	std::stringstream ss2;
	if (ExecDB(socket, ss2 << "DELETE FROM ce_receipt_totals WHERE system_id=" << system_id) == NULL)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not DELETE previous records");

	// SELECT new totals into ledger_totals table //
	std::stringstream ss3;
	if (ExecDB(socket, ss3 << "INSERT INTO ce_receipt_totals(system_id, user_id, amount, count) SELECT " << tmp << ".system_id, " << tmp << ".user_id, " << tmp << ".sum, " << tmp << ".count FROM " << tmp) == NULL)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. New record totals were not imported");

	// Drop the temp table //
	std::stringstream ss4;
	if (ExecDB(socket, ss4 << "DROP TABLE " << tmp) == NULL)
		return SetError(503, "API", "RebuildReceiptTotals error", "Database error. Could not DROP temp table");

	m_ConnPool.Enable();
*/
	return SetJson(200, "");
}

/////////////////////////////////////////////////
// Query all ledger records for a given system //
/////////////////////////////////////////////////
const char *CDb::QueryLedger(int socket, int system_id)
{
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, system_id, batch_id, user_id, ledger_type, amount, event_date, generation, authorized, disabled, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_ledger WHERE system_id=" << system_id)) == NULL)
		return SetError(503, "API", "cdb::queryledger error", "Database error. Could not SELECT from database");

	std::stringstream ss2;
	ss2 << ",\"ledger\":[";
	while (FetchRow(conn) == true)
	{
		ss2 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss2 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss2 << "\"batchid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss2 << "\"userid\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss2 << "\"ledgertype\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss2 << "\"amount\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss2 << "\"eventdate\":\"" << conn->m_RowMap[6].c_str() << "\",";
		ss2 << "\"generation\":\"" << conn->m_RowMap[7].c_str() << "\",";
		ss2 << "\"authorized\":\"" << conn->m_RowMap[8].c_str() << "\",";
		ss2 << "\"disabled\":\"" << conn->m_RowMap[9].c_str() << "\",";
		ss2 << "\"createdat\":\"" << conn->m_RowMap[10].c_str() << "\",";
		ss2 << "\"updatedat\":\"" << conn->m_RowMap[11].c_str() << "\"},";
	}

	std::string json;
    json = ss2.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryLedger - ThreadReleaseConn == false");
		return SetError(503, "API", "cdb::queryledger error", "The database connection could not be released");
	}

	return SetJson(200, json.c_str());
}

///////////////////////////////////////////////
// Query all ledger records for a given user //
///////////////////////////////////////////////
const char *CDb::QueryLedgerUser(int socket, int system_id, const char *user_id)
{
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, system_id, batch_id, user_id, ledger_type, amount, event_date, generation, authorized, disabled, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_ledger WHERE system_id=" << system_id << " AND user_id='" << user_id << "'")) == NULL)
		return SetError(503, "API", "queryledgeruser error", "Database error. Could not SELECT from database");

	std::stringstream ss2;
	ss2 << ",\"ledger\":[";
	while (FetchRow(conn) == true)
	{
		ss2 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss2 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss2 << "\"batchid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss2 << "\"userid\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss2 << "\"ledgertype\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss2 << "\"amount\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss2 << "\"eventdate\":\"" << conn->m_RowMap[6].c_str() << "\",";
		ss2 << "\"generation\":\"" << conn->m_RowMap[7].c_str() << "\",";
		ss2 << "\"authorized\":\"" << conn->m_RowMap[8].c_str() << "\",";
		ss2 << "\"disabled\":\"" << conn->m_RowMap[9].c_str() << "\",";
		ss2 << "\"createdat\":\"" << conn->m_RowMap[10].c_str() << "\",";
		ss2 << "\"updatedat\":\"" << conn->m_RowMap[11].c_str() << "\"},";
	}

	std::string json;
    json = ss2.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryLedgerUser - ThreadReleaseConn == false");
		return SetError(503, "API", "queryledgeruser error", "Could not release database connection");
	}

	return SetJson(200, json.c_str());
}

////////////////////////////////////
// Query valid balances of ledger //
////////////////////////////////////
const char *CDb::QueryLedgerBalance(int socket, int system_id, string searchsql, string sqlend)
{
	std::stringstream ss;
	int systemcount = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_ledger_totals WHERE system_id=" << system_id << searchsql);
	if (systemcount == 0)
		return SetError(400, "API", "db::queryledgerbalance error", "There are no users in the selected systems");

	CConn *conn;
	std::stringstream ss1;
	if ((conn = ExecDB(socket, ss1 << "SELECT id, system_id, user_id, amount, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_ledger_totals WHERE system_id=" << system_id << sqlend)) == NULL)
		return SetError(503, "API", "db::queryledgerbalance error", "There was an internal error that prevented a select from the database");

	std::stringstream ss2;
	ss2 << ",\"count\":\"" << systemcount << "\"";
	ss2 << ",\"ledger\":[";
	while (FetchRow(conn) == true)
	{
		ss2 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss2 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss2 << "\"userid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss2 << "\"amount\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss2 << "\"createdat\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss2 << "\"updatedat\":\"" << conn->m_RowMap[5].c_str() << "\"},";
	}

	std::string json;
    json = ss2.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryLedgerUser - ThreadReleaseConn == false");
		return SetError(503, "API", "db::queryledgerbalance error", "Could not release database connection");
	}

	return SetJson(200, json.c_str());
}

/////////////////////////////////
// Update the poolpots receipt //
/////////////////////////////////
bool CDb::UpdatePoolPots(int socket, int poolpot_id, double receipts)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_poolpots SET receipts=" << receipts << "WHERE id=" << poolpot_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::UpdatePoolPots - Error UPDATING database");

	return true;
}

////////////////////////////////////////
// Push INSERT statements to database //
////////////////////////////////////////
bool CDb::Flush(bool pretend, int socket, int system_id, int batch_id)
{
	//Debug(DEBUG_DEBUG, "CDb::Flush - TOP");

	if (pretend == true)
		return false;

	if (m_strBinarySQL.size() != 0)
	{
		m_strBinarySQL.erase(m_strBinarySQL.size()-2, 2); // Trim the ", " off the end //
		if (ExecDB(socket, m_strBinarySQL.c_str()) == NULL)
		{
			Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_strBinarySQL Error");
			return false;
		}
		m_strBinarySQL.clear(); // Clear out the string //
	}

	//////////////////
	// Finish Ranks //
	//////////////////
	map <string, map <string, string> >::iterator r;
	for (r=m_RankMap.begin(); r != m_RankMap.end(); ++r)
	{
		if ((m_RankCount = BulkAdd(this, socket, "ce_ranks", r->second, &m_strRankSQL, m_RankCount)) == -1)
			return Debug(DEBUG_ERROR, "CDb::Flush - dblus.BulkAdd rank breakage Error");
	}
	m_RankMap.clear(); // Empty out the map so it won'y be used again //
	m_RankCount = 0;
	if (BulkFinish(this, socket, &m_strRankSQL) == false)
		return Debug(DEBUG_ERROR, "CDb::Flush - BulkFinish m_strRankSQL Error");

	///////////////////////
	// Finish Achv Bonus //
	///////////////////////
	m_AchvCount = 0;
	if (BulkFinish(this, socket, &m_strAchvSQL) == false)
		return Debug(DEBUG_ERROR, "CDb::Flush - BulkFinish m_strAchvSQL Error");

	///////////////////////
	// Finish Rank Bonus //
	///////////////////////
	m_RankBonusCount = 0;
	if (BulkFinish(this, socket, &m_strRankBonusSQL) == false)
		return Debug(DEBUG_ERROR, "CDb::Flush - BulkFinish m_strRankBonusSQL Error");

	//////////////////////
	// Finish breakdown //
	//////////////////////
	if (m_strBreakdownSQL.size() != 0)
	{
		if (ExecDB(socket, m_strBreakdownSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB Breakdown Error");
		m_strBreakdownSQL.clear();
		m_BreakdownCount = 0;
	}

	////////////////////////
	// Finish commissions //
	////////////////////////
	if (m_strCommSQL.size() != 0)
	{
		if (ExecDB(socket, m_strCommSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_strCommSQL Error");
		m_strCommSQL.clear();
		m_CommCount = 0;
	}

	//////////////////
	// Levels Table //
	//////////////////
	FlushLevels(socket);
/*
	////////////////
	// User Stats //
	////////////////
	if (m_strStatMonthSQL.size() != 0)
	{
		if (ExecDB(socket, m_strStatMonthSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_strStatMonthSQL Error");
		m_strStatMonthSQL.clear();
		m_UserStatMonthCount = 0;
	}

	if (m_strStatMonthLVL1SQL.size() != 0)
	{
		if (ExecDB(socket, m_strStatMonthLVL1SQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_strStatMonthLVL1SQL Error");
		m_strStatMonthLVL1SQL.clear();
		m_UserStatMonthLVL1Count = 0;
	}

	if (m_strStatMonthLegSQL.size() != 0)
	{
		if (ExecDB(socket, m_strStatMonthLegSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_strStatMonthLegSQL Error");
		m_strStatMonthLegSQL.clear();
		m_UserStatMonthLegCount = 0;
	}
*/

	////////////
	// Ledger //
	////////////
	if (m_strLedgerSQL.size() != 0)
	{
		if (ExecDB(socket, m_strLedgerSQL.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_ssLedgerSQL Error");
		m_strLedgerSQL.clear();
		m_LedgerCount = 0;
	}

	return true;
}

////////////////////////////////
// Flush out the grand totals //
////////////////////////////////
bool CDb::FlushGrand(bool pretend, int socket, int system_id, int batch_id)
{
	if (pretend == true)
		return true;

	// Alert us there are not grand total records to be written //
	int grandtotalcount = m_GrandAmountMap.size();
	if (grandtotalcount == 0)
		Debug(DEBUG_INFO, "CDb::FlushGrand - TOP - grandtotalcount", grandtotalcount);

	////////////////////////
	// Finish grandtotals //
	////////////////////////
	std::stringstream ss;
	int grandcount = 0;
	std::map <std::string, double>::iterator k;
	for (k=m_GrandAmountMap.begin(); k != m_GrandAmountMap.end(); ++k)
	{
		if (grandcount >= MAX_SQL_APPEND)
		{
			if (ss.str().size() == 0)
			{
				//Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_GrandAmountMap size=0 #1");
			}
			else if (ExecDB(socket, ss) == NULL)
				return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_GrandAmountMap Error");

			ss.clear();
			ss.str("");
			grandcount = 0;
		}

		if (grandcount == 0)
			ss << "INSERT INTO ce_grandtotals (system_id, batch_id, user_id, amount) VALUES (" << system_id << "," << batch_id << ",'" << k->first << "'," << k->second << ")";
		else
			ss << ",(" << system_id << "," << batch_id << ",'" << k->first << "'," << k->second << ")";
		grandcount++;
	}

	// Do final segment of grandtotals //
	if (ss.str().size() == 0)
	{
		//Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_GrandAmountMap size=0 #2");
	}
	else if (ExecDB(socket, ss) == NULL)
		return Debug(DEBUG_ERROR, "CDb::Flush - ExecDB m_GrandAmountMap Error");
	ss.clear();
	ss.str("");
	grandcount = 0;
	m_GrandAmountMap.clear();

	return true;
}

////////////////////////
// Flush Levels table //
////////////////////////
bool CDb::FlushLevels(int socket)
{
	std::string tmpstr = m_LevelsSS.str();
	if (tmpstr.size() != 0)
	{
		tmpstr.erase(tmpstr.size()-2, 2); // Trim the ", " off the end //
		if (ExecDB(socket, tmpstr.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::FlushLevels - ExecDB m_LevelsSS Error");
		m_LevelsSS.clear();
		m_LevelsSS.str("");
		m_LevelsCount = 0;
	}

	return true;
}

///////////////////////////////
// Clear for next processing //
///////////////////////////////
bool CDb::Clear()
{
	m_JSON.clear();
	return true;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////// All engine database calls /////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////
// Run the cron commissions calculations //
///////////////////////////////////////////
bool CDb::CronCommissions()
{
	int socket = 0;

	// Grab current date //
	std::stringstream ss;
	std::string currentdate = GetFirstCharDB(socket, ss << "SELECT now()::DATE");

	// Grab current month //
	std::stringstream ss1;
	std::string month = GetFirstCharDB(socket, ss1 << "SELECT extract(month from now())");

	// Grab current day of month //
	std::stringstream ss2;
	std::string dayofmonth;
#ifdef COMPILE_UNITED
		dayofmonth = "10";
#else
		dayofmonth = GetFirstCharDB(socket, ss2 << "SELECT extract(day from now())");
#endif

	// Grab current day of week //
	std::stringstream ss3;
	std::string dayofweek = GetFirstCharDB(socket, ss3 << "SELECT extract(dow from now())");

	// Startdate and enddate for month calc //
	std::stringstream ss4;
	std::string mstartdate = GetFirstCharDB(socket, ss4 << "SELECT (now() - interval '1 month')::DATE");
	std::stringstream ss5;
	std::string enddate = GetFirstCharDB(socket, ss5 << "SELECT now()::DATE");
	std::stringstream ss6;
	std::string wstartdate = GetFirstCharDB(socket, ss6 << "SELECT (now() - interval '1 week')::DATE");

	//Debug(DEBUG_DEBUG, "CDb::CronCommissions - Before first loop");
/*
	//////////////////
	// Handle pools //
	//////////////////
	std::stringstream ss7;
	if (GetFirstDB(ss7 << "SELECT count(*) FROM poolpots WHERE end_date::DATE='" << currentdate << "'") > 0)
	{
		std::stringstream ssbatch;
		if (ExecDB(ssbatch << "INSERT INTO batches (system_id, start_date, end_date) VALUES (" << atoi(m_RowMap[1].c_str()) << ", " << atoi(m_RowMap[4].c_str()) << ", " << atoi(m_RowMap[5].c_str()) << ")") == false)
			Debug(DEBUG_DEBUG, "CDb::CronCommissions - Error in batches creation");

		std::stringstream ssbatch2;
		int batch_id = GetFirstDB(ssbatch2 << "SELECT id FROM batches WHERE system_id=" << system_id << " ORDER BY id DESC");

		std::stringstream ss8;
		if (ExecDB(ss8 << "SELECT id, system_id, qualify_type, amount, start_date::DATE, end_date::DATE FROM poolpots WHERE end_date::DATE='" << currentdate << "' AND disabled=false") == true)
		{
			while (FetchRow(conn) == true)
			{
				CCommissions comm;
				Debug(DEBUG_DEBUG, "CDb::CronCommissions - Before RunPool");

				// Need to store cause other database calls can affect it //
				std::string startdate = m_RowMap[4].c_str();
				std::string enddate = m_RowMap[5].c_str();

				comm.RunPool(this, atoi(m_RowMap[0].c_str()), batch_id, atoi(m_RowMap[1].c_str()), atoi(m_RowMap[2].c_str()), atoi(m_RowMap[3].c_str()), startdate.c_str(), enddate.c_str());
			}
		}
	}
*/

	////////////////////////////////
	// Handle monthly Commissions //
	////////////////////////////////
	Debug(DEBUG_DEBUG, "CDb::CronCommissions - Before monthly Commissions");
	//CronCommMonth(0, dayofmonth, mstartdate, enddate); // Standard type //
	//CronCommMonth(2, dayofmonth, mstartdate, enddate); // United Core Type // Rank needs to be achiveved here //
	//CronCommMonth(1, dayofmonth, mstartdate, enddate); // United Games Type // Rank defined from core //
	//CronCheckMatch(mstartdate.c_str(), enddate.c_str());

	CronCommMonth(socket, 0, "10", mstartdate, enddate); // Standard type //
	CronCommMonth(socket, ALTCORE_UNITED_MAIN, "10", mstartdate, enddate); // United Core Type // Rank needs to be achiveved here //
	CronCommMonth(socket, ALTCORE_UNITED_GAME, "10", mstartdate, enddate); // United Games Type // Rank defined from core //
	CronCheckMatch(socket, mstartdate.c_str(), enddate.c_str());

	///////////////////////////////
	// Handle weekly Commissions //
	///////////////////////////////
	Debug(DEBUG_DEBUG, "CDb::CronCommissions - Before weekly Commissions");
	std::stringstream ss11;
	if (GetFirstDB(socket, ss11 << "SELECT count(*) FROM ce_systems WHERE payout_type='2' AND payout_weekday='" << dayofweek << "' AND disabled=false") > 0)
	{
		CConn *conn;
		std::stringstream ss12;
		if ((conn = ExecDB(socket, ss12 << "SELECT id, commtype, updated_url, updated_username, updated_password FROM ce_systems WHERE payout_type='2' AND payout_weekday='" << dayofweek << "' AND disabled=false")) != NULL)
		{
			CronProcLoop(conn, socket, wstartdate.c_str(), enddate.c_str());
			if (ThreadReleaseConn(conn->m_Resource) == false)
				return Debug(DEBUG_ERROR, "CDb::CronCommissions - #1 ThreadReleaseConn == false");
		}
	}

	//////////////////////////////
	// Handle daily Commissions //
	//////////////////////////////
	Debug(DEBUG_DEBUG, "CDb::CronCommissions - Before daily Commissions");
	std::stringstream ss13;
	int count = 0;
	if ((count = GetFirstDB(socket, ss13 << "SELECT count(*) FROM ce_systems WHERE payout_type='3' AND disabled=false")) > 0)
	{
		CConn *conn;
		std::stringstream ss14;
		if ((conn = ExecDB(socket, ss14 << "SELECT id, commtype, updated_url, updated_username, updated_password FROM ce_systems WHERE payout_type='3' AND disabled=false")) != NULL)
		{
			CronProcLoop(conn, socket, enddate.c_str(), enddate.c_str());
			if (ThreadReleaseConn(conn->m_Resource) == false)
				return Debug(DEBUG_ERROR, "CDb::CronCommissions - #2 ThreadReleaseConn == false");
		}
	}

	// Communicate with MWL to send payment amounts //
	// Commissions, achvbonus or poolpayouts //

	return true;
}

///////////////////////////////////////
// Manage monthly payout differently //
///////////////////////////////////////
bool CDb::CronCommMonth(int socket, int altcore, std::string dayofmonth, std::string mstartdate, std::string enddate)
{
	std::stringstream ss9;
	if (GetFirstDB(socket, ss9 << "SELECT count(*) FROM ce_systems WHERE payout_type='1' AND payout_monthday='" << dayofmonth << "' AND disabled=false AND altcore='" << altcore << "'") > 0)
	{
		CConn *conn;
		std::stringstream ss10;
		if ((conn = ExecDB(socket, ss10 << "SELECT id, commtype, updated_url, updated_username, updated_password FROM ce_systems WHERE payout_type='1' AND payout_monthday='" << dayofmonth << "' AND disabled=false AND altcore='" << altcore << "' AND id IN (SELECT DISTINCT system_id FROM ce_receipts WHERE (wholesale_date >='" << mstartdate << "' AND wholesale_date <='" << enddate << "') OR (retail_date >='" << mstartdate << "' AND retail_date <='" << enddate << "')) ORDER BY id")) == NULL)
			return Debug(DEBUG_ERROR, "CDb::CronCommMonth - ExecDB == NULL");

		if (ThreadReleaseConn(conn->m_Resource) == false)
			return Debug(DEBUG_ERROR, "CDb::CronCommMonth - ThreadReleaseConn == false");

		//Debug(DEBUG_TRACE, "CDb::CronCommMonth - Before CronProcLoop");

		CronProcLoop(conn, socket, mstartdate.c_str(), enddate.c_str());

		//Debug(DEBUG_TRACE, "CDb::CronCommMonth - After CronProcLoop");

		if (ThreadReleaseConn(conn->m_Resource) == false)
			return Debug(DEBUG_ERROR, "CDb::CronCommMonth - ThreadReleaseConn == false");

		//Debug(DEBUG_TRACE, "CDb::CronCommMonth - After ThreadReleaseConn");
	}

	return true;
}

////////////////////////////
// Process each cron loop //
////////////////////////////
bool CDb::CronProcLoop(CConn *conn, int socket, const char *startdate, const char *enddate)
{
	// Store the results //
	std::list <CTmpCommission> TmpComm;
	while (FetchRow(conn) == true)
	{
		CTmpCommission NewTmp;
		NewTmp.SetVars(atoi(conn->m_RowMap[0].c_str()), atoi(conn->m_RowMap[1].c_str()), conn->m_RowMap[2].c_str(), conn->m_RowMap[3].c_str(), conn->m_RowMap[4].c_str());
		TmpComm.push_back(NewTmp);
	}

	// Look through results //
	// This needed to avoid database conflicts //
	std::list<CTmpCommission>::iterator i;
	for (i=TmpComm.begin(); i != TmpComm.end(); ++i)
	{
		string compression_str = GetSystemCompression(socket, (*i).m_ID);
		bool compression = true;
		if (compression_str == "false")
			compression = false;
		else if (compression_str == "true")
			compression = true;

		CCommissions comm;
		int socket = 0;

		Debug(DEBUG_DEBUG, "CDb::CronProcLoop - Before comm.Run");

		comm.Run(this, socket, (*i).m_ID, (*i).m_CommType, false, true, startdate, enddate, "", compression);

		// Notify of updated rank information //
		CurlUpdatedURL(socket, (*i).m_ID, (*i).m_URL.c_str(), (*i).m_Username.c_str(), (*i).m_Password.c_str());

		// Sync with payman //
		//SyncWithPayman((*i).m_ID);
	}
	TmpComm.clear();

	return true;
}

////////////////////////
// Handle check match //
////////////////////////
bool CDb::CronCheckMatch(int socket, const char *start_date, const char *end_date)
{
	// Grab all systems
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT DISTINCT c.system_id, b.id FROM ce_cmcommrules c INNER JOIN ce_batches b ON c.system_id=b.system_id WHERE b.start_date::DATE='" << start_date << "' AND b.end_date::DATE='" << end_date << "'")) == NULL)
		return Debug(DEBUG_DEBUG, "CDb::CronCheckMatch - SQL Error");

	std::map <int, int> TmpBatch;
	while (FetchRow(conn) == true)
	{
		int system_id = atoi(conn->m_RowMap[0].c_str());
		int batch_id = atoi(conn->m_RowMap[1].c_str());

		TmpBatch[system_id] = batch_id;
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::CronCheckMatch - ThreadReleaseConn == false");

	// This needed to avoid database conflicts //
	std::map <int, int>::iterator i;
	for (i=TmpBatch.begin(); i != TmpBatch.end(); ++i)
	{
		int system_id = i->first;
		int batch_id = i->second;

		CCommissions comm;
		comm.DoCheckMatch(this, socket, false, system_id, batch_id, start_date, end_date);
		Flush(false, socket, system_id, batch_id);

		// Notify of updated rank information //
		//CurlUpdatedURL((*i).m_ID, (*i).m_URL.c_str(), (*i).m_Username.c_str(), (*i).m_Password.c_str());

		// Sync with payman //
		//SyncWithPayman((*i).m_ID);
	}

	return true;
}

///////////////////////////////////////////////////
// Call update_url after commission calculations //
///////////////////////////////////////////////////
bool CDb::CurlUpdatedURL(int socket, int system_id, const char *updated_url, const char *updated_username, const char *updated_password)
{
	if (strlen(updated_url) == 0)
		return Debug(DEBUG_DEBUG, "CDb::CurlUpdatedURL - updated_url is NULL");

	if (strlen(updated_password) == 0)
		return Debug(DEBUG_DEBUG, "CDb::CurlUpdatedURL - updated_password is NULL");

	Debug(DEBUG_INFO, "CDb::CurlUpdatedURL - updated_url =", updated_url);

	////////////////
	// Build the  //
	////////////////
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT user_id, rank FROM ce_users WHERE system_id=" << system_id)) == NULL)
		Debug(DEBUG_ERROR, "CDb::CurlUpdatedURL - Error with ExecDB commissions");

	std::stringstream ss2;
	ss2 << "json=["; // Set json into the POST variable //
	while (FetchRow(conn) == true)
	{
		// Build json string //
		ss2 << "{\"userId\":\"" << conn->m_RowMap[0].c_str() << "\"}";

		// Rank was split into own table for cause it made better sense //
		//ss2 << "\"rank\":\"" << m_RowMap[1].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::CurlUpdatedURL - ThreadReleaseConn == false");

	std::string json;
    json = ss2.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	// Grab username and password //
	std::string send_str = "token: ";
	send_str += updated_username;
	send_str += "\n";
	send_str += "password: ";
	send_str += updated_password;
	CezCurl curl;
	curl.SetHeader(send_str.c_str());
	//std::string retjson = curl.SendJson(updated_url, json.c_str());
	std::string url = updated_url;
	std::string retjson = curl.SendJson(url.c_str(), json.c_str());

	Debug(DEBUG_ERROR, "CDb::CurlUpdatedURL retjson =", retjson.c_str());

	return true;
}

///////////////////////////////////////
// Clear out given batch from system //
///////////////////////////////////////
bool CDb::ClearBatch(int socket, int batch_id)
{
	std::stringstream ss1;
	if (ExecDB(true, socket, ss1 << "DELETE FROM ce_achvbonus WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_achvbonus DELETE SQL error");

	std::stringstream ss2;
	if (ExecDB(true, socket, ss2 << "DELETE FROM ce_batches WHERE id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_batches DELETE SQL error");

	std::stringstream ss3;
	if (ExecDB(true, socket, ss3 << "DELETE FROM ce_breakdown WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_breakdown DELETE SQL error");

	std::stringstream ss4;
	if (ExecDB(true, socket, ss4 << "DELETE FROM ce_checkmatch WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_checkmatch DELETE SQL error");

	std::stringstream ss5;
	if (ExecDB(true, socket, ss5 << "DELETE FROM ce_checkpoint WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_checkpoint DELETE SQL error");

	std::stringstream ss6;
	if (ExecDB(true, socket, ss6 << "DELETE FROM ce_commissions WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_commissions DELETE SQL error");

	std::stringstream ss7;
	if (ExecDB(true, socket, ss7 << "DELETE FROM ce_grandtotals WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_grandtotals DELETE SQL error");

	std::stringstream ss8;
	if (ExecDB(true, socket, ss8 << "DELETE FROM ce_ledger WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_ledger DELETE SQL error");

	std::stringstream ss9;
	if (ExecDB(true, socket, ss9 << "DELETE FROM ce_poolpayouts WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_poolpayouts DELETE SQL error");

	std::stringstream ss10;
	if (ExecDB(true, socket, ss10 << "DELETE FROM ce_userstats_month WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_userstats_month DELETE SQL error");

	std::stringstream ss11;
	if (ExecDB(true, socket, ss11 << "DELETE FROM ce_userstats_month_legs WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_userstats_month_legs DELETE SQL error");

	std::stringstream ss12;
	if (ExecDB(true, socket, ss12 << "DELETE FROM ce_userstats_month_lvl1 WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_userstats_month_lvl1 DELETE SQL error");

	std::stringstream ss13;
	if (ExecDB(true, socket, ss13 << "DELETE FROM ce_userstats_total WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_userstats_total DELETE SQL error");

	std::stringstream ss14;
	if (ExecDB(true, socket, ss14 << "DELETE FROM ce_userstats_total_legs WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_userstats_total_legs DELETE SQL error");

	std::stringstream ss15;
	if (ExecDB(true, socket, ss15 << "DELETE FROM ce_userstats_total_lvl1 WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_userstats_total_lvl1 DELETE SQL error");

	std::stringstream ss16;
	if (ExecDB(true, socket, ss16 << "DELETE FROM ce_signupbonus WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_signupbonus DELETE SQL error");

	std::stringstream ss17;
	if (ExecDB(true, socket, ss17 << "DELETE FROM ce_rankrules_missed WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_rankrules_missed DELETE SQL error");

	std::stringstream ss18;
	if (ExecDB(true, socket, ss18 << "DELETE FROM ce_ranks WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_ranks DELETE SQL error");

	std::stringstream ss19;
	if (ExecDB(true, socket, ss19 << "UPDATE ce_bonus SET batch_id=NULL WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_bonus SET batch_id error");

	std::stringstream ss20;
	if (ExecDB(true, socket, ss20 << "DELETE FROM ce_rankgenbonus WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_rankgenbonus DELETE SQL error");

	std::stringstream ss21;
	if (ExecDB(true, socket, ss21 << "DELETE FROM ce_breakdown_gen WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_breakdown_gen DELETE SQL error");

	std::stringstream ss22;
	if (ExecDB(true, socket, ss22 << "DELETE FROM ce_breakdown_orders WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_breakdown_orders DELETE SQL error");

	std::stringstream ss23;
	if (ExecDB(true, socket, ss23 << "DELETE FROM ce_breakdown_users WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_breakdown_users DELETE SQL error");

	std::stringstream ss24;
	if (ExecDB(true, socket, ss24 << "DELETE FROM ce_audit_generations WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_audit_generations DELETE SQL error");

	std::stringstream ss25;
	if (ExecDB(true, socket, ss25 << "DELETE FROM ce_audit_ranks WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_audit_ranks DELETE SQL error");

	std::stringstream ss26;
	if (ExecDB(true, socket, ss26 << "DELETE FROM ce_audit_users WHERE batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::ClearBatch ce_audit_users DELETE SQL error");
	

	return true;
}

////////////////////////////////////
// Reset the systemusers password //
////////////////////////////////////
bool CDb::ResetSysUserPassword(int socket, string sysuser_id, string password)
{
	CezCrypt crypt;
	stringstream ss;
	string salt = GetFirstCharDB(socket, ss << "SELECT salt FROM ce_systemusers WHERE id='" << sysuser_id << "'");
	if (salt == "-1")
		return Debug(DEBUG_ERROR, "CDb::ResetSysUserPassword - GetFirstCharDB == -1");

	string password_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), password.c_str());
 
	Debug(DEBUG_TRACE, "CDb::ResetSysUserPassword - salt", salt.c_str());
	Debug(DEBUG_TRACE, "CDb::ResetSysUserPassword - password", password.c_str());
	Debug(DEBUG_TRACE, "CDb::ResetSysUserPassword - password_hash", password_hash.c_str());
	Debug(DEBUG_TRACE, "CDb::ResetSysUserPassword - m_HashPass", m_pSettings->m_HashPass.c_str());

	int passsize = password.size();
	int hashsize = m_pSettings->m_HashPass.size();
	Debug(DEBUG_TRACE, socket, "CDb::ResetSysUserPassword - password.size()", passsize);
	Debug(DEBUG_TRACE, socket, "CDb::ResetSysUserPassword - m_HashPass.size()", hashsize);

	stringstream ss2;
	if (ExecDB(socket, ss2 << "UPDATE ce_systemusers SET password_hash='" << password_hash << "' WHERE id='" << sysuser_id << "'") == NULL)
		return Debug(DEBUG_ERROR, "CDb::ResetSysUserPassword - Problem with UPDATE statement");

	return true;
}

////////////////////////////////////////////////
// Does systemuser have rights to the system? //
////////////////////////////////////////////////
bool CDb::IsRightsSystem(int socket, int system_id, int sysuser_id)
{
	if (sysuser_id == 1)
		return true;

	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_systems WHERE id=" << system_id << " AND sysuser_id=" << sysuser_id) == 1)
		return true;
	else
		return false;
}

////////////////////////////////////////////////
// Does systemuser have rights to the system? //
////////////////////////////////////////////////
bool CDb::IsUserRightsSystem(int socket, int system_id, string user_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_users WHERE system_id=" << system_id << " AND user_id='" << user_id << "'") == 1)
		return true;
	else
		return false;
}

//////////////////////////////
// Authenticate an API user //
//////////////////////////////
int CDb::AuthAPIUser(int socket, const char *email, const char *api_key)
{
	stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'") == 0)
		return -1;

	stringstream ss;
	int sysuser_id = GetFirstDB(socket, ss << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "'");
	if (sysuser_id == -1)
	{
		Debug(DEBUG_WARN, "CDb::AuthAPIUser - There were problems with the authemail", email);
		return -1;
	}

	CezCrypt crypt;
	std::stringstream ss2;
	std::string salt = GetFirstCharDB(socket, ss2 << "SELECT salt FROM ce_systemusers WHERE id='" << sysuser_id << "'");
	std::string apikey_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), api_key);

	// Do final check of api_key //
	std::stringstream ss3;
	return GetFirstDB(socket, ss3 << "SELECT id FROM ce_systemusers WHERE id='" << sysuser_id << "' AND apikey_hash='" << apikey_hash << "'");
}

/////////////////////////////////////////
// Verify authorization of system user //
/////////////////////////////////////////
int CDb::AuthSysUser(int socket, const char *email, const char *authpass, const char *ipaddress)
{
	if (strlen(email) == 0)
	{
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - strlen(email) == 0");
		return -1;
	}
	else if (strlen(email) > API_EMAIL_LENGTH) // Make sure email isn't too long //
	{
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - strlen(email) > API_EMAIL_LENGTH");
		return -1;
	}
	else if (is_email(email) == false)
	{	
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - is_email(email) == false");
		return -1;
	}
	else if (is_password(authpass) == false)
	{
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - is_password(authpass) == false");
		return -1;
	}
	else if (is_ipaddress(ipaddress) == false)
	{
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - is_ipaddress(ipaddress) == false, ipaddress", ipaddress);
		return -1;
	}

	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'") == 0)
	{
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - email not found in ce_systemusers");
		return -1;
	}

	CezCrypt crypt;
	std::stringstream ss;
	std::string salt = GetFirstCharDB(socket, ss << "SELECT salt FROM ce_systemusers WHERE email ILIKE '" << email << "'");
	std::string authpass_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), authpass);

	Debug(DEBUG_TRACE, socket, "CDb::AuthSysUser - salt", salt.c_str());
	Debug(DEBUG_TRACE, socket, "CDb::AuthSysUser - authpass", authpass);
	Debug(DEBUG_TRACE, socket, "CDb::AuthSysUser - authpass_hash", authpass_hash.c_str());
	Debug(DEBUG_TRACE, socket, "CDb::AuthSysUser - m_HashPass", m_pSettings->m_HashPass.c_str());

	int authsize = strlen(authpass);
	int hashsize = m_pSettings->m_HashPass.size();
	Debug(DEBUG_TRACE, socket, "CDb::AuthSysUser - authpass.size()", authsize);
	Debug(DEBUG_TRACE, socket, "CDb::AuthSysUser - m_HashPass.size()", hashsize);

	std::stringstream ss2;
	int count = GetFirstDB(socket, ss2 << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "' AND password_hash='" << authpass_hash.c_str() << "' AND disabled='false'"); // disabled to not allow disabled accounts //
	if (count == 0) // Unable to login //
	{
		Debug(DEBUG_DEBUG, socket, "CDb::AuthSysUser - email and password_hash does not authenticate");
		return -1;
	}
	else if (count == 1) // Login successful //
	{
		std::stringstream ss3;
		int sysuser_id = GetFirstDB(socket, ss3 << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "' AND password_hash='" << authpass_hash.c_str() << "'");
		return sysuser_id;
	}
	else if (count == -1)
	{
		Debug(DEBUG_ERROR, socket, "CDb::AuthSysUser - Bad Database connection. count", count);
		return -1;
	}

	Debug(DEBUG_ERROR, socket, "CDb::AuthSysUser - It should never get here. count", count);
	return -1;
}

/////////////////////////////////
// Authenticate a session user //
/////////////////////////////////
int CDb::CheckSessionUser(int socket, const char *email, const char *sessionkey)
{
	Debug(DEBUG_ERROR, "CDb::CheckSessionUser - Sessions are disabled for now");
	return -1;

	// Make sure email exsists //
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'") != 1)
	{
		Debug(DEBUG_ERROR, "CDb::CheckSessionUser - email/sessionkey not found in system");
		return -1;
	}

	// Lookup email from systemusers first //
	std::stringstream ss2;
	int sysuser_id = GetFirstDB(socket, ss2 << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "'");

	CezCrypt crypt;
	std::stringstream ss3;
	std::string salt = GetFirstCharDB(socket, ss3 << "SELECT salt FROM ce_systemusers WHERE email ILIKE '" << email << "'");
	std::string pbkdf2_sess = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), sessionkey);

	// Check for 24 minute timeout //
	std::stringstream ss4;
	std::string sessiontime = GetFirstCharDB(socket, ss4 << "SELECT created_at + interval '" << SESSION_TIMEOUT << " minutes' - now() FROM ce_sessions WHERE sysuser_id=" << sysuser_id << " AND sessionkey='" << pbkdf2_sess << "' ORDER BY id DESC LIMIT 1");
	Debug(DEBUG_DEBUG, "CDb::CheckSessionUser - sessiontime =", sessiontime.c_str());
	if (sessiontime.at(0) == '-')
	{
		Debug(DEBUG_TRACE, "CDb::CheckSessionUser - No session in system. Please login again");
		return -1;
	}

	std::stringstream ss5;
	int id = GetFirstDB(socket, ss5 << "SELECT id FROM ce_sessions WHERE sysuser_id='" << sysuser_id << "'' AND sessionkey='" << pbkdf2_sess << "' ORDER BY id DESC LIMIT 1");

	// Log the authentication //
	std::stringstream ss6;
	if (ExecDB(socket, ss6 << "UPDATE ce_sessions SET updated_at='now()', hit_count=hit_count+1 WHERE id=" << id) == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::CheckSessionUser - unable to update session record");
		return -1;
	}

	return sysuser_id;
}

///////////////////////
// Add a system user //
///////////////////////
const char *CDb::AddSystemUser(int socket, const char *firstname, const char *lastname, const char *email, const char *password, const char *ipaddress)
{
	//Debug(DEBUG_TRACE, socket, "CDb::AddSystemUser - ipaddress", ipaddress);

	if (strlen(password) == 0)
		return SetError(400, "API", "addsystemuser error", "The password in empty");
	if (is_ipaddress(ipaddress) == false)
		return SetError(400, "API", "addsystemuser error", "The ipaddress has invalid characters");

	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_systemusers WHERE email ILIKE '" << email << "'") > 0)
		return SetError(400, "API", "addsystemuser error", "Cannot add system user. Email is taken.");

	CezCrypt crypt;
	std::string salt = crypt.GenSalt();
	std::string password_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), password);
	std::string apikey = crypt.GenSha256();
	std::string apikey_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), apikey.c_str());

	// Do inital insert //
	std::stringstream ss2;
	if (ExecDB(socket, ss2 << "INSERT INTO ce_systemusers (firstname, lastname, email, password_hash, salt, apikey_hash, created_at) VALUES ('" << firstname << "','" << lastname << "','" << email << "', '" << password_hash.c_str() << "', '" << salt.c_str() << "', '" << apikey_hash.c_str() << "', 'now()')") == NULL)
		return SetError(503, "API", "addsystemuser error", "There was an internal error that prevented the system user from being added into the database");

	// Grab the user_id back out from the system //
	std::stringstream ss3;
	int sysuser_id = GetFirstDB(socket, ss3 << "SELECT id FROM ce_systemusers WHERE email ILIKE '" << email << "'");

	// Generate a session_key //
	CezCrypt crypt3;
	std::string sessionkey = crypt3.GenSha256();
	std::string sessionkey_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), sessionkey.c_str());
	std::stringstream ss4;
	if (ExecDB(socket, ss4 << "INSERT INTO ce_sessions (sysuser_id, sessionkey, ipaddress, hit_count, created_at) VALUES (" << sysuser_id << ",'" << sessionkey_hash.c_str() << "','" << ipaddress << "', 1, 'now()')") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::AddSystemUser - error inserting new session record entry");
		return SetError(503, "API", "addsystemuser error", "Database error. Could not INSERT into database");
	}

	// UPDATE the updated_at field //
	std::stringstream ss5;
	if (ExecDB(socket, ss5 << "UPDATE ce_systemusers SET updated_at='now()' WHERE id=" << sysuser_id) == NULL)
		return SetError(503, "API", "addsystemuser error", "There was an internal error that prevented a field to UPDATE to the database");

	stringstream ssSysUserID;
	ssSysUserID << sysuser_id;

	if (ResetSysUserPassword(socket, ssSysUserID.str(), password) == false)
		return SetError(503, "API", "addsystemuser error", "There was a problem settings the sysuser password");

	std::stringstream ss6;
	return SetJson(200, ss6 << ",\"systemuser\":[{\"id\":\"" << sysuser_id << "\",\"sessionkey\":\"" << sessionkey.c_str() << "\"}]");
}

/*
/////////////////////////
// Login a system user //
/////////////////////////
const char *CDb::LoginSystemUser(const char *email, const char *password, const char *ipaddress)
{
	if ((m_pgConn == NULL) && (m_myConn == NULL))
		return SetError(400, "API", "addsystemuser error", "No Database Connection. Connect to a database first");

	// Verify email //
	int sysuser_id = 0;
	std::stringstream ss1;
	if ((sysuser_id = GetFirstDB(ss1 << "SELECT id FROM ce_systemusers WHERE email='" << email << "'")) == -1)
		return SetError(400, "API", "addsystemuser error", "Invalid email address/password");

	// Grab salt //
	std::stringstream ss2;
	string salt = GetFirstCharDB(ss2 << "SELECT salt FROM ce_systemusers WHERE id='" << sysuser_id << "'");
	string password_hash = GetFirstCharDB(ss2 << "SELECT password_hash FROM ce_systemusers WHERE id='" << sysuser_id << "'");
	
	// Verify password //
	CezCrypt crypt;
	string password_hash2 = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), password);
	if (password_hash != password_hash2)
		return SetError(400, "API", "addsystemuser error", "Invalid email address/password");

	// Generate a session_key //
	CezCrypt crypt2;
	string sessionkey = crypt2.GenSha256();
	string sessionkey_hash = crypt2.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), sessionkey.c_str());
	stringstream ss4;
	if (ExecDB(ss4 << "INSERT INTO ce_sessions (sysuser_id, sessionkey, ipaddress, hit_count, created_at) VALUES (" << sysuser_id << ",'" << sessionkey_hash.c_str() << "','" << ipaddress << "', 1, 'now()')") == false)
	{
		Debug(DEBUG_ERROR, "CDb::AddSystemUser - error inserting new session record entry");
		return SetError(503, "API", "addsystemuser error", "Database error. Could not INSERT into database");
	}

	std::stringstream ss6;
	return SetJson(200, ss6 << ",\"systemuser\":[{\"id\":\"" << sysuser_id << "\",\"sessionkey\":\"" << sessionkey.c_str() << "\"}]");
}
*/

//////////////////////////
// Update a system user //
//////////////////////////
const char *CDb::EditSystemUser(int socket, int sysuser_id, const char *email, const char *password, const char *ipaddress)
{
	if (is_ipaddress(ipaddress) == false)
		return SetError(400, "API", "editsystemuser error", "The ipaddress has invalid characters");

	CezCrypt crypt;
	std::string salt = crypt.GenSalt();
	std::string pbkdf2 = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), password); // No password //
	std::string hash_api = crypt.GenSha256();
	std::string api_hash = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), hash_api.c_str());

	//printf("salt = %s\n", salt.c_str());
	//printf("HashPass = %s\n", m_pSettings->m_HashPass.c_str());
	//printf("password = %s\n", password);
	//printf("pbkdf2 = %s\n", pbkdf2.c_str());

	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_systemusers SET email='" << email << "', password_hash='" << pbkdf2 << "', salt='" << salt << "', apikey_hash='" << api_hash << "', updated_at='now()' WHERE id=" << sysuser_id) == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::UpdateSystemUser - error updating systemuser record entry");
		return SetError(503, "API", "updatesystemuser error", "Database error. Could not UPDATE database");
	}

	CezCrypt crypt2;
	std::string sessionkey = crypt2.GenSha256();
	std::string pbkdf2_sess = crypt.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), sessionkey.c_str());

	std::stringstream ss2;
	if (ExecDB(socket, ss2 << "INSERT INTO ce_sessions (sysuser_id, sessionkey, ipaddress, hit_count, created_at) VALUES (" << sysuser_id << ",'" << pbkdf2_sess.c_str() << "','" << ipaddress << "', 1, 'now()')") == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::UpdateSystemUser - error inserting new session record entry");
		return SetError(503, "API", "updatesystemuser error", "Database error. Could not INSERT into database");
	}

	std::stringstream ss4;
	return SetJson(200, ss4 << ",\"systemuser\":[{\"id\":\"" << sysuser_id << "\",\"apikey\":\"" << hash_api.c_str() << "\",\"sessionkey\":\"" << sessionkey.c_str() << "\"}]");
}

///////////////////////////////////////////////
// Grab list of all system users in database //
///////////////////////////////////////////////
const char *CDb::QuerySystemUsers(int socket)
{
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, email, disabled, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_systemusers ORDER BY id")) == NULL)
		return SetError(503, "API", "querysystemysers error", "Database error. Could not SELECT from database");

	std::stringstream ss2;
	ss2 << ",\"systemusers\":[";
	while (FetchRow(conn) == true)
	{
		ss2 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss2 << "\"email\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss2 << "\"disabled\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss2 << "\"createdat\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss2 << "\"updatedat\":\"" << conn->m_RowMap[4].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QuerySystemUsers - ThreadReleaseConn == false");
		return SetError(503, "API", "querysystemysers error", "Could not release the database connection");
	}

	std::string json;
    json = ss2.str();

    json = json.substr(0, json.size()-1);
    json += "]";

	return SetJson(200, json.c_str());
}

///////////////////////////
// Disable a system user //
///////////////////////////
const char *CDb::DisableSystemUser(int socket, int sysuser_id)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systemusers WHERE id=" << sysuser_id) != 1)
		return SetError(503, "API", "disablesystemuser error", "sysuserid not found in database");

	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_systemusers SET disabled=true, updated_at='now()' WHERE id=" << sysuser_id) == NULL)
		return SetError(503, "API", "disablesystemuser error", "Database error. Could not UPDATE database");

	return SetJson(200, "");
}

//////////////////////////
// Enable a system user //
//////////////////////////
const char *CDb::EnableSystemUser(int socket, int sysuser_id)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systemusers WHERE id=" << sysuser_id) != 1)
		return SetError(503, "API", "enablesystemuser error", "sysuserid not found in database");

	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_systemusers SET disabled=false, updated_at='now()' WHERE id=" << sysuser_id) == NULL)
		return SetError(503, "API", "enablesystemuser error", "Database error. Could not UPDATE database");

	return SetJson(200, "");
}

////////////////////////
// Reissue an API key //
////////////////////////
const char *CDb::ReissueApiKey(int socket, int sysuser_id)
{
	// Run Crypt generation //
	CezCrypt crypt;
	stringstream ss0;
	string salt = GetFirstCharDB(socket, ss0 << "SELECT salt FROM ce_systemusers WHERE id='" << sysuser_id << "'");
	CezCrypt crypt2;
	string apikey = crypt2.GenSha256();
	string apikeyhash = crypt2.GenPBKDF2(m_pSettings->m_HashPass.c_str(), salt.c_str(), apikey.c_str()); // No password //

	// Update the record //
	stringstream ss1;
	if (ExecDB(socket, ss1 << "UPDATE ce_systemusers SET apikey_hash='" << apikeyhash << "' WHERE id='" << sysuser_id << "'") == NULL)
		return SetError(400, "API", "reissueapikey error", "The there was an error updating the database with the new apikey");

	// Return actual apikey //
	stringstream ss2;
	return SetJson(200, ss2 << ",\"apikey\":\""+apikey+"\"");
}

////////////////////////////////////
// Disable system from being used //
////////////////////////////////////
const char *CDb::DisableSystem(int socket, int system_id)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systems WHERE id=" << system_id) != 1)
		return SetError(503, "API", "disablesystem error", "systemid not found in database");

	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_systems SET disabled=true, updated_at='now()' WHERE id=" << system_id) == NULL)
		return SetError(503, "API", "disablesystem error", "Database error prevented an UPDATE");

	return SetJson(200, "");
}

//////////////////////////////////////
// Enable a system to be used again //
//////////////////////////////////////
const char *CDb::EnableSystem(int socket, int system_id)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_systems WHERE id=" << system_id) != 1)
		return SetError(503, "API", "enablesystem error", "systemid not found in database");

	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_systems SET disabled=false, updated_at='now()' WHERE id=" << system_id) == NULL)
		return SetError(503, "API", "enablesystem error", "Database error prevented an UPDATE");

	return SetJson(200, "");
}

////////////////////////////////////
// Build the levels table entries //
////////////////////////////////////
const char *CDb::BuildLevels(int socket, int system_id, int user_id, int sponsor_id)
{
	// Pull sponsors records and alter //
	if (sponsor_id != 0)
	{
		CConn *conn;
		std::stringstream ss5;
		if ((conn = ExecDB(socket, ss5 << "SELECT user_id, ancestor_id, level FROM ce_levels WHERE system_id=" << system_id << " AND user_id='" << sponsor_id << "'")) == NULL)
			return SetError(503, "API", "add/edit error", "There was an internal error that prevented a level records being SELECT-ed from the database");

		std::stringstream ss6;
		ss6 << "INSERT INTO ce_levels (system_id, user_id, ancestor_id, level) VALUES ";
		while (FetchRow(conn) == true)
		{
			int level = atoi(conn->m_RowMap[2].c_str())+1; // Increment the level by +1 //
			ss6 << "(" << system_id << ", '" << user_id << "', '" << conn->m_RowMap[1].c_str() << "', " << level << "),";
		}

		if (ThreadReleaseConn(conn->m_Resource) == false)
		{
			Debug(DEBUG_ERROR, "CDb::BuildLevels - ThreadReleaseConn == false");
			return SetError(503, "API", "buildlevels error", "Could not release the database connection");
		}

		std::string sql;
	    sql = ss6.str();
	    sql = sql.substr(0, sql.size()-1);
	    std::stringstream ss7;
		ss7 << sql;

		if (ExecDB(socket, ss7) == NULL)
			return SetError(503, "API", "add/edit error", "There was an internal error that prevented INSERT-ing multiple level records into the database");
	}

	// INSERT base entry for levels table //
	std::stringstream ss8;
	if (ExecDB(socket, ss8 << "INSERT INTO ce_levels (system_id, user_id, ancestor_id, level) VALUES (" << system_id << ", '" << user_id << "', '" << sponsor_id << "', 1)") == NULL)
		return SetError(503, "API", "add/edit error", "There was an internal error that prevented a level record being added to the database");

	return SetJson(200, "");
}

////////////////////////
// Rebuild All levels //
////////////////////////
bool CDb::RebuildAllLevels(int socket, const char *start_date, const char *end_date)
{
	std::stringstream ss1;
	if (ExecDB(socket, ss1 << "DELETE FROM ce_levels") == NULL)
		return Debug(DEBUG_ERROR, "CDb::RebuildAllLevels DELETE FROM levels == false");

	CConn *conn;
	std::stringstream ss2;
#ifdef COMPILE_UNITED
	if ((conn = ExecDB(socket, ss2 << "SELECT DISTINCT system_id FROM ce_users WHERE signup_date>='" << start_date << "' AND signup_date<='" << end_date << "' AND system_id!=1")) == NULL)
#else
	if ((conn = ExecDB(socket, ss2 << "SELECT DISTINCT system_id FROM ce_users WHERE signup_date>='" << start_date << "' AND signup_date<='" << end_date << "'")) == NULL)
#endif
		return Debug(DEBUG_ERROR, "CDb::RebuildAllLevels DELETE FROM levels == false");

	// Make a list of systems id's //
	std::list <int> SystemsLL;
	while (FetchRow(conn) == true)
	{
		int system_id = atoi(conn->m_RowMap[0].c_str());
		SystemsLL.push_back(system_id); 
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::RebuildAllLevels - ThreadReleaseConn == false");

	// Process each system_id //
	std::list <int>::iterator i;
	for (i=SystemsLL.begin(); i != SystemsLL.end(); ++i) 
	{
		//Debug(DEBUG_DEBUG, "i", (*i));
		RebuildLevel(socket, (*i));
	}

	return true;
}

/////////////////////////////////////////////////////
// Rebuild the whole levels table for given system //
/////////////////////////////////////////////////////
bool CDb::RebuildLevel(int socket, int system_id)
{
	// Check for recurrsion loops //
	bool recurr_parent = false;
	bool recurr_sponspor = false;
	recurr_parent = IsRecursionLoop(socket, system_id, UPLINE_PARENT_ID);
	recurr_sponspor = IsRecursionLoop(socket, system_id, UPLINE_SPONSOR_ID);
	if (recurr_parent == true)
		Debug(DEBUG_ERROR, "CDb::RebuildLevel - Recurrsion Loop found PARENT_ID");
	if (recurr_sponspor == true)	
		Debug(DEBUG_ERROR, "CDb::RebuildLevel - Recurrsion Loop found SPONSOR_ID");

	// Make sure to exit out if an error was found //
	if ((recurr_parent == true) || (recurr_sponspor == true))
	{
		Debug(DEBUG_ERROR, "-----------------------------------------");
		return false;
	}

	// Delete all of the system levels records //
	std::stringstream ss1;
	if (ExecDB(socket, ss1 << "DELETE FROM ce_levels WHERE system_id=" << system_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::RebuildLevel DELETE FROM levels == false");

	std::map <std::string, CUser> UsersMap;
	std::stringstream ss2;
	std::string enddate = GetFirstCharDB(socket, ss2 << "SELECT now()::DATE");
	if (GetUsers(socket, system_id, true, UsersMap, UPLINE_PARENT_ID, "2016-1-1", enddate.c_str()) == false)
		return Debug(DEBUG_ERROR, "CDb::RebuildLevel GetUsers == false");

	int strcount = m_LevelsSS.str().size();
	if (strcount == 0)
		m_LevelsSS << "INSERT INTO ce_levels (system_id, user_id, ancestor_id, level) VALUES ";

	//Debug(DEBUG_ERROR, "CDb::RebuildLevel - m_LevelsCount", m_LevelsCount);

	std::map <std::string, CUser>::iterator j;
	for (j=UsersMap.begin(); j != UsersMap.end(); ++j)
	{
		CUser *puser = &j->second;
		if ((puser->m_UserID.size() != 0) && (puser->m_UserType == 1))
		{
			m_LevelsSS << "(" << system_id << ", '" << puser->m_UserID << "', '" << puser->m_SponsorID << "', 1), ";
			m_LevelsCount++;
		}
		RebuildLevelsLadder(socket, system_id, UsersMap, puser->m_UserID, puser->m_SponsorID, 2);
	}

	return true;
}

////////////////////////////////////////
// Climb up the rebuild levels ladder //
////////////////////////////////////////
bool CDb::RebuildLevelsLadder(int socket, int system_id, std::map <std::string, CUser> &UsersMap, std::string user_id, std::string sponsor_id, int generation)
{
	if (UsersMap[sponsor_id].m_SponsorID.empty())
		return false;

	CUser *puser = &UsersMap[user_id];
	if ((puser->m_UserID.size() != 0) && (puser->m_UserType == 1))
	{
		m_LevelsSS << "(" << system_id << ", '" << user_id << "', '" << UsersMap[sponsor_id].m_SponsorID << "', " << generation << "), ";
		m_LevelsCount++;
	}

	// Maximum INSERT of inline records //
	if (m_LevelsCount >= MAX_SQL_APPEND)
	{
		std::string tmpstr = m_LevelsSS.str();
		tmpstr.erase(tmpstr.size()-2, 2); // Trim the ", " off the end //
		if (ExecDB(socket, tmpstr.c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDb::RebuildLevelsLadder - ExecDB m_LevelsSS Error");

		m_LevelsSS.clear();
		m_LevelsSS.str("");
		m_LevelsCount = 0;

		m_LevelsSS << "INSERT INTO ce_levels (system_id, user_id, ancestor_id, level) VALUES ";

		//Debug(DEBUG_WARN, "CDb::RebuildLevelsLadder - m_LevelsSS", m_LevelsSS);
	}

	return RebuildLevelsLadder(socket, system_id, UsersMap, user_id, UsersMap[sponsor_id].m_SponsorID, generation+1);
}

/////////////////////////////////
// Is there a recurrsion loop? //
/////////////////////////////////
bool CDb::IsRecursionLoop(int socket, int system_id, int upline)
{
	std::map <std::string, CUser> UsersMap;
	if (GetUsers(socket, system_id, true, UsersMap, upline, "2016-1-1", "2100-1-1") == false)
		return Debug(DEBUG_ERROR, "CDb::IsRecursionLoop - Problems with GetUsers");

	//Debug(DEBUG_WARN, "CCommissions::RecursionLadder - system_id", system_id);

	std::map <std::string, CUser>::iterator j;
	for (j=UsersMap.begin(); j != UsersMap.end(); ++j) 
	{
		CUser *puser = &UsersMap[j->first]; // This seems to be more accurate //
		m_RecurrGen = 1;
		if (RecursionLadder(puser, system_id, upline) == true)
		{
			int usercount = UsersMap.size();
			Debug(DEBUG_ERROR, "CDb::IsRecursionLoop - system usercount", usercount);
			return true;
		}
	}

	return false;
}

/////////////////////////////////////////
// Go up the ladder looking for errors //
/////////////////////////////////////////
bool CDb::RecursionLadder(CUser *puser, int system_id, int upline)
{
	if (puser == NULL)
		return false;

	// Allows us to see the user_id's that are causing problems //
	//Debug(DEBUG_ERROR, "CDb::RecursionLadder - puser->m_UserID", puser->m_UserID);
	//Debug(DEBUG_ERROR, "CDb::RecursionLadder - puser->m_ParentID", puser->m_ParentID);
	//Debug(DEBUG_ERROR, "CDb::RecursionLadder - puser->m_SponsorID", puser->m_SponsorID);
	//Debug(DEBUG_WARN, "-----------------------");
	//Debug(DEBUG_ERROR, "CDb::RecursionLadder - m_RecurrGen", m_RecurrGen);

	m_RecurrGen++;
	if (m_RecurrGen > GENERATION_MAX)
	{
		//if (upline == 1)
		//	Debug(DEBUG_ERROR, "CDb::RecursionLadder - UPLINE PARENT_ID");
		//else if (upline == 2)
		//	Debug(DEBUG_ERROR, "CDb::RecursionLadder - UPLINE SPONSOR_ID");
		Debug(DEBUG_ERROR, "CDb::RecursionLadder - Recursion Loop detected - system_id", system_id);
		Debug(DEBUG_ERROR, "CDb::RecursionLadder - puser->m_UserID", puser->m_UserID);
		Debug(DEBUG_ERROR, "CDb::RecursionLadder - puser->m_SponsorID", puser->m_SponsorID);
		//Debug(DEBUG_ERROR, "CDb::RecursionLadder - **Danger** > GENERATION_MAX! system_id", system_id);
		return true;
	}
		
	return RecursionLadder(puser->m_pSponsor, system_id, upline); // Climb up the sponsor tree //
}

/////////////////////////////
// Query Receipts sumation //
/////////////////////////////
const char *CDb::QueryReceiptSum(int socket, int system_id, string searchsql, string sqlend)
{
	std::stringstream ss;
	int receiptcount = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_receipt_totals WHERE system_id=" << system_id << searchsql);
	if (receiptcount == 0)
		return SetError(400, "API", "queryreceiptsum error", "There are no receipts in the system for given start and end dates");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, user_id, count, amount, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_receipt_totals WHERE system_id=" << system_id << sqlend)) == NULL)
		return SetError(503, "API", "queryreceiptsum error", "There was an internal error that prevented an SELECT from the database");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << receiptcount << "\"";
	ss3 << ",\"receipts\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"userid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"count\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"amount\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"createdat\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss3 << "\"updatedat\":\"" << conn->m_RowMap[6].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryReceiptSum - ThreadReleaseConn == false");
		return SetError(503, "API", "queryreceiptsum error", "Could not release the database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1);
    json += "]";
	return SetJson(200, json.c_str());
}

//////////////////////////////////////
// Grab a list of breakdown payouts //
//////////////////////////////////////
const char *CDb::QueryBreakdown(int socket, int system_id, int receipt_id)
{
	std::stringstream ss;
	int breakdowncount = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_breakdown WHERE system_id=" << system_id);
	if (breakdowncount == 0)
		return SetError(400, "API", "querybreakdown error", "There are no breakdown entries for the given receipt");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, batch_id, user_id, amount, created_at, updated_at FROM ce_breakdown WHERE system_id=" << system_id << " AND receipt_id='" << receipt_id << "'")) == NULL)
		return SetError(503, "API", "querybreakdown error", "There was an internal error that prevented a SELECT from the database");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << breakdowncount << "\"";
	ss3 << ",\"breakdown\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"batchid\":\"" << conn->m_RowMap[1].c_str() << "\",";		
		ss3 << "\"userid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"amount\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"createdat\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"updatedat\":\"" << conn->m_RowMap[5].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBreakdown - ThreadReleaseConn == false");
		return SetError(503, "API", "querybreakdown error", "Could not release the database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1);
    json += "]";
	return SetJson(200, json.c_str());
}

///////////////////////////////////////////////
// Handle query of breakdown with pagination //
///////////////////////////////////////////////
const char *CDb::QueryBreakdownAlt(int socket, int system_id, string receipt_id, string searchsql, string sqlend)
{
	std::stringstream ss;
	int breakdowncount = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_breakdown WHERE system_id=" << system_id << " AND receipt_id='" << receipt_id << "'" << searchsql);
	if (breakdowncount == 0)
		return SetError(400, "API", "querybreakdownalt error", "There are no breakdown entries for the given receipt");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, batch_id, receipt_id, user_id, amount, commrule_id, generation, percent, infinitybonus, created_at, updated_at FROM ce_breakdown WHERE system_id=" << system_id << sqlend)) == NULL)
		return SetError(503, "API", "querybreakdownalt error", "There was an internal error that prevented a SELECT from the database");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << breakdowncount << "\"";
	ss3 << ",\"breakdown\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"batchid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"receiptid\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"userid\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"amount\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss3 << "\"commruleid\":\"" << conn->m_RowMap[6].c_str() << "\",";
		ss3 << "\"generation\":\"" << conn->m_RowMap[7].c_str() << "\",";
		ss3 << "\"percent\":\"" << conn->m_RowMap[8].c_str() << "\",";
		ss3 << "\"infinitybonus\":\"" << conn->m_RowMap[9].c_str() << "\",";
		ss3 << "\"createdat\":\"" << conn->m_RowMap[10].c_str() << "\",";
		ss3 << "\"updatedat\":\"" << conn->m_RowMap[11].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{	
		Debug(DEBUG_ERROR, "CDb::QueryBreakdownAlt - ThreadReleaseConn == false");
		return SetError(503, "API", "querybreakdownalt error", "Could not release the database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1);
    json += "]";
	return SetJson(200, json.c_str());
}

////////////////////////
// Needed for testing //
// Run actual poolpot //
////////////////////////
const char *CDb::RunPoolPot(int socket, int system_id, int poolpotid)
{
	// Make sure we have a pool to run //
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_poolpots WHERE system_id=" << system_id << " AND id=" << poolpotid) == 0)
		return SetError(400, "API", "db::runpoolpot error", "That is an invalid poolpot");

	// Grab the pool values //
	CConn *conn;
	std::stringstream ss1;
	if ((conn = ExecDB(socket, ss1 << "SELECT qualify_type, amount, start_date::DATE, end_date::DATE FROM ce_poolpots WHERE system_id=" << system_id << " AND id=" << poolpotid)) == NULL)
		return SetError(503, "API", "db::runpoolpot error", "Database Error. Could not SELECT from database");
	if (FetchRow(conn) == false)
		return SetError(503, "API", "db::runpoolpot error", "Database Error. Could not fetch row");

	// Retain values //
	int qualify_type = atoi(conn->m_RowMap[0].c_str());
	int amount = atoi(conn->m_RowMap[1].c_str());
	std::string startdate = conn->m_RowMap[2].c_str();
	std::string enddate = conn->m_RowMap[3].c_str();

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{	
		Debug(DEBUG_ERROR, "CDb::RunPoolPot - ThreadReleaseConn == false");
		return SetError(503, "API", "runpoolpot error", "Could not release the database connection");
	}

	// Create the batch //
	std::stringstream ssbatch;
	if (ExecDB(socket, ssbatch << "INSERT INTO ce_batches (system_id, start_date, end_date) VALUES (" << system_id << ", '" << startdate << "', '" << enddate << "')") == NULL)
		return SetError(503, "API", "db::runpoolpot error", "Database Error. Problem with batch creation");
	std::stringstream ssbatch2;
	int batch_id = GetFirstDB(socket, ssbatch2 << "SELECT id FROM ce_batches WHERE system_id=" << system_id << " ORDER BY id DESC");

	// Process pool commissions //
	CCommissions comm;
	std::string result = comm.RunPool(this, socket, system_id, batch_id, poolpotid, qualify_type, amount, startdate.c_str(), enddate.c_str());
	return result.c_str();
}

////////////////////////////////
// Grab a list of all batches //
////////////////////////////////
const char *CDb::QueryBatches(int socket, int system_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_batches WHERE system_id=" << system_id) == 0)
		return SetError(503, "API", "querybatches error", "There currently no batches in the database for that system");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, start_date, end_date, disabled, created_at, updated_at FROM ce_batches WHERE system_id=" << system_id << " ORDER BY id")) == NULL)
		return SetError(503, "API", "querybatches error", "There was an internal error that prevented an SELECT in the database");

	std::stringstream ss3;
	ss3 << ",\"batches\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"startdate\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"enddate\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"disabled\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"createdat\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss3 << "\"updatedat\":\"" << conn->m_RowMap[6].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBatches - ThreadReleaseConn == false");
		return SetError(503, "API", "querybatches error", "Could not release the database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

////////////////////////////////////////////
// Grab a list of batches with pagenation //
////////////////////////////////////////////
const char *CDb::QueryBatchesAlt(int socket, int system_id, string searchsql, string sqlend)
{
	std::stringstream ss;
	int count = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_batches WHERE system_id=" << system_id << searchsql);
	if (count == 0)
		return SetError(503, "API", "querybatchesalt error", "There currently no batches in the database for that system");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, start_date, end_date, receipts_wholesale, commissions, bonuses, pools, disabled, created_at, updated_at FROM ce_batches WHERE system_id=" << system_id << sqlend)) == NULL)
		return SetError(503, "API", "querybatchesalt error", "There was an internal error that prevented an SELECT in the database");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << count << "\"";
	ss3 << ",\"batches\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"startdate\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"enddate\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"receipts\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"commissions\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss3 << "\"bonuses\":\"" << conn->m_RowMap[6].c_str() << "\",";
		ss3 << "\"pools\":\"" << conn->m_RowMap[7].c_str() << "\",";
		ss3 << "\"disabled\":\"" << conn->m_RowMap[8].c_str() << "\",";
		ss3 << "\"createdat\":\"" << conn->m_RowMap[9].c_str() << "\",";
		ss3 << "\"updatedat\":\"" << conn->m_RowMap[10].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBatchesAlt - ThreadReleaseConn == false");
		return SetError(503, "API", "querybatchesalt error", "Could not release the database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

////////////////////////////////////////
// Grab a commission value for a user //
////////////////////////////////////////
const char *CDb::QueryUserComm(int socket, int system_id, const char *user_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_commissions WHERE system_id=" << system_id << " AND user_id='" << user_id << "'") == 0)
		return SetError(412, "API", "queryusercomm error", "There are no commission records for given user");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, batch_id, user_id, amount FROM ce_commissions WHERE system_id=" << system_id << " AND user_id='" << user_id << "'")) == NULL)
		return SetError(503, "API", "queryusercomm error", "There was with a problem with a database call for queryusercomm");

	std::stringstream ss3;
	ss3 << ",\"commission\":[";
    while (FetchRow(conn) == true)
    {
    	ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
    	ss3 << "\"system_id\":\"" << conn->m_RowMap[1].c_str() << "\",";
    	ss3 << "\"batch_id\":\"" << conn->m_RowMap[2].c_str() << "\",";
    	ss3 << "\"user_id\":\"" << conn->m_RowMap[3].c_str() << "\",";
    	ss3 << "\"amount\":\"" << conn->m_RowMap[4].c_str() << "\"},";
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryUserComm - ThreadReleaseConn == false");
		return SetError(503, "API", "queryusercomm error", "Could not release the database connection");
	}

    std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
    return SetJson(200, json.c_str());

	//return SetError(503, "API", "queryusercomm error", "There was with a problem with a database call for queryusercomm");
}

///////////////////////////////////////////
// Grab a commission value for all users //
///////////////////////////////////////////
const char *CDb::QueryBatchComm(int socket, int system_id, int batch_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_commissions WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == 0)
		return SetError(412, "API", "querybatchcomm error", "There are no commission records for given batch");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, batch_id, user_id, amount FROM ce_commissions WHERE system_id=" << system_id << " AND batch_id=" << batch_id)) == NULL)
		return SetError(503, "API", "querybatchcomm error", "There was with a problem with a database call for queryalluserscomm");

	std::stringstream ss3;
	ss3 << ",\"commissions\":[";
	while (FetchRow(conn) == true)
    {
    	ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
    	ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
    	ss3 << "\"batchid\":\"" << conn->m_RowMap[2].c_str() << "\",";
    	ss3 << "\"userid\":\"" << conn->m_RowMap[3].c_str() << "\",";
    	ss3 << "\"amount\":\"" << conn->m_RowMap[4].c_str() << "\"},";
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBatchComm - ThreadReleaseConn == false");
		return SetError(503, "API", "querybatchcomm error", "Could not release the database connection");
	}

    std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

////////////////////////////////////////////////
// Grab list of grandtotal entries to be paid //
////////////////////////////////////////////////
const char *CDb::QueryGrandPayout(int socket, int system_id, const char *authorized, string searchsql, string sqlend)
{
	std::stringstream ss;
	int count = GetFirstDB(socket, ss << "SELECT count(*) FROM ce_grandtotals WHERE system_id=" << system_id << " AND authorized=" << authorized << " AND syncd_payman=false " << searchsql);
	if (count == 0)
		return SetError(412, "API", "querygrandpayout error", "There are no records for given criteria");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, user_id, amount, authorized, syncd_payman, disabled, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_grandtotals WHERE system_id=" << system_id << " AND authorized=" << authorized << " AND syncd_payman=false " << sqlend)) == NULL)
		return SetError(503, "API", "querygrandpayout error", "There was with a problem with a SELECT");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << count << "\"";
	ss3 << ",\"grandtotals\":[";
	while (FetchRow(conn) == true)
    {
    	ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
    	ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
    	ss3 << "\"userid\":\"" << conn->m_RowMap[2].c_str() << "\",";
    	ss3 << "\"amount\":\"" << conn->m_RowMap[3].c_str() << "\",";
    	ss3 << "\"authorized\":\"" << conn->m_RowMap[4].c_str() << "\",";
    	ss3 << "\"syncdpayman\":\"" << conn->m_RowMap[5].c_str() << "\",";
    	ss3 << "\"disabled\":\"" << conn->m_RowMap[6].c_str() << "\",";
    	ss3 << "\"createdat\":\"" << conn->m_RowMap[7].c_str() << "\",";
    	ss3 << "\"updatedat\":\"" << conn->m_RowMap[8].c_str() << "\"},";
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
	{	
		Debug(DEBUG_ERROR, "CDb::QueryGrandPayout - ThreadReleaseConn == false");
		return SetError(503, "API", "querygrandpayout error", "Could not release the database connection");
	}

    std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

////////////////////////////////////////
// Auth a grandtotal entry to be paid //
////////////////////////////////////////
const char *CDb::AuthGrandPayout(int socket, int system_id, int grand_id, const char *authorized)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_grandtotals WHERE system_id=" << system_id << " AND id=" << grand_id << " AND syncd_payman=false AND disabled=false") == false)
		return SetError(503, "API", "authgrandpayout error", "The record is unavailable for authorization");

	std::stringstream ss2;
	if (ExecDB(socket, ss2 << "UPDATE ce_grandtotals SET authorized=" << authorized << " WHERE system_id=" << system_id << " AND id=" << grand_id) == NULL)
		return SetError(503, "API", "authgrandpayout error", "There was with a problem with a UPDATE");

	return SetJson(200, "");
}

/////////////////////////////////////////////////
// Authorize all transactions for given system //
/////////////////////////////////////////////////
const char *CDb::AuthGrandBulk(int socket, int system_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_grandtotals SET authorized=true WHERE system_id=" << system_id << " AND authorized=false AND disabled=false AND syncd_payman=false") == NULL)
		return SetError(503, "API", "authgrandbulk error", "There was with a problem with a UPDATE");

	return SetJson(200, "");
}

//////////////////////////////////
// Disable a grandpayout record //
//////////////////////////////////
const char *CDb::DisableGrandPayout(int socket, int system_id, int grand_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_grandtotals SET disabled=true WHERE id=" << grand_id) == NULL)
		return SetError(503, "API", "disablegrandpayout error", "There was with a problem with a UPDATE");

	return SetJson(200, "");
}

/////////////////////////////////
// Enable a grandpayout record //
/////////////////////////////////
const char *CDb::EnableGrandPayout(int socket, int system_id, int grand_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_grandtotals SET disabled=false WHERE id=" << grand_id) == NULL)
		return SetError(503, "API", "enablegrandpayout error", "There was with a problem with a UPDATE");

	return SetJson(200, "");
}

//////////////////////////////
// Sync payouts with Payman //
//////////////////////////////
const char *CDb::SyncWithPayman(int socket, int system_id)
{
#ifndef COMPILE_RUBYRICE
	//////////////////////////
	// Sync the commissions //
	//////////////////////////
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, user_id, amount FROM ce_grandtotals WHERE disabled=false AND syncd_payman=false AND system_id=" << system_id)) == NULL)
		Debug(DEBUG_ERROR, "CDb::SyncWithPayman - Error with ExecDB commissions");

	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After ExecDB");

	std::stringstream ss2;
	ss2 << "[";
	while (FetchRow(conn) == true)
	{
		// Build json string //
		ss2 << "{\"id\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss2 << "\"userId\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss2 << "\"amount\":\"" << conn->m_RowMap[2].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		Debug(DEBUG_ERROR, "CDb::SyncWithPayman - ThreadReleaseConn == false");

	std::string json;
    json = ss2.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After FetchRow");

	// curl send json to payman //
	std::string apikey = "Authorization: APIKey ";
	apikey += m_pSettings->m_PaymanAPIkey;
	CezCurl curl;
	curl.SetHeader(apikey.c_str());
	std::string retjson = curl.SendJson(m_pSettings->m_PaymanURL.c_str(), json.c_str());

	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After curl.SendJson");

	// Parse the response json //
	Json::FastWriter fastWriter;
	std::string jsonMessage = fastWriter.write(retjson);
	Json::Value jsonValue;
	Json::Reader reader;

	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After fastWriter.write");

	bool parsingSuccessful = reader.parse(jsonMessage, jsonValue);
	if (parsingSuccessful)
	{
		Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After parsingSuccessful");

		if (jsonValue.size() == 1)
		{
			bool flagfound = false;

			Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After flagfound = false");

			for (Json::ValueIterator i = jsonValue[0].begin(); i != jsonValue[0].end(); i++)
			{
				if (i.key() == "processedIds")
				{
					Debug(DEBUG_ERROR, "CDb::SyncWithPayman - After i.key() == processedIds");

					flagfound = true;
					int max = jsonValue[0]["processedIds"].size();
					int index;
					for (index=0; index < max; index++)
					{
						//std::cout << index << " = " << jsonValue[0]["processedIds"][index] << "\n";

						if (SetSyncGrand(socket, atoi(jsonValue[0]["processedIds"][index].asString().c_str())) == false)
							Debug(DEBUG_ERROR, "CDb::SyncWithPayman - SetSyncGrand == false?!?");
					}
				}
	        }

	        if (flagfound == false)
	        {
	        	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - response json missing processedIds");
	        	return SetError(503, "API", "SyncWithPayman error", "response json missing processedIds");
	        }
	    }
	    else
	    {
	    	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - response json missing data");
	        return SetError(503, "API", "SyncWithPayman error", "response json missing data");
	    }
	}
	else
	{
	   	Debug(DEBUG_ERROR, "CDb::SyncWithPayman - Unsuccessful parsing of json");
	    return SetError(503, "API", "SyncWithPayman error", "Unsuccessful parsing of json");
	}

//COMPILE_RUBYRICE
#endif

	// Return the json to send to payman for now //
	return SetJson(200, "");
}

///////////////////////////////////////
// Sync Grandpayout with local nacha //
///////////////////////////////////////
const char *CDb::SyncWithNacha(int socket, int system_id)
{
	CConn *conn;
	std::stringstream ss;
	if ((conn = ExecDB(socket, ss << "SELECT id, user_id, amount FROM ce_grandtotals WHERE disabled=false AND syncd_payman=false AND system_id=" << system_id)) == NULL)
		Debug(DEBUG_ERROR, "CDb::SyncWithPayman - Error with ExecDB commissions");

	// Open nacha file for writing
	Debug(DEBUG_ERROR, "CDb::SyncWithNacha - After ExecDB");

	while (FetchRow(conn) == true)
	{
		// Add the record to nacha
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::SyncWithNacha - ThreadReleaseConn == false");
		return "Database Error could not release";
	}

	Debug(DEBUG_ERROR, "CDb::SyncWithNacha - After FetchRow");

	// Close the nacha file //

	// How do we notify use the process is finished? //

	return "Needs to be finished";
}

////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////// Bank account functions ////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////
// Make entries for inital validation //
////////////////////////////////////////
const char *CDb::InitiateValidation(int socket, int system_id, const char *user_id,  double amount1, double amount2)
{
	// Make sure there is a valid bank account //
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id) < 1)
		return SetError(400, "API", "initiatevalidation error", "There is not bank account in ref to the userid");

	// Grab the account id //
	std::stringstream ss2;
	int account_id = GetFirstDB(socket, ss2 << "SELECT id FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id);

	// Insert into bank validation //
	std::stringstream query;
	if (ExecDB(socket, query << "INSERT INTO ce_bankvalidation(system_id, account_id, amount1, amount2, created_at) VALUES (" << system_id << ", " << account_id << ", '" << amount1 << "', '" << amount2 << "', 'now()')") == NULL)
		return SetError(503, "API", "initiavevalidation error", "There was an error inserting into the database the validation information");

	// Return the validationid //
//	std::stringstream query2;
//	const char *str_id = GetFirstCharDB(query2 << "SELECT id FROM bankvalidation WHERE account_id=" << account_id << " AND system_id=" << system_id << " ORDER BY id DESC LIMIT 1");

	// Write out Json //
	return SetJson(200, "");
	//std::stringstream ss3;
	//return SetJson(200, ss3 << ",\"validation\":{\"amount1\":\"" << amount1 << "\",\"amount2\":\"" << amount2 << "\"}");

	//ss3 << "{"success":{"status":"200"}, "validation"}
}

///////////////////////////////////////
// Validate bank account information //
///////////////////////////////////////
const char *CDb::ValidateBankAccount(int socket, int system_id, const char *user_id, const char *amount1, const char *amount2)
{
	// Make sure there is a valid bank account //
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id) < 1)
		return SetError(400, "API", "validatebankaccount error", "There is no bank account in ref to the userid");

	std::stringstream ss2;
	if (GetFirstDB(socket, ss2 << "SELECT count(*) FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id << " AND validated=true") == 1)
		return SetError(400, "API", "validatebankaccount error", "The given bank account has already been validated");

	// Grab the account id //
	std::stringstream ss3;
	int account_id = GetFirstDB(socket, ss3 << "SELECT id FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id << " ORDER BY id DESC");

	// Query amounts //
	CConn *conn;
	std::stringstream query;
	if ((conn = ExecDB(socket, query << "SELECT amount1, amount2 FROM ce_bankvalidation WHERE account_id=" << account_id << " AND system_id=" << system_id)) == NULL)
		return SetError(503, "API", "validatebankaccount error", "There was an error verifying in the database the validation information");

	// Do comparison of amounts //
	double u_amount1 = atof(amount1);
	double u_amount2 = atof(amount2);
	double d_amount1 = 0;
	double d_amount2 = 0;
	if (FetchRow(conn) == true)
    {
    	d_amount1 = atof(conn->m_RowMap[0].c_str());
    	d_amount2 = atof(conn->m_RowMap[1].c_str());

    	if (((u_amount1 == d_amount1) && (u_amount2 == d_amount2)) ||
			((u_amount1 == d_amount2) && (u_amount2 == d_amount1)))
		{
			// Update account to validated status //
			std::stringstream query2;
			if (ExecDB(socket, query2 << "UPDATE ce_bankaccounts SET validated=true, updated_at='now()' WHERE id=" << account_id << " AND system_id=" << system_id) == NULL)
				return SetError(503, "API", "validatebankaccount error", "There was an error updating the database with the validation success");

			if (ThreadReleaseConn(conn->m_Resource) == false)
			{	
				Debug(DEBUG_ERROR, "CDb::ValidateBankAccount - #1 ThreadReleaseConn == false");
				return SetError(503, "API", "validatebankaccount error", "Could not release the database connection");
			}

			return SetJson(200, "");
		}
    }
    else // Error //
    	return SetError(503, "API", "validatebankaccount error", "No validation record available. Try initiating a validation first");

    if (ThreadReleaseConn(conn->m_Resource) == false)
	{	
		Debug(DEBUG_ERROR, "CDb::ValidateBankAccount - #2 ThreadReleaseConn == false");
		return SetError(503, "API", "validatebankaccount error", "Could not release the database connection");
	}

	return SetError(406, "API", "validatebankaccount failure", "value amounts did not validate");
}

//////////////////////////////////////////
// Add a record for a bank payment file //
//////////////////////////////////////////
int CDb::AddBankPayoutFile(int socket, int system_id, int batch_id, const char *filename)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "INSERT INTO ce_bankpayoutfile (system_id, batch_id, filename, filedate, created_at) VALUES (" << system_id << ", " << batch_id << ", '" << filename << "', 'now()', 'now()')") == NULL)
		return 0;

	// Grab and return the file_id back out //
	std::stringstream ss2;
	int file_id = GetFirstDB(socket, ss2 << "SELECT id FROM ce_bankpayoutfile WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " ORDER BY id DESC LIMIT 1");
	return file_id;
}

///////////////////////////////
// Add a bank payment record //
///////////////////////////////
bool CDb::AddBankPayment(int socket, int system_id, int batch_id, const char *user_id, double amount, int payoutfile_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "INSERT INTO ce_bankpayments (system_id, batch_id, user_id, amount, pay_date, payoutfile_id, created_at) VALUES (" << system_id << ", " << batch_id << ", '" << user_id << "', '" << amount << "', 'now()', '" << payoutfile_id << "', 'now()')") == NULL)
		return false;

	return true;
}

/////////////////////////
// Query User Payments //
/////////////////////////
const char *CDb::QueryUserPayments(int socket, int system_id, const char *user_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_bankpayments WHERE user_id='" << user_id << "' AND system_id=" << system_id) < 1)
		return SetError(400, "API", "queryuserpayments error", "There are no payments in ref to the userid");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT amount, pay_date, created_at FROM ce_bankpayments WHERE user_id='" << user_id << "' AND system_id=" << system_id << " ORDER BY id")) == NULL)
		return SetError(503, "API", "queryuserpayments error", "There was an error running a SELECT database statement");

	std::stringstream ss3;
	ss3 << ",\"userpayments\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"user_id\":\"" << user_id << "\",";
		ss3 << "\"amount\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"pay_date\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"created_at\":\"" << conn->m_RowMap[2].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryUserPayments - ThreadReleaseConn == false");
		return SetError(503, "API", "queryuserpayments error", "Could not release database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

//////////////////////////
// Query Batch Payments //
//////////////////////////
const char *CDb::QueryBatchPayments(int socket, int system_id, int batch_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_bankpayments WHERE system_id=" << system_id << " AND batch_id=" << batch_id) < 1)
		return SetError(400, "API", "querybatchpayments error", "There are no payments in ref to the batchid");

	CConn *conn;
	std::stringstream ss2;
	if ((conn = ExecDB(socket, ss2 << "SELECT id, system_id, user_id, amount, pay_date, created_at::timestamp(0), updated_at::timestamp(0) FROM ce_bankpayments WHERE system_id=" << system_id << " AND batch_id=" << batch_id << " ORDER BY id")) == NULL)
		return SetError(503, "API", "querybatchpayments error", "There was an error running a SELECT database statement");

	std::stringstream ss3;
	ss3 << ",\"batchpayments\":[";
	while (FetchRow(conn) == true)
	{
		ss3 << "{\"id\":\"" << conn->m_RowMap[0].c_str()<< "\",";
		ss3 << "\"systemid\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"userid\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"amount\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"paydate\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"createdat\":\"" << conn->m_RowMap[5].c_str() << "\",";
		ss3 << "\"updatedat\":\"" << conn->m_RowMap[6].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBatchPayments - ThreadReleaseConn == false");
		return SetError(503, "API", "querybatchpayments error", "Could not release database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

//////////////////////////////////////////////////
// Grab a list of users that won't be paid 		//
// because a bank account hasn't been validated //
//////////////////////////////////////////////////
const char *CDb::GetNoPayUsers(int socket, int system_id, int batch_id)
{
	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_commissions WHERE batch_id=" << batch_id << " AND system_id=" << system_id << " AND user_id NOT IN (SELECT user_id FROM ce_bankaccounts WHERE validated=true AND system_id=" << system_id << ")") == 0)
		return SetJson(200, ""); // Successful but no records returned //

	// Grab a list of all users getting a commission - WITHOUT a validated bank account //
	CConn *conn;
	std::stringstream query;
	if ((conn = ExecDB(socket, query << "SELECT user_id, amount FROM ce_commissions WHERE batch_id=" << batch_id << " AND system_id=" << system_id << " AND user_id NOT IN (SELECT user_id FROM ce_bankaccounts WHERE validated=true AND system_id=" << system_id << ")")) == NULL)
		return SetError(503, "API", "getnopayusers error", "There was an error retrieving information in database in ref to no pay users");

	std::stringstream ss;
	ss << ",\"commissions\":[";
	while (FetchRow(conn) == true)
	{
		ss << "{\"user_id\":\"" << conn->m_RowMap[0].c_str() << "\",\"amount\":\"" << conn->m_RowMap[1].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::GetNoPayUsers - ThreadReleaseConn == false");
		return SetError(503, "API", "getnopayusers error", "Could not release database connection");
	}

	std::string json;
    json = ss.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

//////////////////////
// Query Bonus User //
//////////////////////
const char *CDb::QueryBonusUser(int socket, int system_id, const char *user_id)
{
	CConn *conn;
	std::stringstream ss0;
	if ((conn = ExecDB(socket, ss0 << "SELECT id, system_id, user_id, amount, bonus_date FROM ce_bonus WHERE disabled='false' AND system_id=" << system_id << " AND user_id='" << user_id << "' ORDER BY id")) == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBonusUser - Error on SELECT");
		return SetError(503, "API", "querybonususer error", "could not SELECT bonus record from database");
	}

	std::stringstream ss;
	ss << ",\"bonus\":[";
	while (FetchRow(conn) == true)
	{
		ss << "{\"bonus_id\":\"" << conn->m_RowMap[0].c_str() << "\",\"system_id\":\"" << conn->m_RowMap[1].c_str() << "\",\"user_id\":\"" << conn->m_RowMap[2].c_str() << "\",\"amount\":\"" << conn->m_RowMap[3].c_str() << "\",\"bonus_date\":\"" << conn->m_RowMap[4].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::QueryBonusUser - ThreadReleaseConn == false");
		return SetError(503, "API", "querybonususer error", "Could not release database connection");
	}

	std::string json;
    json = ss.str();
    json = json.substr(0, json.size()-1); // Remove the trailing comma //
	json += "]";
	return SetJson(200, json.c_str());
}

/////////////////////////////////////////////////
// Transfer the bonus data to the ledger table //
/////////////////////////////////////////////////
bool CDb::BonusToLedger(int socket, int system_id, int batch_id, const char *start_date, const char *end_date)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_bonus WHERE disabled='false' AND system_id=" << system_id << " AND bonus_date >='" << start_date << "' AND bonus_date<='" << end_date << "'") == 0)
		return false;

	std::stringstream ss0;
	if (GetFirstDB(socket, ss0 << "SELECT count(*) FROM ce_bonus WHERE disabled='false' AND batch_id > 0 AND system_id=" << system_id << " AND bonus_date >='" << start_date << "' AND bonus_date<='" << end_date << "'") > 0)
		return Debug(DEBUG_ERROR, "CDb::BonusToLedger - batch_id is already set in more than one record. system_id", system_id);

	std::stringstream ss1;
	if (ExecDB(socket, ss1 << "UPDATE ce_bonus SET batch_id=" << batch_id << " WHERE disabled='false' AND system_id=" << system_id << " AND bonus_date >='" << start_date << "' AND bonus_date<='" << end_date << "'") == NULL)
		return Debug(DEBUG_ERROR, "CDb::BonusToLedger - Problems Executing UPDATE");

	m_ConnPool.WaitForThreads(socket);

	std::stringstream ss2;
	if (ExecDB(socket, ss2 << "INSERT INTO ce_ledger (system_id, batch_id, user_id, amount, ledger_type, event_date) SELECT system_id, batch_id, user_id, amount, '" << LEDGER_BONUS << "', now() FROM ce_bonus WHERE disabled='false' AND system_id=" << system_id << " AND bonus_date >='" << start_date << "' AND bonus_date<='" << end_date << "'") == NULL)
		return Debug(DEBUG_ERROR, "CDb::BonusToLedger - Problems Executing INSERT");

	return true;
}

///////////////////////////////////////
// Transfer Rank Gen Bonus to Ledger //
///////////////////////////////////////
bool CDb::RankGenBonusToLedger(int socket, int system_id, int batch_id, const char *end_date)
{
	stringstream ss;

	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_rankgenbonus WHERE disabled='false' AND system_id=" << system_id << " AND batch_id='" << batch_id << "'") == 0)
		return false;

	string event_date = end_date;

	stringstream ss2;
	if (ExecDB(socket, ss2 << "INSERT INTO ce_ledger (system_id, batch_id, user_id, amount, ledger_type, event_date) SELECT " << system_id << ", " << batch_id << ", user_id, SUM(amount), '" << LEDGER_RANKGENBONUS << "', '" << event_date << "' FROM ce_rankgenbonus WHERE disabled='false' AND system_id=" << system_id << " AND batch_id='" << batch_id << "' GROUP BY user_id") == NULL)
		return Debug(DEBUG_ERROR, "CDb::BonusToLedger - Problems Executing INSERT");

	return true;
}

/////////////////////////////////////
// Get the bonus amount from batch //
/////////////////////////////////////
double CDb::GetBatchBonus(int socket, int system_id, const char *start_date, const char *end_date)
{
	stringstream ss;
	string sumbonus = GetFirstCharDB(socket, ss << "SELECT sum(amount) FROM ce_bonus WHERE disabled='false' AND system_id=" << system_id << " AND bonus_date >='" << start_date << "' AND bonus_date<='" << end_date << "'");
	return atof(sumbonus.c_str());
}

/////////////////////////////////////////////////////
// Need to grab to pass to payments for validation //
/////////////////////////////////////////////////////
CBankAccount *CDb::GetBankAccount(int socket, int system_id, const char *user_id)
{
	// Make sure there is a valid bank account //
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id) < 1)
		return 0;

	// Grab the account id //
	std::stringstream ss2;
	int account_id = GetFirstDB(socket, ss2 << "SELECT id FROM ce_bankaccounts WHERE user_id='" << user_id << "' AND system_id=" << system_id);

	// Grab the bank account //
	CConn *conn;
	std::stringstream query;
	if ((conn = ExecDB(socket, query << "SELECT id, user_id, account_type, routing_number, account_number, holder_name FROM ce_bankaccounts WHERE id=" << account_id << " AND system_id=" << system_id)) == NULL)
	{
		Debug(DEBUG_ERROR, "CDb::GetBankAccount - ExecDB Error");
		return 0;
	}

	if (FetchRow(conn) == true)
    {
		m_BankAcount.m_ID = atoi(conn->m_RowMap[0].c_str());
		m_BankAcount.m_UserID = atoi(conn->m_RowMap[1].c_str());
		m_BankAcount.m_AccountType = atoi(conn->m_RowMap[2].c_str());
		m_BankAcount.m_RoutingNumber = conn->m_RowMap[3].c_str();
		m_BankAcount.m_AccountNumber = conn->m_RowMap[4].c_str();
		m_BankAcount.m_HolderName = conn->m_RowMap[5].c_str();
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDb::GetBankAccount - ThreadReleaseConn == false");
		return 0;
	}

	return &m_BankAcount;
}

/////////////////////////////////////////
// Build a linked list of users to pay //
/////////////////////////////////////////
bool CDb::BuildPayUserList(int socket, int system_id, int batch_id, std::list <CPayUser> *pPayUsersLL)
{
	// Grab a list of all users getting a commission - WITH a validated bank account //
	CConn *conn;
	std::stringstream query;
	if ((conn = ExecDB(socket, query << "SELECT c.user_id, c.amount, b.account_type, b.routing_number, b.account_number, b.holder_name FROM ce_commissions c LEFT JOIN ce_bankaccounts b ON c.user_id=b.user_id WHERE c.batch_id=" << batch_id << " AND b.validated=true AND b.system_id=" << system_id)) == NULL) // Only commissions to be paid //
	{
		Debug(DEBUG_ERROR, "CDb::BuildPayUserList - ExecDB Error");
		return false;
	}

	// Do we want to double verify NO duplicate user_id's //

	while (FetchRow(conn) == true)
	{
		CPayUser NewPayUser;
		NewPayUser.m_UserID = conn->m_RowMap[0].c_str();
		NewPayUser.m_Commission = atof(conn->m_RowMap[1].c_str());
		NewPayUser.m_AccountType = atoi(conn->m_RowMap[2].c_str());
		NewPayUser.m_RoutingNumber = conn->m_RowMap[3].c_str();
		NewPayUser.m_AccountNumber = conn->m_RowMap[4].c_str();
		NewPayUser.m_HolderName = conn->m_RowMap[5].c_str();
		pPayUsersLL->push_back(NewPayUser);
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return Debug(DEBUG_ERROR, "CDb::BuildPayUserList - ThreadReleaseConn == false");

	return true;
}

//////////////////////////////////////////
// Get the startdate for the checkpoint //
//////////////////////////////////////////
std::string CDb::GetStartDateCP(int socket, int system_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint!=" << CHECKPOINT_FINISHED) == 0)
	{
		Debug(DEBUG_ERROR, "CDb::GetStartDateCP - no valid records from ce_checkpoint");
		return ""; // Empty string in no record available //
	}

	std::stringstream ss1;
	int batch_id = GetFirstDB(socket, ss1 << "SELECT batch_id FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint!=" << CHECKPOINT_FINISHED << " ORDER BY id DESC");
	if (batch_id == -1)
	{
		Debug(DEBUG_ERROR, "CDb::GetStartDateCP - Error batch_id = -1");
		return "";
	}

	std::stringstream ss2;
	std::string startdate = GetFirstCharDB(socket, ss2 << "SELECT start_date FROM ce_batches WHERE id=" << batch_id);
	return startdate;
}

////////////////////////////////////////
// Get The enddate for the checkpoint //
////////////////////////////////////////
std::string CDb::GetEndDateCP(int socket, int system_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint!=" << CHECKPOINT_FINISHED) == 0)
	{
		Debug(DEBUG_ERROR, "CDb::GetEndDateCP - no valid records from ce_checkpoint");
		return ""; // Empty string in no record available //
	}

	std::stringstream ss1;
	int batch_id = GetFirstDB(socket, ss1 << "SELECT batch_id FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint!=" << CHECKPOINT_FINISHED << " ORDER BY id DESC");
	if (batch_id == -1)
	{
		Debug(DEBUG_ERROR, "CDb::GetEndDateCP - Error batch_id = -1");
		return "";
	}

	std::stringstream ss2;
	std::string enddate = GetFirstCharDB(socket, ss2 << "SELECT end_date FROM ce_batches WHERE id=" << batch_id);
	return enddate;
}

//////////////////////
// Add a checkpoint //
//////////////////////
int CDb::AddCP(bool pretend, int socket, int system_id, int batch_id, int checkpoint, int newcheckpoint)
{
	if (pretend == true)
		return newcheckpoint;

	if (checkpoint != -1)
		return checkpoint;

	std::stringstream ss;
	if (ExecDB(socket, ss << "INSERT INTO ce_checkpoint (system_id, batch_id, checkpoint) VALUES (" << system_id << ", " << batch_id << ", " << newcheckpoint << ")") == NULL)
		return Debug(DEBUG_ERROR, "CDb::AddCheckPoint - Problems with INSERT SQL");

	return newcheckpoint;
}

/////////////////////////
// Update a checkpoint //
/////////////////////////
int CDb::EditCP(bool pretend, int socket, int system_id, int batch_id, int checkpoint, int newcheckpoint)
{
	if (pretend == true)
		return newcheckpoint;

	// Loading of database records and calculations //
	if ((checkpoint != CHECKPOINT_STARTED) && (newcheckpoint == CHECKPOINT_GETUSERS1))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_GETUSERS1) && (newcheckpoint == CHECKPOINT_USERSCOUNT))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_USERSCOUNT) && (newcheckpoint == CHECKPOINT_RECEIPTCOUNT))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_RECEIPTCOUNT) && (newcheckpoint == CHECKPOINT_RECURSIONLOOP))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_RECURSIONLOOP) && (newcheckpoint == CHECKPOINT_READINDB1))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_READINDB1) && (newcheckpoint == CHECKPOINT_CALCSALES1))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_CALCSALES1) && (newcheckpoint == CHECKPOINT_CALCUSED))
		return checkpoint;

	// Infinity //
	if (((checkpoint == CHECKPOINT_CALCSALES1) || (checkpoint == CHECKPOINT_CALCUSED)) && (newcheckpoint == CHECKPOINT_RANKRULES1))
	{
		// Do Nothing //
	}
	else if (newcheckpoint == CHECKPOINT_RANKRULES1)
		return checkpoint;

	if ((checkpoint != CHECKPOINT_RANKRULES1) && (newcheckpoint == CHECKPOINT_BREAKDOWN1))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_BREAKDOWN1) && (newcheckpoint == CHECKPOINT_COMMISSIONS1))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_COMMISSIONS1) && (newcheckpoint == CHECKPOINT_INFINITYCAP1))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_INFINITYCAP1) && (newcheckpoint == CHECKPOINT_GETUSERS2))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_GETUSERS2) && (newcheckpoint == CHECKPOINT_READINDB2))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_READINDB2) && (newcheckpoint == CHECKPOINT_CALCSALES2))
		return checkpoint;

	// Database Updating records //
	if (((checkpoint == CHECKPOINT_CALCSALES1) || (checkpoint == CHECKPOINT_CALCUSED) || (checkpoint == CHECKPOINT_CALCSALES2)) && (newcheckpoint == CHECKPOINT_RANKRULES2))
	{
		// Do Nothing //
	}
	else if (newcheckpoint == CHECKPOINT_RANKRULES2)
		return checkpoint;

	if ((checkpoint != CHECKPOINT_RANKRULES2) && (newcheckpoint == CHECKPOINT_BREAKDOWN2))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_BREAKDOWN2) && (newcheckpoint == CHECKPOINT_COMMISSIONS2))
		return checkpoint;
	if ((checkpoint != CHECKPOINT_COMMISSIONS2) && (newcheckpoint == CHECKPOINT_USERSTATS))
		return checkpoint;

	if ((checkpoint != CHECKPOINT_USERSTATS) && (newcheckpoint == CHECKPOINT_SIGNUPBONUS))
		return checkpoint;

	if ((checkpoint != CHECKPOINT_SIGNUPBONUS) && (newcheckpoint == CHECKPOINT_LEDGER))
		return checkpoint;

	if ((checkpoint != CHECKPOINT_LEDGER_TOTALS) && (newcheckpoint == CHECKPOINT_FINISHED))
		return checkpoint;

	if (checkpoint == CHECKPOINT_CALCSALES1)
	{
		std::stringstream ss0;
		if (ExecDB(socket, ss0 << "UPDATE ce_checkpoint SET batch_id=" << batch_id << ", updated_at='now()' WHERE system_id=" << system_id << " AND batch_id=-1") == NULL)
			return Debug(DEBUG_ERROR, "CDb::AddCheckPoint - Problems with UPDATE #1 SQL");
	}

	std::stringstream ss;
	if (ExecDB(socket, ss << "UPDATE ce_checkpoint SET checkpoint=" << newcheckpoint << ", updated_at='now()' WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::EditCheckPoint - Problems with UPDATE #2 SQL");

	return newcheckpoint;
}

////////////////////////////////
// Get most recent checkpoint //
////////////////////////////////
int CDb::GetCP(int socket, int system_id, const char *start_date, const char *end_date)
{
#ifdef COMPILE_UNITED
	// Verify to see if it's already been run //
	std::stringstream ss1;
	if (GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint=" << CHECKPOINT_FINISHED << " AND batch_id IN (SELECT id FROM ce_batches WHERE start_date::DATE='" << start_date << "' AND end_date::DATE='" << end_date << "')") == 1)
		return CHECKPOINT_FINISHED;
#else
	// Verify to see if it's already been run //
	std::stringstream ss1;
	if (GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint=" << CHECKPOINT_FINISHED << " AND batch_id IN (SELECT id FROM ce_batches WHERE system_id=" << system_id << " AND start_date::DATE='" << start_date << "' AND end_date::DATE='" << end_date << "')") == 1)
		return CHECKPOINT_FINISHED;
#endif

	// Is there a checkpoint unfinished? //
	std::stringstream ss2;
	if (GetFirstDB(socket, ss2 << "SELECT count(*) FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint!=" << CHECKPOINT_FINISHED) == 0)
		return -1;

	std::stringstream ss3;
	return GetFirstDB(socket, ss3 << "SELECT checkpoint FROM ce_checkpoint WHERE system_id=" << system_id << " AND checkpoint!=" << CHECKPOINT_FINISHED << " ORDER BY id DESC");
}

////////////////////////////////////////////////
// Get the batch_id from the checkpoint table //
////////////////////////////////////////////////
int CDb::GetCPBatchID(int socket, int system_id)
{
	std::stringstream ss;
	if (GetFirstDB(socket, ss << "SELECT count(*) FROM ce_checkpoint WHERE system_id=" << system_id << " AND batch_id > 1") == 0)
		return -1;

	std::stringstream ss2;
	return GetFirstDB(socket, ss2 << "SELECT batch_id FROM ce_checkpoint WHERE system_id=" << system_id << " ORDER BY batch_id DESC");
}

/////////////////////////
// Cleanup Ranks table //
/////////////////////////
bool CDb::CleanupRanks(int socket, int system_id, int batch_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "DELETE FROM ce_ranks WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::CleanupRanks - Problems with DELETE SQL");

	return true;
}

/////////////////////////////
// Cleanup Breakdown table //
/////////////////////////////
bool CDb::CleanupBreakdown(int socket, int system_id, int batch_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "DELETE FROM ce_breakdown WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::CleanupBreakdown - Problems with DELETE SQL");

	return true;
}

///////////////////////////////
// Cleanup Commissions table //
///////////////////////////////
bool CDb::CleanupCommissions(int socket, int system_id, int batch_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "DELETE FROM ce_commissions WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::CleanupCommissions - Problems with DELETE SQL");

	return true;
}

// Cleanup userstats tables //
bool CDb::CleanupUserstats(int socket, int system_id, int batch_id)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "DELETE FROM ce_userstats_month WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::CleanupCommissions - Problems with DELETE SQL #1");

	std::stringstream ss1;
	if (ExecDB(socket, ss1 << "DELETE FROM ce_userstats_total WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::CleanupCommissions - Problems with DELETE SQL #2");

	return true;
}

//////////////////////////
// Cleanup ledger table //
//////////////////////////
bool CDb::CleanupLedger(int socket, int system_id, int batch_id)
{
	// There could be problems with below //
	// BUT we might not need to use it //

	std::stringstream ss;
	if (ExecDB(socket, ss << "DELETE FROM ce_ledger WHERE system_id=" << system_id << " AND batch_id=" << batch_id) == NULL)
		return Debug(DEBUG_ERROR, "CDb::CleanupLedger - Problems with DELETE SQL");

	return true;
}

////////////////////////
// Speed up functions //
////////////////////////
bool CDb::AddGroupUsed(int socket, int system_id, const char *user_id, double amount, const char *start_date, const char *end_date)
{
	std::stringstream ss;
	if (ExecDB(socket, ss << "INSERT INTO ce_calcused(system_id, user_id, groupused, start_date, end_date) VALUES (" << system_id << ", " << user_id << ", " << amount << ", '" << start_date << "', '" << end_date << "')") == NULL)
		return Debug(DEBUG_ERROR, "CDb::AddGroupUsed - Problems with INSERT SQL");

	return true;
}
