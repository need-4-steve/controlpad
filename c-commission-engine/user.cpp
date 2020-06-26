#include "user.h"
#include "commissions.h"

#include <stdio.h>
#include <stdlib.h>
#include <sstream>

extern string g_Debug_UserID;

///////////////////////
// Set inital values //
///////////////////////
CUser::CUser()
{
	m_pSponsor = NULL;
	m_pParent = NULL;
	m_pAdvisor = NULL;
	m_UserType = 0;
	m_CarrerRank = 0;
	//m_StatCounted = false;

	// Final calculation data //
	m_Disabled = false;
	m_Rank = 0;
	m_CMRank = 0;
	m_TopRankLeg = 0;
	m_Breakage = false;
	m_PoolQualify = false;

	m_PersonalPurchase = 0;
	m_PersonalUsed = 0;

	// Only Level 1 //
	m_LvL1PersonalSales = 0;
	m_LvL1SignupCount = 0;
	m_LvL1CustomerCount = 0;
	m_LvL1AffiliateCount = 0;
	m_LvL1ResellerCount = 0;
	m_LvL1MyWholesaleSales = 0;
	m_LvL1MyRetailSales = 0;

	// United //
	m_MyCustomerWholesaleSales = 0;

	// All generations //
	m_GroupWholesaleSales = 0;
	m_GroupRetailSales = 0;
	m_GroupUsed = 0;
	m_SignupCount = 0;
	m_AllSignupCount = 0;

	m_AffiliateWholesaleSales = 0;
	m_AffiliateRetailSales = 0;
	m_CustomerWholesaleSales = 0;
	m_CustomerRetailSales = 0;
	m_ResellerWholesaleSales = 0;
	m_ResellerRetailSales = 0;

	m_TeamWholesaleSales = 0;
	m_TeamRetailSales = 0;

	m_CorpWholeSales = 0; // Sales fulfilled by corporate //
	m_CorpRetailSales = 0; // Sales fulfilled by corporate //

	m_AffiliateCount = 0; 
	m_CustomerCount = 0;
	m_ResellerCount = 0;

	m_PSQ = 0;

	m_PVItemCountWholesale = 0;
	m_PVItemCountRetail = 0;

	m_EVItemCountWholesale = 0;
	m_EVItemCountRetail = 0;

	// Hope5000 //
	m_DacCount = 0;
	m_TopLegDacCount = 0;

	// The ultimate final result //
	m_Commission = 0;
	m_AchvBonus = 0;
	m_PoolPayout = 0;

	m_UniqueUsersReceipts = 0;

	m_RuleGroupPassed = 0;
}

//////////////////////////
// Build level 1 values //
//////////////////////////
void CUser::BuildLVL1()
{
	std::list <CUser*>::iterator i;
	for (i=m_CommLegsLL.begin(); i!=m_CommLegsLL.end(); ++i)
	{
		if ((*i)->m_UserType == USERTYPE_RESELLER)
			m_LvL1ResellerCount++;
		else if ((*i)->m_UserType == USERTYPE_CUSTOMER)
			m_LvL1CustomerCount++;
		else if ((*i)->m_UserType == USERTYPE_AFFILIATE)
			m_LvL1AffiliateCount++;

		m_LvL1SignupCount++;
		m_LvL1PersonalSales += (*i)->m_PersonalPurchase;
	}
}

//////////////////////
// Build all levels //
//////////////////////
void CUser::BuildAllLevels(std::map <std::string, std::string> &UserBuildMap)
{
	//Debug(DEBUG_DEBUG, "CUser::BuildAllLevels - TOP");

	std::list <CUser*>::iterator i;
	for (i=m_CommLegsLL.begin(); i!=m_CommLegsLL.end(); ++i)
	{
		//Debug(DEBUG_DEBUG, "CUser::BuildAllLevels - Before (*i)->BuildAllLevels");

		(*i)->BuildAllLevels(UserBuildMap); // Go recurrsive //

		//Debug(DEBUG_DEBUG, "CUser::BuildAllLevels - After (*i)->BuildAllLevels");

		if (UserBuildMap[(*i)->m_UserID] != "true")
		//if ((*i)->m_StatCounted == false)
		{
			UserBuildMap[(*i)->m_UserID] = "true";
			//(*i)->m_StatCounted = true;

			// Build statistic values //
			if ((*i)->m_UserType == USERTYPE_AFFILIATE)
			{
				m_AffiliateCount++;
				m_AffiliateCount += (*i)->m_AffiliateCount;
			}
			else if ((*i)->m_UserType == USERTYPE_CUSTOMER)
			{
				m_CustomerCount++;
				m_CustomerCount += (*i)->m_CustomerCount;
			}
			else if ((*i)->m_UserType == USERTYPE_RESELLER)
			{
				m_ResellerCount++;
				m_ResellerCount += (*i)->m_ResellerCount;
			}

			m_AllSignupCount++;
			m_AllSignupCount += (*i)->m_ResellerCount + (*i)->m_CustomerCount + (*i)->m_AffiliateCount;

			// Group Sales calculated in commissions.cpp //
			//m_GroupSales += (*i)->m_PersonalPurchase;
		}
	}

	//Debug(DEBUG_DEBUG, "CUser::BuildAllLevels - BOTTOM");
}

////////////////////////////////
// Stops at active affiliates //
////////////////////////////////
void CUser::BuildCustomerLevels()
{
	std::list <CUser*>::iterator i;
	for (i=m_CommLegsLL.begin(); i!=m_CommLegsLL.end(); ++i)
	{
		if (((*i)->m_UserType == 1) && ((*i)->m_Disabled == false)) // Breakage at active affiliates //
		{
			// Stop if affiliate is active //
		}
		else // Build values from customers, converted to customers and skip past disabled affiliates //
		{
			(*i)->BuildCustomerLevels(); // Go recurrsive //
		}

		// Build statistic values //
		if ((*i)->m_UserType == USERTYPE_CUSTOMER)
		{
			m_MyCustomerWholesaleSales += (*i)->m_LvL1MyWholesaleSales;
		}
	}
}

/////////////////////////////////////////////////
// Build the Enterprise Volume count Wholesale //
/////////////////////////////////////////////////
void CUser::BuildEVItemCount(int wholesale_count, int retail_count)
{
	if (m_pSponsor == NULL)
		return;

	m_EVItemCountWholesale += wholesale_count;
	m_EVItemCountRetail += retail_count;

	m_pSponsor->BuildEVItemCount(wholesale_count, retail_count);
}

/////////////////////////////////////////////
// This needed for binary commission plans //
/////////////////////////////////////////////
CUser *CUser::Find2ndBestLeg()
{
	if (m_CommLegsLL.size() == 0)
		return 0;

	if (m_CommLegsLL.size() == 1)
	{
		return 0;

		// What if only 1 leg? //
		//std::list <CUser*>::iterator i;
		//i=m_CommLegsLL.begin(); // Go to the beginning //
		//return *i;
	}

	m_CommLegsLL.sort(compare_commlegs); // Sort with largest group sales up top //
	std::list <CUser*>::iterator i;
	i=m_CommLegsLL.begin(); // Go to the beginning //
	++i; // Go to the 2nd leg //

	return *i;
}

/////////////////////////////////////////////
// This needed for binary commission plans //
/////////////////////////////////////////////
CUser *CUser::Find1stBestLeg()
{
	if (m_CommLegsLL.size() == 0)
		return 0;

	// What if only 1 leg? //
	std::list <CUser*>::iterator i;
	i=m_CommLegsLL.begin(); // Go to the beginning //
	return *i;
}

//////////////////////////
// Find the top leg DAC //
//////////////////////////
int CUser::FindTopLegDAC()
{
	if (m_TopLegDacCount != 0)
		return m_TopLegDacCount;

	if (m_CommLegsLL.size() == 0)
		return 0;

	if (m_CommLegsLL.size() == 1)
	{
		return 0;

		// What if only 1 leg? //
		//std::list <CUser*>::iterator i;
		//i=m_CommLegsLL.begin(); // Go to the beginning //
		//return *i;
	}

	m_CommLegsLL.sort(compare_daclegs); // Sort with largest group sales up top //
	std::list <CUser*>::iterator i;
	i=m_CommLegsLL.begin(); // Go to the beginning //

	// Store for comparison later //
	m_TopLegDacCount = (*i)->m_AffiliateCount+(*i)->m_CustomerCount;

	// Return the DAC count //
	return m_TopLegDacCount;
}

/*
///////////////////////////////////////
// Grab the rank count of advisor LL //
///////////////////////////////////////
int CUser::AdvisorLLRankCount(int rank, int rankmax, map <string, CUser> &UsersMap)
{
	int rankcount = 0;

	list <CUser*>::iterator i;
	for (i=m_AdvisorLegsLL.begin(); i!=m_AdvisorLegsLL.end(); ++i)
	{
		// Exact rank //
		if (((*i)->m_Rank >= rank) && ((*i)->m_Rank <= rankmax))
		{
			if ((m_UserID == g_Debug_UserID) && ((*i)->m_Rank >= 4))
			{
				stringstream ssTest;
				ssTest << "CUser::AdvisorLLRankCount - rank=" << rank << ", rankmax=" << rankmax << ", (*i).m_Rank=" << (*i)->m_Rank << ", (*i).m_UserID=" << (*i)->m_UserID;
				//Debug(DEBUG_ERROR, ssTest.str().c_str());
			}

			rankcount++;
		}
		else // Allow further drill down on users legs for 1st generation rank count //
		{
			rankcount += UsersMap[(*i)->m_UserID].AdvisorLLRankCount(rank, rankmax, UsersMap);
		}
	}

	return rankcount;
}
*/

///////////////////////////////////////
// Grab the rank count of advisor LL //
///////////////////////////////////////
int CUser::AdvisorLvl1RankCount(int rank, int rankmax, map <string, CUser> &UsersMap)
{
	int rankcount = 0;

	list <CUser*>::iterator i;
	for (i=m_AdvisorLegsLL.begin(); i!=m_AdvisorLegsLL.end(); ++i)
	{
		// Exact rank //
		if (((*i)->m_Rank >= rank) && ((*i)->m_Rank <= rankmax))
		{
			if ((m_UserID == g_Debug_UserID) && ((*i)->m_Rank >= 4))
			{
				stringstream ssTest;
				ssTest << "CUser::AdvisorLLRankCount - rank=" << rank << ", rankmax=" << rankmax << ", (*i).m_Rank=" << (*i)->m_Rank << ", (*i).m_UserID=" << (*i)->m_UserID;
				//Debug(DEBUG_ERROR, ssTest.str().c_str());
			}

			rankcount++;
		}
	}

	return rankcount;
}

///////////////////////////////////////
// Grab the rank count of advisor LL //
///////////////////////////////////////
int CUser::AdvisorLegRankCount(int rank, int rankmax, map <string, CUser> &UsersMap)
{
	int rankcount = 0;

	list <CUser*>::iterator i;
	for (i=m_AdvisorLegsLL.begin(); i!=m_AdvisorLegsLL.end(); ++i)
	{
		// Exact rank //
		if (((*i)->m_Rank >= rank) && ((*i)->m_Rank <= rankmax))
		{
			if ((m_UserID == g_Debug_UserID) && ((*i)->m_Rank >= 4))
			{
				stringstream ssTest;
				ssTest << "CUser::AdvisorLLRankCount - rank=" << rank << ", rankmax=" << rankmax << ", (*i).m_Rank=" << (*i)->m_Rank << ", (*i).m_UserID=" << (*i)->m_UserID;
				//Debug(DEBUG_ERROR, ssTest.str().c_str());
			}

			rankcount++;
		}
		else // Allow further drill down on users legs for 1st generation rank count //
		{
			rankcount += UsersMap[(*i)->m_UserID].AdvisorLegRankCount(rank, rankmax, UsersMap);
		}
	}

	return rankcount;
}

///////////////////////////////////////
// Grab the rank count of advisor LL //
///////////////////////////////////////
string CUser::AdvisorLvl1RankString(int rank, int rankmax, map <string, CUser> &UsersMap)
{
	string retstr;
	list <CUser*>::iterator i;
	for (i=m_AdvisorLegsLL.begin(); i!=m_AdvisorLegsLL.end(); ++i)
	{
		// Exact rank //
		if (((*i)->m_Rank >= rank) && ((*i)->m_Rank <= rankmax))
		{
			if ((m_UserID == g_Debug_UserID) && ((*i)->m_Rank >= 4))
			{
				stringstream ssTest;
				ssTest << "CUser::AdvisorLLRankCount - rank=" << rank << ", rankmax=" << rankmax << ", (*i).m_Rank=" << (*i)->m_Rank << ", (*i).m_UserID=" << (*i)->m_UserID;
				//Debug(DEBUG_ERROR, ssTest.str().c_str());
			}

			retstr += (*i)->m_UserID+",";
		}
	}

	return retstr;
}

///////////////////////////////////////
// Grab the rank count of advisor LL //
///////////////////////////////////////
string CUser::AdvisorLegRankString(int rank, int rankmax, map <string, CUser> &UsersMap)
{
	string retstr;
	list <CUser*>::iterator i;
	for (i=m_AdvisorLegsLL.begin(); i!=m_AdvisorLegsLL.end(); ++i)
	{
		// Exact rank //
		if (((*i)->m_Rank >= rank) && ((*i)->m_Rank <= rankmax))
		{
			if ((m_UserID == g_Debug_UserID) && ((*i)->m_Rank >= 4))
			{
				stringstream ssTest;
				ssTest << "CUser::AdvisorLLRankCount - rank=" << rank << ", rankmax=" << rankmax << ", (*i).m_Rank=" << (*i)->m_Rank << ", (*i).m_UserID=" << (*i)->m_UserID;
				//Debug(DEBUG_ERROR, ssTest.str().c_str());
			}

			retstr += (*i)->m_UserID+",";
		}
		else // Allow further drill down on users legs for 1st generation rank count //
		{
			retstr += UsersMap[(*i)->m_UserID].AdvisorLegRankCount(rank, rankmax, UsersMap)+",";
		}
	}

	return retstr;
}
 
///////////////////////////////////////////////////////
// Increment unique users on receipts through upline //
///////////////////////////////////////////////////////
void CUser::IncrUniqueUsersReceipts()
{
	//Debug(DEBUG_ERROR, "CUser::IncrUniqueUsersReceipts - TOP");

	m_UniqueUsersReceipts++;

	//Debug(DEBUG_ERROR, "CUser::IncrUniqueUsersReceipts - TOP 1.1");

	if (m_pSponsor == NULL)
	{
		//Debug(DEBUG_ERROR, "CUser::IncrUniqueUsersReceipts - m_pSponsor == NULL");
		return;
	}
	else if (m_pSponsor->m_UserID.size() == 0)
	{
		//Debug(DEBUG_ERROR, "CUser::IncrUniqueUsersReceipts - m_pSponsor->m_UserID.size() == 0");
		return;
	}

	//Debug(DEBUG_ERROR, "CUser::IncrUniqueUsersReceipts - MID 1.2");

	if (m_pSponsor != NULL)
	{
		//Debug(DEBUG_ERROR, "CUser::IncrUniqueUsersReceipts - m_pSponsor->m_UserID", m_pSponsor->m_UserID.c_str());
		m_pSponsor->IncrUniqueUsersReceipts();
	}
}

////////////////////////////////////////
// Compare for binary commission type //
////////////////////////////////////////
bool compare_commlegs(const CUser *first, const CUser *second)
{
	if (first->m_GroupWholesaleSales > second->m_GroupWholesaleSales)
		return true;
	
	return false;
}

/////////////////////////
// Compare for top DAC //
/////////////////////////
bool compare_daclegs(const CUser *first, const CUser *second)
{
	if (first->m_AffiliateCount+first->m_CustomerCount > second->m_AffiliateCount+second->m_CustomerCount)
		return true;
	
	return false;
}
