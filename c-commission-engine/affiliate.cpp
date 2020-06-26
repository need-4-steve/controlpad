
#include "affiliate.h"
#include "commissions.h"

#include <stdio.h>

#include <sstream>

///////////////////////
// Set inital values //
///////////////////////
CAffiliate::CAffiliate()
{
	m_pSponsor = NULL;
	m_UserType = 0;
	//m_StatCounted = false;

	// Final calculation data //
	m_Rank = 0;
	m_Breakage = false;
	m_PoolQualify = false;

	m_PersonalPurchase = 0;
	m_PersonalUsed = 0;

	// Only Level 1 //
	m_LvL1PersonalSales = 0;
	m_LvL1SignupCount = 0;
	m_LvL1CustomerCount = 0;
	m_LvL1AffiliateCount = 0;

	// All generations //
	m_GroupSales = 0;
	m_GroupUsed = 0;
	m_SignupCount = 0;
	m_AllSignupCount = 0;
	m_AffiliateSales = 0;
	m_CustomerSales = 0;
	m_AffiliateCount = 0; 
	m_CustomerCount = 0;

	// Hope5000 //
	m_DacCount = 0;
	m_TopLegDacCount = 0;

	// The ultimate final result //
	m_Commission = 0;
	m_AchvBonus = 0;
	m_PoolPayout = 0;
}

//////////////////////////
// Build level 1 values //
//////////////////////////
void CAffiliate::BuildLVL1()
{
	std::list <CUser*>::iterator i;
	for (i=m_CommLegsLL.begin(); i!=m_CommLegsLL.end(); ++i)
	{
		if ((*i)->m_UserType == USERTYPE_AFFILIATE)
			m_LvL1AffiliateCount++;
		else if ((*i)->m_UserType == USERTYPE_CUSTOMER)
			m_LvL1CustomerCount++;
		
		m_LvL1SignupCount++;
		m_LvL1PersonalSales += (*i)->m_PersonalPurchase;
	}
}

//////////////////////
// Build all levels //
//////////////////////
void CAffiliate::BuildAllLevels(std::map <std::string, std::string> &UserBuildMap)
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
				m_AffiliateCount += (*i)->m_AffiliateCount; //(*i)->m_AffiliateCount;
			//	m_AffiliateSales += (*i)->m_PersonalPurchase;
				//m_AffiliateSales += (*i)->m_AffiliateSales;
			}
			else if ((*i)->m_UserType == USERTYPE_CUSTOMER)
			{
				m_CustomerCount++;
			//	m_CustomerSales += (*i)->m_PersonalPurchase;
				//m_CustomerSales += (*i)->m_CustomerSales;
			}

			m_AllSignupCount += m_SignupCount;
			m_CustomerCount += (*i)->m_CustomerCount;

			// Group Sales calculated in commissions.cpp //
			//m_GroupSales += (*i)->m_PersonalPurchase;
		}
	}

	//Debug(DEBUG_DEBUG, "CUser::BuildAllLevels - BOTTOM");
}

/////////////////////////////////////////////
// This needed for binary commission plans //
/////////////////////////////////////////////
CUser *CAffiliate::Find2ndBestLeg()
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
CUser *CAffiliate::Find1stBestLeg()
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
int CAffiliate::FindTopLegDAC()
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
////////////////////////////////////////
// Compare for binary commission type //
////////////////////////////////////////
bool compare_commlegs(const CUser *first, const CUser *second)
{
	if (first->m_GroupSales > second->m_GroupSales)
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
*/