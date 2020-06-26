#ifndef _CEPAYOUT_H
#define _CEPAYOUT_H

#include "dbplus.h"

#include <string>

using namespace std;

class CcePayout : private CDbPlus
{
public:
	CcePayout(CDb *pDB, string origin);
	const char *Query(int socket, int system_id, string authorized, string search, string sort);
	const char *Auth(int socket, int system_id, string grandid, string authorized);
	const char *AuthBulk(int socket, int system_id);
	const char *Disable(int socket, int system_id, string grandid);
	const char *Enable(int socket, int system_id, string grandid);

private:
	CDb *m_pDB;
};

#endif