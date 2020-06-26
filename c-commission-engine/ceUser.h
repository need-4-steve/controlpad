#ifndef _CEUSER_H
#define _CEUSER_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceUser : public CDbPlus
{
public:
	// Ruby Rice //
	CceUser(); 
	string AddRuby(int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype);
	string EditRuby(int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype);
	string DisableRuby(int system_id, string user_id);
	string EnableRuby(int system_id, string user_id);

	// Standard Commission Engine //
	CceUser(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype,
		string firstname, string lastname, string email, string cell, string address, string city, string state, string zip);
	const char *Edit(int socket, int system_id, string user_id, string parent_id, string sponsor_id, string signup_date, string usertype,
		string firstname, string lastname, string email, string cell, string address, string city, string state, string zip);
	const char *UpdateAddress(int socket, int system_id, string user_id, string address, string city, string state, string zip);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *Disable(int socket, int system_id, string user_id);
	const char *Enable(int socket, int system_id, string user_id);
	const char *Get(int socket, int system_id, string user_id);

	bool RebuildAllUpline(CDb *pDB, int socket, int system_id);
	bool RebuildAdvisorUpline(CDb *pDB, int socket, int system_id, map <string, CUser> &UsersMap);

private:
	string BuildUplineDB(int socket, int system_id, string parent_id, int upline_type);
	string BuildUplineLocal(int socket, string user_id, int upline_type);

	string BuildUplineAdvisor(int socket, string user_id, int upline_type, map <string, CUser> &UsersMap);

	map <string, CUser> m_UsersMap;

private:
	CDb *m_pDB = NULL;
	string m_Retval;
};

#endif