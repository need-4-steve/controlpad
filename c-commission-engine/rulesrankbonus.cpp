#include "rulesrankbonus.h"

#include <memory.h>
#include <stdio.h>

//////////////////////
// Reset all values //
//////////////////////
CRulesRankBonus::CRulesRankBonus()
{
	int m_ID; // We ID for updating records later //
	int m_Rank; // What rank level does this apply to? //
	double m_Bonus; // What type of Achievement bonus do they get //

	m_ID = 0;
	m_Rank = 0;
	m_Bonus = 0; 
}

///////////////////
// Copy the rule //
///////////////////
void CRulesRankBonus::Copy(CRulesRankBonus *RuleRankBonus)
{
	m_ID = RuleRankBonus->m_ID;
	m_Rank = RuleRankBonus->m_Rank;
	m_Bonus = RuleRankBonus->m_Bonus; 
}
