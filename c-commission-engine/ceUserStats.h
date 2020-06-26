#ifndef _CEUSERSTATS_H
#define _CEUSERSTATS_H

#include "dbplus.h"
#include <string>

using namespace std;

//////////////////////////
// Manage the userstats //
//////////////////////////
class CceUserStats : public CDbPlus, CDbBulk
{
public:
	CceUserStats();
	bool AddBulk(CDb *pDB, int socket, int system_id, int batch_id, CUser *puser, string first_id, double firstsales, string second_id, double second_sales);
	bool FinishBulk(CDb *pDB, int socket);

	int m_UserStatMonthCount;
	string m_strStatMonthSQL;

	int m_UserStatMonthLVL1Count;
	string m_strStatMonthLVL1SQL;

	int m_UserStatMonthLegCount;
	string m_strStatMonthLegSQL;

private:
	CDb *m_pDB;
};

#endif