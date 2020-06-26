/////////////////
// rulescomm.h //
/////////////////
#ifndef _RULESCOMM_H
#define _RULESCOMM_H

// Predefine event types //
#define EVENT_WHOLESALE		1
#define EVENT_RETAIL		2

/////////////////////////////
// Handle commission rules //
/////////////////////////////
class CRulesComm
{
public:
	CRulesComm(); // Reset all values //

	int m_ID;
	int m_Rank; // Rank you need to be for rule to apply //
	int m_Generation; // Starting Generation the rule applies //
	bool m_InfinityBonus;
	double m_Percent; // What percent payout //
	double m_Dollar; // What dollar payout //
	int m_InvType;
	double m_PrePercent; // What percent payout //
	bool m_Compress; // Allow payout to move past unqualifiers up the ladder to find full payout //
	bool m_Binary; // Do we take the two highest legs and payout the lesser of the two? //
	int m_Event; // Wholesale or Retail? //
	int m_PayType; // Commission on wholesale or retail //
	bool m_ForcePay;
	
	// We need to track how much per each rule //
	double m_InfinityTotal;
};

#endif