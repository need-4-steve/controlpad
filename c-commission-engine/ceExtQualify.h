#ifndef _CEEXTQUALIFY_H
#define _CEEXTQUALIFY_H

#include "dbplus.h"
#include <string>

using namespace std;

////////////////////////////////////
// Store external qualify package //
////////////////////////////////////
class CExtQualify
{
public:
	string m_UserID;
	string m_VarID;
	double m_Value;
};

////////////////////////////////////
// Handle external qualify values //
////////////////////////////////////
class CceExtQualify : public CDbPlus
{
public:
	CceExtQualify(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string user_id, string varid, string value, string event_date);
	const char *Edit(int socket, int system_id, string id, string user_id, string varid, string value, string event_date);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);

	// Internal use in commissions //
	bool ReadInData(int socket, int system_id, string startdate, string enddate, list <CExtQualify> *pExtQualifyList);

private:
	CDb *m_pDB;
};

#endif