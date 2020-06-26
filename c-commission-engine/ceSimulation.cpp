#include "ceSimulation.h"
#include "Simulations.h"
#include "CommissionEngine.h"
#include <stdlib.h>

extern CDb *g_pDB;
extern CDb *g_pSimDB;

/////////////////
// Constructor //
/////////////////
CceSimulation::CceSimulation()
{

}

///////////////////////////
// Copy or seed the data //
///////////////////////////
const char *CceSimulation::CopySeed(int socket, string simini, int system_id, string copyseedoption, string seed_type, string users_max, 
	string receipts_max, string min_price, string max_price, string start_date, string end_date)
{
	if (g_pDB == NULL)
		return SetError(409, "API", "payout::query error", "A database connection needs to be made first");

	if (is_alphanum(simini) == false)
		return SetError(400, "API", "simulation::copyseed error", "The sim ini needs to be defined");
	if (simini == "live")
		return SetError(400, "API", "simulation::copyseed error", "The sim ini cannot be live");
	if (simini.size() > 64)
		return SetError(400, "API", "simulation::copyseed error", "The sim ini filename needs to be less than 64 chars");
	if (is_number(copyseedoption) == false)
		return SetError(400, "API", "simulation::copyseed error", "The copyseedoption needs to be a number");
	if ((atoi(copyseedoption.c_str()) < 1) || (atoi(copyseedoption.c_str()) > 4))
		return SetError(400, "API", "simulation::copyseed error", "The copyseedoption needs to be 1-4");

	// Seed receipts type //
	if (copyseedoption == "1")
	{
		if (strlen(seed_type.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The seedtype needs to be empty on copyseedoption = 1");
		if (strlen(users_max.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The usersmax needs to be empty on copyseedoption = 1");
		if (is_number(receipts_max) == false)
			return SetError(400, "API", "simulation::copyseed error", "The receiptsmax needs to be a number on copyseedoption = 1");
		if (is_number(min_price) == false)
			return SetError(400, "API", "simulation::copyseed error", "The minprice needs to be a number on copyseedoption = 1");
		if (is_number(max_price) == false)
			return SetError(400, "API", "simulation::copyseed error", "The maxprice needs to be a number on copyseedoption = 1");
		stringstream ss1;
		ss1 << "The receiptmax needs to between 1 and " << g_pDB->m_pSettings->m_MaxReceipts;
		if ((atoi(receipts_max.c_str()) < 1) || (atoi(receipts_max.c_str()) > g_pDB->m_pSettings->m_MaxReceipts))
			return SetError(400, "API", "simulation::copyseed error", ss1.str().c_str());
		if ((atoi(min_price.c_str()) < 1) || (atoi(min_price.c_str()) > 50))
			return SetError(400, "API", "simulation::copyseed error", "The minprice needs to between 1 and 50 on copyseedoption = 1");
		if ((atoi(max_price.c_str()) < 50) || (atoi(max_price.c_str()) > 5000))
			return SetError(400, "API", "simulation::copyseed error", "The maxprice needs to between 50 and 5000 on copyseedoption = 1");
		if (is_date(start_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The startdate is needed for copyseedoption = 1");
		if (strlen(end_date.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The enddate needs to be empty on copyseedoption = 1");
	}
	else if (copyseedoption == "2") // Seed users //
	{
		if (is_number(seed_type) == false)
			return SetError(400, "API", "simulation::copyseed error", "The seedtype needs to be a number on copyseedoption = 2");
		if ((seed_type != "1") && (seed_type != "2"))
			return SetError(400, "API", "simulation::copyseed error", "The seedtype needs to be between 1-2 on copyseedoption = 2");
		if (is_number(users_max) == false)
			return SetError(400, "API", "simulation::copyseed error", "The usersmax needs to be a number on copyseedoption = 2");
		stringstream ss1;
		ss1 << "The usersmax needs to between 1 and " << g_pDB->m_pSettings->m_MaxUsers << " copyseedoption = 2";
		if ((atoi(users_max.c_str()) < 1) || (atoi(users_max.c_str()) > g_pDB->m_pSettings->m_MaxUsers))
			return SetError(400, "API", "simulation::copyseed error", ss1.str().c_str());
		if (strlen(receipts_max.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The receiptsmax needs to be empty on copyseedoption = 2");
		if (atoi(min_price.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The minprice needs to be empty on copyseedoption = 2");
		if (atoi(max_price.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The maxprice needs to be empty on copyseedoption = 2");
		if (is_date(start_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The startdate is needed for copyseedoption = 2");
		if (is_date(end_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The enddate is needed for copyseedoption = 2");
	}
	else if (copyseedoption == "3") // Copy both users and receipts //
	{
		if (strlen(seed_type.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The seedtype needs to be empty on copyseedoption = 3");
		if (strlen(users_max.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The usersmax needs to be empty on copyseedoption = 3");
		if (strlen(receipts_max.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The receiptsmax needs to be empty on copyseedoption = 3");
		if (strlen(min_price.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The minprice needs to be empty on copyseedoption = 3");
		if (strlen(max_price.c_str()) > 0)
			return SetError(400, "API", "simulation::copyseed error", "The maxprice needs to be empty on copyseedoption = 3");
		if (is_date(start_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The startdate is needed for copyseedoption = 3");
		if (is_date(end_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The enddate is needed for copyseedoption = 3");
	}
	else if (copyseedoption == "4") // Seed both users and receipts //
	{
		if (is_number(seed_type) == false)
			return SetError(400, "API", "simulation::copyseed error", "The seedtype needs to be a number on copyseedoption = 4");
		if ((seed_type != "1") && (seed_type != "2"))
			return SetError(400, "API", "simulation::copyseed error", "The seedtype needs to be between 1-2 on copyseedoption = 4");
		if (is_number(users_max) == false)
			return SetError(400, "API", "simulation::copyseed error", "The usersmax needs to be a number on copyseedoption = 4");
			
		int numusersmax = 0;
		if (g_pDB->m_pSettings->m_MaxUsers < 1)
			numusersmax = SIM_USER_MAX;
		else
			numusersmax = g_pDB->m_pSettings->m_MaxUsers;
		stringstream ss1;
		ss1 << "The usersmax needs to between 1 and " << numusersmax << " on copyseedoption = 4";
		if ((atoi(users_max.c_str()) < 1) || (atoi(users_max.c_str()) > numusersmax))
			return SetError(400, "API", "simulation::copyseed error", ss1.str().c_str());
		if (is_number(receipts_max) == false)
			return SetError(400, "API", "simulation::copyseed error", "The receiptsmax needs to be a number on copyseedoption = 4");
		
		int numreceiptsmax = 0;
		if (g_pDB->m_pSettings->m_MaxReceipts < 1)
			numreceiptsmax = SIM_RECEIPT_MAX;
		else
			numreceiptsmax = g_pDB->m_pSettings->m_MaxReceipts;
		stringstream ss2;
		ss2 << "The receiptmax needs to between 1 and " << numreceiptsmax;
		if ((atoi(receipts_max.c_str()) < 1) || (atoi(receipts_max.c_str()) > numreceiptsmax))
			return SetError(400, "API", "simulation::copyseed error", ss2.str().c_str());
		if (is_number(min_price) == false)
			return SetError(400, "API", "simulation::copyseed error", "The minprice needs to be a number on copyseedoption = 4");
		if (is_number(max_price) == false)
			return SetError(400, "API", "simulation::copyseed error", "The maxprice needs to be a number on copyseedoption = 4");
		if ((atoi(min_price.c_str()) < 1) || (atoi(min_price.c_str()) > 50))
			return SetError(400, "API", "simulation::copyseed error", "The minprice needs to between 1 and 50 on copyseedoption = 4");
		if ((atoi(max_price.c_str()) < 50) || (atoi(max_price.c_str()) > 5000))
			return SetError(400, "API", "simulation::copyseed error", "The maxprice needs to between 50 and 5000 on copyseedoption = 4");
		if (is_date(start_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The startdate is needed for copyseedoption = 4");
		if (is_date(end_date) == false)
			return SetError(400, "API", "simulation::copyseed error", "The enddate is needed for copyseedoption = 4");
	}
 
	// Fix the date format //
	start_date = FixDate(start_date);
	end_date = FixDate(end_date);

	// Is the sim ini file valid? //
	stringstream filename;
	filename << INI_PATH << simini << ".ini";
	Debug(DEBUG_INFO, "CceSimulation::CopySeed - ini filename", filename.str().c_str());
	if (FILE *file = fopen(filename.str().c_str(), "r"))
		fclose(file);
	else
	{
	    Debug(DEBUG_ERROR, "CceSimulation::CopySeed - invalid ini filename", filename.str());
	    return SetError(400, "API", "simulation::copyseed error", "Invalid sim");
	}

	// Read in settings //
	CCommissionEngine simcomm;
	simcomm.Startup(simini);

	// Connect to sim database //
	CDb simdb;
	if (simdb.Connect(&simcomm.m_Settings) == false)
	{
		Debug(DEBUG_ERROR, "CceSimulation::CopySeed - Couldn't Connect to sim database");
		return SetError(400, "API", "simulation::copyseed error", "Couldn't Connect to sim database");
	}

	// Run the copy and seed //
	CSimulations sim;
	if (sim.CopySeed(g_pDB, &simdb, socket, system_id, atoi(seed_type.c_str()), atoi(copyseedoption.c_str()), atoi(users_max.c_str()), 
		atoi(receipts_max.c_str()), atoi(min_price.c_str()), atoi(max_price.c_str()), start_date, end_date) == false)
		return SetError(400, "API", "simulation::copyseed error", "There was a problem with copy and seeding");

	return SetJson(200, "");
}

////////////////////////
// Run the simulation //
////////////////////////
const char *CceSimulation::Run(int socket, string simini, int system_id, string start_date, string end_date)
{
	Debug(DEBUG_TRACE, "CceSimulation::Run - TOP");

	if (g_pDB == NULL)
		return SetError(409, "API", "simulation::run error", "A database connection needs to be made first");
	if (is_date(start_date) == false)
		return SetError(400, "API", "simulation::run error", "The startdate is needed for copyseedoption = 4");
	if (is_date(end_date) == false)
		return SetError(400, "API", "simulation::run error", "The enddate is needed for copyseedoption = 4");
	if (simini == "live")
		return SetError(400, "API", "simulation::run error", "The sim ini cannot be live");
	if (simini.size() > 64)
		return SetError(400, "API", "simulation::run error", "The sim ini filename needs to be less than 64 chars");
	if (is_alphanum(simini) == false)
		return SetError(400, "API", "simulation::run error", "The sim ini needs to be defined");

	// Fix the date format //
	start_date = FixDate(start_date);
	end_date = FixDate(end_date);

	// Read in settings //
	CCommissionEngine simcomm;
	simcomm.Startup(simini);

	// Connect to sim database //
	CDb simdb;
	if (simdb.Connect(&simcomm.m_Settings) == false)
	{
		Debug(DEBUG_ERROR, "CceSimulation::CopySeed - Couldn't Connect to sim database");
		return SetError(400, "API", "simulation::copyseed error", "Couldn't Connect to sim database");
	}

	CSimulations sim;
	m_Json = sim.Run(g_pDB, &simdb, socket, system_id, start_date, end_date);
	Debug(DEBUG_TRACE, "CceSimulation::CopySeed - sim.Run() finished");
	return m_Json.c_str();
}