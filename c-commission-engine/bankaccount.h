#ifndef _BANKACCOUNT_H
#define _BANKACCOUNT_H

#include <string>

/////////////////////////////////////
// Package bankaccount information //
/////////////////////////////////////
class CBankAccount
{
public:
	CBankAccount();

	int m_ID;
	int m_UserID;
	int m_AccountType;
	std::string m_RoutingNumber;
	std::string m_AccountNumber;
	std::string m_HolderName;

	// Account verification //
	double m_Amount1;
	double m_Amount2;

private:

	std::string m_JSON;
};

#endif