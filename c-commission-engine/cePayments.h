#ifndef _CEPAYMENTS_H
#define _CEPAYMENTS_H

#include "ezJson.h"
#include "debug.h"
#include "db.h"
#include "validate.h"
#include <string>

using namespace std;

// Types of ways to handle payment processing //
#define PAY_PROC_PAYMAN		1
#define PAY_PROC_LOCAL		2

class CcePayments : CezJson, CDebug, CValidate
{
public:
	CcePayments(CDb *pDB, string origin);
	const char *SetPaymentType(int socket, int system_id, int payment_type);
	const char *Process(int socket, int system_id, string batch_id);
	const char *QueryUser(int socket, int system_id, string user_id);
	const char *QueryBatch(int socket, int system_id, string batch_id);
	const char *QueryNoPay(int socket, int system_id, string batch_id);

	const char *QueryPaymentsTotal(int socket, int system_id);
	const char *QueryPayments(int socket, int system_id);

private:
	string m_Json;
	CDb *m_pDB;
};

#endif