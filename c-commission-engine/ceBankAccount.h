#ifndef _CEBANKACCOUNT_H
#define _CEBANKACCOUNT_H

#include "ezJson.h"
#include "debug.h"
#include "validate.h"
#include "dbplus.h"
#include <string>

using namespace std;

class CceBankAccount : private CDbPlus
{
public:
	CceBankAccount(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string user_id, string account_type, string routing_number, string account_number, string holder_name);
	const char *Edit(int socket, int system_id, string user_id, string account_type, string routing_number, string account_number, string holder_name);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string user_id);
	const char *Enable(int socket, int system_id, string user_id);
	const char *Get(int socket, int system_id, string id);

	const char *InitiateValidation(int socket, int system_id, string user_id);
	const char *Validate(int socket, int system_id, string user_id, string amount1, string amount2);

private:
	string m_Json;
	CDb *m_pDB;
};

#endif