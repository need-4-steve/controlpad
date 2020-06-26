#ifndef _RECEIPT_H
#define _RECEIPT_H

#include <string>

#define MAX_DATE_LEN	11
 
#define INV_WHOLESALE		"1"
#define INV_RETAIL			"2"
#define INV_CASHANDCARRY	"3"
#define INV_SOLDONCORP		"4" // Fulfilled by corporte //
#define INV_CUSTOM			"5" // United used this for fast start bonus // AND Affiliate at ControlPad // 

using namespace std;
 
////////////////////////////////
// Retain Receipt Information //
////////////////////////////////
class CReceipt
{
public:
	CReceipt();

	int m_ID;
	int m_ReceiptID;
	std::string m_UserID;
	int m_UserType; // Affiliate or Customer // 
	//double m_Amount;
	double m_WholesalePrice;
	double m_RetailPrice;
	char m_InvType;
	bool m_EventWholesale;
	bool m_EventRetail;
	bool m_Commissionable;
	string m_WholesaleDate;
	string m_RetailDate;
	string m_MetaDataOnAdd;
};

#endif