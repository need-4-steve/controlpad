#ifndef _CESYSTEM_USER_H
#define _CESYSTEM_USER_H

#include "db.h"
#include "ezJson.h"
#include "debug.h"
#include "validate.h"
#include "convert.h"

#include <string>

using namespace std;

class CceSystemUser : CezJson, CValidate, CDebug, CConvert
{
public:
	CceSystemUser(CDb *pDB, string origin);

	const char *AuthSessionUser(int socket, const char *email, const char *authpass, const char *ipaddress, string remoteaddress);

	const char *IndexStats(int socket, int system_id);

	// Password and Login functions //
	const char *UserValidCheck(int socket, string email);
	const char *PasswordHashGen(int socket, string email, string remoteaddress);
	const char *PasswordHashValid(int socket, string hash);
	const char *PasswordHashUpdate(int socket, string hash);
	bool LoginLog(int socket, string email, string remoteaddress);
	const char *LogoutLog(int socket, string email);

	// The OTHER functions //
	const char *Add(int socket, string firstname, string lastname, string email, string password, string remoteaddress, string ipaddress); 
	const char *AddRuby(int socket, string email, string password); // United Override Function for default 127.0.0.1 //
	const char *Edit(int socket, int coresysuser_id, int sysuser_id, string email, string password, string ipaddress);
	const char *Query(int socket);
	const char *Disable(int socket, int coresysuser_id, int sysuser_id);
	const char *Enable(int socket, int coresysuser_id, int sysuser_id);

	const char *ReissueApiKey(int socket, int sysuser_id);
	const char *ResetPassword(int socket, int coresysuser_id, string sysuser_id, string password);

private:
	CDb *m_pDB;
};

#endif