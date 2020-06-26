#ifndef _FASTSTART_H
#define _FASTSTART_H

#include "dbplus.h"
#include <string>

using namespace std;

//////////////////////////////
// Fast start entry for API //
//////////////////////////////
class CceFastStart : public CDbPlus
{
public:
	CceFastStart(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string rank, string qualify_type, string qualify_threshold, string days_count, string bonus, string rulegroup);
	const char *Edit(int socket, int system_id, string id, string rank, string qualify_type, string qualify_threshold, string days_count, string bonus, string rulegroup);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

private:
	CDb *m_pDB;
};

#endif