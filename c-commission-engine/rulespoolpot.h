////////////////////
// rulespoolpot.h //
////////////////////
#ifndef _RULESPOOLPOT_H
#define _RULESPOOLPOT_H

#include <list>
#include <string>

#include "rulespool.h"
#include "receipts.h"

///////////////////////////
// Define the actual pot //
///////////////////////////
class CRulesPoolPot
{
public:
	CRulesPoolPot();
	double m_Amount; // How much is the pot worth //
	std::string m_PayoutDate; // All pools expire after a given date //

	std::list <CRulesPool> m_RulesPoolLL; // List of rules that apply to the pot //
};

#endif