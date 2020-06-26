#include "receipts.h"

/////////////////
// Constructor //
/////////////////
CReceipt::CReceipt()
{
	m_ID = 0;
	m_ReceiptID = 0;
	m_UserID = "";
	m_UserType = 0; // Affiliate or Customer // 
	m_WholesalePrice = 0;
	m_RetailPrice = 0;
	m_Commissionable = false;
	m_InvType = 0;
	m_EventWholesale = false;
	m_EventRetail = false;
}