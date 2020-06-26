#ifndef _CEAPIKEY_H
#define _CEAPIKEY_H

#include <string>
#include "dbplus.h"
#include "ezJson.h"
#include "debug.h"
#include "validate.h"

using namespace std;

class CceApiKey : public CDbPlus
{
public:
	CceApiKey(CDb *pDB, string origin);
	const char *Add(int socket, int sysuser_id, int system_id, string label);
	const char *Edit(int socket, int sysuser_id, int system_id, string id, string label);
	const char *Query(int socket, int sysuser_id, int system_id, string search, string sort);
	const char *Disable(int socket, int sysuser_id, int system_id, string id);
	const char *Enable(int socket, int sysuser_id, int system_id, string id);

	const char *Reissue(int socket, int sysuser_id);

private:
	string m_Json;
	CDb *m_pDB;
};

#endif
