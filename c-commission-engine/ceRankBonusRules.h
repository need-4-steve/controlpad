#ifndef _CERANKBONUSRULES_H
#define _CERANKBONUSRULES_H

#include "dbplus.h"
#include <string>

using namespace std;

//////////////////////////////////////
// Package class for Gen Rank Bonus //
//////////////////////////////////////
class CceRankBonusRules : public CDbPlus
{
public:
	CceRankBonusRules(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string rank, string bonus);
	const char *Edit(int socket, int system_id, string id, string rank, string bonus);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

	const char *QueryBonus(int socket, int system_id, string search, string sort);

private:
	CDb *m_pDB;
};

#endif