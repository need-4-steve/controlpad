/////////////////
// rulescomm.h //
/////////////////
#ifndef _RULESBASICCOMM_H
#define _RULESBASICCOMM_H

/////////////////////////////
// Handle commission rules //
/////////////////////////////
class CRulesBasicComm
{
public:
	CRulesBasicComm(); // Reset all values //

	int m_ID;
	int m_Generation; // Starting Generation the rule applies //
	int m_InvType;
	int m_Event; // Wholesale or Retail? //
	double m_Percent; // What percent payout //
	int m_Modulus; // Incremental payout. Every $20? //
	int m_QualifyType; // PERSONAL_SALES, GROUP_SALES, SIGNUP_COUNT //
	double m_StartThreshold; // Either dollar amount or Number of signups //
	double m_EndThreshold; // Either dollar amount or Number of signups //
	int m_PayLimit; // Cutoff at limit // Chalkatour needed //
	bool m_PVOverride; // Allow calc on personal volume and not just up the receipt ladder //
	int m_PayType; // Wholesale or Retail price for commission Calculation //
	int m_Rank; // Allow rank parameter //

	//bool m_Compress; // Allow payout to move past unqualifiers up the ladder to find full payout //
	//bool m_Binary; // Do we take the two highest legs and payout the lesser of the two? //
};

#endif