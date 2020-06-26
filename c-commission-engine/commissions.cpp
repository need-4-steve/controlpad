#include "commissions.h"
#include "receipts.h"
#include "Compile.h"
#include "ceBonus.h"
#include "ceSignupBonus.h"
#include "ceRankRuleMissed.h"
#include "ceUser.h"
#include "date.h"
#include "ezReports.h"
#include "ezCopy.h"
#include "ezTok.h"
#include "rankgenbonus.h"

#include <stdio.h>
#include <math.h>
#include <stdlib.h>
#include <sstream>
#include <iomanip> // Precision //

//#include <sys/types.h>

#include <unistd.h> // fork() //
#include <sys/wait.h> // waitpid() //

#include <algorithm> // count(s.begin, s.end, ",") //

int g_RankOverride = 0;
string g_Debug_UserID = "##NO-ID##"; //"##NO-ID##"; //"6739"; //"##NO-ID##"; //"238"; //"246"; // 659558 - missing gen 3 in ce_breakdown_users //

/////////////////
// Constructor //
/////////////////
CCommissions::CCommissions()
{
	m_pDB = NULL;
	m_Generation = 0;
	m_GenLimit = 0;
	m_BatchID = -1;
	m_AltCore = 0;

	m_GrandTotal = 0;
	m_GrandAchvBonus = 0;
	m_GrandBonus = 0;

	m_ReceiptsWholesaleTotal = 0;
	m_ReceiptsRetailTotal = 0;
	m_GrandSignupBonus = 0;

	m_RankMax = 0;
	m_CMRankMax = 0;

	m_RankRuleMissedCount = 0;


	m_RankGenBonusCount = 0;
	m_LegRankCount = 0;
	//m_LegRankGen2Count = 0;
	//m_LegRankGen3Count = 0;

	m_CompressionEnabled = true;

	Reset();
}

/////////////
// Cleanup //
/////////////
CCommissions::~CCommissions()
{
	
}

//////////////////////////////////////////////////////////////////
// Spawn multiple child processes to speed up game calculations //
//////////////////////////////////////////////////////////////////
bool CCommissions::RunSpawnProc(CDb *pDB, int socket, int proc_count, CezSettings *pSettings, const char *start_date, const char *end_date)
{
	std::list <int> ChildLL;

	// Get the first id //
 	std::stringstream ss1;
 	int start_id = pDB->GetFirstDB(socket, ss1 << "SELECT id from ce_systems WHERE id!=1 AND id IN (SELECT DISTINCT system_id FROM ce_receipts WHERE wholesale_date >='" << start_date << "' AND wholesale_date <='" << end_date << "') ORDER BY id LIMIT 1");

 	// Get the last id //
 	std::stringstream ss2;
 	int end_id = pDB->GetFirstDB(socket, ss2 << "SELECT id from ce_systems WHERE id!=1 AND id IN (SELECT DISTINCT system_id FROM ce_receipts WHERE wholesale_date >='" << start_date << "' AND wholesale_date <='" << end_date << "') ORDER BY id DESC LIMIT 1");

 	int sys_count = end_id-start_id;
 	int segments = sys_count/proc_count;

 	// CalcUsed Distributed //
 	int index;
 	for (index=0; index < proc_count; index++)
 	{	
 		int start = start_id+index*segments;
 		int end = start_id+((index+1)*segments)-1;
 		
 		// Overcompensate for the last ending bit //
 		if (index == proc_count-1)
 			end = end_id;

 		int pid = fork();
 		if (pid == 0)
 		{
 			CDb db;
 			db.Connect(pSettings);

 			// Calculation run through system id ranges //
 			CCommissions comm;
 			comm.RunSpeed(&db, socket, 1, start, end, false, start_date, end_date);
 			exit(0);
 		}
 		else if (pid < 0)
 		{
 			// I bet this is where we get our integrity error //
 			Debug(DEBUG_ERROR, "CCommissions::RunSpawnProc - Failed to fork - proc error");
 			Debug(DEBUG_ERROR, "CCommissions::RunSpawnProc - #1 - There will probably be bad commission run database data");
 			exit(1);
 		}
 		else
 		{
 			ChildLL.push_back(pid);
 		}
 	}

 	// Wait for the child processes to finish //
 	std::list<int>::iterator i;
	for (i=ChildLL.begin(); i != ChildLL.end(); ++i) 
	{
		int pid = (*i);
	 	int status;
	 	while (-1 == waitpid(pid, &status, 0));
	 	if (!WIFEXITED(status) || WEXITSTATUS(status) != 0)
	 	{
	 		Debug(DEBUG_ERROR, "CCommissions::RunSpawnProc - Process Failed");
	 		Debug(DEBUG_ERROR, "CCommissions::RunSpawnProc - #2 - There will probably be bad commission run database data");
	 		//ss << "Process " << i << " (pid " << pids[i] << ") failed" << endl;
		    exit(1);
		}
	}

	return true;
}
 
//////////////////////////////////////////////////////////////
// Allow breaking apart of commissions for quick processing //
//////////////////////////////////////////////////////////////
bool CCommissions::RunSpeed(CDb *pDB, int socket, int system_id, int start_sys_id, int end_sys_id, bool pretend, const char *start_date, const char *end_date)
{
	m_pDB = pDB;

	// Build list of systems for Group Used //
	std::list <CSystem> SystemsLL;
	//Debug(DEBUG_DEBUG, "CCommissions::CalcUsed - Before GetSystemsUsed (system_id)", system_id);
	m_pDB->GetSystemsSpeed(socket, system_id, start_sys_id, end_sys_id, &SystemsLL); // Exclude the system_id passed in //

	// Loop through each system and calculate //
	std::list<CSystem>::iterator i;
	for (i=SystemsLL.begin(); i != SystemsLL.end(); ++i) 
	{
		CSystem *pSystem = &(*i);
		CCommissions comm;
		bool onlygrand = true;
		int socket = 0; // -3 because it really doens't naturally occur //
		bool compressionenabled = true;
		comm.Run(pDB, socket, pSystem->m_SystemID, pSystem->m_CommType, pretend, onlygrand, start_date, end_date, "", compressionenabled);		
	}

	return true;
}

//////////////////////////////////
// Handle setting rank override //
//////////////////////////////////
const char *CCommissions::SetRankOverride(int rank)
{
	g_RankOverride = rank;
	return SetJson(200, "");
}

///////////////////////////////////////
// We need a pointer to the database //
///////////////////////////////////////
const char *CCommissions::Run(CDb *pDB, int socket, int system_id, int comm_type, bool pretend, bool onlygrand, const char *start_date, const char *end_date, string affiliate_id, bool compression_enabled)
{
	Debug(DEBUG_DEBUG, "CCommissions::Run - TOP");

	m_pDB = pDB;
	m_SystemID = system_id;
	m_CommType = comm_type;
	m_CompressionEnabled = compression_enabled;

	TimeStart();

	// Show everytime a commission is run //
	std::stringstream ss;
	ss << "CCommissions::Run - Start Commissions - system_id=" << system_id << ", start_date=" << start_date << ", end_date=" << end_date;
	std::string tmpstr = ss.str();
	Debug(DEBUG_MESSAGE, tmpstr.c_str());

	// Allow to pull users and receipts from another system with piggy back id //
	stringstream ssPiggy;
	int piggy_id = m_pDB->GetFirstDB(socket, ssPiggy << "SELECT piggy_id FROM ce_systems WHERE id=" << system_id);
	if (piggy_id < 1) // Just from normal system_id if piggy_id invalid //
		piggy_id = system_id;
	else
	{
		Debug(DEBUG_MESSAGE, "CCommissions::Run - PIGGY BACK COPY");

		// DELETE Users from non-piggy system //
		stringstream ssDelUsers;
		if (m_pDB->ExecDB(true, socket, ssDelUsers << "DELETE FROM ce_users WHERE system_id=" << system_id) == NULL)
			Debug(DEBUG_ERROR, "CCommissionEngine::Run - Problems DELETEing users before copy");

		// DELETE Receipts from non-piggy system //
		stringstream ssDelReceipts;
		if (m_pDB->ExecDB(true, socket, ssDelReceipts << "DELETE FROM ce_receipts WHERE system_id=" << system_id) == NULL)
			Debug(DEBUG_ERROR, "CCommissionEngine::Run - Problems DELETEing users before copy");

		// Copy all Users from piggy system //
		ezCopy copy(m_pDB, m_pDB);
		if (copy.Users(socket, piggy_id, system_id) == false)
			Debug(DEBUG_ERROR, "CCommissionEngine::Run - copy.Users() == false");

		// Copy Receipts from piggy system //
		if (copy.Receipts(socket, piggy_id, system_id) == false) //, const char *startdate, const char *enddate)
			Debug(DEBUG_ERROR, "CCommissionEngine::Run - copy.Receipts() == false");
	
		// Wait for PoolConn to finish INSERTs //
		pDB->m_ConnPool.WaitForThreads(socket);
	}

	// Grab the PSQ limit //
	stringstream ssPSQLimit;
	int psq_limit = m_pDB->GetFirstDB(socket, ssPSQLimit << "SELECT psq_limit FROM ce_systems WHERE id=" << system_id);

	// Grab the signupbonus value for later //
	string signupbonus = pDB->GetSignupBonus(socket, system_id);

	//if (pretend == false)
	//	m_pDB->Begin();

	// Handle checkpoints //
	//int checkpoint = m_pDB->GetCP(system_id, -1);
	int checkpoint = m_pDB->GetCP(socket, system_id, start_date, end_date);
	if (checkpoint == CHECKPOINT_FINISHED)
	{
		Debug(DEBUG_WARN, "CCommissions::Run - Commission already run for system_id", system_id);
		//Debug(DEBUG_WARN, "CCommissions::Run - batch_id", system_id);
		return SetError(409, "API", "run commissions error", "system_id has already been processed");
	}

	//if (checkpoint != -1)
	//{
	//	m_BatchID = m_pDB->GetCPBatchID(socket, system_id);
	//	Debug(DEBUG_TRACE, "CCommissions::Run - GetCPBatchID() - m_BatchID", m_BatchID);
	//	PreCleanup(socket, system_id, m_BatchID, checkpoint);
	//}

	// Start the checkpoint processing //
	checkpoint = m_pDB->AddCP(pretend, socket, system_id, -1, checkpoint, CHECKPOINT_STARTED);
	m_pDB->GetUsers(socket, system_id, true, m_UsersMap, UPLINE_PARENT_ID, start_date, end_date);
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, -1, checkpoint, CHECKPOINT_GETUSERS1);
	// Veryify a user count //

	unsigned long usercount = m_UsersMap.size();
	Debug(DEBUG_WARN, "CCommissions::Run - usercount", usercount);
	if (usercount == 0)
	{
		Debug(DEBUG_WARN, "CCommissions::Run - The usercount is 0. Cannot continue");
		return SetError(409, "API", "run commissions error", "The usercount is 0. Cannot continue");
	}

	checkpoint = m_pDB->EditCP(pretend, socket, system_id, -1, checkpoint, CHECKPOINT_USERSCOUNT);
	// Verify a receipt count //
	int receiptcount = pDB->GetReceiptCount(socket, system_id, start_date, end_date);
	Debug(DEBUG_WARN, "CCommissions::Run - receiptcount", receiptcount);
	if (receiptcount == 0)
	{
		Debug(DEBUG_WARN, "CCommissions::Run - The receiptcount is 0. Cannot continue");
		return SetError(409, "API", "run commissions error", "The receiptcount is 0. Cannot continue");
	}
		
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, -1, checkpoint, CHECKPOINT_RECEIPTCOUNT);
	// Test for this serious error. This would crash the program from running //
	if (IsRecursionLoop(system_id) == true)
	{
		Debug(DEBUG_ERROR, "CCommissions::Run - There was a sponsor_id/parent_id recurrsion loop found");
		return SetError(409, "API", "run commissions error", "There was a sponsor_id/parent_id recurrsion loop found");
	}
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, -1, checkpoint, CHECKPOINT_RECURSIONLOOP);
	
	ReadInDB(socket, m_SystemID, system_id, m_BatchID, start_date, end_date); // Read the database in again //
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, -1, checkpoint, CHECKPOINT_READINDB1);

	// Read in ExternalQualify data //
	CceExtQualify extqualify(m_pDB, "");
	extqualify.ReadInData(socket, m_SystemID, start_date, end_date, &m_ExtQualifyList);

	////////////////////////////////////
	// Do inital generation of values //
	////////////////////////////////////
	if (m_BatchID == -1)
	{
		m_BatchID = m_pDB->AddBatch(pretend, socket, m_SystemID, start_date, end_date);
		Debug(DEBUG_TRACE, "CCommissions::Run - AddBatch() - m_BatchID", m_BatchID);
	}
	m_AltCore = pDB->GetAltCore(socket, system_id);
	CalcSalesAndStats(socket, system_id, start_date, end_date); // Calculate the Personal and Group Sales //
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_CALCSALES1);
	BuildPSQ(socket, psq_limit, end_date);

	// Handle Group_Used (TOKENS PLAYED) inside of ALTCORE_UNITED_MAIN //
	if (m_AltCore == ALTCORE_UNITED_MAIN)
	{
		Debug(DEBUG_DEBUG, "CCommissions::Run - Before CalcUsed (system_id)", system_id);
		CalcUsed(socket, system_id, pDB, start_date, end_date); // Calc data for all GroupUsed (TOKENS PLAYED) systems //
		checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_CALCUSED);
	}

	///////////////////////////
	// Handle infinity bonus //
	/////////////////////////// 
	int infinitycap = pDB->GetInfinityCap(socket, system_id); // Needed to check limits //
	if (infinitycap > 0)
	{
		bool pretendinfinity = true; // Do a pretend calc to make sure we don't go over infinity bonus //
		BuildPSQ(socket, psq_limit, end_date);
		DoRankRules(pretendinfinity, socket, m_SystemID, m_BatchID, &m_RulesRankLL, "ce_rankrules"); // Give credit to moving up a rank //
		checkpoint = m_pDB->EditCP(pretendinfinity, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_RANKRULES1);

		CalcCommBreakdown(pretendinfinity, socket, system_id, start_date, end_date); // Do actual rules calculatons //
		checkpoint = m_pDB->EditCP(pretendinfinity, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_BREAKDOWN1);
		FinishCommissions(pretendinfinity, socket);
		checkpoint = m_pDB->EditCP(pretendinfinity, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_COMMISSIONS1);

		//////////////////////////////////
		// Check for infinity cap limit //
		//////////////////////////////////
		double infpaylimit = m_ReceiptsWholesaleTotal*infinitycap*0.01;
		double ratio = infpaylimit / m_InfinityTotal;
		std::list<CRulesComm>::iterator i;
		for (i=m_RulesCommLL.begin(); i != m_RulesCommLL.end(); ++i)
		{
			if ((*i).m_InfinityTotal > 0)
			{
				double paylimit = ratio * (*i).m_InfinityTotal;
				double percent = paylimit/infpaylimit;

				(*i).m_PrePercent = (*i).m_Percent;
				(*i).m_Percent = percent;
			}
		}
		checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_INFINITYCAP1);

		Reset(); // Clear/reset everything //
		m_pDB->GetUsers(socket, system_id, true, m_UsersMap, UPLINE_PARENT_ID, start_date, end_date); 
		checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_GETUSERS2);
		ReadInDB(socket, m_SystemID, system_id, m_BatchID, start_date, end_date); // Read the database in again //
		checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_READINDB2);
		CalcSalesAndStats(socket, system_id, start_date, end_date); // Calculate the Personal and Group Sales //
		checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_CALCSALES2);
		BuildPSQ(socket, psq_limit, end_date);
	}

	// This also sets compressed AdvisorID //
	Debug(DEBUG_INFO, "CCommissions::Run - Before UpdateNewAdvisor and Advisor Calc");
	UpdateNewAdvisor(pretend, socket, m_SystemID);
	CceUser tmpuser(m_pDB, "");
	tmpuser.RebuildAdvisorUpline(m_pDB, socket, m_SystemID, m_UsersMap);
 	
 	// Handle rebuild of commlegs with advisor upline //
 	RebuildCommLegsWAdvisor(comm_type);

	//////////////////////////////////
	// Do real database entries now //
	//////////////////////////////////
	//if ((checkpoint == CHECKPOINT_CALCSALES1) || (checkpoint == CHECKPOINT_CALCUSED) || (checkpoint == CHECKPOINT_CALCSALES2))
	//{
		DoRankRules(pretend, socket, m_SystemID, m_BatchID, &m_RulesRankLL, "ce_rankrules"); // Give credit to moving up a rank //
		m_pDB->Flush(pretend, socket, m_SystemID, m_BatchID); // Do final writes to the database //
		CceRankRuleMissed rankrulemissed(m_pDB, "");
		if (rankrulemissed.BulkFinish(socket, m_StrRankRuleMissedSQL) == false)
			return SetError(409, "API", "run commissions error", "BulkFinish is false");
	//}
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_RANKRULES2);

	////////////////////////////////
	// Apply the basic Comm Rules //
	////////////////////////////////
	Debug(DEBUG_INFO, "CCommissions::Run - Before ApplyBasicCommRules");
	if (ApplyBasicCommRules(pretend, socket, m_SystemID, m_BatchID, end_date) == false)
		return SetError(409, "API", "run commissions error", "ApplyBasicCommRules is false");

	//if (checkpoint == CHECKPOINT_RANKRULES2)
	//{
		Debug(DEBUG_INFO, "CCommissions::Run - Before CalcCommBreakdown");
		CalcCommBreakdown(pretend, socket, m_SystemID, start_date, end_date); // Do actual rules calculatons //
		m_pDB->Flush(pretend, socket, m_SystemID, m_BatchID); // Do final writes to the database //
	//}
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_BREAKDOWN2);

	// Handle extended breakdown //
	Debug(DEBUG_INFO, "CCommissions::Run - Before WaitForThreads #1 and DoExtBreakdown");
	pDB->m_ConnPool.WaitForThreads(socket);
	if (pretend == false)
		pDB->DoExtBreakdown(socket, m_SystemID, m_BatchID);

	//if (checkpoint == CHECKPOINT_BREAKDOWN2)
	//{
		Debug(DEBUG_INFO, "CCommissions::Run - Before FinishCommissions");
		FinishCommissions(pretend, socket);
		m_pDB->Flush(pretend, socket, m_SystemID, m_BatchID); // Do final writes to the database //
	//}

	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_COMMISSIONS2);

	//if (checkpoint == CHECKPOINT_COMMISSIONS2)
	//{
		Debug(DEBUG_INFO, "CCommissions::Run - Before FinishUserStats");
		FinishUserStats(pretend, socket, m_SystemID, m_BatchID, end_date);
		Debug(DEBUG_INFO, "CCommissions::Run - (real) Before Flush #1");
		m_pDB->Flush(pretend, socket, m_SystemID, m_BatchID); // Do final writes to the database //
	//}
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_USERSTATS);

	//if (checkpoint == CHECKPOINT_USERSTATS)
	//{
		pDB->FlushGrand(pretend, socket, system_id, m_BatchID); // Finally flush grand totals to database //
		Debug(DEBUG_INFO, "CCommissions::Run - Before BuildLedger");
		BuildLedger(pretend, socket, m_SystemID, m_BatchID, atof(signupbonus.c_str()), start_date, end_date);
		Debug(DEBUG_INFO, "CCommissions::Run - (real) Before Flush #2");
		m_pDB->Flush(pretend, socket, m_SystemID, m_BatchID); // Do final writes to the database //
	//}
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_SIGNUPBONUS);

	//if ((checkpoint == CHECKPOINT_SIGNUPBONUS) && (signupbonus != "0")) // signup bonus must be present //
	if (atoi(signupbonus.c_str()) != 0)
	{
		CceSignupBonus ceSignupBonus(pDB, "");

		// Loop through all users //
		std::map <std::string, CUser>::iterator j;
		for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
		{
			CUser *puser = &m_UsersMap[j->first];
			
			// Only reseller and affiliates //
			if ((puser->m_UserType == 1) || (puser->m_UserType == 3))
			{
				CDateCompare date_window(start_date, end_date);
				if (date_window.IsBetween(puser->m_SignupDate.c_str()) == true)
				{
					if (puser->m_SponsorID != "0")
						m_GrandSignupBonus += atof(signupbonus.c_str());

					if (ceSignupBonus.AddBulk(pretend, socket, system_id, puser->m_SponsorID, puser->m_UserID, m_BatchID, signupbonus) == false)
						Debug(DEBUG_INFO, "CCommissions::Run - ceSignupBonus.AddBulk Error. Probably user_id is 0");
				}
			}
		}

		if (ceSignupBonus.FinishBulk(pretend, socket) == false)
			Debug(DEBUG_ERROR, "CCommissions::Run - ceSignupBonus.FinishBulk Error");
	}

	// We need to wait for all threads to finish //
	Debug(DEBUG_INFO, "CCommissions::Run - Before WaitForThreads #2");
	pDB->m_ConnPool.WaitForThreads(socket);

	// Handle Fast Start Bonuses //
	DoFastStartBonuses(pretend, socket, system_id, start_date, end_date);

	// Handle Grand Totals //
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_LEDGER);
	m_GrandBonus = m_pDB->GetBatchBonus(socket, m_SystemID, start_date, end_date);
	BuildGrandTotals(pretend, socket, system_id, end_date); // Build the grand totals amounts from memory //

	// We need to wait for all threads to finish //
	Debug(DEBUG_INFO, "CCommissions::Run - Before WaitForThreads #3");
	pDB->m_ConnPool.WaitForThreads(socket);

	// Do CheckMatch // This pulls in ledger records //
	Debug(DEBUG_INFO, "CCommissions::Run - Before DoCheckMatch");
	DoCheckMatch(pDB, socket, pretend, system_id, m_BatchID, start_date, end_date);
	m_pDB->Flush(pretend, socket, m_SystemID, m_BatchID); // Do final writes to the database //

	// Run ledger totals //
	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_LEDGER_TOTALS);
	Debug(DEBUG_INFO, "CCommissions::Run - Before RebuildLedgerTotals");
	pDB->RebuildLedgerTotals(pretend, socket, system_id);
	pDB->RebuildReceiptTotals(pretend, socket, system_id);

#ifdef COMPILE_UNITED
	if ((pretend == false) && (system_id == 1)) // For United only compile reports for system_id=1 //
#else
	if (pretend == false)
#endif
	{
		pDB->m_ConnPool.WaitForThreads(socket);
		
		Debug(DEBUG_INFO, "CCommissions::Run - Before CezReports reports");
		CezReports reports(pDB, "");
		if (reports.CalcAll(socket, system_id, m_BatchID) == false)
			Debug(DEBUG_DEBUG, "CCommissions::Run - CalcAll == false");
	}

	//if (pretend == false)
	//	m_pDB->Commit();

	// Compile data for the ce_pre legrankgen table //
	//if ((pretend == false) && (m_pDB->m_pSettings->m_LegRankGen == true))
	if (pretend == false)
	{
		Debug(DEBUG_INFO, "CCommissions::Run - Before CalcPreLegRankGen");
		CezReports reports(pDB, "");
		reports.CalcPreLegRankGen(socket, system_id, m_BatchID, m_RankMax, m_UsersMap);
	}

	checkpoint = m_pDB->EditCP(pretend, socket, system_id, m_BatchID, checkpoint, CHECKPOINT_FINISHED);

	TimeEnd();

	// Flush the achvlookup //
	m_pDB->m_AchvLookup.clear();

	// Delete all from checkpoint // United (Ben) Request //
	m_pDB->ExecDB(true, socket, "DELETE FROM ce_checkpoint");

	if (onlygrand == true)
		return BuildGrandTotalJSON(); // Only return grandtotal //

	return BuildJSON(affiliate_id); // Return JSON payout of each user //
}

/////////////////////////////////////////////////////////////////////
// Cleanup the database from a previous attempt to run commissions //
/////////////////////////////////////////////////////////////////////
bool CCommissions::PreCleanup(int socket, int system_id, int batch_id, int checkpoint)
{
	// Cleanup database from previous attempt //
	switch (checkpoint)
	{
		case CHECKPOINT_CALCSALES1:
		case CHECKPOINT_CALCUSED:
		case CHECKPOINT_CALCSALES2:
		{
			m_pDB->CleanupRanks(socket, system_id, batch_id);
		}
		case CHECKPOINT_RANKRULES2:
		{
			m_pDB->CleanupBreakdown(socket, system_id, batch_id);
		}
		case CHECKPOINT_BREAKDOWN2:
		{
			m_pDB->CleanupCommissions(socket, system_id, batch_id);
		}
		case CHECKPOINT_COMMISSIONS2:
		{
			m_pDB->CleanupUserstats(socket, system_id, batch_id);
		}
		case CHECKPOINT_USERSTATS:
		{
			// Does this get tricky because of tokens purchase, tokens played, check match? //

			m_pDB->CleanupLedger(socket, system_id, batch_id);
		}
	}

	return true;
}

/////////////////////////////////////
// Test for recursion loop problem //
// ------------------------------- //
// This is where people try to be  //
// each others sponsors, or even   //
// further downline loops 		   //
/////////////////////////////////////
bool CCommissions::IsRecursionLoop(int system_id)
{
	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //
		m_Generation = 1;
		if (RecursionLadder(puser) == true)
			return true;
	}

	return false;
}

/////////////////////////////////////////
// Go up the ladder looking for errors //
/////////////////////////////////////////
bool CCommissions::RecursionLadder(CUser *puser)
{
	if (puser == NULL)
		return false;

	// Allows us to see the user_id's that are causing problems //
	//Debug(DEBUG_ERROR, "CCommissions::RecursionLadder - puser->m_UserID", puser->m_UserID.c_str());

	m_Generation++;
	if (m_Generation > GENERATION_MAX)
	{
		Debug(DEBUG_ERROR, "CCommissions::RecursionLadder - **Danger** > GENERATION_MAX! m_SystemID", m_SystemID);
		return true;
	}
		
	return RecursionLadder(puser->m_pSponsor); // Climb up the sponsor tree //
}

/////////////////////
// Run used totals //
/////////////////////
bool CCommissions::RunUsed(int socket, int system_id, CDb *pDB, const char *start_date, const char *end_date)
{
	m_pDB = pDB;

	Debug(DEBUG_DEBUG, "CCommissions::RunUsed - Before GetUsers - (system_id)", system_id);
	m_pDB->GetUsers(socket, system_id, true, m_UsersMap, UPLINE_PARENT_ID, start_date, end_date);
	Debug(DEBUG_DEBUG, "CCommissions::RunUsed - Before GetRankRules - (system_id)", system_id);
	m_RankMax = m_pDB->GetRankRules(socket, system_id, &m_RulesRankLL, "ce_rankrules");
	Debug(DEBUG_DEBUG, "CCommissions::RunUsed - Before GetCommission Rules - (system_id)", system_id);
	m_pDB->GetCommRules(socket, system_id, &m_RulesCommLL);
	Debug(DEBUG_DEBUG, "CCommissions::RunUsed - Before GetBasicCommRules - (system_id)", system_id);
	m_pDB->GetBasicCommRules(socket, system_id, &m_RulesBasicCommLL);
	Debug(DEBUG_DEBUG, "CCommissions::RunUsed - Before GetReceipts - (system_id)", system_id);
	CReceiptTotal receipts;
	m_pDB->GetReceipts(socket, -1, m_UsersMap, m_ReceiptsLL, start_date, end_date, &receipts); // -1 for tokens used //
	m_pDB->GetRankBonusRules(socket, system_id, &m_RulesRankBonusLL);

	CalcSalesAndStats(socket, system_id, start_date, end_date); // Calculate the Personal and Group Sales //

	return true;
}

//////////////////////////////////////////////
// Calculated the GroupUsed (tokens played) //
//////////////////////////////////////////////
bool CCommissions::CalcUsed(int socket, int system_id, CDb *pDB, const char *start_date, const char *end_date)
{
	m_pDB = pDB;

	CCommissions comm;
	Debug(DEBUG_DEBUG, "CCommissions::CalcUsed - Before RunUsed (1)");
	comm.RunUsed(socket, 1, pDB, start_date, end_date);

	// Transfer AffiliateSales for Tokens Played (group_used) volume //
	std::map <std::string, CUser>::iterator j;
	for (j=comm.m_UsersMap.begin(); j != comm.m_UsersMap.end(); ++j) 
	{
		CUser *puser = &comm.m_UsersMap[j->first];
		m_UsersMap[j->first].m_GroupUsed = puser->m_GroupWholesaleSales;
	}

	return true;
}

/*
//////////////////////////////////////////////
// Calculated the GroupUsed (tokens played) //
//////////////////////////////////////////////
bool CCommissions::CalcUsedSpeed(int system_id, int start_sys_id, int end_sys_id, CDb *pDB, const char *start_date, const char *end_date)
{
	m_pDB = pDB;

	// Build list of systems for Group Used //
	std::list <int> m_SystemsUsedLL;
	//Debug(DEBUG_DEBUG, "CCommissions::CalcUsed - Before GetSystemsUsed (system_id)", system_id);
	m_pDB->GetSystemsSpeed(system_id, start_sys_id, end_sys_id, &m_SystemsUsedLL); // Exclude the system_id passed in //

	// Loop through each system and calculate //
	std::list<int>::iterator i;
	for (i=m_SystemsUsedLL.begin(); i != m_SystemsUsedLL.end(); ++i) 
	{
		int tmp_sys_id = (*i);

		CCommissions comm;
		//Debug(DEBUG_DEBUG, "CCommissions::CalcUsed - Before RunUsed (tmp_sys_id)", tmp_sys_id);
		m_pDB->GetUsers(tmp_sys_id, comm.m_UsersMap, UPLINE_PARENT_ID, start_date, end_date);
		comm.RunUsed(tmp_sys_id, pDB, start_date, end_date);

		// Loop through users and distribute values //
		std::map <std::string, CUser>::iterator j;
		for (j=comm.m_UsersMap.begin(); j != comm.m_UsersMap.end(); ++j) 
		{
			CUser *puser1 = &m_UsersMap[j->first];
			CUser *puser2 = &comm.m_UsersMap[j->first]; // This seems to be more accurate //
			//puser1->m_GroupUsed += puser2->m_GroupSales;

			if (puser2->m_GroupSales > 0)
			{
				//std::stringstream ss1;
				//ss1 << "tmp_sys_id=" << tmp_sys_id << ", user_id=" << j->first.c_str() << ", puser2->m_GroupSales=" << puser2->m_GroupSales;
				//std::string tmpstr = ss1.str();
				//Debug(DEBUG_ERROR, "CCommissions::CalcUsedSpeed - ", tmpstr.c_str());
				
				pDB->AddGroupUsed(tmp_sys_id, j->first.c_str(), puser2->m_GroupSales, start_date, end_date);
			}
		}
	}

	return true;
}
*/
/*
//////////////////////////////////////////////
// Calculated the GroupUsed (tokens played) //
//////////////////////////////////////////////
bool CCommissions::CalcUsed(int system_id, CDb *pDB, const char *start_date, const char *end_date)
{
	m_pDB = pDB;

	// Different way of calculation //
	std::map <std::string, CUser> UsersMap;
	m_pDB->GetGroupUsed(system_id, UsersMap);

	// Loop through each user //
	std::map <std::string, CUser>::iterator i;
	for (i=UsersMap.begin(); i != UsersMap.end(); ++i) 
	{
		CUser *puser1 = &m_UsersMap[i->first];
		CUser *puser2 = &UsersMap[i->first]; // This seems to be more accurate //
		
		puser1->m_GroupUsed += puser2->m_GroupUsed;
	}
}
*/

/////////////////////////////////////
// Run commissions on a given pool //
/////////////////////////////////////
const char *CCommissions::RunPool(CDb *pDB, int socket, int system_id, int batch_id, int poolpot_id, int qualify_type, int amount, const char *start_date, const char *end_date)
{
	m_pDB = pDB;

	if (strlen(start_date) < 8)
		return SetError(400, "API", "CCommissions::RunPool error", "start_date < 8");
	if (strlen(end_date) < 8)
		return SetError(400, "API", "CCommissions::RunPool error", "end_date < 8");
	if (m_pDB->GetPoolRules(socket, system_id, poolpot_id, &m_RulesPoolLL) == false)
		return SetError(400, "API", "CCommissions::RunPool error", "GetPoolRules == false");
	if (m_pDB->GetUsers(socket, system_id, false, m_UsersMap, UPLINE_PARENT_ID, start_date, end_date) == false) // Grab user ranks based on batch_id //
		return SetError(400, "API", "CCommissions::RunPool error", "GetUsers == false");
	
	CReceiptTotal receipts;
	m_pDB->GetReceipts(socket, system_id, m_UsersMap, m_ReceiptsLL, start_date, end_date, &receipts); // Grab the receipts in dated winow //
		//return Debug(DEBUG_ERROR, "CCommissions::RunPool - GetReceipts == false");

	// Build personal and group sales numbers //
	CalcSalesAndStats(socket, system_id, start_date, end_date); // GroupSales and PersonalPurchase //

	// Maybe we don't user ranks? Waiting for Brian Picket to call me back //
	// Assign rank from poolrules qualification //

	// See who qualifies //
	double pooltotal = 0;
	std::list<CRulesPool>::iterator i;
	for (i=m_RulesPoolLL.begin(); i != m_RulesPoolLL.end(); ++i) 
	{
		std::map <std::string, CUser>::iterator j;
		for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
		{
			CUser *puser = &m_UsersMap[j->first];

			//if ((puser->m_Rank >= (*i).m_StartRank) && (puser->m_Rank <= (*i).m_EndRank)) // Only apply if it's the next rank up //
			// Do rule comparison //

			if (QualifyCompareRules(puser, qualify_type, (*i).m_QualifyThreshold, 0) == true)
			{
				if ((qualify_type == QLFY_PERSONAL_SALES) && (puser->m_LvL1PersonalSales >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_LvL1PersonalSales;
				else if ((qualify_type == QLFY_GROUP_SALES) && (puser->m_GroupWholesaleSales >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_GroupWholesaleSales;
				else if ((qualify_type == QLFY_SIGNUP_COUNT) && (puser->m_SignupCount >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_SignupCount;
				else if ((qualify_type == QLFY_RANK) && (puser->m_Rank >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_Rank;
				else if ((qualify_type == QLFY_CUSTOMER_COUNT) && (puser->m_CustomerCount >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_CustomerCount;
				else if ((qualify_type == QLFY_AFFILIATE_COUNT) && (puser->m_AffiliateCount >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_AffiliateCount;
				else if ((qualify_type == QLFY_CUSTAFFIL_COUNT) && (puser->m_DacCount >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_DacCount;
				else if ((qualify_type == QLFY_CUSTLVLONE_COUNT) && (puser->m_LvL1CustomerCount >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_LvL1CustomerCount;
				else if ((qualify_type == QLFY_AFFILVLONE_COUNT) && (puser->m_LvL1AffiliateCount >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_LvL1AffiliateCount;
				else if ((qualify_type == QLFY_GROUP_USED) && (puser->m_GroupUsed >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_GroupUsed;
				else if ((qualify_type == QLFY_GROUP_SALESUSED) && (puser->m_GroupWholesaleSales+puser->m_GroupUsed >= (*i).m_QualifyThreshold))
					pooltotal += puser->m_GroupWholesaleSales+puser->m_GroupUsed;

				puser->m_PoolQualify = true;
			}
		}
	}

	// Calc percentage for each //
	std::map <std::string, CUser>::iterator q;
	for (q=m_UsersMap.begin(); q != m_UsersMap.end(); ++q) 
	{
		CUser *puser = &m_UsersMap[q->first];
		if (puser->m_PoolQualify == true)
		{
			double percent = ActualUserValue(puser, qualify_type)/pooltotal;
			double pool_payout = amount*percent;

			// Write payout to the database //
			m_pDB->AddPoolPayout(socket, system_id, batch_id, poolpot_id, puser->m_UserID.c_str(), pool_payout);
		}
	}

	// Update the poolpot with the receipt total //
	m_pDB->UpdatePoolPots(socket, poolpot_id, receipts.m_WholesaleTotal);

	// Update the Batch values //
	bool pretend = false;
	m_pDB->UpdateBatch(pretend, socket, system_id, batch_id, receipts.m_WholesaleTotal, receipts.m_RetailTotal, 0, 0, 0, amount);

	// Build json //
	std::stringstream ss3;
	ss3 << ",\"poolpot\":[";
	ss3 << "{\"id\":\"" << poolpot_id << "\",\"receiptswholesale\":\"" << receipts.m_WholesaleTotal << "\",\"payout\":\"" << amount << "\"},";
	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1);
    json += "]";
	return SetJson(200, json.c_str());
}

/////////////////////////////////////////
// Dump the database into linked lists //
/////////////////////////////////////////
bool CCommissions::ReadInDB(int socket, int system_id, int piggy_id, int batch_id, const char *start_date, const char *end_date)
{
	if (m_pDB == NULL)
		return Debug(DEBUG_ERROR, "CCommissions::ReadInDB m_pDB == NULL");

	Debug(DEBUG_DEBUG, "CCommissions::ReadInDB - Before GetRankRules - (system_id)", system_id);
	m_RankMax = m_pDB->GetRankRules(socket, system_id, &m_RulesRankLL, "ce_rankrules");
	Debug(DEBUG_DEBUG, "CCommissions::ReadInDB - Before GetCMRankRules - (system_id)", system_id);
	m_CMRankMax = m_pDB->GetRankRules(socket, system_id, &m_CMRulesRankLL, "ce_cmrankrules");
	Debug(DEBUG_DEBUG, "CCommissions::ReadInDB - Before GetCommission Rules - (system_id)", system_id);
	m_pDB->GetCommRules(socket, system_id, &m_RulesCommLL);
	Debug(DEBUG_DEBUG, "CCommissions::RunUsed - Before GetBasicCommRules - (system_id)", system_id);
	m_pDB->GetBasicCommRules(socket, system_id, &m_RulesBasicCommLL);
	Debug(DEBUG_DEBUG, "CCommissions::ReadInDB - Before GetReceipts - (system_id)", system_id);
	CReceiptTotal receipts;
	m_pDB->GetReceipts(socket, system_id, m_UsersMap, m_ReceiptsLL, start_date, end_date, &receipts); // Apply receipts to usermap //
	m_pDB->GetRankBonusRules(socket, system_id, &m_RulesRankBonusLL);
	m_pDB->GetFastStartRules(socket, system_id, &m_FastStartRulesLL);

// Only United has rank carry over from system=1 //
// All others have to re-qualify each pay period // 
#ifdef COMPILE_UNITED
	Debug(DEBUG_DEBUG, "CCommissions::ReadInDB - Before GetRanks - (system_id)", system_id);
	m_pDB->GetRanks(socket, system_id, batch_id, m_UsersMap); // Read in the rank from previous calc system=1 //
#endif

	if ((m_RulesRankLL.size() == 0) && (m_RulesBasicCommLL.size() == 0))
	{
#ifdef COMPILE_UNITED
		if (system_id == 1) // Only apply to system = 1 //
		{
			Debug(DEBUG_ERROR, "CCommissions::Run - There are no rank rules nor basic-comm rules");
			return SetError(409, "API", "run commissions error", "There are no rank rules nor basic-comm rules");
		}
#else
		// Applies to all systems //
		Debug(DEBUG_ERROR, "CCommissions::Run - There are no rank rules nor basic-comm rules");
		return SetError(409, "API", "run commissions error", "There are no rank rules nor basic-comm rules");
#endif	
	}
	if ((m_RulesCommLL.size() == 0) && (m_RulesBasicCommLL.size() == 0))
	{
		Debug(DEBUG_ERROR, "CCommissions::Run - There are no basic-comm rules nor commission rules. system_id", system_id);
		return SetError(409, "API", "run commissions error", "There are no basic-comm rules nor commission rules");
	}

	return true;
}

//////////////////////
// Clear all values //
//////////////////////
void CCommissions::Reset()
{
	m_Generation = 0;
	m_GenLimit = 0;
	//m_BatchID = -1;
	m_ReceiptsWholesaleTotal = 0;

	/*
	// Cycle through all the users resetting values //
	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //

		puser->m_Rank = 0; // They need to requalify every pay period //
		puser->m_GroupSales = 0;
		puser->m_PoolQualify = 0;

		// The ultimate final result //
		puser->m_Commission = 0;
		puser->m_AchvBonus = 0;
		puser->m_PoolPayout = 0;

		puser->m_CustomerSales = 0;

		puser->m_AffiliateCount = 0; // Only Customer
		puser->m_LvL1CustomerCount = 0; // Customer Level 1 //
		puser->m_CustomerCount = 0; // Customer and Affiliate 

		puser->m_Commission = 0;
        puser->m_AchvBonus = 0;
	}
	*/
	// It's 2 am. Adam wants this done tomorrow. No leway. F^&* it. Read the database in again //

	m_UsersMap.clear(); // Map makes user lookup way easy and quick //
	m_RulesCommLL.clear();
	m_RulesRankLL.clear();
	m_RulesPoolLL.clear(); 
}

///////////////////////////////////////////////
// Calculate Personal and Group sales totals //
///////////////////////////////////////////////
bool CCommissions::CalcSalesAndStats(int socket, int system_id, const char *start_date, const char *end_date)
{
	Debug(DEBUG_DEBUG, "CCommissions::CalcSalesAndStats Begin");

	//if ((m_CommType == COMMRULE_BREAKAWAY) || (m_CommType == COMMRULE_HYBRIDUNI))
	//	m_GenLimit = m_pDB->GetGenLimit(m_SystemID); // These are set in the commission rules //
	//else
		m_GenLimit = -1; // BINARY goes unlimited //

	// Get the systems->teamgenmax //
	stringstream ssTeamGen;
	int teamgenmax = m_pDB->GetFirstDB(socket, ssTeamGen << "SELECT teamgenmax FROM ce_systems WHERE id=" << system_id);

	Debug(DEBUG_INFO, "CCommissions::CalcSalesAndStats - Do math intensive calculations");

	// Keep a flag list of all unique users //
	map <string, string> uniqueusers;
 
	////////////////////////////////////////////
	// Build personal and group sales numbers //
	////////////////////////////////////////////
	std::list<CReceipt>::iterator r;
	for (r=m_ReceiptsLL.begin(); r != m_ReceiptsLL.end(); ++r) 
	{
		//Debug(DEBUG_INFO, "CCommissions::CalcSalesAndStats - Build numbers. (*r).m_UserID", (*r).m_UserID.c_str());

		CUser *puser = &m_UsersMap[(*r).m_UserID];

		if (uniqueusers[(*r).m_UserID] != "T")
		{	
			uniqueusers[(*r).m_UserID] = "T";

			//Debug(DEBUG_INFO, "CCommissions::CalcSalesAndStats - Test 0");

			puser->IncrUniqueUsersReceipts(); // Increment up the users upline //
		}

		CDateCompare date_window(start_date, end_date);;
		if (date_window.IsBetween((*r).m_WholesaleDate.c_str()) == true)
		{
			m_ReceiptsWholesaleTotal = m_ReceiptsWholesaleTotal + (*r).m_WholesalePrice;

			if ((*r).m_WholesalePrice > 0)
			{
				puser->m_PVItemCountWholesale++; // Track how many items totaled up // Piphany // Wholesale //
			}
			else if ((*r).m_WholesalePrice < 0)
				puser->m_PVItemCountWholesale--;
		}
		if (date_window.IsBetween((*r).m_RetailDate.c_str()) == true)
		{
			// Debug Receipts do a given user on Retail Sales - Maverick //
			//if ((*r).m_UserID == "349")
			//{
			//	stringstream ssTest;
			//	ssTest << "startdate = " << start_date << ", enddate = " << end_date << ", ID = " << (*r).m_ID << ", Retail Date = " << (*r).m_RetailDate << ", Retail Price = " << (*r).m_RetailPrice;
			//	Debug(DEBUG_INFO, "CCommissions::CalcSalesAndStats", ssTest.str().c_str());
			//}

			m_ReceiptsRetailTotal = m_ReceiptsRetailTotal + (*r).m_RetailPrice;
			if ((*r).m_RetailPrice > 0)
			{
				puser->m_PVItemCountRetail++; // Track how many items totaled up // Piphany // Retail //

				if (puser->m_UserID == "223")
				{
					//Debug(DEBUG_ERROR, "Retail - metadata_onadd", (*r).m_MetaDataOnAdd.c_str());
				}
			}
			else if ((*r).m_RetailPrice < 0)
				puser->m_PVItemCountRetail--;
		}
	
		if (((*r).m_UserType == USERTYPE_CUSTOMER) && ((*r).m_UserID != puser->m_UserID))
		{
			puser->m_CustomerWholesaleSales += (*r).m_WholesalePrice;
			puser->m_CustomerRetailSales += (*r).m_RetailPrice;
		}

		if (((*r).m_UserType == USERTYPE_AFFILIATE) && ((*r).m_UserID != puser->m_UserID))
		{
			puser->m_AffiliateWholesaleSales += (*r).m_WholesalePrice;
			puser->m_AffiliateRetailSales += (*r).m_RetailPrice;
		}

		if (((*r).m_UserType == USERTYPE_RESELLER) && ((*r).m_UserID != puser->m_UserID))
		{
			puser->m_ResellerWholesaleSales += (*r).m_WholesalePrice;
			puser->m_ResellerRetailSales += (*r).m_RetailPrice;
		}

		Debug(DEBUG_TRACE, "CCommissions::CalcSalesAndStats - Build numbers. MID loop");

		if ((*r).m_UserID == puser->m_UserID)
		{
			if (puser->m_UserID == g_Debug_UserID)
			{
				static double sumamount = 0;
				sumamount += (*r).m_WholesalePrice;  

				stringstream ss;
				ss << "id=" << (*r).m_ID << ", receiptid=" << (*r).m_ReceiptID << ", wholesale=" << (*r).m_WholesalePrice << ", sumamount=" << sumamount;
				Debug(DEBUG_WARN, ss.str().c_str());
			}

			// Dated window is needed for accurate PV //
			//CDateCompare date_window(start_date, end_date);
			if (date_window.IsBetween((*r).m_WholesaleDate.c_str()) == true)
			{
				puser->m_LvL1MyWholesaleSales += (*r).m_WholesalePrice;

				if (puser->m_UserID == g_Debug_UserID)
				{
					static int count = 0;
					count++;

					stringstream sstest;
					sstest << "### count=" << count << ", userid=" << (*r).m_UserID << ", id=" << (*r).m_ID << ", WholesalePrice=" << (*r).m_WholesalePrice << ", total=" << puser->m_LvL1MyWholesaleSales;
					Debug(DEBUG_INFO, sstest.str().c_str());
				}
			}

			if (date_window.IsBetween((*r).m_RetailDate.c_str()) == true)
			{
				puser->m_LvL1MyRetailSales = puser->m_LvL1MyRetailSales + (*r).m_RetailPrice;

				//if (puser->m_UserID == "223")
				//{
					stringstream ssTest;
					ssTest << "UserID=" << puser->m_UserID << ", (*r).m_ID=" << (*r).m_ID << ", puser->m_LvL1MyRetailSales=" << puser->m_LvL1MyRetailSales;
					//Debug(DEBUG_ERROR, "CCommissions::CalcSalesAndStats", ssTest.str().c_str());
				//}
			}
		}

		Debug(DEBUG_TRACE, "CCommissions::CalcSalesAndStats - Before ReceiptUpLadder");

		// Group Sales //
		m_Generation = 1;
		ReceiptUpLadder(puser->m_pSponsor, &(*r), teamgenmax, start_date, end_date);
	}

	/////////////////////////////////
	// Build the statcount numbers //
	/////////////////////////////////
	std::map <std::string, std::string> UserBuildMap;
	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		Debug(DEBUG_TRACE, "CCommissions::CalcSalesAndStats - In statcount j->first", j->first);

		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //
		puser->BuildLVL1();
		puser->BuildAllLevels(UserBuildMap);
		puser->BuildCustomerLevels();
		puser->BuildEVItemCount(puser->m_PVItemCountWholesale, puser->m_PVItemCountRetail);

		//puser->m_EVItemCountRetail = puser->m_EVItemCountRetail - puser->m_PVItemCountRetail;

		//Debug(DEBUG_ERROR, "CCommissions::BuildStatCount - puser->m_UserID=", puser->m_UserID.c_str());

		//std::stringstream ssCalc;
		//ssCalc << "CALC - user_id=" << j->first << ", GroupSales=" << puser->m_GroupSales;
		//std::string tmpstr = ssCalc.str();
		//Debug(DEBUG_ERROR, tmpstr.c_str());

		//Debug(DEBUG_WARN, "puser->m_LvL1PersonalSales", puser->m_LvL1PersonalSales);
	}

	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //

		puser->m_EVItemCountRetail = puser->m_EVItemCountRetail - puser->m_PVItemCountRetail;
		puser->m_EVItemCountWholesale = puser->m_EVItemCountWholesale - puser->m_PVItemCountWholesale;
	}

	return true;
}

///////////////////////////////////////
// Apply basic comm rules percentage //
///////////////////////////////////////
bool CCommissions::ApplyBasicCommRules(bool pretend, int socket, int system_id, int batch_id, const char *end_date)
{
	Debug(DEBUG_TRACE, "CCommissions::ApplyBasicCommRules - TOP");
	//Debug(DEBUG_TRACE, "CCommissions::ApplyBasicCommRules - m_RulesBasicCommLL.size()", m_RulesBasicCommLL.size());
	//Debug(DEBUG_TRACE, "CCommissions::ApplyBasicCommRules - m_UsersMap.size()", m_UsersMap.size());

	CceBonus bonus(m_pDB, "");

	// Loop through all commission rules //
	list<CRulesBasicComm>::iterator c;
	for (c=m_RulesBasicCommLL.begin(); c != m_RulesBasicCommLL.end(); ++c) 
	{
		// Add into ce_bonus table //
		if ((*c).m_PVOverride == true)
		{
			//Debug(DEBUG_TRACE, "CCommissions::ApplyBasicCommRules - (*c).m_PVOverride == true");

			std::map <std::string, CUser>::iterator q;
			for (q=m_UsersMap.begin(); q != m_UsersMap.end(); ++q) 
			{
				CUser *puser = &m_UsersMap[q->first];

				double commission = puser->m_LvL1MyWholesaleSales * (*c).m_Percent * 0.01;
				if ((*c).m_Modulus != 0)
				{
					double remain = fmod(commission, (*c).m_Modulus);
					commission = commission - remain;
				}

				if (puser->m_UserID == g_Debug_UserID)
				{
					Debug(DEBUG_ERROR, "CCommissions::ApplyBasicCommRules - OVOverride - commission", commission);
				}

				if (commission != 0) // No Blank Zero Entries. Still allow negative for coupons, charge backs, etc... //
				{ 
					// Handle Pay Limit Cap //
					if (((*c).m_PayLimit != 0) && (commission > (*c).m_PayLimit))
					{
						commission = (*c).m_PayLimit;
					}

					// Add as a bonus //
					if (bonus.BulkAdd(socket, system_id, batch_id, puser->m_UserID, commission, end_date) == false)
						Debug(DEBUG_ERROR, "CCommissions::ApplyBasicCommRules - Problems with bonus.BulkAdd");
				}
			}
		} 
		else // Do normal receipt ladder payout //
		{
			//Debug(DEBUG_TRACE, "CCommissions::ApplyBasicCommRules - (*c).m_PVOverride == false");

			// Loop through all receipts //
			list<CReceipt>::iterator r;
			for (r=m_ReceiptsLL.begin(); r != m_ReceiptsLL.end(); ++r) 
			{
				CUser *puser = &m_UsersMap[(*r).m_UserID];

				// Check pre-defined parameters //
				if (((*c).m_InvType == (*r).m_InvType) &&
					((((*c).m_Event == EVENT_WHOLESALE) && ((*r).m_EventWholesale == true)) ||
					(((*c).m_Event == EVENT_RETAIL) && ((*r).m_EventRetail == true)))
				   )
				{
					if (((*c).m_QualifyType == 0) || 
						(QualifyCompareRules(puser, (*c).m_QualifyType, (*c).m_StartThreshold, (*c).m_EndThreshold) == true)
					   )
					{
						double commission = 0;
						if ((*c).m_PayType == PAYOUT_RETAIL) // Affiliate on Corporate //
							commission = (*r).m_RetailPrice * (*c).m_Percent * 0.01; // commission = 25% on retail //
						else if ((*c).m_PayType == PAYOUT_WHOLESALE) // Affiliate on Corporate //
							commission = (*r).m_WholesalePrice * (*c).m_Percent * 0.01; // commission = 60% // Wholsale price 
						
						if ((*c).m_Modulus != 0)
						{
							double remain = fmod(commission, (*c).m_Modulus);
							commission = commission - remain;
						}

						// Modulus can cause a commission to be zero //
						if (commission != 0) // No Blank Zero Entries. Still allow negative for coupons, charge backs, etc... //
						{ 
							// Handle Pay Limit Cap //
							if (((*c).m_PayLimit != 0) && (commission > (*c).m_PayLimit))
							{
								commission = (*c).m_PayLimit;
							}

							m_Generation = 0;
							BasicReceiptUpLadder(socket, puser, &(*c), &(*r), commission);
						}

						//if (puser->m_UserID == g_Debug_UserID)
						//{
						//	stringstream ss;
						//	ss << "CCommissions::ApplyBasicCommRules - User_ID=" << puser->m_UserID << ", commission=" << commission;
						//	Debug(DEBUG_ERROR, ss.str().c_str());
						//}
					}
				}
			}
		}
	}

	if (bonus.BulkFinish(socket) == false)
		Debug(DEBUG_TRACE, "CCommissions::ApplyBasicCommRules - BulkFinish == false");

	return true;
}

////////////////////////////////////////////////////
// Go up the receipt ladder with basic commission //
////////////////////////////////////////////////////
bool CCommissions::BasicReceiptUpLadder(int socket, CUser *puser, CRulesBasicComm *prule, CReceipt *preceipt, double commission)
{
	//Debug(DEBUG_TRACE, "CCommissions::BasicReceiptUpLadder - TOP");

	if (puser == NULL)
		return false;
	if (prule == NULL)
		return false;
	if (preceipt == NULL)
		return false;
	if (puser->m_pSponsor == NULL)
		return false;

//	if ((puser->m_UserID == g_Debug_UserID) && (preceipt->m_ReceiptID == 4837) && (prule->m_Generation == 0))
//	{
//		stringstream ss;
//		ss << "CCommissions::BasicReceiptUpLadder - m_Generation=" << m_Generation << ", prule->m_Generation=" << prule->m_Generation << ", prule->m_Rank=" << prule->m_Rank << ", puser->m_Rank=" << puser->m_Rank;
//		Debug(DEBUG_ERROR, ss.str().c_str());
//	}

	if ((m_Generation == prule->m_Generation) && ((prule->m_Rank == 0) || ((prule->m_Rank == puser->m_Rank) && (puser->m_Rank != 0))))
	{	
		double dollar = 0; // Basic Comm rules don't support dollar yet //

		Debug(DEBUG_TRACE, "CCommissions::BasicReceiptUpLadder - m_Generation == prule->m_Generation");	
		puser->m_Commission += commission;
		m_pDB->AddReceiptBreakdown(socket, m_SystemID, m_BatchID, preceipt->m_ID, preceipt->m_ReceiptID, puser->m_UserID.c_str(), commission, prule->m_ID, m_Generation, prule->m_Percent, false, COMMTYPE_BASIC, preceipt->m_MetaDataOnAdd, preceipt->m_InvType, dollar);
		return true;
	}
	//else if ((puser->m_UserID == g_Debug_UserID))
	//{
	//	stringstream ss;
	//	ss << "CCommissions::BasicReceiptUpLadder - m_Generation=" << m_Generation << ", prule->m_Generation=" << prule->m_Generation << ", prule->m_Rank=" << prule->m_Rank << ", puser->m_Rank=" << puser->m_Rank;
	//	Debug(DEBUG_ERROR, ss.str().c_str());
	//}

	m_Generation++;

	return BasicReceiptUpLadder(socket, puser->m_pSponsor, prule, preceipt, commission);
}

////////////////////////////////////////////
// Test to see if someone moved up a rank //
////////////////////////////////////////////
bool CCommissions::DoRankRules(bool pretend, int socket, int system_id, int batch_id, list <CRulesRank> *pRulesRank, string tablename)
{ 
	Debug(DEBUG_DEBUG, "CCommissions::DoRankRules - Begin - system_id", system_id);
	Debug(DEBUG_INFO, "CCommissions::DoRankRules - g_Debug_UserID", g_Debug_UserID);

	int rulestype = 0;
	int maxrank = 0;
	if (tablename == "ce_rankrules")
	{
		rulestype = DORANK_STANDARD;
		maxrank = m_RankMax;
	}
	else if (tablename == "ce_cmrankrules")
	{
		rulestype = DORANK_CHECKMATCH;
		maxrank = m_CMRankMax;
	}
	else
		return Debug(DEBUG_ERROR, "CCommissions::DoRankRules - table needs to be either ce_rankrules or ce_cmrankrules");

#ifdef COMPILE_UNITED
	if (system_id != 1)
	{
		m_pDB->GetRanks(socket, system_id, batch_id, m_UsersMap); // Read in the rank from previous calc system=1 //
		return true;
	}
#endif

	// Rank rules not needed for game cause MAIN_CORE already calculated globally //
	if (m_AltCore == ALTCORE_UNITED_GAME)
		return false;

	//////////////////////////////////////
	// Set all default rule passed=true //
	//////////////////////////////////////
	map <string, CUser>::iterator q;
	for (q=m_UsersMap.begin(); q != m_UsersMap.end(); ++q) 
	{
		CUser *puser = &m_UsersMap[q->first];

		// Set the RankRuleGroup default to true=passed //
		puser->m_RuleGroupPassed = true;

		int toplegdac = puser->FindTopLegDAC();
		if (toplegdac > 0)
			m_UsersMap[puser->m_UserID].m_TopLegDacCount = toplegdac;

		// Make sure rank 0 is added just in case NO rank achieved. Needed for Chalkatour //
		if (pretend == false)
			m_pDB->AddRank(socket, system_id, m_BatchID, puser, 0, false, 0, 0); // Store in the database //

		////////////////////////////////////
		// Do inital ranksum setting of 0 //
		////////////////////////////////////
		int index;
		for (index=1; index <= maxrank; index++)
		{
			if (puser->m_UserID.size() != 0)
			{
				stringstream ss;
				ss << puser->m_UserID << "#" << index;
				m_MapRankLvl1Sum[ss.str()] = 0;
				//m_MapRankLegSum[ss.str()] = 0;
			}
		}
	}

	//Debug(DEBUG_ERROR, "pRulesRank->size()", pRulesRank->size());

	//////////////////////////////////////////
	// Loop all rules, applied to each user //
	//////////////////////////////////////////
	list<CRulesRank>::iterator i;
	for (i=pRulesRank->begin(); i != pRulesRank->end(); ++i) 
	{
		//int m_Rank; // What rank level does this apply to? //
		//int m_QualifyType; // PERSONAL_SALES, GROUP_SALES, SIGNUP_COUNT //
		//double m_QualifyThreshold; // Either dollar amount or Number of signups //

		//Debug(DEBUG_WARN, "CCommissions::DoRankRules - (*i).m_RuleID", (*i).m_ID);

		map <string, CUser>::iterator j;
		for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
		{
			CUser *puser = &m_UsersMap[j->first];

			if ((puser->m_UserID == g_Debug_UserID) && (puser->m_UserType == 2))
			{
				int usertype = puser->m_UserType;
				stringstream ssTest;
				ssTest << "CCommissions::DoRankRules - puser->m_UserID = " << puser->m_UserID << " is usertype=2 (Customer). No payout will be received";
				Debug(DEBUG_ERROR, ssTest.str().c_str());
			}
			if ((puser->m_UserID == g_Debug_UserID) && (puser->m_Disabled == true))
			{
				int usertype = puser->m_UserType;
				stringstream ssTest;
				ssTest << "CCommissions::DoRankRules - puser->m_UserID = " << puser->m_UserID << " is disabled=true. No payout will be received";
				Debug(DEBUG_ERROR, ssTest.str().c_str());
			}

			// Only resellers and affiliates move ranks and bonuses //
			if (puser->m_Disabled == true)
			{
				// Disabled users don't get achieve rank //
			}
			else if ((puser->m_UserType == 1) || (puser->m_UserType == 3)) // Reseller == 1, Customer == 2, Affiliate == 3 //
			{
				// Make sure Rank Sum is handled differently //
				if (TestRankRules(pretend, socket, system_id, batch_id, &(*i), puser, maxrank) == false)
				{
					puser->m_RuleGroupPassed = false;

					if (puser->m_UserID == g_Debug_UserID)
					{
						stringstream ssTest;
						ssTest << "CCommissions::DoRankRules - (*i).m_ID=" << (*i).m_ID << ", puser->m_RuleGroupPassed = false, (*i).m_RuleGroup=" << (*i).m_RuleGroup;
						Debug(DEBUG_WARN, ssTest.str().c_str());
					}
				}
				else if (puser->m_UserID == g_Debug_UserID)
				{
					stringstream ssTest;
					ssTest << "CCommissions::DoRankRules - (*i).m_ID=" << (*i).m_ID << ", puser->m_RuleGroupPassed = true, (*i).m_RuleGroup=" << (*i).m_RuleGroup;
					Debug(DEBUG_WARN, ssTest.str().c_str());
				}
		
				// Did it qualify to apply rank rules //
				if (((*i).m_EndFlag == true) && (puser->m_RuleGroupPassed == true))
				{
					if (puser->m_UserID == g_Debug_UserID)
					{
						stringstream ssTest;
						ssTest << "CCommissions::DoRankRules - APPLY = true, (*i).m_ID=" << (*i).m_ID << ", (*i).m_RuleGroup=" << (*i).m_RuleGroup;
						Debug(DEBUG_MESSAGE, ssTest.str().c_str());
					}
					
					ApplyRankRules(pretend, socket, &(*i), puser, system_id, rulestype);

					// Apply rank change for all of upline for maxleg rule qualification //
					if (rulestype == DORANK_STANDARD)
						ApplyUpRankLeg(puser, puser->m_UserID, puser->m_Rank);

				}
				else if (puser->m_UserID == g_Debug_UserID)
				{
					Debug(DEBUG_INFO, "CCommissions::DoRankRules - APPLY = false, (*i).m_RuleGroup", (*i).m_RuleGroup);
					if ((*i).m_EndFlag == true)
						Debug(DEBUG_INFO, "CCommissions::DoRankRules - APPLY = false, (*i).m_EndFlag == true");
					if (puser->m_RuleGroupPassed == true)
						Debug(DEBUG_INFO, "CCommissions::DoRankRules - APPLY = false, puser->m_RuleGroupPassed == true");
				}

				// Do we reset to prepare next set of looping? //
				if ((*i).m_EndFlag == true)
				{
					puser->m_RuleGroupPassed = true;

					if ((*i).m_ID == 37)
					{
					//	Debug(DEBUG_MESSAGE, "END");
					}

					if (puser->m_UserID == g_Debug_UserID)
					{
						stringstream ssTest;
						ssTest << "CCommissions::DoRankRules - m_EndFlag == true, (*i).m_ID=" << (*i).m_ID << ", (*i).m_RuleGroup=" << (*i).m_RuleGroup;
						Debug(DEBUG_INFO, ssTest.str().c_str());
					}
				}
			}
		}

		if ((*i).m_EndFlag == true)
		{
			// Increment each rank count //
			CalcRankSum((*i).m_Rank, true);
/*
			// Loop all users=rank and count-- //
			for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
			{
				CUser *puser = &m_UsersMap[j->first];

				if (puser->m_Rank >= (*i).m_Rank)
				{
					UpRankLadderSponsor(puser->m_pSponsor, puser, puser->m_Rank-1, false, puser->m_UserID);
				}
			}
*/
		}
	}

	///////////////////////////
	// Calculate Carrer Rank //
	///////////////////////////
	// Create a list for each rank //
	map <int, CRulesRankTmp> TmpRanks;
	int index;
	for (index=1; index <= maxrank; index++)
	{
		TmpRanks[index].m_Rank = index;
		TmpRanks[index].m_Count = 0;
	}

	// Handle the carrer rank update. United doesn't need this so allow disable //
	if (m_pDB->m_pSettings->m_DisableCarrerRanksSQL == false)
	{
		// Update the ce_users->carrer_rank //
		CDbBulk bulk;
		CConvert convert;
		map <string, CUser>::iterator j;
		for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j)
		{
			CUser *puser = &m_UsersMap[j->first];
			
			std::map <int, CRulesRankTmp>::iterator k;
			for (k=TmpRanks.begin(); k != TmpRanks.end(); ++k)
			{
				CRulesRankTmp *pRankTmp = &k->second;

				if (puser->m_CarrerRank == pRankTmp->m_Rank)
				{
					string condition = "user_id='"+puser->m_UserID+"'";
					pRankTmp->m_Count = bulk.BulkUpdate(m_pDB, socket, "ce_users", "carrer_rank", convert.IntToStr(pRankTmp->m_Rank), condition, &pRankTmp->m_SQL, pRankTmp->m_Count);
				}
			}
		}

		std::map <int, CRulesRankTmp>::iterator k;
		for (k=TmpRanks.begin(); k != TmpRanks.end(); ++k)
		{
			CRulesRankTmp *pRankTmp = &k->second;

			if (bulk.BulkFinish(m_pDB, socket, &pRankTmp->m_SQL) == false)
				Debug(DEBUG_ERROR, "CCommissions::DoRankRules - BulkFinish == false");
		}
	}

	return true;
}

////////////////////////////////////
// Do qualify compare in one spot //
////////////////////////////////////
bool CCommissions::QualifyCompareRules(CUser *puser, int qualifytype, int startthreshold, int endthreshold)
{
	if (puser == NULL)
		return Debug(DEBUG_ERROR, "CCommissions::QualifyCompareRules - puser == NULL");

	if (endthreshold <= 0)
		endthreshold = 2000000000; // Roughly 2 billion largest int4 will go //

	double teamandpersonalwhole = puser->m_TeamWholesaleSales+puser->m_LvL1MyWholesaleSales;
	double customerandmywhole = puser->m_MyCustomerWholesaleSales+puser->m_LvL1MyWholesaleSales;

	if (((qualifytype == QLFY_PERSONAL_SALES) && (puser->m_LvL1PersonalSales >= startthreshold) && (puser->m_LvL1PersonalSales < endthreshold)) ||
//		((qualifytype == QLFY_GROUP_SALES) && (puser->m_GroupWholesaleSales >= startthreshold) && (puser->m_GroupWholesaleSales < endthreshold)) ||
		((qualifytype == QLFY_GROUP_SALES) && (puser->m_GroupWholesaleSales+puser->m_LvL1MyWholesaleSales >= startthreshold) && (puser->m_GroupWholesaleSales+puser->m_LvL1MyWholesaleSales < endthreshold)) ||
		((qualifytype == QLFY_SIGNUP_COUNT) && (puser->m_SignupCount >= startthreshold) && (puser->m_SignupCount < endthreshold)) ||
		((qualifytype == QLFY_RANK) && (puser->m_Rank == startthreshold)) || 
		((qualifytype == QLFY_CUSTOMER_COUNT) && (puser->m_CustomerCount >= startthreshold) && (puser->m_CustomerCount < endthreshold)) || 
		((qualifytype == QLFY_AFFILIATE_COUNT) && (puser->m_AffiliateCount >= startthreshold) && (puser->m_AffiliateCount < endthreshold)) ||
		((qualifytype == QLFY_CUSTAFFIL_COUNT) && (puser->m_DacCount >= startthreshold) && (puser->m_DacCount < endthreshold)) ||
		((qualifytype == QLFY_CUSTLVLONE_COUNT) && (puser->m_LvL1CustomerCount >= startthreshold) && (puser->m_LvL1CustomerCount < endthreshold)) ||
		((qualifytype == QLFY_AFFILVLONE_COUNT) && (puser->m_LvL1AffiliateCount >= startthreshold) && (puser->m_LvL1AffiliateCount < endthreshold)) ||
		((qualifytype == QLFY_GROUP_USED) && (puser->m_GroupUsed >= startthreshold) && (puser->m_GroupUsed < endthreshold)) ||
		((qualifytype == QLFY_GROUP_SALESUSED) && (puser->m_GroupWholesaleSales+puser->m_GroupUsed >= startthreshold) && (puser->m_GroupWholesaleSales+puser->m_GroupUsed < endthreshold)) ||
		((qualifytype == QLFY_MYWHOLESALE) && (puser->m_LvL1MyWholesaleSales >= startthreshold) && (puser->m_LvL1MyWholesaleSales < endthreshold)) ||
		((qualifytype == QLFY_MYRETAIL) && (puser->m_LvL1MyRetailSales >= startthreshold) && (puser->m_LvL1MyRetailSales < endthreshold)) ||
		((qualifytype == QLFY_TEAMVOLUMEWHOLESALE) && (puser->m_TeamWholesaleSales >= startthreshold) && (puser->m_TeamWholesaleSales < endthreshold)) ||
		((qualifytype == QLFY_TEAMVOLUMERETAIL) && (puser->m_TeamRetailSales >= startthreshold) && (puser->m_TeamRetailSales < endthreshold)) ||
		((qualifytype == QLFY_RESELLER_COUNT) && (puser->m_ResellerCount >= startthreshold) && (puser->m_ResellerCount < endthreshold)) ||
		((qualifytype == QLFY_RESELLER_LVL1_COUNT) && (puser->m_LvL1ResellerCount >= startthreshold) && (puser->m_LvL1ResellerCount < endthreshold)) ||
		((qualifytype == QLFY_TEAMANDMYWHOLESALE) && (teamandpersonalwhole >= startthreshold) && (teamandpersonalwhole < endthreshold)) ||
		((qualifytype == QLFY_CUSTOMERANDMYWHOLESALE) && (customerandmywhole >= startthreshold) && (customerandmywhole < endthreshold)) ||
		((qualifytype == QLFY_ITEMCOUNTWHOLESALE_PV) && (puser->m_PVItemCountWholesale >= startthreshold) && (puser->m_PVItemCountWholesale < endthreshold)) ||
		((qualifytype == QLFY_ITEMCOUNTRETAIL_PV) && (puser->m_PVItemCountRetail >= startthreshold) && (puser->m_PVItemCountRetail < endthreshold)) ||
	   	((qualifytype == QLFY_UNIQUEUSERSRECEIPTS) && (puser->m_UniqueUsersReceipts >= startthreshold) && (puser->m_UniqueUsersReceipts < endthreshold)) ||
	   	((qualifytype == QLFY_ITEMCOUNTWHOLESALE_EV) && (puser->m_EVItemCountWholesale >= startthreshold) && (puser->m_EVItemCountWholesale < endthreshold)) ||
		((qualifytype == QLFY_ITEMCOUNTRETAIL_EV) && (puser->m_EVItemCountRetail >= startthreshold) && (puser->m_EVItemCountRetail < endthreshold)) ||
		((qualifytype == QLFY_MY_WHOLE_RETAIL) && (puser->m_LvL1MyWholesaleSales+puser->m_LvL1MyRetailSales >= startthreshold) && (puser->m_LvL1MyWholesaleSales+puser->m_LvL1MyRetailSales < endthreshold))
	   )
	{
		return true;
	}

	return false;
}

////////////////////////////////
// Grab the actual user value //
////////////////////////////////
double CCommissions::ActualUserValue(CUser *puser, int qualifytype)
{
	// Prepare for testing and verification //
	double actualuservalue = 0;
	if (qualifytype == QLFY_PERSONAL_SALES)
		actualuservalue = puser->m_LvL1PersonalSales;
	else if (qualifytype == QLFY_GROUP_SALES)
		actualuservalue = puser->m_GroupWholesaleSales;
	else if (qualifytype == QLFY_SIGNUP_COUNT)
		actualuservalue = puser->m_SignupCount;
	else if (qualifytype == QLFY_RANK)
		actualuservalue = puser->m_Rank;
	else if (qualifytype == QLFY_CUSTOMER_COUNT)
		actualuservalue = puser->m_CustomerCount;
	else if (qualifytype == QLFY_AFFILIATE_COUNT)
		actualuservalue = puser->m_AffiliateCount;
	else if (qualifytype == QLFY_CUSTAFFIL_COUNT)
		actualuservalue = puser->m_DacCount;
	else if (qualifytype == QLFY_AFFILVLONE_COUNT)
		actualuservalue = puser->m_LvL1AffiliateCount;
	else if (qualifytype == QLFY_CUSTLVLONE_COUNT)
		actualuservalue = puser->m_LvL1CustomerCount;
	else if (qualifytype == QLFY_GROUP_USED)
		actualuservalue = puser->m_GroupUsed;
	else if (qualifytype == QLFY_GROUP_SALESUSED)
		actualuservalue = puser->m_GroupWholesaleSales+puser->m_GroupUsed;
	else if (qualifytype == QLFY_MYWHOLESALE)
		actualuservalue = puser->m_LvL1MyWholesaleSales;
	else if (qualifytype == QLFY_MYRETAIL)
		actualuservalue = puser->m_LvL1MyRetailSales;
	else if (qualifytype == QLFY_TEAMVOLUMEWHOLESALE)
		actualuservalue = puser->m_TeamWholesaleSales;
	else if (qualifytype == QLFY_TEAMVOLUMERETAIL)
		actualuservalue = puser->m_TeamRetailSales;
	else if (qualifytype == QLFY_RESELLER_COUNT)
		actualuservalue = puser->m_ResellerCount;
	else if (qualifytype == QLFY_RESELLER_LVL1_COUNT)
		actualuservalue = puser->m_LvL1ResellerCount;
	else if (qualifytype == QLFY_TEAMANDMYWHOLESALE)
		actualuservalue = puser->m_TeamWholesaleSales+puser->m_LvL1MyWholesaleSales;
	else if (qualifytype == QLFY_CUSTOMERANDMYWHOLESALE)
		actualuservalue = puser->m_MyCustomerWholesaleSales+puser->m_LvL1MyWholesaleSales;
	else if (qualifytype == QLFY_ITEMCOUNTWHOLESALE_PV)
		actualuservalue = puser->m_PVItemCountWholesale;
	else if (qualifytype == QLFY_ITEMCOUNTRETAIL_PV)
		actualuservalue = puser->m_PVItemCountRetail;
	else if (qualifytype == QLFY_UNIQUEUSERSRECEIPTS)
		actualuservalue = puser->m_UniqueUsersReceipts;
	else if (qualifytype == QLFY_ITEMCOUNTWHOLESALE_EV)
		actualuservalue = puser->m_EVItemCountWholesale;
	else if (qualifytype == QLFY_ITEMCOUNTRETAIL_EV)
		actualuservalue = puser->m_EVItemCountRetail;
	else if (qualifytype == QLFY_MY_WHOLE_RETAIL)
		actualuservalue = puser->m_LvL1MyWholesaleSales+puser->m_LvL1MyRetailSales;

	if (puser->m_UserID == g_Debug_UserID)
		Debug(DEBUG_INFO, "CCommissions::ActualUserValue - actualuservalue", actualuservalue);

	return actualuservalue;
}

////////////////////////////
// Do the rank rules test //
////////////////////////////
bool CCommissions::TestRankRules(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank)
{
	//Debug(DEBUG_DEBUG, "CCommissions::TestRankRules - TOP");

	// Handle Max DAC leg //
	puser->m_DacCount = puser->m_CustomerCount+puser->m_AffiliateCount;
	if (puser->m_TopLegDacCount > pRule->m_MaxDacLeg)
	{
		int diff = puser->m_TopLegDacCount - pRule->m_MaxDacLeg;
		puser->m_DacCount = puser->m_DacCount - diff;
	}

	if (TestPSQ(pretend, socket, system_id, batch_id, pRule, puser, maxrank) == true)
		return true;

	if (TestRankSumLeg(pretend, socket, system_id, batch_id, pRule, puser, maxrank) == true)
		return true;

	if (TestRankLvl1(pretend, socket, system_id, batch_id, pRule, puser, maxrank) == true)
		return true;

	if (TestExtQualify(pretend, socket, pRule, puser) == true)
		return true;

	//////////////////////////////////
	// Do all other rule comparison //
	//////////////////////////////////
	if (QualifyCompareRules(puser, pRule->m_QualifyType, pRule->m_QualifyThreshold, 0) == true)
	{
		//if (puser->m_UserID == g_Debug_UserID)
		//{
		//	stringstream ssTest;
		//	ssTest << "CCommissions::TestRankRules - pRule->m_ID=" << pRule->m_ID << ", pRule->m_QualifyType=" << pRule->m_QualifyType << ", pRule->m_QualifyThreshold=" << pRule->m_QualifyThreshold << ", puser->m_GroupWholesaleSales=" << puser->m_GroupWholesaleSales << ", puser->m_LvL1PersonalSales=" << puser->m_LvL1PersonalSales << " pRule->m_RuleGroup=" << pRule->m_RuleGroup;
		//	Debug(DEBUG_ERROR, ssTest.str().c_str());
		//}

		return true;
	}
	else if ((pRule->m_QualifyType != QLFY_RANKSUMLEG) && 
			 (pRule->m_QualifyType != QLFY_RANKSUMLVL1) &&
			 (pRule->m_QualifyType != QLFY_PSQ)) // Track rank rule missed //
	{
		// This can reduce number of records not needed //
		if (pRule->m_Rank == puser->m_Rank+1)
		{
			if (pretend == false)
			{
				double actualuservalue = ActualUserValue(puser, pRule->m_QualifyType);

				//CceRankRuleMissed rankrulemissed(m_pDB);
				//rankrulemissed.Add(socket, system_id, batch_id, puser->m_UserID, pRule->m_ID, pRule->m_Rank, pRule->m_QualifyType, pRule->m_QualifyThreshold, actualuservalue);
				CceRankRuleMissed rankrulemissed(m_pDB, "");
				m_RankRuleMissedCount = rankrulemissed.BulkAdd(m_RankRuleMissedCount, &m_StrRankRuleMissedSQL, socket, system_id, batch_id, puser->m_UserID, pRule->m_ID, pRule->m_Rank, pRule->m_QualifyType, pRule->m_QualifyThreshold, actualuservalue);
			}
			return false;
		}
	}

	return false;
}

///////////////////////////////////////////
// Handle the RankSumLeg rule comparison //
///////////////////////////////////////////
bool CCommissions::TestPSQ(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank)
{
	if (pRule->m_QualifyType != QLFY_PSQ)
		return false;

	bool logmissed = false;
	int index;
	for (index=1; index <= maxrank; index++)
	{
		if ((pRule->m_QualifyType == QLFY_PSQ) && // The Sum of Rank rule //
			(puser->m_PSQ >= pRule->m_QualifyThreshold)) // Greater than the threshold //
		{
			if (puser->m_UserID == g_Debug_UserID)
			{
				stringstream ssTest;
				ssTest << "CCommissions::TestPSQ - pRule->m_ID=" << pRule->m_ID << ", pRule->m_QualifyType == QLFY_PSQ, puser->m_PSQ=" << puser->m_PSQ;
				Debug(DEBUG_INFO, ssTest.str().c_str());
			}

			return true;
		}
		else if (pRule->m_QualifyType == QLFY_PSQ)
		{
			if ((pretend == false) && (logmissed == false))
			{
				float actualuservalue = puser->m_PSQ;
				CceRankRuleMissed rankrulemissed(m_pDB, "");
				m_RankRuleMissedCount = rankrulemissed.BulkAdd(m_RankRuleMissedCount, &m_StrRankRuleMissedSQL, socket, system_id, batch_id, puser->m_UserID, pRule->m_ID, pRule->m_Rank, pRule->m_QualifyType, pRule->m_QualifyThreshold, actualuservalue);
				logmissed = true;
			}

			if (index == pRule->m_SumRankEnd) // Last of the rule //
				return false;
		}
	}

	return false;
}

///////////////////////////////////////////
// Handle the RankSumLeg rule comparison //
///////////////////////////////////////////
bool CCommissions::TestRankSumLeg(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank)
{
	if (pRule->m_QualifyType != QLFY_RANKSUMLEG)
		return false;

	bool logmissed = false;
	int index;
	for (index=1; index <= maxrank; index++)
	{
		stringstream ss1;
		ss1 << puser->m_UserID << "#" << index;

		//int fullsum = m_UsersMap[puser->m_UserID].AdvisorLLRankCount(index, maxrank, m_UsersMap);
		int fullsum = puser->AdvisorLegRankCount(index, maxrank, m_UsersMap);

		if ((puser->m_UserID == g_Debug_UserID) && 
			((pRule->m_ID == 105)))
		{
			stringstream ssTest;
			ssTest << "CCommissions::TestRankSumLeg - pRule->m_ID=" << pRule->m_ID << ", fullsum=" << fullsum << ", pRule->m_QualifyThreshold=" << pRule->m_QualifyThreshold << ", index=" << index;
			Debug(DEBUG_ERROR, ssTest.str().c_str());
		}

		if ((pRule->m_QualifyType == QLFY_RANKSUMLEG) && // The Sum of Rank rule //
			(index >= pRule->m_SumRankStart) && // Compare to pre-defined rank //
			(index <= pRule->m_SumRankEnd) && // Compare to pre-defined rank //
			//(m_MapRankLegSum[ss1.str()] >= pRule->m_QualifyThreshold)) // Greater than the threshold //
			(fullsum >= pRule->m_QualifyThreshold)) // Greater than the threshold //
		{
			if (puser->m_UserID == g_Debug_UserID)
			{
				stringstream ssTest;
				ssTest << "CCommissions::TestRankSumLeg - return true - pRule->m_ID=" << pRule->m_ID << ", pRule->m_QualifyType == QLFY_RANKSUMLEG, fullsum=" << fullsum;
				Debug(DEBUG_INFO, ssTest.str().c_str());
			}

			return true;
		}
		else if ((pRule->m_QualifyType == QLFY_RANKSUMLEG) &&
				 (index >= pRule->m_SumRankStart) && // Compare to pre-defined rank //
				 (index <= pRule->m_SumRankEnd))
		{
			// This can reduce number of records not needed //
			if (pRule->m_Rank == puser->m_Rank+1)
			{
				if ((pretend == false) && (logmissed == false))
				{
					if (puser->m_UserID == g_Debug_UserID)
					{
						stringstream ssInfo;
						ssInfo << "CCommissions::TestRankSumLeg - pRule->m_ID=" << pRule->m_ID << ", fullsum=" << fullsum;
						Debug(DEBUG_INFO, ssInfo.str().c_str());
					}

					//float actualuservalue = m_MapRankLegSum[ss1.str()];
					//float actualuservalue = puser->AdvisorLLRankCount(index, pRule->m_SumRankEnd, m_UsersMap);
					float actualuservalue = puser->AdvisorLegRankCount(index, pRule->m_SumRankEnd, m_UsersMap);
					CceRankRuleMissed rankrulemissed(m_pDB, "");
					m_RankRuleMissedCount = rankrulemissed.BulkAdd(m_RankRuleMissedCount, &m_StrRankRuleMissedSQL, socket, system_id, batch_id, puser->m_UserID, pRule->m_ID, pRule->m_Rank, pRule->m_QualifyType, pRule->m_QualifyThreshold, actualuservalue);
					logmissed = true;
				}

				if (index == pRule->m_SumRankEnd) // Last of the rule //
					return false;
			
			}
		}
	}

	return false;
}

////////////////////////////////////////////
// Handle the RankSumLvl1 rule comparison //
////////////////////////////////////////////
bool CCommissions::TestRankLvl1(bool pretend, int socket, int system_id, int batch_id, CRulesRank *pRule, CUser *puser, int maxrank)
{
	if (pRule->m_QualifyType != QLFY_RANKSUMLVL1)
		return false;

	int index;
	for (index=1; index <= maxrank; index++)
	{
		int fullsum = puser->AdvisorLvl1RankCount(index, maxrank, m_UsersMap);

		if (puser->m_UserID == g_Debug_UserID)
		{
			stringstream ssInfo;
			ssInfo << "CCommissions::TestRankLvl1 - pRule->m_ID=" << pRule->m_ID << ", fullsum=" << fullsum;
			Debug(DEBUG_INFO, ssInfo.str().c_str());
		}

		if ((pRule->m_QualifyType == QLFY_RANKSUMLVL1) && // The Sum of Rank rule //
			(index >= pRule->m_SumRankStart) && // Compare to pre-defined rank //
			(index <= pRule->m_SumRankEnd) && // Compare to pre-defined rank //
			(fullsum >= pRule->m_QualifyThreshold))
			//(m_MapRankLvl1Sum[ss1.str()] >= pRule->m_QualifyThreshold)) // Greater than the threshold //
		{
			return true;
		}
		else if ((pRule->m_QualifyType == QLFY_RANKSUMLVL1) &&
				 (index >= pRule->m_SumRankStart) && // Compare to pre-defined rank //
				 (index <= pRule->m_SumRankEnd)) // Compare to pre-defined rank //
		{
			// This can reduce number of records not needed //
			if (pRule->m_Rank == puser->m_Rank+1)
			{
				if ((pretend == false) && (index == pRule->m_SumRankEnd))
				{
					stringstream ss1;
					ss1 << puser->m_UserID << "#" << index;

					//stringstream ssInfo;
					//ssInfo << "CCommissions::TestRankLvl1 - pRule->m_ID=" << pRule->m_ID << ", fullsum=" << fullsum;
					//Debug(DEBUG_INFO, ssInfo.str().c_str());

					float actualuservalue = m_MapRankLvl1Sum[ss1.str()];
					CceRankRuleMissed rankrulemissed(m_pDB, "");
					m_RankRuleMissedCount = rankrulemissed.BulkAdd(m_RankRuleMissedCount, &m_StrRankRuleMissedSQL, socket, system_id, batch_id, puser->m_UserID, pRule->m_ID, pRule->m_Rank, pRule->m_QualifyType, pRule->m_QualifyThreshold, actualuservalue);
				}

				if (index == pRule->m_SumRankEnd)
					return false;
			}
		}
	}

	return false;
}

//////////////////////////////////
// Test external qualify values //
//////////////////////////////////
bool CCommissions::TestExtQualify(bool pretend, int socket, CRulesRank *pRule, CUser *puser)
{
	// Make sure we have correct alignment of data before continuing //
	if (pRule->m_QualifyType != QLFY_EXTERNAL_QUALIFY) 
		return false;
	if (pRule->m_Rank != puser->m_Rank+1)
		return false;

	// Cycle through the external qualify list //
	std::list<CExtQualify>::iterator i;
	for (i=m_ExtQualifyList.begin(); i != m_ExtQualifyList.end(); ++i) 
	{		
		if ((puser->m_UserID == (*i).m_UserID) &&
		    (pRule->m_VarID == (*i).m_VarID) &&
			(pRule->m_QualifyThreshold == (*i).m_Value)) // Keep == or >= ? //
		{
			return true;
		}
	}

	return false;
}

//////////////////////////////////////////////
// Apply the rank rules after qualification //
//////////////////////////////////////////////
void CCommissions::ApplyRankRules(bool pretend, int socket, CRulesRank *pRule, CUser *puser, int system_id, int rulestype)
{
	//Debug(DEBUG_TRACE, "CCommissions::ApplyRankRules - TOP");

	// Make sure the user hadn't been paid the achvbonus //
	if (m_pDB->IsAchvBonusPaid(system_id, puser->m_UserID.c_str(), pRule->m_Rank) == false)
	{
		puser->m_AchvBonus += pRule->m_AchvBonus; // Apply the achievement bonus, if there is any //
	}
	
	// Account for breakage //
	if (pRule->m_Breakage == true)
		puser->m_Breakage = true;

	if (rulestype == DORANK_STANDARD)
	{
		if ((pRule->m_Rank > 0) && (pRule->m_Rank > puser->m_Rank))
			m_UsersMap[puser->m_UserID].m_Rank = pRule->m_Rank;

		if (g_RankOverride > 0)
			puser->m_Rank = g_RankOverride;

		if (pretend == false)
		{
			m_pDB->AddRank(socket, system_id, m_BatchID, puser, puser->m_Rank, puser->m_Breakage, pRule->m_AchvBonus, pRule->m_ID); // Store in the database //
		
			// Handle RankBonus // This is different that achievement. This is not just a one time bonus //
			ApplyRankBonus(pretend, socket, system_id, m_BatchID, puser->m_UserID, puser->m_Rank);
		}
	}
	else if (rulestype == DORANK_CHECKMATCH)
	{
		if ((pRule->m_Rank > 0) && (pRule->m_Rank > puser->m_CMRank))
			m_UsersMap[puser->m_UserID].m_CMRank = pRule->m_Rank;
	}
}

//////////////////////////
// Apply the rank bonus //
//////////////////////////
void CCommissions::ApplyRankBonus(bool pretend, int socket, int system_id, int batchid, string userid, int rank)
{
	if (pretend == true)
		return;

	// Loop through all the rank bonus rules //
	std::list<CRulesRankBonus>::iterator i;
	for (i=m_RulesRankBonusLL.begin(); i != m_RulesRankBonusLL.end(); ++i) 
	{
		if ((*i).m_Rank == rank) // What rank level does this apply to? //
		{
			if (m_pDB->AddRankBonus(socket, system_id, batchid, userid, rank, (*i).m_Bonus) == false)
				Debug(DEBUG_ERROR, socket, "CCommissions::ApplyRankBonus - m_pDB->AddRankBonus() == false");
		}
	}
}

///////////////////////////////////
// Apply the new rank up the leg //
///////////////////////////////////
bool CCommissions::ApplyUpRankLeg(CUser *puser, string rankuser, int rank)
{
	if (puser == NULL)
		return false;

	if ((rank > puser->m_TopRankLeg) && // Takes the top //
		(puser->m_UserID != rankuser) && // You can't be your own toprankleg // 
		(puser->m_Rank > 0)) // Must be greator than 0
	{
		puser->m_TopRankLeg = rank;
		puser->m_TopUserLeg = rankuser;
	}

	return ApplyUpRankLeg(puser->m_pSponsor, rankuser, rank);
}

/////////////////////////////////////////////////////////////////////
// Do 2nd run cause some rules based on other downline rank totals //
/////////////////////////////////////////////////////////////////////
bool CCommissions::CalcRankSum(int rank, bool add)
{
	map <string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first];

		if (rank == puser->m_Rank)
		{
			// Handle full leg //
			//if (UpRankLadderSponsor(puser->m_pSponsor, puser, puser->m_Rank, add, puser->m_UserID) == false)
			//	Debug(DEBUG_ERROR, "CCommissions::CalcRankRulesExt - UpRankLadderSponsor == false");

			// Handle lvl1 //
			if (puser->m_pSponsor != NULL)
			{
				stringstream ss;
				//ss << puser->m_pSponsor->m_UserID << "#" << rank;
				ss << puser->m_AdvisorID << "#" << rank;

				stringstream ssPrev;
				//ssPrev << puser->m_pSponsor->m_UserID << "#" << rank-1;
				ssPrev << puser->m_AdvisorID << "#" << rank-1;

				if ((add == true) && (strstr(m_MapRankLvl1Data[ss.str()].c_str(), puser->m_UserID.c_str()) == NULL))
				{
					m_MapRankLvl1Sum[ss.str()]++;

					// Add a comma at the very front //
					if (m_MapRankLvl1Data[ss.str()].size() == 0)
						m_MapRankLvl1Data[ss.str()] = ",";

					if (rank > 1)
					{
						CConvert conv;
						conv.StrReplace(m_MapRankLvl1Data[ssPrev.str()], ","+puser->m_UserID+",", ",");
					}

					m_MapRankLvl1Data[ss.str()] += puser->m_UserID+",";
				}
				else if ((add == false)) // && (rank != 0))
				{
					m_MapRankLvl1Sum[ss.str()]--;

					CConvert conv;
					conv.StrReplace(m_MapRankLvl1Data[ss.str()], puser->m_UserID, "-"+puser->m_UserID);
				}
			}
		}
	}

	return true;
}

///////////////////////////////////////////////////////////
// Find out if the leg userid is already in the leg data //
///////////////////////////////////////////////////////////
bool CCommissions::IsSameLeg(string legdata, string leg_userid)
{
	bool found = false;
	char legdatastr[100000];

	memset(legdatastr, 0, 100000);
	sprintf(legdatastr, "%s", legdata.c_str());

	////////////////////////////////////////////////////////
	// REASON TO BELIEVE THIS FUNCTION ISN'T USED ANYMORE //
	////////////////////////////////////////////////////////

	// Loop through each userid in the legdata //
	char *pch = NULL;
	pch = strtok(legdatastr, ",");
	while (pch != NULL)
	{
		//if ((pparent->m_UserID == "239") && (rank == 6))
		//	printf("%s\n", pch);

		string match_user = pch;
		//string strmatch = " "+m_UsersMap[puserleg->m_UserID].m_UserID+" ";
		string strmatch = " "+leg_userid+" ";
		if (strstr(m_UsersMap[match_user].m_UplineAdvisor.c_str(), strmatch.c_str()) != NULL)
	    	found = true;

	    pch = strtok(NULL, ",");
	}

	return found;
}

///////////////////
// Stat calc PSQ //
///////////////////
void CCommissions::BuildPSQ(int socket, int psq_limit, const char *end_date)
{
	Debug(DEBUG_TRACE, "CCommissions::BuildPSQ - TOP");

	map <string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first];

		//if (puser->m_Rank > 0)
		if (puser->m_LvL1MyWholesaleSales >= psq_limit)
		{
			if ((puser->m_pSponsor != NULL) && (puser->m_Disabled == false))
			{
				CDateCompare comparedate("2018-1-1", end_date); // Handle retro calculations //

				if (comparedate.IsBetween(puser->m_SignupDate.c_str()) == true)
				{
					puser->m_pSponsor->m_PSQ++;
					if (puser->m_pSponsor->m_UserID == g_Debug_UserID)
					{
						stringstream sstest;
						sstest << "CCommissions::BuildPSQ - Sponsor->m_UserID=" << g_Debug_UserID << ", userid=" << puser->m_UserID << ", m_SignupDate=" << puser->m_SignupDate.c_str() << ", end_date=" << end_date;
						Debug(DEBUG_ERROR, sstest.str().c_str());
					}
				}
			}
		}
	}
}

///////////////////////////////
// Calculate the commissions //
///////////////////////////////
bool CCommissions::CalcCommBreakdown(bool pretend, int socket, int system_id, const char *start_date, const char *end_date)
{
	Debug(DEBUG_TRACE, "CCommissions::CalcCommBreakdown - Begin - system_id", system_id);

	///////////////////////////
	// HYBRIDUNI commissions //
	///////////////////////////
	if (m_CommType == COMMRULE_HYBRIDUNI)
	{	
		std::list<CReceipt>::iterator r;	
		for (r=m_ReceiptsLL.begin(); r != m_ReceiptsLL.end(); ++r) 
		{
			//CUser *psponsor = m_UsersMap[(*r).m_UserID].m_pSponsor;
			//m_Generation = 1;
			CUser *puser = &m_UsersMap[(*r).m_UserID];
			m_Generation = 0;

			HybridUniFinal(pretend, socket, puser, puser, &(*r), start_date, end_date);
		}
	}

	///////////////////////////
	// BREAKAWAY commissions //
	///////////////////////////
	if (m_CommType == COMMRULE_BREAKAWAY)
	{		
		std::list<CReceipt>::iterator r;
		for (r=m_ReceiptsLL.begin(); r != m_ReceiptsLL.end(); ++r) 
		{
			//CUser *psponsor = m_UsersMap[(*r).m_UserID].m_pSponsor;
			//m_Generation = 1;
			CUser *puser = &m_UsersMap[(*r).m_UserID];
			m_Generation = 0;

			BreakawayFinal(pretend, socket, puser, &(*r), start_date, end_date);
		}
	}

	////////////////////////
	// BINARY commissions //
	////////////////////////
	if (m_CommType == COMMRULE_BINARY)
	{	
		// Do final INSERT of commissions into database //
		std::map <std::string, CUser>::iterator j;
		for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
		{
			CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //
			BinaryFinal(pretend, socket, puser);
		}
	}

	return true;
}

//////////////////////////////////////////
// Finish adding the commission entries //
//////////////////////////////////////////
bool CCommissions::FinishCommissions(bool pretend, int socket)
{
	////////////////////////////////////////////////////////////////////////////
	// Loop through all users again after commission totals are fully tallied //
	////////////////////////////////////////////////////////////////////////////
	std::map <std::string, CUser>::iterator k;
	for (k=m_UsersMap.begin(); k != m_UsersMap.end(); ++k)
	{
		CUser *puser = &m_UsersMap[k->first]; // This seems to be more accurate //

		// All types add a commission entry // Only affiliates get commissions //
		if ((puser->m_Commission > 0) && (pretend == false) && (puser->m_UserType == 1))
		{	
			m_pDB->AddCommission(socket, m_SystemID, m_BatchID, puser->m_UserID.c_str(), puser->m_Commission);
		}
	}

	return true;
}

/////////////////
// All 3 types //
/////////////////
bool CCommissions::ReceiptUpLadder(CUser *puser, CReceipt *preceipt, int teamgenmax, const char *start_date, const char *end_date)
{
	// Upper limit and failsafe //
	if ((puser == NULL) || (preceipt == NULL))
		return false;

	CDateCompare date_window(start_date, end_date);

	/////////////////
	// Group Sales //
	/////////////////
	if (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true)
		puser->m_GroupWholesaleSales += preceipt->m_WholesalePrice;
	if (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)
		puser->m_GroupRetailSales += preceipt->m_RetailPrice;

	////////////////////
	// Customer Sales //
	////////////////////
	if (preceipt->m_UserType == USERTYPE_CUSTOMER)
	{
		if (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true)
			puser->m_CustomerWholesaleSales += preceipt->m_WholesalePrice;
		if (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)
			puser->m_CustomerRetailSales += preceipt->m_RetailPrice;
	}

	if (preceipt->m_UserType == USERTYPE_RESELLER)
	{
		if (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true)
			puser->m_ResellerWholesaleSales += preceipt->m_WholesalePrice;
		if (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)
			puser->m_ResellerRetailSales += preceipt->m_RetailPrice;
	}

	if (preceipt->m_UserType == USERTYPE_AFFILIATE)
	{
		if (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true)
			puser->m_AffiliateWholesaleSales += preceipt->m_WholesalePrice;
		if (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)
			puser->m_AffiliateRetailSales += preceipt->m_RetailPrice;
	}

	// Team Sales limited to user defined max generation //
	if ((preceipt->m_UserID != puser->m_UserID) && (m_Generation < teamgenmax+1)) // +1 cause personal receipts count as generation //
	{
		if (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true)
			puser->m_TeamWholesaleSales += preceipt->m_WholesalePrice;
		if (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)
			puser->m_TeamRetailSales += preceipt->m_RetailPrice;
	}

	// Stop crediting upline on breakage //
	if (puser->m_Breakage == true)
		return false;

	if (puser->m_Disabled == false) // Compress if disabled == true //
		m_Generation++;
	
	// United League no generation limit on qualification //
	//if ((m_Generation > m_GenLimit) && (m_GenLimit != -1)) // Exit out once we go higher then the max generation //
	//	return false;

	if (m_Generation > GENERATION_MAX)
	{
		Debug(DEBUG_ERROR, "CCommissions::ReceiptUpLadder - **Danger** > GENERATION_MAX! m_Generation", m_Generation);
		Debug(DEBUG_ERROR, "CCommissions::ReceiptUpLadder - puser->m_UserID", puser->m_UserID.c_str());
		Debug(DEBUG_ERROR, "CCommissions::ReceiptUpLadder - preceipt->m_ReceiptID", preceipt->m_ReceiptID);
		return false;
	}

	if (ReceiptUpLadder(puser->m_pSponsor, preceipt, teamgenmax, start_date, end_date) == false)
		return false;

	return true;
}

//////////////////////////////////
// This one allows compressions //
//////////////////////////////////
bool CCommissions::HybridUniFinal(bool pretend, int socket, CUser *preceiptuser, CUser *puser, CReceipt *preceipt, const char *start_date, const char *end_date)
{
	// Failsafe //
	if ((puser == NULL) || (preceipt == NULL))
		return false;

//#ifndef COMPILE_UNITED
//	bool payout = false;
//#endif

	// Loop through each rule //
	std::list<CRulesComm>::iterator i;
	for (i=m_RulesCommLL.begin(); i != m_RulesCommLL.end(); ++i)
	{
		// RANK and GENERATION --- QUALIFY TYPE and THRESHOLD limits //
//#ifdef COMPILE_UNITED
//			if ((puser->m_UserID != preceipt->m_UserID) && // Can't earn commissions on yourself //
//#endif
			int pseudo_rank = puser->m_Rank;

			// Allow a force payout all the way up. United: faststart bonus //
			if ((*i).m_ForcePay == true)
				pseudo_rank = (*i).m_Rank; // Allow an override on the rank for payout //

			if (
				(puser->m_UserID == g_Debug_UserID) && 
			    ((puser->m_UserType == 1) || (puser->m_UserType == 3)) && // 1 = Reseller. 3 = Affiliate. 
				(puser->m_Disabled == false) && // Make sure the user isn't inactive //
				(pseudo_rank == (*i).m_Rank) && // Make sure ranks line up //
				(m_Generation == abs((*i).m_Generation)) && // Make sure generations line up //
				(preceipt->m_InvType == (*i).m_InvType) && // Make sure inventory types line up //
				(preceipt->m_Commissionable == true) && // Make sure it's not like a sales kit //
				//(((preceipt->m_EventWholesale == true) && ((*i).m_Event == EVENT_WHOLESALE)))
			    (((preceipt->m_EventRetail == true) && ((*i).m_Event == EVENT_RETAIL)))
			)
			{
				std::stringstream ss;
				ss << "#1 - Hybrid - preceipt->m_ReceiptID=" << preceipt->m_ReceiptID << ", user_id=" << puser->m_UserID << ", puser->m_Rank=" << puser->m_Rank << ", m_Generation=" << m_Generation << ", preceipt->m_UserID=" << preceipt->m_UserID;
				std::string tmpstr = ss.str();
				//Debug(DEBUG_ERROR, tmpstr.c_str());
			}

			if ( // ControlPad seeding of data is different than United //
				((puser->m_UserType == 1) || (puser->m_UserType == 3)) && // 1 = Reseller. 3 = Affiliate. 
				(puser->m_Disabled == false) && // Make sure the user isn't inactive //
				(pseudo_rank == (*i).m_Rank) && // Make sure ranks line up //
				(m_Generation == abs((*i).m_Generation)) && // Make sure generations line up //
				(preceipt->m_InvType == (*i).m_InvType) && // Make sure inventory types line up //
				(preceipt->m_Commissionable == true) && // Make sure it's not like a sales kit //
				(((preceipt->m_EventWholesale == true) && ((*i).m_Event == EVENT_WHOLESALE)) ||
				 ((preceipt->m_EventRetail == true) && ((*i).m_Event == EVENT_RETAIL)))
			   )
			{
				CDateCompare date_window(start_date, end_date);

				double commission = 0;
				if (((*i).m_PayType == PAYOUT_RETAIL) && (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)) // Affilliate corporate on retail price //
				{
					if ((*i).m_Percent != 0)
						commission += preceipt->m_RetailPrice * (*i).m_Percent * 0.01;
				}
				else if (((*i).m_PayType == PAYOUT_WHOLESALE) && (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true))
				{
					if ((*i).m_Percent != 0)
						commission += preceipt->m_WholesalePrice * (*i).m_Percent * 0.01;
				}

				// Handle a specific dollar payout 
				if ((*i).m_Dollar != 0)
					commission += (*i).m_Dollar;

				// Make sure personally sponsored qualified is true //
				bool qualify = true;
				if (((*i).m_Generation == -1) && (preceiptuser->m_pSponsor->m_UserID != puser->m_UserID))
					qualify = false;

				if ((*i).m_InfinityBonus == true)
				{
					m_InfinityTotal += commission; // For tracking and testing //
					(*i).m_InfinityTotal += commission; // As per commission rule //
				}

				if (qualify == true)
				{
					// Subtotal commissions //
					puser->m_Commission += commission;

					if (pretend == false)
						m_pDB->AddReceiptBreakdown(socket, m_SystemID, m_BatchID, preceipt->m_ID, preceipt->m_ReceiptID, puser->m_UserID.c_str(), commission, (*i).m_ID, m_Generation, (*i).m_Percent, (*i).m_InfinityBonus, COMMTYPE_RANK, preceipt->m_MetaDataOnAdd, preceipt->m_InvType, (*i).m_Dollar);
					
					// Prevent multiple payouts on the same person on forcepay //
					if ((*i).m_ForcePay == true)
						break;
				}

	//#ifndef COMPILE_UNITED
	//			payout = true;
	//#endif
			}
		
	}

	// Compression //
//#ifdef COMPILE_UNITED
	// Allow conversion to player to compress. Or non-subscription payment //
	if (m_CompressionEnabled == true)
	{
		if ((puser->m_UserType == 2) && (m_Generation == 0))
		{
			// United: We saw a problem with user 48733
			// Who had tons of customer, but only 1 affiliate //
			// He had tons of receipts and was only getting paid like $2.50 //
			// The problem was we need to disable compression for the first initial generation //
			// Crazy huh? // 
			m_Generation++;
		}
		if (puser->m_Disabled == true) 
		{
			// Compress //
		}
		else if (puser->m_UserType == 2)
		{
			// Compress //
		}
		else
		{
			m_Generation++;
		}
	}
	else // No Compression //
	{
		m_Generation++;
	}

//#else 
	//if (payout == true) // Only if it hasn't paid out yet //
	//	m_Generation++;
//#endif

	if ((m_Generation > m_GenLimit) && (m_GenLimit != -1)) // Exit out once we go higher then the max generation //
	{
		std::stringstream ssGen;
		ssGen << "CCommissions::HybridUniFinal - m_Generation > m_GenLimit (" << m_Generation << " > " << m_GenLimit << ")";
		std::string strGen = ssGen.str();
		Debug(DEBUG_TRACE, strGen.c_str());
		return false;
	}

	if (m_Generation > GENERATION_MAX)
	{
		Debug(DEBUG_ERROR, "CCommissions::HybridUniFinal - **Danger** > GENERATION_MAX! m_Generation", m_Generation);
		Debug(DEBUG_ERROR, "CCommissions::HybridUniFinal - puser->m_UserID", puser->m_UserID.c_str());
		Debug(DEBUG_ERROR, "CCommissions::HybridUniFinal - preceipt->m_ReceiptID", preceipt->m_ReceiptID);
		return false;
	}

	return HybridUniFinal(pretend, socket, preceiptuser, puser->m_pSponsor, preceipt, start_date, end_date);
}

////////////////////////////////////////
// This one doesn't allow compression //
////////////////////////////////////////
bool CCommissions::BreakawayFinal(bool pretend, int socket, CUser *puser, CReceipt *preceipt, const char *start_date, const char *end_date)
{
	if ((puser == NULL) || (preceipt == NULL))
		return false;

	// Loop through each rule //
	std::list<CRulesComm>::iterator i;
	for (i=m_RulesCommLL.begin(); i != m_RulesCommLL.end(); ++i) 
	{
			// RANK and GENERATION --- QUALIFY TYPE and THRESHOLD limits //
			if ((puser->m_UserID != preceipt->m_UserID) && // Can't earn commissions on yourself //
				(puser->m_UserType == 1) && // 1 = Affiliate. Only affiliates can receive commissions //
				(puser->m_Disabled == false) && // Make sure the user isn't inactive //
				(puser->m_Rank == (*i).m_Rank) && // Make sure ranks line up //
				(m_Generation == (*i).m_Generation) && // Make sure generations line up //
				(preceipt->m_InvType == (*i).m_InvType) && // Make sure inventory types line up //
				(preceipt->m_Commissionable == true) && // Make sure it's not like a sales kit //
				(((preceipt->m_EventWholesale == true) && ((*i).m_Event == EVENT_WHOLESALE)) ||
				 ((preceipt->m_EventRetail == true) && ((*i).m_Event == EVENT_RETAIL)))
			   )
			{		
				CDateCompare date_window(start_date, end_date);

				double commission = 0;
				if (((*i).m_PayType == PAYOUT_RETAIL) && (date_window.IsBetween(preceipt->m_RetailDate.c_str()) == true)) // Affilliate corporate on retail price //
				{
					if ((*i).m_Percent != 0)
						commission += preceipt->m_RetailPrice * (*i).m_Percent * 0.01;
				}
				else if (((*i).m_PayType == PAYOUT_WHOLESALE) && (date_window.IsBetween(preceipt->m_WholesaleDate.c_str()) == true))
				{
					if ((*i).m_Percent != 0)
						commission += preceipt->m_WholesalePrice * (*i).m_Percent * 0.01;
				}

				// Handle a specific dollar payout 
				if ((*i).m_Dollar != 0)
					commission += (*i).m_Dollar;

				puser->m_Commission += commission;
				if ((pretend == false) && (puser->m_UserType == 1)) // 1 = Affiliate //
				{	
					m_pDB->AddReceiptBreakdown(socket, m_SystemID, m_BatchID, preceipt->m_ID, preceipt->m_ReceiptID, puser->m_UserID.c_str(), commission, (*i).m_ID, m_Generation, (*i).m_Percent, (*i).m_InfinityBonus, COMMTYPE_RANK, preceipt->m_MetaDataOnAdd, preceipt->m_InvType, (*i).m_Dollar);
				}

				if ((*i).m_InfinityBonus == true)
				{
					m_InfinityTotal += commission; // For tracking and testing global wide //
					(*i).m_InfinityTotal += commission; // As per commission rule //
				}
			}	
		
	}

	// Non-compress - Applies all the way upline //
	m_Generation++;
	if ((m_Generation > m_GenLimit) && (m_GenLimit != -1)) // Exit out once we go higher then the max generation //
		return false;

	if (m_Generation > GENERATION_MAX)
	{
		Debug(DEBUG_ERROR, "CCommissions::BreakawayFinal - **Danger** > GENERATION_MAX! m_Generation", m_Generation);
		Debug(DEBUG_ERROR, "CCommissions::BreakawayFinal - puser->m_UserID", puser->m_UserID.c_str());
		Debug(DEBUG_ERROR, "CCommissions::BreakawayFinal - preceipt->m_ReceiptID", preceipt->m_ReceiptID);
		return false;
	}

	return BreakawayFinal(pretend, socket, puser->m_pSponsor, preceipt, start_date, end_date);
}

///////////////////////////
// Do final binary tally //
///////////////////////////
bool CCommissions::BinaryFinal(bool pretend, int socket, CUser *puser)
{
	CUser *pUser2ndLeg = puser->Find2ndBestLeg();
	if (pUser2ndLeg != 0)
	{
		puser->m_Commission = pUser2ndLeg->m_GroupWholesaleSales; 
		CUser *pUser1stLeg = puser->Find1stBestLeg();

		// Add binary entry to show both legs... all sales vs payout //
		double groupsales = puser->m_GroupWholesaleSales;
				
		// Finally add ledger for auditing purposes //
		if (pUser1stLeg->m_GroupWholesaleSales > 0)
		{
			if ((pretend == false) && (puser->m_UserType == 1) && (puser->m_Disabled == false)) // 1 = Affiliate //
			{
				m_pDB->AddBinaryLedger(socket, m_SystemID, m_BatchID, puser->m_UserID.c_str(), puser->m_Commission, pUser1stLeg->m_GroupWholesaleSales, pUser2ndLeg->m_GroupWholesaleSales, groupsales);
			}
		}
	}

	return true;
}

///////////////////////////
// Rebuild the userstats //
///////////////////////////
bool CCommissions::FinishUserStats(bool pretend, int socket, int system_id, int batch_id, const char *end_date)
{
	Debug(DEBUG_DEBUG, "CCommissions::FinishUserStats - Begin - system_id", system_id);

	if (pretend == true)
		return false;

	// Grab RankGenBonus records //
	list <CRankGenBonus> RankGenBonus;
	if (m_pDB->GetRankGenBonus(socket, system_id, &RankGenBonus) == false)
		Debug(DEBUG_INFO, "CCommissions::FinishUserStats - GetRankGenBonus == false. Could be zero records");

	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //

		// Calc first and second leg group sales //
		CUser *secondleg = puser->Find2ndBestLeg(); // This needs to go first cause it runs sorting function // 
		CUser *firstleg = puser->Find1stBestLeg();
		
		// Handle sales //
		double firstsales = 0;
		std::string first_id;
		if (firstleg != NULL)
		{
			first_id = firstleg->m_UserID;
			firstsales = firstleg->m_GroupWholesaleSales;
		}
		else
		{
			first_id = ""; // No 2nd downline. -1 is easier to search
			firstleg = 0;
		}
		double secondsales = 0;
		std::string second_id;
		if (secondleg != NULL)
		{
			second_id = secondleg->m_UserID;
			secondsales = secondleg->m_GroupWholesaleSales;
		}
		else
		{
			second_id = "";
			secondleg = 0;
		}

		if (m_UserStats.AddBulk(m_pDB, socket, system_id, batch_id, puser, first_id, firstsales, second_id, secondsales) == false)
			return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - AddBulk = false");
	}

	if (m_UserStats.FinishBulk(m_pDB, socket) == false)
		return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - FinishBulk = false");

	Debug(DEBUG_DEBUG, "CCommissions::FinishUserStats - #1 - After Mid BulkFinish");

	////////////////////////////////////////////////////////////////////////
	// Handle storing the precompiled rank leg sum values in the database //
	////////////////////////////////////////////////////////////////////////
	CDbBulk bulk;
	CConvert convert;
	int legcount = 0;
	string legrankSQL;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first];
		LegRankCalc(pretend, &RankGenBonus, socket, system_id, batch_id, puser, puser, end_date);
	}

	Debug(DEBUG_DEBUG, "CCommissions::FinishUserStats - #2 - Before Mid BulkFinish");

	// Finish Bulk Records //
	if (bulk.BulkFinish(m_pDB, socket, &m_LegRankSQL) == false)
		return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - #2 - Error m_LegRankGen1SQL", m_LegRankSQL.c_str());
	if (bulk.BulkFinish(m_pDB, socket, &m_RankGenBonusSQL) == false)
		return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - #2 - Error m_RankGenBonusSQL", m_RankGenBonusSQL.c_str());

	Debug(DEBUG_DEBUG, "CCommissions::FinishUserStats - After Mid BulkFinish");

	/////////////////////////////////////////////////////////////////////////
	// Handle storing the precompiled rank lvl1 sum values in the database //
	/////////////////////////////////////////////////////////////////////////
	legcount = 0;
	map <string, int>::iterator m;
	for (m=m_MapRankLvl1Sum.begin(); m != m_MapRankLvl1Sum.end(); ++m)
	{
		//char tmpuserank[1000];
		//sprintf(tmpuserank, "%s", m->first.c_str());
		//string userid = strtok(tmpuserank, "#");
		//string rank = strtok(NULL, "#");

		CezTok tok(m->first.c_str(), '#');
		string userid = tok.GetValue(0);
		string rank = tok.GetValue(1);

		int rigcount = count(m_MapRankLvl1Data[m->first].begin(), m_MapRankLvl1Data[m->first].end(), ',')-1;
		if (rigcount < 0)
			rigcount = 0;

		// Strip commas on both sides for final data //
		string finaluserdata;
	    finaluserdata = m_MapRankLvl1Data[m->first];
	    if (finaluserdata.size() == 1)
	    	finaluserdata = "";
	    if (finaluserdata.size() > 2)
	    	finaluserdata = finaluserdata.substr(1, finaluserdata.size()-2);

	    // Chalkcouture needed ALL entries for downline report //
	    // United avoid blank entries. It takes too long for ALL //
	    if ((m_pDB->m_pSettings->m_DisableLvlRankSQL == false) || 
	    	((m_pDB->m_pSettings->m_DisableLvlRankSQL == true) && (rigcount != 0)))
	    {
			map <string, string> columns;
			columns["system_id"] = convert.IntToStr(system_id);
			columns["batch_id"] = convert.IntToStr(batch_id);
			columns["user_id"] = userid;
			columns["rank"] = rank;
			columns["total"] = convert.IntToStr(rigcount); //convert.IntToStr(ranksum);
			columns["userdata"] = finaluserdata; //m_MapRankLvl1Data[m->first];
			if ((legcount = bulk.BulkAdd(m_pDB, socket, "ce_userstats_month_lvl1_rank", columns, &legrankSQL, legcount)) == -1)
				return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - #3 - Error SQL", legrankSQL.c_str());
		}
	}

	Debug(DEBUG_DEBUG, "CCommissions::FinishUserStats - Before Last BulkFinish");

	// Finish Bulk Records //
	if (bulk.BulkFinish(m_pDB, socket, &legrankSQL) == false)
		return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - #4 - Error SQL", legrankSQL.c_str());

	return true;
}

///////////////////////////////////
// Calculate the leg rank values //
///////////////////////////////////
bool CCommissions::LegRankCalc(bool pretend, list <CRankGenBonus> *RankGenBonus, int socket, int system_id, int batch_id, CUser *pBaseUser, CUser *puser, const char *end_date)
{
	if (puser == NULL)
		return false;

	// Fill with empty set. This needed for mydownlinereport.php //
	int generation;
	for (generation=1; generation <= m_pDB->m_pSettings->m_MaxRankBonusGen; generation++)
	{
		//Debug(DEBUG_DEBUG, "CCommissions::LegRankCalc - #1 Loop TOP - generation", generation);
		//Debug(DEBUG_DEBUG, "CCommissions::LegRankCalc - #1.1 Loop TOP - m_RankMax", m_RankMax);

		// Build the leg sum and data //
		map <int, int> MapRankLegSum;
		map <int, string> MapRankLegData;

		// Clear the values //
		int clearindex;
		for (clearindex=0; clearindex <= m_RankMax; clearindex++)
		{
			MapRankLegSum[clearindex] = 0;
			MapRankLegData[clearindex] = " ";
		}

		//Debug(DEBUG_DEBUG, "CCommissions::LegRankCalc - #2 Loop MID - generation", generation);

		int rank;
		for (rank=0; rank <= m_RankMax; rank++)
		{
			// Build the data //
			BuildLegRankGenData(puser->m_UserID, puser, MapRankLegSum, MapRankLegData, rank, 1, generation);
		}

		//Debug(DEBUG_DEBUG, "CCommissions::LegRankCalc - #3 Loop MID DOWN - generation", generation);

		// Grab sum of each combined data //
		map <int, int>::iterator k;
		for (k=MapRankLegSum.begin(); k != MapRankLegSum.end(); ++k) 
		{
			int rank = k->first;
			string finaluserdata = MapRankLegData[rank];
			finaluserdata = finaluserdata.substr(0, finaluserdata.size()-1);
			int rigcount = count(MapRankLegData[rank].begin(), MapRankLegData[rank].end(), ',');

			//Debug(DEBUG_DEBUG, "CCommissions::LegRankCalc - #4 Before LegRankLadder - generation", generation);

			LegRankLadder(pretend, RankGenBonus, socket, system_id, batch_id, pBaseUser, rank, rigcount, finaluserdata, end_date, generation);
		}
	}

	return true;
}

/////////////////////////////
// Build the rank leg data //
/////////////////////////////
bool CCommissions::BuildLegRankGenData(string baseuserid, CUser *puser, map <int, int> &MapRankLegSum, map <int, string> &MapRankLegData, int rank, int countgen, int endgen)
{
	if (baseuserid.size() == 0)
		return false;
	if (puser->m_AdvisorLegsLL.size() == 0)
		return false;

	//stringstream ss;
	//ss << "LLcount=" << puser->m_AdvisorLegsLL.size() << ", baseuserid=" << baseuserid << ", rank=" << rank << ", countgen=" << countgen << ", endgen=" << endgen;
	//Debug(DEBUG_DEBUG, "CCommissions::BuildLegRankGenData ", ss.str().c_str());

	list <CUser*>::iterator q;
	for (q=puser->m_AdvisorLegsLL.begin(); q != puser->m_AdvisorLegsLL.end(); ++q)  
	{
		if (countgen == endgen)
		{
			if ((*q)->m_Rank > rank)
			{
				MapRankLegSum[rank]++;
				MapRankLegData[rank] += " "+(*q)->m_UserID+",";
			}
			else if ((*q)->m_Rank == rank)
			{
				MapRankLegSum[(*q)->m_Rank]++;
				MapRankLegData[(*q)->m_Rank] += " "+(*q)->m_UserID+",";
			}
		}

		int currgen = countgen;
		if ((*q)->m_Rank >= rank)
			currgen++;
			
		if (countgen <= endgen)
		{
			CUser *pNextUser = &m_UsersMap[(*q)->m_UserID];
			BuildLegRankGenData(baseuserid, pNextUser, MapRankLegSum, MapRankLegData, rank, currgen, endgen);
		}
	}

	return true;
}

////////////////////////////////
// Handle the leg rank ladder //
////////////////////////////////
bool CCommissions::LegRankLadder(bool pretend, list <CRankGenBonus> *RankGenBonus, int socket, int system_id, int batch_id, CUser *puser, int rank, int rigcount, string finaluserdata, const char *end_date, int generation)
{
	if (puser->m_UserID == "")
		return false;
	if (generation > m_pDB->m_pSettings->m_MaxRankBonusGen)
		return false;
	if (puser == NULL)
		return true;

	CDbBulk bulk;
	CConvert convert;

	/////////////////////////////////
	// Handle the Rank Gen Bonuses //
	/////////////////////////////////
	//Debug(DEBUG_ERROR, "RankGenBonus->size()", RankGenBonus->size());
	if (RankGenBonus->size() > 0)
	{
		list <CRankGenBonus>::iterator b;
		for (b=RankGenBonus->begin(); b != RankGenBonus->end(); ++b) 
		{
			if ((puser->m_Rank == (*b).m_MyRank) && (rigcount > 0))
			{
				//Debug(DEBUG_ERROR, "AFTER puser->m_Rank", puser->m_Rank);

				// Test each bonus criteria //
				if ((rank == (*b).m_UserRank) && (generation == (*b).m_Generation))
				{
					//Debug(DEBUG_ERROR, "AFTER (*b).m_UserRank", (*b).m_UserRank);

					string event_date = end_date;

					map <string, string> bonuscols;
					bonuscols["system_id"] = convert.IntToStr(system_id);
					bonuscols["batch_id"] = convert.IntToStr(batch_id);
					bonuscols["user_id"] = puser->m_UserID;
					bonuscols["amount"] = convert.DoubleToStr((*b).m_Bonus*rigcount);
					bonuscols["event_date"] = event_date;
					bonuscols["generation"] = convert.IntToStr(generation);
					bonuscols["userdata"] = trim(finaluserdata);
					bonuscols["my_rank"] = convert.IntToStr(puser->m_Rank);
					bonuscols["user_rank"] = convert.IntToStr(rank);
					bonuscols["rule_id"] = convert.IntToStr((*b).m_ID);

					if ((m_RankGenBonusCount = bulk.BulkAdd(m_pDB, socket, "ce_rankgenbonus", bonuscols, &m_RankGenBonusSQL, m_RankGenBonusCount)) == -1)
						return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - bulk.BulkAdd #1 - Error SQL", m_RankGenBonusSQL.c_str());
				}
			}
		}
	}

	/////////////////////////////////////////
 	// Add entries to specific stats table //
 	/////////////////////////////////////////
 	if ((generation == 1) || 
 		((generation > 1) && (rigcount > 0)))
 	{
		map <string, string> columns;
		columns["system_id"] = convert.IntToStr(system_id);
		columns["batch_id"] = convert.IntToStr(batch_id);
		columns["user_id"] = puser->m_UserID; 
		columns["rank"] = convert.IntToStr(rank); 
		columns["total"] = convert.IntToStr(rigcount); 
		columns["userdata"] = trim(finaluserdata); 
		columns["generation"] = convert.IntToStr(generation);
		if ((m_LegRankCount = bulk.BulkAdd(m_pDB, socket, "ce_userstats_month_leg_rank", columns, &m_LegRankSQL, m_LegRankCount)) == -1)
			return Debug(DEBUG_ERROR, "CCommissions::FinishUserStats - bulk.BulkAdd #2.1 - Error SQL", m_LegRankSQL.c_str());
	}
 	
	return true;
}

//////////////////////////////
// Update the new AdvisorID //
//////////////////////////////
bool CCommissions::UpdateNewAdvisor(bool pretend, int socket, int system_id)
{
	map <string, string> MapAdvisorSQL;
	map <string, int> MapAdvisorCount;

	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //

		string advisor_id;
		if (puser->m_pSponsor == NULL)
		{
			advisor_id = "0";
		}
		else if (puser->m_pSponsor->m_Disabled == true)
		{
			advisor_id = GetNewAdvisor(puser->m_pSponsor);
		}
		else
		{
			advisor_id = puser->m_pSponsor->m_UserID;
		}

		puser->m_AdvisorID = advisor_id;

		stringstream ssSys;
		ssSys << system_id;

		// Handle the SQL using maphash to speed it up //
		if (MapAdvisorSQL[advisor_id].size() == 0)
		{
			MapAdvisorSQL[advisor_id] = "UPDATE ce_users SET advisor_id='"+advisor_id+"' WHERE system_id='"+ssSys.str()+"' AND (user_id='"+puser->m_UserID+"'";
			MapAdvisorCount[advisor_id] = 1;
		}
		else
		{
			MapAdvisorSQL[advisor_id] += " OR user_id='"+puser->m_UserID+"'";
			MapAdvisorCount[advisor_id]++;

			// Only allow 5000 at a time //
			if (MapAdvisorCount[advisor_id] >= MAX_SQL_APPEND)
			{
				MapAdvisorSQL[advisor_id] += ")";

				if (m_pDB->m_pSettings->m_DisableAdvisorSQL == false)
				{				
					// Was true. Testing //
					if (m_pDB->ExecDB(false, socket, MapAdvisorSQL[advisor_id].c_str()) == NULL)
						Debug(DEBUG_ERROR, "CCommissions::HybridUniFinal - 5000 limit UPDATE advisorid Error");
				}
				MapAdvisorSQL[advisor_id] = "";
				MapAdvisorCount[advisor_id] = 0;
			}
		}
	}

	map <string, string>::iterator k;
	for (k=MapAdvisorSQL.begin(); k != MapAdvisorSQL.end(); ++k) 
	{
		string sql = k->second;
		sql += ")";

		if (m_pDB->m_pSettings->m_DisableAdvisorSQL == false)
		{	
			// Was true. Testing //
			if (m_pDB->ExecDB(false, socket, sql.c_str()) == NULL)
				Debug(DEBUG_ERROR, "CCommissions::HybridUniFinal - Final UPDATE advisorid Error");
		}
	}

	return true;
}

/////////////////////////
// Get the new advisor //
///////////////////////// 
string CCommissions::GetNewAdvisor(CUser *puser)
{
	if (puser == NULL)
		return "1";
	
	/*
	// This was commented out because of problems with Chalkcouture
	// On April 5, 2018 we found userid 328 had the advisor_id compress up to userid 1
	// It should have compressed to userid 268
	// This was left in just in case another problem arises and 
	// Someone (Probably West) goes to fix it and this info would be relevant 
	if (puser->m_pSponsor != NULL)
	{
		if (puser->m_pSponsor->m_UserID == "1")
		{
			return "1";
		}
	}
	*/

	if ((puser->m_Disabled == true) || (puser->m_UserType == 2))
		return GetNewAdvisor(puser->m_pSponsor);

	return puser->m_UserID;
}

///////////////////////////////////////////////////////////////////
// Rebuild the comm legs to reflect compression with new advisor //
///////////////////////////////////////////////////////////////////
bool CCommissions::RebuildCommLegsWAdvisor(int comm_type)
{
	if (comm_type == COMMRULE_BINARY)
 		return false;

 	map <string, CUser>::iterator q;
	for (q=m_UsersMap.begin(); q != m_UsersMap.end(); ++q) 
	{
		CUser *puser = &m_UsersMap[q->first];
		m_UsersMap[puser->m_AdvisorID].m_AdvisorLegsLL.push_back(&m_UsersMap[puser->m_UserID]);

		// Set Advisor Pointer //
		puser->m_pAdvisor = &m_UsersMap[puser->m_AdvisorID];
	}
	return true;
}

//////////////////////////////
// Build the ledger entries //
//////////////////////////////
bool CCommissions::BuildLedger(bool pretend, int socket, int system_id, int batch_id, float signupbonus, const char *start_date, const char *end_date)
{
	Debug(DEBUG_DEBUG, "CCommissions::BuildLedger - Begin - system_id", system_id);

	if (pretend == true)
		return false;

	// Grab the base system_id //
	int base_system_id = system_id;
	int base_batch_id = batch_id;
	// Override if a United Game //
	if (m_AltCore == ALTCORE_UNITED_GAME)
		base_system_id = m_pDB->GetBaseSystemID(socket, system_id);

	// Wait for all threads //
	m_pDB->m_ConnPool.WaitForThreads(socket);

	// Grab the grandtotals //
	std::list <CGrandTotal> GrandTotals;
	m_pDB->GetGrandTotals(socket, system_id, batch_id, &GrandTotals);
	std::list <CGrandTotal>::iterator j;
	for (j=GrandTotals.begin(); j != GrandTotals.end(); ++j) 
	{
		// Add Ledger Entries //
		if ((m_AltCore == ALTCORE_UNITED_MAIN) || (m_AltCore == 0))
		{
			m_pDB->AddLedger(socket, base_system_id, base_batch_id, (*j).m_UserID.c_str(), (*j).m_ID, LEDGER_GRANDTOTAL, system_id, (*j).m_UserID.c_str(), (*j).m_Amount, 0, end_date);	
		}
		else if (m_AltCore == ALTCORE_UNITED_GAME)
		{
			m_pDB->AddLedger(socket, base_system_id, base_batch_id, (*j).m_UserID.c_str(), (*j).m_ID, LEDGER_TRANSFER, system_id, (*j).m_UserID.c_str(), (*j).m_Amount, 0, end_date);	
		}
		else
		{
			Debug(DEBUG_ERROR, "CCommissions::BuildLedger - No Ledger Record (user_id)", (*j).m_UserID.c_str());
		}
	}

	// Add bonus entries into ledger //
	if (m_pDB->BonusToLedger(socket, system_id, batch_id, start_date, end_date) == false)
		Debug(DEBUG_INFO, "CCommissions::BuildLedger - m_pDB->BonusToLedger == false");

	// Move the rankgenbonus to ledger //
	if (m_pDB->RankGenBonusToLedger(socket, system_id, batch_id, end_date) == false)
		Debug(DEBUG_INFO, "CCommissions::BuildLedger - m_pDB->RankGenBonusToLedger == false");

	// Add signup bonus entries to ledger //
	if (signupbonus > 0)
	{
		std::map <std::string, CUser>::iterator i;
		for (i=m_UsersMap.begin(); i != m_UsersMap.end(); ++i) 
		{
			CUser *puser = &m_UsersMap[i->first];
			if (puser->m_SignupCount > 0)
			{
				double bonuscalc = puser->m_SignupCount*signupbonus;
				m_pDB->AddLedger(socket, base_system_id, base_batch_id, puser->m_UserID.c_str(), 0, LEDGER_SIGNUPBONUS, system_id, puser->m_UserID.c_str(), bonuscalc, 0, end_date);	
			}
		}
	}

	return true;
}

/////////////////////
// Run Check Match //
/////////////////////
bool CCommissions::DoCheckMatch(CDb *pDB, int socket, bool pretend, int system_id, int batch_id, const char *start_date, const char *end_date)
{
	if (pretend == true)
		return false;

	m_pDB = pDB;

	std::list <CRulesComm> CMRulesCommLL;
	m_pDB->GetCMCommRules(socket, system_id, &CMRulesCommLL);

	if (CMRulesCommLL.size() == 0)
		return Debug(DEBUG_INFO, "CCommissions::DoCheckMatch - No CM CommRules. CheckMatch will not be calculated");

	DoRankRules(true, socket, m_SystemID, m_BatchID, &m_CMRulesRankLL, "ce_cmrankrules"); // Give credit to moving up a rank //

	// Clean up any previous incomplete checkmatch //
//	m_pDB->ResetCheckmatch(socket, system_id, batch_id);
/*
	Debug(DEBUG_INFO, "CCommissions::DoCheckMatch - Before Get Users");
	m_pDB->GetUsers(socket, system_id, false, m_UsersMap, UPLINE_SPONSOR_ID, start_date, end_date); // Signup count done in here //

//#ifdef COMPILE_UNITED
	Debug(DEBUG_INFO, "CCommissions::DoCheckMatch - Before GetRanks");
	m_pDB->GetRanks(socket, system_id, batch_id, m_UsersMap); // Read in the rank from previous calc system=1 //
//#endif

	if (IsRecursionLoop(system_id) == true)
		return Debug(DEBUG_ERROR, "CCommissions::DoCheckMatch - There was a sponsor_id/parent_id recurrsion loop found");
*/

	/////////////////////////
	// Do checkmatch spent //
	/////////////////////////
	Debug(DEBUG_INFO, "CCommissions::DoCheckMatch - Before DoCheckMatchCommission Loop");
	std::list <CGrandTotal> LedgerGrand;
	m_pDB->GetLedgerRecs(socket, system_id, batch_id, LEDGER_GRANDTOTAL, &LedgerGrand);
	std::list <CGrandTotal>::iterator j;
	for (j=LedgerGrand.begin(); j != LedgerGrand.end(); ++j)
	{
		CUser *puser = &m_UsersMap[(*j).m_UserID];
		m_Generation = 1;
		DoCheckMatchCommission(socket, system_id, batch_id, puser->m_pSponsor, (*j).m_ID, (*j).m_UserID.c_str(), (*j).m_Amount, &CMRulesCommLL, end_date);		
	}

/*
	////////////////////////
	// Do checkmatch used //
	////////////////////////
	std::list <CGrandTotal> LedgerUsed;
	m_pDB->GetLedgerRecs(socket, system_id, batch_id, LEDGER_TRANSFER, &LedgerUsed);
	std::list <CGrandTotal>::iterator k;
	for (k=LedgerUsed.begin(); k != LedgerUsed.end(); ++k) 
	{	
		CUser *puser = &m_UsersMap[(*k).m_UserID];
		m_Generation = 1;
		DoCheckMatchUsed(socket, system_id, batch_id, puser->m_pSponsor, (*k).m_ID, (*k).m_UserID.c_str(), (*k).m_Amount, &CMRulesCommLL, end_date);
	}
*/
	return true;
}

///////////////////////////////////
// Do check match on commissions //
///////////////////////////////////
bool CCommissions::DoCheckMatchCommission(int socket, int system_id, int batch_id, CUser *puser, int ref_id, const char *baseuser_id, double amount, std::list <CRulesComm> *pRulesComm, const char *end_date)
{
	if (puser == NULL)
		return false;

	bool paid = false;
	std::list<CRulesComm>::iterator k;
	for (k=pRulesComm->begin(); k != pRulesComm->end(); ++k) 
	{
		if ((puser->m_CMRank == (*k).m_Rank) && (m_Generation == (*k).m_Generation) && (paid == false))
		{
			if ((puser->m_UserType == 1) && (puser->m_Disabled == false)) // 1 = Affiliate //
			{	
				double checkmatchpay = amount * (*k).m_Percent * 0.01;
				m_pDB->AddLedger(socket, system_id, batch_id, puser->m_UserID.c_str(), ref_id, LEDGER_CM_PURCHASED, system_id, baseuser_id, checkmatchpay, m_Generation, end_date);	
				paid = true;
			}
		}

		if (paid == true)
			break;
	}

	// Handle compression //
	if (m_CompressionEnabled == true)
	{
		if (puser->m_Disabled == true)
		{
			// Compress //
		}
		else if (puser->m_UserType == 2)
		{
			// Compress //
		}
		else
		{
			m_Generation++;
		}
	}
	else // No Compression //
	{
		m_Generation++;
	}

	return DoCheckMatchCommission(socket, system_id, batch_id, puser->m_pSponsor, ref_id, baseuser_id, amount, pRulesComm, end_date);
}

///////////////////////////////////
// Do check match on commissions //
///////////////////////////////////
bool CCommissions::DoCheckMatchUsed(int socket, int system_id, int batch_id, CUser *puser, int ref_id, const char *baseuser_id, double amount, std::list <CRulesComm> *pRulesComm, const char *end_date)
{
	if (puser == NULL)
		return false;

	bool paid = false;
	std::list<CRulesComm>::iterator k;
	for (k=pRulesComm->begin(); k != pRulesComm->end(); ++k) 
	{
		if ((puser->m_Rank == (*k).m_Rank) && (m_Generation == (*k).m_Generation) && (paid == false))
		{
			//Debug(DEBUG_WARN, "CCommissions::DoCheckMatchUsed - MID");

			if ((puser->m_UserType == 1) && (puser->m_Disabled == false)) // 1 = Affiliate //
			{	
				double checkmatchpay = amount * (*k).m_Percent * 0.01;
				m_pDB->AddLedger(socket, system_id, batch_id, puser->m_UserID.c_str(), ref_id, LEDGER_CM_USED, system_id, baseuser_id, checkmatchpay, m_Generation, end_date);	
				paid = true;
			}
		}

		if (paid == true)
			break;
	}

	// Handle compression //
	if (m_CompressionEnabled == true)
	{
		if (puser->m_Disabled == true)
		{
			// Compress //
		}
		else if (puser->m_UserType == 2)
		{
			// Compress //
		}
		else
		{
			m_Generation++;
		}
	}
	else // No compression //
	{
		m_Generation++;
	}

	return DoCheckMatchUsed(socket, system_id, batch_id, puser->m_pSponsor, ref_id, baseuser_id, amount, pRulesComm, end_date);
}

///////////////////////////////////
// Handle the fast start bonuses //
///////////////////////////////////
bool CCommissions::DoFastStartBonuses(bool pretend, int socket, int system_id, const char *start_date, const char *end_date)
{
	//Debug(DEBUG_ERROR, "CCommissions::DoFastStartBonuses - TOP");

	// Loop through each fast start bonus rule //
	list<CFastStartRules>::iterator k;
	for (k=m_FastStartRulesLL.begin(); k != m_FastStartRulesLL.end(); ++k) 
	{
		// Loop through each user //
		std::map <std::string, CUser>::iterator i;
		for (i=m_UsersMap.begin(); i != m_UsersMap.end(); ++i) 
		{
			CUser *puser = &m_UsersMap[i->first];

			// Either only matching ranks or all ranks //
			if (((*k).m_Rank == -1) || ((*k).m_Rank == puser->m_Rank))
			{
				// Qualify Type and Threshold //
				if (QualifyCompareRules(puser, (*k).m_QualifyType, (*k).m_QualifyThreshold, 0) == true)
				{
					//Debug(DEBUG_ERROR, "CCommissions::DoFastStartBonuses - QualifyCompareRules - user", puser->m_UserID);
					//Debug(DEBUG_ERROR, "CCommissions::DoFastStartBonuses - QualifyCompareRules - (*k).m_DaysCount", (*k).m_DaysCount);

					string firstdate;
					string seconddate;

					//CDateCompare date_window(start_date, end_date);
					//if (date_window.IsBetween(puser->m_SignupDate.c_str()) == true)
					//{
					//	firstdate = start_date;
					//	seconddate = puser->m_SignupDate;
					//}
					//else
					//{
						firstdate = puser->m_SignupDate;
						seconddate = end_date;
					//}

					CDateCompare datecount(firstdate.c_str(), seconddate.c_str());
					long daycount = datecount.IsDaysCount();
					
					stringstream ssTest1;
					ssTest1 << "QualifyCompareRules - daycount = " << (int)daycount << ", m_SignupDate=" << puser->m_SignupDate.c_str();
					
					//Debug(DEBUG_ERROR, "CCommissions::DoFastStartBonuses", ssTest1.str().c_str());

					if (daycount <= (*k).m_DaysCount)
					{
						// Give credit for bonus //
						stringstream ssTest;
						ssTest << "IsDaysCount - user=" << puser->m_UserID << ", m_SignupDate=" << puser->m_SignupDate.c_str() << ", daycount=" << daycount;
						//Debug(DEBUG_ERROR, "CCommissions::DoFastStartBonuses - IsDaysCount - ", ssTest.str().c_str());
					}
				}
			}
		}
	}

	/*
	newfast.m_ID = StrToInt(conn->m_RowMap[0].c_str());
	newfast.m_Rank = StrToInt(conn->m_RowMap[1].c_str());
	newfast.m_QualifyType = StrToInt(conn->m_RowMap[2].c_str());
	newfast.m_QualifyThreshold = StrToInt(conn->m_RowMap[3].c_str());
	newfast.m_DaysCount = StrToInt(conn->m_RowMap[4].c_str());
	newfast.m_RuleGroup = StrToInt(conn->m_RowMap[5].c_str());
	*/

}

/////////////////////////////////
// Build the grandtotal values //
/////////////////////////////////
bool CCommissions::BuildGrandTotals(bool pretend, int socket, int system_id, const char *date_last_earned)
{
	string datelast = "'";
	datelast += date_last_earned;
	datelast += "'";
	CDbBulk bulk;
	int earnedcount = 0;
	string earnedSQL;
	std::map <std::string, CUser>::iterator j;
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //
        m_GrandTotal += puser->m_Commission;
        m_GrandAchvBonus += puser->m_AchvBonus;

        //stringstream ssDebug;
        //ssDebug << "CCommissions::BuildGrandTotals - userid=" << puser->m_UserID << ", puser->m_AchvBonus=" << puser->m_AchvBonus;
        //Debug(DEBUG_WARN, ssDebug.str().c_str());

        // UPDATE ce_users->date_last_earned //
        if ((puser->m_Commission > 0) && (pretend == false))
        {
        	//string condition = "user_id='"+puser->m_UserID+"' AND system_id='"+system_id+"'";
        	stringstream condition;
        	condition << "(user_id='" << puser->m_UserID << "' AND system_id='" << system_id << "')";
        	earnedcount = bulk.BulkUpdate(m_pDB, socket, "ce_users", "date_last_earned", datelast, condition.str(), &earnedSQL, earnedcount);
        }
	}

	if (pretend == false)
	{
		if (bulk.BulkFinish(m_pDB, socket, &earnedSQL) == false)
			Debug(DEBUG_ERROR, "CCommissions::BuildGrandTotals - bulk.BulkFinish == false");
	}

	//Debug(DEBUG_WARN, "CCommissions::BuildGrandTotals - m_GrandAchvBonus", m_GrandAchvBonus);
	
	m_pDB->UpdateBatch(pretend, socket, m_SystemID, m_BatchID, m_ReceiptsWholesaleTotal, m_ReceiptsRetailTotal, m_GrandTotal, m_GrandAchvBonus, m_GrandBonus, 0);
	return true;
}

/////////////////////////////////////////////////////
// Build the JSON object for returning information //
/////////////////////////////////////////////////////
const char *CCommissions::BuildJSON(string affiliate_id)
{
	std::stringstream ss; 
	ss << std::setprecision(2) << std::fixed; // Set precision for decimal dollar/cents values //
	std::map <std::string, CUser>::iterator j;
	ss << ",\"payouts\":[";
	for (j=m_UsersMap.begin(); j != m_UsersMap.end(); ++j) 
	{
		CUser *puser = &m_UsersMap[j->first]; // This seems to be more accurate //

		if ((affiliate_id.length() > 0) && (affiliate_id == puser->m_UserID))
		{
			ss << "{\"userid\":\"" << puser->m_UserID << "\",";
			ss << "\"commission\":\"" << puser->m_Commission << "\",";
			ss << "\"achvbonus\":\"" << puser->m_AchvBonus << "\"},";
			break;
		}
		else if ((puser->m_UserID != "0") && (puser->m_Commission > 0))
		{
			ss << "{\"userid\":\"" << puser->m_UserID << "\",";
			ss << "\"commission\":\"" << puser->m_Commission << "\",";
			ss << "\"achvbonus\":\"" << puser->m_AchvBonus << "\"},";
		}
	}

	std::string json;
    json = ss.str();
    json = json.substr(0, json.size()-1);
    json += "]";
	return SetJson(200, json.c_str());
}

////////////////////////////////////
// Build the JSON for grand total //
////////////////////////////////////
const char *CCommissions::BuildGrandTotalJSON()
{
	std::stringstream ss;
	ss << std::setprecision(2) << std::fixed; // Set precision for decimal dollar/cents values //	
	ss << ",\"grandpayouts\":{\"receiptswholesale\":\"" << m_ReceiptsWholesaleTotal << "\",\"receiptsretail\":\"" << m_ReceiptsRetailTotal << "\",\"commissions\":\"" << m_GrandTotal << "\",\"achvbonuses\":\"" << m_GrandAchvBonus << "\",\"bonuses\":\"" << m_GrandBonus << "\" ,\"signupbonuses\":\"" << m_GrandSignupBonus << "\"}";
	return SetJson(200, ss);
}

///////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////// CTmpCommission ////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////
void CTmpCommission::SetVars(int id, int commtype, std::string url, std::string username, std::string password)
{
	m_ID = id;
	m_CommType = commtype;
	m_URL = url;
	m_Username = username;
	m_Password = password;
}