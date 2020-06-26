#ifndef _CEPOOLS_H
#define _CEPOOLS_H
 
#include "dbplus.h"
#include <string>

using namespace std;

class CcePools : private CDbPlus
{
public:
	CcePools(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string amount, string start_date, string end_date);
	const char *Edit(int socket, int system_id, string id, string amount, string start_date, string end_date);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

	const char *RunPool(int socket, int system_id, string poolid);

private:
	CDb *m_pDB;
};

#endif