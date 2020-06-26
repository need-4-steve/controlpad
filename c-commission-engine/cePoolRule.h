#ifndef _CEPOOLRULE_H
#define _CEPOOLRULE_H

#include "dbplus.h"
#include <string>
 
using namespace std;

class CcePoolRule : public CDbPlus
{
public:
	CcePoolRule(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string pool_id, string start_rank, string end_rank, string qualify_type, string qualify_threshold);
	const char *Edit(int socket, int system_id, string id, string start_rank, string end_rank, string qualify_type, string qualify_threshold);
	const char *Query(int socket, int system_id, string pool_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

public:
	CDb *m_pDB;
};

#endif