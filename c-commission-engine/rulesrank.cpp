#include "rulesrank.h"

#include <memory.h>
#include <stdio.h>

//////////////////////
// Reset all values //
//////////////////////
CRulesRank::CRulesRank()
{
	m_SystemID = 0;
	m_ID = 0;
	m_Rank = 0;
	m_QualifyType = 0; 
	m_QualifyThreshold = 0; 
	m_AchvBonus = 0; 
	m_Breakage = 0; 
	m_RuleGroup = 0;
	m_MaxDacLeg = 0;
	m_SumRankStart = 0; 
	m_SumRankEnd = 0; 
	m_EndFlag = false;
	memset(m_Label, 0, 100);
}

///////////////////
// Copy the rule //
///////////////////
void CRulesRank::Copy(CRulesRank *RuleRank)
{
	m_ID = RuleRank->m_ID;
	m_Rank = RuleRank->m_Rank;
	m_QualifyType = RuleRank->m_QualifyType; 
	m_QualifyThreshold = RuleRank->m_QualifyThreshold; 
	m_AchvBonus = RuleRank->m_AchvBonus; 
	m_Breakage = RuleRank->m_Breakage; 
	m_RuleGroup = RuleRank->m_RuleGroup;
	m_MaxDacLeg = RuleRank->m_MaxDacLeg;
	m_SumRankStart = RuleRank->m_SumRankStart;
	m_SumRankEnd = RuleRank->m_SumRankEnd;
	m_EndFlag = RuleRank->m_EndFlag;
	sprintf(m_Label, "%s", RuleRank->m_Label);
}

/////////////////////////////////////
// Needed for carrer_rank updating //
/////////////////////////////////////
CRulesRankTmp::CRulesRankTmp()
{
	m_Rank = 0;
	m_Count = 0;
}
