#ifndef _PAYMENTS_H
#define _PAYMENTS_H

#include "bankaccount.h"
#include "debug.h"
#include "payuser.h"
#include "ezJson.h"
#include "db.h"

#include <string>
#include <list>

#define PAY_TYPE_CHECKING	1
#define PAY_TYPE_SAVINGS	2

#define MAX_PENNY			50

/////////////////////////////
// Retain User Information //
/////////////////////////////
class CPayments : public CDebug, CezJson
{
public:
	bool InitiateValidation(int payproc, CBankAccount *paccount); // Make the inital first two deposits //
	const char *ProcessPayments(int socket, int payproc, int system_id, int batch_id, std::list <CPayUser> *pPayUsersLL, CDb *pDB);

private:
	bool ProcessAPayment(int socket, int payproc, int system_id, int batch_id, int file_id, CPayUser *pPayUser, CDb *pDB);
};

#endif