#ifndef _CERECEIPT_H
#define _CERECEIPT_H

#include "dbplus.h"
#include "dbbulk.h"

#include <string>

using namespace std;

class CceReceipt : private CDbPlus, CDbBulk
{
public:
	// Ruby Rice //
	CceReceipt(); // Connect to database in here //
	string AddRuby(int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable);
	string EditRuby(int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable);
	string DisableRuby(int system_id, string id);
	string EnableRuby(int system_id, string id);

	// Standard Commission Engine //
	CceReceipt(CDb *pDB, string origin);
	const char *Add(int socket, int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type);
	const char *Edit(int socket, int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type);
	const char *EditWID(int socket, int system_id, string id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type);
	const char *Query(int socket, int system_id, string search, string sort);
	const char *QuerySum(int socket, int system_id, string search, string orderby, string orderdir, string offset, string limit);
	const char *Disable(int socket, int system_id, string id);
	const char *Enable(int socket, int system_id, string id);
	const char *Get(int socket, int system_id, string id);
	const char *QueryBreakdown(int socket, int system_id, string search, string sort);

	// These needed to make compatible with Controlpad Core cause they are using inventory table to track //
	const char *AddBulk(int socket, int system_id, string qty, string receipt_id, string user_id, string wholesale_price, string wholesale_date, string retail_price, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type);
	const char *UpdateBulk(int socket, int system_id, string qty, string receipt_id, string user_id, string wholesale_price, string wholesale_date, string retail_price, string retail_date, string metadata_onupdate, string product_type);

	// This added so Chalk would stop contacting me to do it by hand //
	const char *CommissionableBulk(int socket, int system_id, string userid, string startdate, string enddate, string commissionable);

	const char *OrderSumWholesale(int socket, int system_id, string batch_id, string userid);

	const char *CancelReceipt(int socket, int system_id, string receipt_id, string metadata_onadd);

private:
	string m_Json;
	CDb *m_pDB;
	string m_Retval;
};

#endif