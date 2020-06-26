#ifndef _FASTSTARTRULES_H
#define _FASTSTARTRULES_H

//////////////////////////////////////
// Fast start for temp list storage //
//////////////////////////////////////
class CFastStartRules
{
public:
	CFastStartRules();
	int m_ID;
	int m_Rank;
	short m_QualifyType;
	double m_QualifyThreshold;
	short m_DaysCount;
	short m_RuleGroup;
};

#endif