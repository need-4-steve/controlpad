#include "rulesbasiccomm.h"

////////////////////////
// Set initial values //
////////////////////////
CRulesBasicComm::CRulesBasicComm()
{
	m_ID = 0;
	m_Generation = 0; // Starting Generation the rule applies //
	m_InvType = 0;
	m_Event = 0; // Wholesale or Retail? //
	m_Percent = 0; // What percent payout //
	m_Modulus = 0; // Incremental payout. Every $20? //
	m_QualifyType = 0; 
	m_StartThreshold = 0;
	m_EndThreshold = 0; 
	m_PayLimit = 0;
	m_PVOverride = false;
	m_PayType = 0;
	m_Rank = 0;
}
