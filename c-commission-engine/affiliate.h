#ifndef _COMMENGINE_AFFILIATE_H
#define _COMMENGINE_AFFILIATE_H

#include "debug.h"
#include "receipts.h"
#include "user.h"
#include <list>
#include <map>

/////////////////////////////
// Retain User Information //
/////////////////////////////
class CAffiliate
{
public:
	CAffiliate();

	void BuildLVL1();
	void BuildAllLevels(std::map <std::string, std::string> &UserBuildMap);

	// Needed for customer/affiliate counts //
	int BuildAffiliateCount(bool debug);
	int BuildCustomerCount(bool debug);
	int BuildCustLVLOneCount();

	// This needed for binary commission plans //
	CUser *Find2ndBestLeg(); 
	CUser *Find1stBestLeg();
	//double AllLegSales();

	// Find the top leg DAC //
	int FindTopLegDAC();

	// Data needed for calculaitons //
	std::string m_UserID;
	std::string m_SponsorID; //
	std::string m_ParentID; // Used in check match //
	int m_UserType;
	std::string m_SignupDate;
	CUser *m_pSponsor; // Point to the sponsor //
	std::list <CReceipt> m_ReceiptsLL;
	//std::list <int> m_CommLegsLL; // Keep a list of all immediate legs... to identify the 2nd largest //
	std::list <CUser*> m_CommLegsLL;
	
	// Handle counting to prevent recurrsion problems //
	//bool m_StatCounted;
	//std::map <int, std::string> m_ReceiptLadderMap;
	//std::map <int, std::string> m_ReceiptHybridMap;
	//std::map <int, std::string> m_ReceiptBreakawayMap;
	//std::string m_CMCommissionMap;
	//std::string m_CMUsedMap;

	// Final calculation data //
	int m_Rank;
	bool m_Breakage; // Are they part of their own branch? //
	bool m_PoolQualify;

	///////////
	// Stats //
	///////////
	double m_PersonalPurchase; // Personal Token Purchased //
	double m_PersonalUsed; // Personal Tokens Used //

	// Only Level 1 //
	double m_LvL1PersonalSales;
	int m_LvL1SignupCount;
	int m_LvL1CustomerCount;
	int m_LvL1AffiliateCount;

	// All generations //
	double m_GroupSales; // Tokens Purchased //
	double m_GroupUsed; // Tokens Played //
	int m_SignupCount;
	int m_AllSignupCount;
	double m_AffiliateSales;
	double m_CustomerSales;
	int m_AffiliateCount; 
	int m_CustomerCount;

	// Hope5000 //
	int m_DacCount;
	int m_TopLegDacCount;

	// The ultimate final result //
	double m_Commission;
	double m_AchvBonus;
	double m_PoolPayout;
};

///////////////////////////////////////////////////////////////////////////////////////////
// We need a compare function for the commission legs. Needed for binary commission type //
///////////////////////////////////////////////////////////////////////////////////////////
//bool compare_commlegs(const CUser *first, const CUser *second);
//bool compare_daclegs(const CUser *first, const CUser *second);

#endif