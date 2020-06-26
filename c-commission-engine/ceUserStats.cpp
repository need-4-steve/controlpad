#include "ceUserStats.h"

#include <stdlib.h>

extern string g_Debug_UserID;

/////////////////
// Constructor //
/////////////////
CceUserStats::CceUserStats()
{
	m_UserStatMonthCount = 0;
	m_UserStatMonthLVL1Count = 0;
	m_UserStatMonthLegCount = 0;
}

//////////////////////
// Add bulk records //
//////////////////////
bool CceUserStats::AddBulk(CDb *pDB, int socket, int system_id, int batch_id, CUser *puser, string first_id, double firstsales, string second_id, double second_sales)
{
	//CDbBulk::Debug(DEBUG_WARN, "CceUserStats::AddBulk - ADD USER STATS - TOP");

// Only handle stats for system_id = 1 for united //
#ifdef COMPILE_UNITED
	if (system_id != 1)
		return true; // Avoid errors on something like this //
#endif

	if (puser->m_UserID.size() == 0)
		return true;

	if ((puser->m_Disabled == true) && (pDB->m_pSettings->m_DisableUserStatsSQL == true))
		return true;

	/*
	// Debug user #1 //
	if (puser->m_UserID == "1")
	{
		CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::AddBulk - puser->m_AllSignupCount", puser->m_AllSignupCount);
		CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::AddBulk - puser->m_AffiliateCount", puser->m_AffiliateCount);
		CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::AddBulk - puser->m_ResellerCount", puser->m_ResellerCount);
		CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::AddBulk - puser->m_CustomerCount", puser->m_CustomerCount);
		exit(1);
	}
	*/

	//////////////////////
	// User Stats Month //
	//////////////////////
	//if ((puser->m_GroupWholesaleSales != 0) || 
	//	(puser->m_GroupRetailSales != 0) || 
	//	(puser->m_GroupUsed != 0) ||
	//	(puser->m_CustomerWholesaleSales != 0) ||
	//	(puser->m_CustomerRetailSales != 0) ||
	//	(puser->m_ResellerWholesaleSales != 0) ||
	//	(puser->m_ResellerRetailSales != 0) ||
	//	(puser->m_AffiliateWholesaleSales != 0) ||
	//	(puser->m_AffiliateRetailSales != 0) ||
	//	(puser->m_AllSignupCount != 0) ||
	//	(puser->m_AffiliateCount != 0) ||
	//	(puser->m_ResellerCount != 0) ||
	//	(puser->m_CustomerCount != 0))
	//{
		// This needed for Chalkatour //
		double teamandpersonalwhole = puser->m_TeamWholesaleSales+puser->m_LvL1MyWholesaleSales;

		map <string, string> columns;
		columns["system_id"] = IntToStr(system_id);
		columns["batch_id"] = IntToStr(batch_id);
		columns["user_id"] = puser->m_UserID;
		columns["team_and_my_wholesale"] = DoubleToStr(teamandpersonalwhole); // This needed for Chalkatour //
		columns["group_wholesale_sales"] = DoubleToStr(puser->m_GroupWholesaleSales);
		columns["group_retail_sales"] = DoubleToStr(puser->m_GroupRetailSales);
		columns["group_used"] = DoubleToStr(puser->m_GroupUsed);
		columns["customer_wholesale_sales"] = DoubleToStr(puser->m_CustomerWholesaleSales);
		columns["customer_retail_sales"] = DoubleToStr(puser->m_CustomerRetailSales);
		columns["reseller_wholesale_sales"] = DoubleToStr(puser->m_ResellerWholesaleSales);
		columns["reseller_retail_sales"] = DoubleToStr(puser->m_ResellerRetailSales);
		columns["affiliate_wholesale_sales"] = DoubleToStr(puser->m_AffiliateWholesaleSales);
		columns["affiliate_retail_sales"] = DoubleToStr(puser->m_AffiliateRetailSales);
		columns["team_wholesale_sales"] = DoubleToStr(puser->m_TeamWholesaleSales);
		columns["team_retail_sales"] = DoubleToStr(puser->m_TeamRetailSales);
		columns["signup_count"] = IntToStr(puser->m_AllSignupCount);
		columns["affiliate_count"] = IntToStr(puser->m_AffiliateCount);
		columns["reseller_count"] = IntToStr(puser->m_ResellerCount);
		columns["customer_count"] = IntToStr(puser->m_CustomerCount);
		columns["item_count_wholesale"] = IntToStr(puser->m_PVItemCountWholesale);
		columns["item_count_retail"] = IntToStr(puser->m_PVItemCountRetail);
		columns["unique_users_receipts"] = IntToStr(puser->m_UniqueUsersReceipts);

		columns["item_count_wholesale_ev"] = IntToStr(puser->m_EVItemCountWholesale);
		columns["item_count_retail_ev"] = IntToStr(puser->m_EVItemCountRetail);

		columns["corp_wholesale_price"] = IntToStr(puser->m_CorpWholeSales);
		columns["corp_retail_price"] = IntToStr(puser->m_CorpRetailSales);

		if ((m_UserStatMonthCount = BulkAdd(pDB, socket, "ce_userstats_month", columns, &m_strStatMonthSQL, m_UserStatMonthCount)) == -1)
			return CDbBulk::Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthSQL.c_str());
	//}

	///////////////////////////
	// User Stats Month LVL1 //
	///////////////////////////
//	if ((puser->m_LvL1PersonalSales != 0) ||
//		(puser->m_LvL1SignupCount != 0) ||
//		(puser->m_LvL1AffiliateCount != 0) ||
//		(puser->m_LvL1CustomerCount != 0) ||
//		(puser->m_LvL1ResellerCount != 0) ||
//		(puser->m_LvL1MyWholesaleSales != 0) ||
//		(puser->m_LvL1MyRetailSales != 0))
//	{
		if (puser->m_UserID == g_Debug_UserID)
		{
			stringstream ss;
			ss << "CceUserStats::AddBulk - userid=" << puser->m_UserID << ", puser->m_LvL1MyWholesaleSales=" << puser->m_LvL1MyWholesaleSales;
			CDbBulk::Debug(DEBUG_INFO, ss.str().c_str());
		}
 
		map <string, string> columnslvl1;
		columnslvl1["system_id"] = IntToStr(system_id);
		columnslvl1["batch_id"] = IntToStr(batch_id);
		columnslvl1["user_id"] = puser->m_UserID;
		columnslvl1["personal_sales"] = DoubleToStr(puser->m_LvL1PersonalSales);
		columnslvl1["signup_count"] = IntToStr(puser->m_LvL1SignupCount);
		columnslvl1["affiliate_count"] = IntToStr(puser->m_LvL1AffiliateCount);
		columnslvl1["customer_count"] = IntToStr(puser->m_LvL1CustomerCount);
		columnslvl1["reseller_count"] = IntToStr(puser->m_LvL1ResellerCount);
		columnslvl1["my_wholesale_sales"] = DoubleToStr(puser->m_LvL1MyWholesaleSales);
		columnslvl1["my_retail_sales"] = DoubleToStr(puser->m_LvL1MyRetailSales);
		columnslvl1["psq"] = IntToStr(puser->m_PSQ);
		if ((m_UserStatMonthLVL1Count = BulkAdd(pDB, socket, "ce_userstats_month_lvl1", columnslvl1, &m_strStatMonthLVL1SQL, m_UserStatMonthLVL1Count)) == -1)
			return CDbBulk::Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthLVL1SQL.c_str());
//	}

	///////////////////////////
	// User Stats Month Legs //
	///////////////////////////
	if ((firstsales != 0) || (second_sales != 0))
	{
		map <string, string> columnslegs;
		columnslegs["system_id"] = IntToStr(system_id);
		columnslegs["batch_id"] = IntToStr(batch_id);
		columnslegs["user_id"] = puser->m_UserID;
		columnslegs["firstbestleg_sales"] = DoubleToStr(firstsales);
		columnslegs["secondbestleg_sales"] = DoubleToStr(second_sales);
		columnslegs["firstbestleg_id"] = first_id;
		columnslegs["secondbestleg_id"] = second_id;
		if ((m_UserStatMonthLegCount = BulkAdd(pDB, socket, "ce_userstats_month_legs", columnslegs, &m_strStatMonthLegSQL, m_UserStatMonthLegCount)) == -1)
			return CDbBulk::Debug(DEBUG_ERROR, "db::adduserstat - Error SQL", m_strStatMonthLegSQL.c_str());
	}

	return true;
}

////////////////////////////
// Finish off bulk insert //
/////////////////////////////
bool CceUserStats::FinishBulk(CDb *pDB, int socket)
{
	// Finish Month Records //
	if (BulkFinish(pDB, socket, &m_strStatMonthSQL) == false)
		return CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::FinishBulk #1 - Error SQL", m_strStatMonthSQL.c_str());

	// Finish Month Lvl1 Records //
	if (BulkFinish(pDB, socket, &m_strStatMonthLVL1SQL) == false)
		return CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::FinishBulk #2 - Error SQL", m_strStatMonthLVL1SQL.c_str());

	// Finish Month Leg Records //
	if (BulkFinish(pDB, socket, &m_strStatMonthLegSQL) == false)
		return CDbBulk::Debug(DEBUG_ERROR, "CceUserStats::FinishBulk #2 - Error SQL", m_strStatMonthLegSQL.c_str());

	return true;
}