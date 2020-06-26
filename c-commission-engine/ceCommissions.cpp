#include "ceCommissions.h"
#include "commissions.h"
#include "db.h"
#include <stdlib.h> // atoi //

extern int g_RankOverride;

/////////////////
// Constructor //
/////////////////
CceCommissions::CceCommissions(CDb *pDB, string origin)
{
	m_pDB = pDB;

	g_RankOverride = 0;
	CezJson::SetOrigin(origin);
}

///////////////////////////////////////////////////////////////////
// Get insight into how much you will be paying out to each user //
///////////////////////////////////////////////////////////////////
const char *CceCommissions::Predict(int socket, int system_id, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::predict error", "A database connection needs to be made first");
	if (is_date(startdate) == false)
		return SetError(400, "API", "commissions::predict error", "The startdate is not in correct date format YYYY-MM-DD");
	if (is_date(enddate) == false)
		return SetError(400, "API", "commissions::predict error", "The enddate is not in correct date format YYYY-MM-DD");

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
	m_Json = comm.Run(m_pDB, socket, system_id, commtype, true, false, startdate.c_str(), enddate.c_str(), "", compression);
	return m_Json.c_str();
}

////////////////////////////////////////////////////////////////////
// Get insight into the current grandtotal you will be paying out //
////////////////////////////////////////////////////////////////////
const char *CceCommissions::PredictGrandTotal(int socket, int system_id, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::predictgrandtotal error", "A database connection needs to be made first");
	if (is_date(startdate) == false)
		return SetError(400, "API", "commissions::predictgrandtotal error", "The startdate is not in correct date format YYYY-MM-DD");
	if (is_date(enddate) == false)
		return SetError(400, "API", "commissions::predictgrandtotal error", "The enddate is not in correct date format YYYY-MM-DD");

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
	m_Json = comm.Run(m_pDB, socket, system_id, commtype, true, true, startdate.c_str(), enddate.c_str(), "", compression);
	return m_Json.c_str();
}

///////////////////////////////
// Calculate the commissions //
///////////////////////////////
const char *CceCommissions::Calc(int socket, int system_id, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::calc error", "A database connection needs to be made first");
	if (is_date(startdate) == false)
		return SetError(400, "API", "commissions::calc error", "The startdate is not in correct date format YYYY-MM-DD");
	if (is_date(enddate) == false)
		return SetError(400, "API", "commissions::calc error", "The enddate is not in correct date format YYYY-MM-DD");

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
	m_Json = comm.Run(m_pDB, socket, system_id, commtype, false, true, startdate.c_str(), enddate.c_str(), "", compression);
	return m_Json.c_str();
}

//////////////////////////////////////////
// Grab a list of batches in the system //
//////////////////////////////////////////
const char *CceCommissions::QueryBatches(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::querybatches error", "A database connection needs to be made first");

	return m_pDB->QueryBatches(socket, system_id);
}

///////////////////////////////////
// Query Batches with pagenation //
///////////////////////////////////
const char *CceCommissions::QueryBatchesAlt(int socket, int system_id, string search, string orderby, string orderdir, string offset, string limit)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::querybatchesalt error", "A database connection needs to be made first");

	// Handle pagination values //
	if ((search.length()!=0) && (is_alphanum(search) == false))
		return SetError(400, "API", "commissions::querybatchesalt error", "The search string needs to be in a date format: 1-1-2000");
	if ((orderdir != "asc") && (orderdir != "desc"))
		return SetError(409, "API", "commissions::querybatchesalt error", "The orderdir variable needs to be either asc or desc");
	if (is_number(offset) == false)
		return SetError(400, "API", "commissions::querybatchesalt error", "The offset is not numeric");
	if (is_number(limit) == false)
		return SetError(400, "API", "commissions::querybatchesalt error", "The limit is not numeric");

	if ((orderby != "id") && 
		(orderby != "systemid") && 
		(orderby != "startdate") && 
		(orderby != "enddate") && 
		(orderby != "receipts") && 
		(orderby != "commissions") && 
		(orderby != "bonuses") && 
		(orderby != "pools") && 
		(orderby != "disabled") &&
		(orderby != "createdat") && 
		(orderby != "updatedat"))
		return SetError(409, "API", "commissions::querybatchesalt error", "The orderby variable needs to be either id, systemid, startdate, enddate, receipts, commissions, bonuses, pools, disabled, createdat or updatedat");

	// Repair columns cause underscore (_) not allowed in HTTP header section //
	if (orderby == "systemid")
		orderby = "system_id";
	else if (orderby == "startdate")
		orderby = "start_date";
	else if (orderby == "enddate")
		orderby = "end_date";
	else if (orderby == "createdat")
		orderby = "created_at";
	else if (orderby == "updatedat")
		orderby = "updated_at";

	// Handle search string //
	string searchsql;
	if (search.length()!=0)
	{
		// Handle dates and timestamps differently //
		if (orderby == "id")
			searchsql = " AND "+orderby+"='"+search+"'";
		else if ((orderby == "created_at") || (orderby == "updated_at"))
			searchsql = " AND "+orderby+"::DATE='"+search+"%'";
		else
			searchsql = " AND "+orderby+"::TEXT ILIKE '"+search+"'";
	}

	// Build SQL ending sorting/limit/offset here //
	string sqlend = searchsql+" ORDER BY "+orderby+" "+orderdir+" OFFSET "+offset+" LIMIT "+limit;

	return m_pDB->QueryBatchesAlt(socket, system_id, searchsql, sqlend);
}

//////////////////////////////////
// Retrieve a users commissions //
//////////////////////////////////
const char *CceCommissions::QueryUser(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::queryuser error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "commissions::queryuser error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	return m_pDB->QueryUserComm(socket, system_id, user_id.c_str());
}

///////////////////////////////////
// Retrieve all user commissions //
///////////////////////////////////
const char *CceCommissions::QueryComm(int socket, int system_id, string batch_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::queryuser error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "commissions::queryuser error", "The batchid is not numeric");
	
	m_Json = m_pDB->QueryBatchComm(socket, system_id, atoi(batch_id.c_str()));
	return m_Json.c_str();
}

///////////////////////////////////////////////////
// Do a full prediction of everything. IN vs OUT //
///////////////////////////////////////////////////
const char *CceCommissions::FullPredict(int socket, string startdate, string enddate)
{
	startdate = FixDate(startdate);
	enddate = FixDate(enddate);

	return SetError(409, "API", "commissions::fullpredict error", "This needs to be completed");

	// Add prediction flag to the following //
	//m_pDB->CronCommMonth(ALTCORE_UNITED_MAIN, "10", mstartdate, enddate); // United Core Type // Rank needs to be achiveved here //
	//m_pDB->CronCommMonth(ALTCORE_UNITED_GAME, "10", mstartdate, enddate); // United Games Type // Rank defined from core //
	//m_pDB->CronCheckMatch(mstartdate.c_str(), enddate.c_str());

	// return tokens purchased vs tokens played //
}

/////////////////////////////////////////
// Do a full calculation of everything //
/////////////////////////////////////////
const char *CceCommissions::FullCalc(int socket, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::fullcalc error", "A database connection needs to be made first");
	if (startdate.size() == 0)
		return SetError(409, "API", "commissions::fullcalc error", "The startdate is empty");
	if (enddate.size() == 0)
		return SetError(409, "API", "commissions::fullcalc error", "The enddate is empty");
	if (is_date(startdate) == false)
		return SetError(409, "API", "commissions::fullcalc error", "The startdate is invalid");
	if (is_date(enddate) == false)
		return SetError(409, "API", "commissions::fullcalc error", "The enddate is invalid");

	startdate = FixDate(startdate);
	enddate = FixDate(enddate);

	m_pDB->CronCommMonth(socket, ALTCORE_UNITED_MAIN, "10", startdate, enddate); // United Core Type // Rank needs to be achiveved here //
	m_pDB->CronCommMonth(socket, ALTCORE_UNITED_GAME, "10", startdate, enddate); // United Games Type // Rank defined from core //
	m_pDB->CronCheckMatch(socket, startdate.c_str(), enddate.c_str());

	return m_pDB->SetJson(200, "");
}

/////////////////////////////////////////////////
// Run Full calc with multi-processor speed up //
/////////////////////////////////////////////////
const char *CceCommissions::FullCalcSpeed(int socket, int proc_count, string startdate, string enddate)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "commissions::fullcalcspeed error", "A database connection needs to be made first");
	if (startdate.size() == 0)
		return SetError(409, "API", "commissions::fullcalcspeed error", "The startdate is empty");
	if (enddate.size() == 0)
		return SetError(409, "API", "commissions::fullcalcspeed error", "The enddate is empty");
	if (is_date(startdate) == false)
		return SetError(409, "API", "commissions::fullcalc error", "The startdate is invalid");
	if (is_date(enddate) == false)
		return SetError(409, "API", "commissions::fullcalc error", "The enddate is invalid");

	startdate = FixDate(startdate);
	enddate = FixDate(enddate);

	// Calc rank and tokens purchased commissions //
	m_pDB->CronCommMonth(socket, ALTCORE_UNITED_MAIN, "10", startdate, enddate); // United Core Type // Rank needs to be achiveved here //
 	
 	// Calc games with multi-thread process //
 	CCommissions comm;
 	if (comm.RunSpawnProc(m_pDB, socket, proc_count, m_pDB->m_pSettings, startdate.c_str(), enddate.c_str()) == false)
 		return SetError(409, "API", "commissions::fullcalcspeed error", "RunSpawnProc returned false");
	
	// Run check match //
	m_pDB->CronCheckMatch(socket, startdate.c_str(), enddate.c_str());

	return m_pDB->SetJson(200, "");
}

/////////////////////////////////////////////
// Allow United to force lowest rank level //
/////////////////////////////////////////////
const char *CceCommissions::SetRankOverride(int socket, int rank)
{
	g_RankOverride = rank;
	return m_pDB->SetJson(200, "");
}