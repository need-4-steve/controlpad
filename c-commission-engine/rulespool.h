/////////////////
// rulespool.h //
/////////////////
#ifndef _RULESPOOL_H
#define _RULESPOOL_H

/////////////////////////////
// Handle commission rules //
/////////////////////////////
class CRulesPool
{
public:
	CRulesPool(); // Reset all values //

	int m_StartRank; // Start Rank you need to be for rule to apply //
	int m_EndRank; // End Rank you need to be for the rule to apply //
	//int m_StartGen; // Staring generation for the rule to apply //
	//int m_EndGen; // Ending generation for the rul to apply //
	//int m_QualifyType; // PERSONAL_SALES, GROUP_SALES or SIGN_UP count //
	double m_QualifyThreshold; // Either "dollar amount" or "number of signups" //

	//int m_PoolPotID; // Retain just in case //
};

#endif