//////////////////
// payments.cpp //
//////////////////
#include "payments.h"
#include <stdlib.h>

////////////////////////////////////////
// Make the inital first two deposits //
////////////////////////////////////////
bool CPayments::InitiateValidation(int payproc, CBankAccount *paccount)
{
	Debug(DEBUG_ERROR, "CPayments::InitiateValidation - Nacha Needs to be finished");
	
	// Calc the two deposits //
	srand(time(NULL));
	int am1 = rand() % MAX_PENNY + 1; // Between 1 penny and 30 cents //
	srand(time(NULL)+am1);
	int am2 = rand() % MAX_PENNY + 1; // Between 1 penny and 30 cents //
	stringstream ssAm1;
	stringstream ssAm2;
	if (am1 > 9)
		ssAm1 << "0." << am1;
	else
		ssAm1 << "0.0" << am1;
	if (am2 > 9)
		ssAm2 << "0." << am2;
	else
		ssAm2 << "0.0" << am2;

	// Define the two deposits for the database //
	paccount->m_Amount1 = atof(ssAm1.str().c_str());
	paccount->m_Amount2 = atof(ssAm2.str().c_str());

	// Nacha file will be written at the end of the day //

	// Do we flag if nacha entry is written? //

	return true;
}

///////////////////////////////////////////
// Start the processing of a given batch //
///////////////////////////////////////////
const char *CPayments::ProcessPayments(int socket, int payproc, int system_id, int batch_id, std::list <CPayUser> *pPayUsersLL, CDb *pDB)
{
	Debug(DEBUG_DEBUG, "CPayments::ProcessPayments");

	// Maybe put this in the loop below, and create a new entry when another file is needed? //
	int file_id = pDB->AddBankPayoutFile(socket, system_id, batch_id, "test.filename.txt");

	// Loop through all user processing each payment //
	std::list<CPayUser>::iterator i;
	for (i=pPayUsersLL->begin(); i != pPayUsersLL->end(); ++i)
	{
		if (ProcessAPayment(socket, payproc, system_id, batch_id, file_id, &(*i), pDB) == false)
			Debug(DEBUG_ERROR, "CPayments::ProcessPayments - Error with ProcessAPayment");

		//id | system_id | batch_id | user_id | amount | pay_date | payoutfile_id | created_at | updated_at
	}

	return SetJson(200, "");
}

//////////////////////////////
// Process a single payment //
//////////////////////////////
bool CPayments::ProcessAPayment(int socket, int payproc, int system_id, int batch_id, int file_id, CPayUser *pPayUser, CDb *pDB)
{
	Debug(DEBUG_ERROR, "CPayments::ProcessAPayment - Nacha needs to be finished");

	Debug(DEBUG_DEBUG, "CPayments::ProcessAPayment");

	Debug(DEBUG_DEBUG, "UserID =", pPayUser->m_UserID.c_str());
	Debug(DEBUG_DEBUG, "Commission =", pPayUser->m_Commission);

	Debug(DEBUG_DEBUG, "Account Info: ");
	Debug(DEBUG_DEBUG, pPayUser->m_AccountNumber.c_str());
	Debug(DEBUG_DEBUG, pPayUser->m_RoutingNumber.c_str());
	Debug(DEBUG_DEBUG, pPayUser->m_HolderName.c_str());

	/*
	pPayUser->m_UserID;
	pPayUser->m_AccountType;
	pPayUser->m_RoutingNumber;
	pPayUser->m_AccountNumber;
	pPayUser->m_HolderName;
	pPayUser->m_Commission;
	*/

	// Write a nacha file for all deposits involved? //

	// How do we create a list of all accounts that came back REJECTED? //

	if (pDB->AddBankPayment(socket, system_id, batch_id, pPayUser->m_UserID.c_str(), pPayUser->m_Commission, file_id) == false)
		return Debug(DEBUG_ERROR, "CPayments::ProcessAPayment - Error with AddBankPayment");

	return true;
}