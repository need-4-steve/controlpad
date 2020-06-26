/////////////////
// rulesrank.h //
/////////////////
#ifndef _RULESRANKBONUS_H
#define _RULESRANKBONUS_H 

#include <string>

using namespace std;

/////////////////////////////
// Handle commission rules //
/////////////////////////////
class CRulesRankBonus
{
public:
	CRulesRankBonus(); // Reset all values //
	void Copy(CRulesRankBonus *RuleRankBonus);

	int m_ID; // We ID for updating records later //
	int m_Rank; // What rank level does this apply to? //
	double m_Bonus; // What type of Achievement bonus do they get //
};

#endif