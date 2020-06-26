#ifndef _CECMRANKRULE_H
#define _CECMRANKRULE_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceCMRankRule : public CDbPlus
{
public:
	CceCMRankRule(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string label, string rank, string qualify_type, string qualify_threshold, string achvbonus, 
		string breakage, string rulegroup, string maxdacleg, string sumrankstart, string sumrankend);
	const char *Edit(int socket, int system_id, string rank_id, string label, string rank, string qualify_type, string qualify_threshold,
		string achvbonus, string breakage, string rulegroup, string maxdacleg, string sumrankstart, string sumrankend);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string rank_id);
	const char *Enable(int socket, int system_id, string rank_id);
	const char *Get(int socket, int system_id, string rank_id);

private:
	CDb *m_pDB;
};

#endif