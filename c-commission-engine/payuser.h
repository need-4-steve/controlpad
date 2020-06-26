#ifndef _PAYUSER_H
#define _PAYUSER_H

#include <string>

///////////////////////////////////////
// Needed LL for payments to process //
///////////////////////////////////////
class CPayUser
{
public:
	CPayUser();

	std::string m_UserID;
	int m_AccountType;
	std::string m_RoutingNumber;
	std::string m_AccountNumber;
	std::string m_HolderName;
	double m_Commission;
};

#endif