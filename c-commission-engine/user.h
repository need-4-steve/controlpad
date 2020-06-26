#ifndef _COMMENGINE_USER_H
#define _COMMENGINE_USER_H

#include "debug.h"
#include "receipts.h"
#include <string>
#include <list>
#include <map>

/////////////////////////////
// Retain User Information //
/////////////////////////////
class CUser : CDebug
{
public:
	CUser();
 
	void BuildLVL1();
	void BuildAllLevels(std::map <std::string, std::string> &UserBuildMap);
	void BuildCustomerLevels(); // Stops at active affiliates //

	// Needed for customer/affiliate counts //
	int BuildAffiliateCount(bool debug);
	int BuildCustomerCount(bool debug);
	int BuildCustLVLOneCount();

	void BuildEVItemCount(int wholesale_count, int retail_count);

	// This needed for binary commission plans //
	CUser *Find2ndBestLeg(); 
	CUser *Find1stBestLeg();
	//double AllLegSales();

	// Find the top leg DAC //
	int FindTopLegDAC();

	//int AdvisorLLRankCount(int rank, int rankmax, map <string, CUser> &UsersMap);

	int AdvisorLvl1RankCount(int rank, int rankmax, map <string, CUser> &UsersMap);
	int AdvisorLegRankCount(int rank, int rankmax, map <string, CUser> &UsersMap);
	string AdvisorLvl1RankString(int rank, int rankmax, map <string, CUser> &UsersMap);
	string AdvisorLegRankString(int rank, int rankmax, map <string, CUser> &UsersMap);

	void IncrUniqueUsersReceipts();

	// Data needed for calculaitons //
	unsigned char m_UserType;
	string m_UserID;
	string m_SponsorID; //
	string m_ParentID; // Used in check match //
	string m_AdvisorID; // Chalkatour compression final parent //
	string m_SignupDate;
	int m_CarrerRank;
	CUser *m_pParent; // Point to the sponsor //
	CUser *m_pSponsor; // Point to the sponsor //
	CUser *m_pAdvisor; // Point to the advisor //
	string m_UplineAdvisor;
	//std::list <CReceipt> m_ReceiptsLL;
	list <CUser*> m_CommLegsLL; // Keep a list of all immediate legs... to identify the 2nd largest //
	list <CUser*> m_AdvisorLegsLL; // Allow compression of advisor updates //

	// Final calculation data //
	bool m_Disabled;
	int m_Rank;
	int m_CMRank; // Check Match Rank is Separate //
	int m_TopRankLeg; // What is the highest rank in this leg? //
	string m_TopUserLeg; // Who is the top user on the leg //
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
	int m_LvL1ResellerCount;
	double m_LvL1MyWholesaleSales;
	double m_LvL1MyRetailSales;

	// United Needed //
	double m_MyCustomerWholesaleSales; // This doesn't go down active affiliates to get customer values //

	// All generations //
	double m_GroupWholesaleSales; // Tokens Purchased //
	double m_GroupRetailSales; // Tokens Purchased //
	double m_GroupUsed; // Tokens Played //
	int m_SignupCount;
	int m_AllSignupCount;
	double m_ResellerWholesaleSales;
	double m_ResellerRetailSales;
	double m_CustomerWholesaleSales;
	double m_CustomerRetailSales;
	double m_AffiliateWholesaleSales;
	double m_AffiliateRetailSales;

	double m_TeamWholesaleSales;
	double m_TeamRetailSales;

	double m_CorpWholeSales; // Sales fulfilled by corporate //
	double m_CorpRetailSales; // Sales fulfilled by corporate //

	int m_AffiliateCount; 
	int m_CustomerCount;
	int m_ResellerCount;

	int m_PSQ; // PersonallySponsoredQualified;

	int m_PVItemCountWholesale;
	int m_PVItemCountRetail;

	int m_EVItemCountWholesale;
	int m_EVItemCountRetail;

	// Hope5000 //
	int m_DacCount;
	int m_TopLegDacCount;

	// The ultimate final result //
	double m_Commission;
	double m_AchvBonus;
	double m_PoolPayout;

	// How many unique users as per all receipt in given time period //
	int m_UniqueUsersReceipts;

	bool m_RuleGroupPassed;
};

///////////////////////////////////////////////////////////////////////////////////////////
// We need a compare function for the commission legs. Needed for binary commission type //
///////////////////////////////////////////////////////////////////////////////////////////
bool compare_commlegs(const CUser *first, const CUser *second);
bool compare_daclegs(const CUser *first, const CUser *second);

#endif