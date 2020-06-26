#ifndef _CECOMMRULE_H
#define _CECOMMRULE_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceCommRule : public CDbPlus
{
public:
	CceCommRule(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string rank, string generation, string infinitybonus, string percent, string dollar, string inv_type, string event, string paytype);
	const char *Edit(int socket, int system_id, string commrule_id, string rank, string generation, string infinitybonus, string percent, string dollar, string inv_type, string event, string paytype);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string commrule_id);
	const char *Enable(int socket, int system_id, string commrule_id);
	const char *Get(int socket, int system_id, string commrule_id);

private:
	CDb *m_pDB;
};

#endif