#ifndef _CEBONUS_H
#define _CEBONUS_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceBonus : public CDbPlus, CDbBulk
{
public:
	CceBonus(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string user_id, string amount, string bonus_date);
	const char *Edit(int socket, int system_id, string id, string user_id, string amount, string bonus_date);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *QueryUser(int socket, int system_id, string user_id);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

	// int batch_id, 
	bool BulkAdd(int socket, int system_id, int batch_id, string user_id, double amount, const char *bonus_date);
	bool BulkFinish(int socket);

private:
	string m_Json;
	CDb *m_pDB;

	int m_BulkCount;
	string m_BulkSQL;
};

#endif
