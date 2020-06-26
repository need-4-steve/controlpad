#ifndef _CESYSTEM_H
#define _CESYSTEM_H

#include "dbplus.h"

#include "ezJson.h"
#include "debug.h"
#include "validate.h"
#include <string>

using namespace std;

class CceSystem : public CDbPlus
{
public:

	// Ruby Access //
	CceSystem();
	string AddRuby(string systemname, string payout_type, string payout_monthday, string payout_weekday, string minpay, string signupbonus, string psqlimit, string compression);
	string EditRuby(int system_id, string systemname, string payout_type, string payout_monthday,
		string payout_weekday, string minpay, string signupbonus, string psqlimit, string compression);
	string DisableRuby(int system_id);
	string EnableRuby(int system_id);
	string GetRuby(int system_id);

	// Normal API //
	CceSystem(CDb *pDB, string origin);
	const char *Add(int socket, int sysuserid, string stacktype, string systemname, string commtype, string altcore, string payout_type, string payout_monthday, 
		string payout_weekday, string autoauthgrand, string infinitycap, string minpay, string updated_url, string updated_username, 
		string updated_password, string signupbonus, string teamgenmax, string piggyid, string psqlimit, string compression);
	const char *Edit(int socket, int sysuserid, int system_id, string stacktype, string systemname, string commtype, string altcore, string payout_type, string payout_monthday,
		string payout_weekday, string autoauthgrand, string infinitycap, string minpay, string updated_url, string updated_username, 
		string updated_password, string signupbonus, string teamgenmax, string piggyid, string psqlimit, string compression);
	const char *Query(int socket, int sysuser_id, string search, string sort);
	const char *Disable(int socket, int system_id);
	const char *Enable(int socket, int system_id);
	const char *Get(int socket, int system_id);
	const char *Count(int socket, int sysuser_id);
	const char *Stats(int socket, int system_id);

	// Ruby-rice only allows 10 parameters. Eliminate REST params for now on ruby-rice //
	const char *AddRuby(int socket, int sysuserid, string stacktype, string systemname, string commtype, string altcore, string payout_type, string payout_monthday, 
		string payout_weekday, string autoauthgrand, string infinitycap, string minpay);
	const char *EditRuby(int socket, int sysuserid, int system_id, string stacktype, string systemname, string commtype, string altcore, string payout_type, string payout_monthday,
		string payout_weekday, string autoauthgrand, string infinitycap, string minpay);

private:
	CDb *m_pDB;
	string m_Retval;

};

#endif