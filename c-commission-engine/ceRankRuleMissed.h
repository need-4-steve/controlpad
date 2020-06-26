#ifndef _CERANKRULEMISSED_H
#define _CERANKRULEMISSED_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceRankRuleMissed : public CDbPlus, CDbBulk
{
public:
	CceRankRuleMissed(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, int batch_id, string user_id, int rule_id, int rank, 
		int qualify_type, double qualify_threshold, double actual_value);
	const char *Query(int socket, int system_id, string search, string sort);

	int BulkAdd(int missedcount, string *missedSQL, int socket, int system_id, int batch_id, string user_id, int rule_id, int rank, 
		int qualify_type, double qualify_threshold, double actual_value);
	bool BulkFinish(int socket, string insertSQL);

private:
	CDb *m_pDB;
};

#endif