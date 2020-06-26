#ifndef _CERANKGENBONUSRULES_H
#define _CERANKGENBONUSRULES_H

#include "dbplus.h"
#include <string>

using namespace std;

//////////////////////////////////////
// Package class for Gen Rank Bonus //
//////////////////////////////////////
class CceRankGenBonusRules : public CDbPlus
{
public:
	CceRankGenBonusRules(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string my_rank, string user_rank, string generation, string bonus);
	const char *Edit(int socket, int system_id, string id, string my_rank, string user_rank, string generation, string bonus);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

	const char *QueryBonus(int socket, int system_id, string search, string sort);

private:
	CDb *m_pDB;
};

#endif