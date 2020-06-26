#ifndef _CECMCOMMRULE_H
#define _CECMCOMMRULE_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceCMCommRule : public CDbPlus
{
public:
	CceCMCommRule(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string rank, string generation, string percent);
	const char *Edit(int socket, int system_id, string cmcommrule_id, string rank, string generation, string percent);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string cmcommrule_id);
	const char *Enable(int socket, int system_id, string cmcommrule_id);

private:
	CDb *m_pDB;
};

#endif