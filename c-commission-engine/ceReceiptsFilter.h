#ifndef _CERECEIPTSFILTER_H
#define _CERECEIPTSFILTER_H

#include "dbplus.h"
#include <string>
 
using namespace std;

class CceReceiptsFilter : public CDbPlus
{
public:
	CceReceiptsFilter(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string inv_type, string product_type);
	const char *Edit(int socket, int system_id, string id, string inv_type, string product_type);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

public:
	CDb *m_pDB;
};

#endif