#ifndef _CEAFFILIATE_H
#define _CEAFFILIATE_H

#include "dbplus.h"
#include <string>

using namespace std;

class CceAffiliate : public CDbPlus
{
public:
	CceAffiliate(CDb *pDB, string origin);

	// Password and Login functions //
	const char *UserValidCheck(int socket, int system_id, string email);
	const char *PasswordHashGen(int socket, int system_id, string email, string remoteaddress);
	const char *PasswordHashValid(int socket, string hash);
	const char *PasswordHashUpdate(int socket, string hash);
	const char *LoginLog(int socket, int system_id, string email, string remoteaddress);
	const char *LogoutLog(int socket, int system_id, string user_id);

	// Other MY functions //
	const char *PasswordReset(int socket, int system_id, string user_id, string password);
	bool Login(int socket, int system_id, string enduseremail, string enduserpass);
	const char *GetUserID(int socket, int system_id, string enduseremail);
	const char *MyProjections(int socket, int system_id, string userid, string startdate, string enddate);
	const char *MyCommissions(int socket, int system_id, string userid, string search, string sort);
	const char *MyAchvBonus(int socket, int system_id, string userid, string search, string sort);
	const char *MyBonus(int socket, int system_id, string userid, string search, string sort);
	const char *MyRankGenBonus(int socket, int system_id, string userid, string search, string sort);
	const char *MyLedger(int socket, int system_id, string userid, string search, string sort);
	const char *MyBreakdown(int socket, int system_id, string userid, string search, string sort);
	const char *MyBreakdownGen(int socket, int system_id, string batch_id, string parentid);
	const char *MyBreakdownUsers(int socket, int system_id, string batch_id, string parentid, string generation);
	const char *MyBreakdownOrders(int socket, int system_id, string batch_id, string parentid, string userid);
	const char *MyDownlineLvl1(int socket, int system_id, string userid);
	const char *MyUpline(int socket, int system_id, string userid);
	const char *MyTopClose(int socket, int system_id, string userid);
	const char *MyRankRulesMissed(int socket, int system_id, string userid, string search, string sort);

	const char *MyStats(int socket, int system_id, string userid, string search, string sort);
	const char *MyStatsLvl1(int socket, int system_id, string userid, string search, string sort);

	const char *MyDownlineRankSumLvl1(int socket, int system_id, string batch_id, string userid); 
	const char *MyDownlineRankSum(int socket, int system_id, string batch_id, string userid, string generation);
	const char *MyTitle(int socket, int system_id, string batch_id, string userid);

	// My Downline information //
	const char *MyDownLineStats(int socket, int system_id, string userid, string search, string sort);
	const char *MyDownLineStatsLvl1(int socket, int system_id, string userid, string search, string sort);
	const char *MyDownLineStatsFull(int socket, int system_id, string userid, string batchid, string search, string sort);
	const char *MyDownLineStatsFullNew(int socket, int system_id, string userid, string batchid, string search, string sort);
	const char *MyDownLineStatsFullOld(int socket, int system_id, string userid, string batchid, string search, string sort);
	const char *MySponsoredStats(int socket, int system_id, string userid, string search, string sort);
	const char *MySponsoredStatsLvl1(int socket, int system_id, string userid, string search, string sort);

	const char *MyDownlineRankRulesMissed(int socket, int system_id, string userid, string search, string sort);

	const char *MyReceiptSum(int socket, int system_id, string userid, string inv_type, string startdate, string enddate);

private:
	string GetRandHash64(int deep);

	string m_Json;
	CDb *m_pDB;
};

#endif