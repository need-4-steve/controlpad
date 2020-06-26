#ifndef _CEBASICCOMMRULE_H
#define _CEBASICCOMMRULE_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceBasicCommRule : public CDbPlus
{
public:
	CceBasicCommRule(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string generation, string qualify_type, string start_threshold, string end_threshold, string inv_type, string event, string percent, string modulus, string paylimit, string pv_override, string paytype, string rank);
	const char *Edit(int socket, int system_id, string basic_commrule_id, string generation, string qualify_type, string start_threshold, string end_threshold, string inv_type, string event, string percent, string modulus, string paylimit, string pv_override, string paytype, string rank);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string basic_commrule_id);
	const char *Enable(int socket, int system_id, string basic_commrule_id);
	const char *Get(int socket, int system_id, string basic_commrule_id);

private:
	CDb *m_pDB;
};

#endif