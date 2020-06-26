#ifndef _CESIGNUPBONUS_H
#define _CESIGNUPBONUS_H

#include "dbplus.h"
#include "dbbulk.h"
#include <string>

using namespace std;

/////////////////////////
// Handle signup bonus //
/////////////////////////
class CceSignupBonus : private CDbPlus, CDbBulk
{
public:
	CceSignupBonus(CDb *pDB, string origin);
	
	// For external viewing //
	const char *Query(int socket, int system_id, string search, string sort);

	// Internal on commission run //
	bool AddBulk(bool pretend, int socket, int system_id, string user_id, string from_user_id, int batch_id, string signupbonus);
	bool FinishBulk(bool pretend, int socket); // Do the last flush of adding //
	
private:
	CDb *m_pDB;

	int m_AddCount;
	string m_strAddSQL;
};

#endif