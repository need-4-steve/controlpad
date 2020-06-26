#include "CommissionEngine.h"
#include "ceReceipt.h"
#include "db.h"
#include "ceUser.h"
#include <stdlib.h> // atoi //

extern CCommissionEngine g_RubyEng; // Handle Ruby DB connection //

///////////////////////////////
// Constructor for ruby rice //
///////////////////////////////
CceReceipt::CceReceipt()
{

}

/////////////////
// Constructor //
/////////////////
CceReceipt::CceReceipt(CDb *pDB, string origin)
{
	m_pDB = pDB;
	CezJson::SetOrigin(origin);
}

////////////////////////////////
// Add a receipt through Ruby //
////////////////////////////////
string CceReceipt::AddRuby(int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable)
{
	m_pDB = &g_RubyEng.m_DB;
	CDbPlus::Setup("receipt", "ce_receipts");
	CezJson::SetOrigin("na");

	m_Retval = Add(0, system_id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, inv_type, commissionable, "", "");
	return m_Retval;
}

/////////////////////////////////
// Edit a receipt through Ruby //
/////////////////////////////////
string CceReceipt::EditRuby(int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable)
{
	m_pDB = &g_RubyEng.m_DB;
	CDbPlus::Setup("receipt", "ce_receipts");
	CezJson::SetOrigin("na");

	m_Retval = Edit(0, system_id, receipt_id, user_id, wholesale_price, retail_price, wholesale_date, retail_date, inv_type, commissionable, "", "");
	return m_Retval;
}

////////////////////////////////////
// Disable a receipt through Ruby //
////////////////////////////////////
string CceReceipt::DisableRuby(int system_id, string id)
{
	m_pDB = &g_RubyEng.m_DB;
	CDbPlus::Setup("receipt", "ce_receipts");
	CezJson::SetOrigin("na");

	m_Retval = Disable(0, system_id, id);
	return m_Retval;
}

///////////////////////////////////
// Enable a receipt through Ruby //
///////////////////////////////////
string CceReceipt::EnableRuby(int system_id, string id)
{
	m_pDB = &g_RubyEng.m_DB;
	CDbPlus::Setup("receipt", "ce_receipts");
	CezJson::SetOrigin("na");

	m_Retval = Enable(0, system_id, id);
	return m_Retval;
}

///////////////////////////////////////////////
// Add a receipt to calculate commissions on //
///////////////////////////////////////////////
const char *CceReceipt::Add(int socket, int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	wholesale_date = FixDate(wholesale_date);
	retail_date = FixDate(retail_date);

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::add error", "A database connection needs to be made first");
	if ((commissionable != "true") && (commissionable != "false"))
		return SetError(400, "API", "receipt::add error", "The commissionable is not true or false");
	if (is_number(receipt_id) == false)
		return SetError(400, "API", "receipt::add error", "The receiptid is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "receipt::add error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (wholesale_date.size() != 0)
	{
		if (is_timestamp(wholesale_date) == false)
			return SetError(400, "API", "receipt::add error", "The wholesaledate is not in correct date format YYYY-MM-DD");
	}
	if (retail_date.size() != 0)
	{
		if (is_timestamp(retail_date) == false)
			return SetError(400, "API", "receipt::add error", "The retaildate is not in correct date format YYYY-MM-DD");
	}
	if (is_decimal(wholesale_price) == false)
		return SetError(400, "API", "receipt::add error", "The wholesaleprice is not a decimal value");
	if (retail_price.size() != 0)
	{
		if (is_decimal(retail_price) == false)
			return SetError(400, "API", "receipt::add error", "The retailprice is not a decimal value");
	}
	if (is_number(inv_type) == false)
		return SetError(400, "API", "receipt::add error", "The invtype is not not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "receipt::add error", "The invtype must bet between 1-5");
	if (metadata_onadd.size() != 0)
	{
		if (is_alphanum(metadata_onadd) == false)
			return SetError(400, "API", "receipt::add error", "The metadata is not alphanumeric");
	}

	if (product_type.size() != 0)
	{
		if (is_number(product_type) == false)
			return SetError(400, "API", "receipt::add error", "The producttype is not not numeric");
	}

	// Check valid userid //
	CceUser ceuser(m_pDB, CezJson::m_Origin);
	if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", user_id) == false)
		return SetError(400, "API", "receipt::add error", "The userid is not valid");

	list <string> unique; // Nothing //
	//unique.push_back("receipt_id");

	map <string, int> mask;

	std::stringstream ss1;
	std::string usertype = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT usertype FROM ce_users WHERE user_id='" << user_id << "'");

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["receipt_id"] = receipt_id;
	columns["user_id"] = user_id;
	columns["wholesale_price"] = wholesale_price;
	if (retail_price.size() != 0) // Only add retail price if available //
		columns["retail_price"] = retail_price;
	columns["inv_type"] = inv_type;
	columns["commissionable"] = commissionable;
	columns["usertype"] = usertype;

	if (metadata_onadd.size() != 0)
		columns["metadata_onadd"] = metadata_onadd;
	if (wholesale_date.size() != 0)
		columns["wholesale_date"] = wholesale_date;
	if (retail_date.size() != 0)
		columns["retail_date"] = retail_date;
	if (product_type.size() != 0)
		columns["product_type"] = product_type;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxReceipts);
}

////////////////////////////////
// Allow editing of a receipt //
////////////////////////////////
const char *CceReceipt::Edit(int socket, int system_id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	wholesale_date = FixDate(wholesale_date);
	retail_date = FixDate(retail_date);
	
	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::edit error", "A database connection needs to be made first");
	if ((commissionable != "true") && (commissionable != "false"))
		return SetError(400, "API", "receipt::edit error", "The commissionable is not true or false");
	if (is_number(receipt_id) == false)
		return SetError(400, "API", "receipt::edit error", "The receipt_id is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "receipt::edit error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (wholesale_date.size() != 0)
	{
		if (is_timestamp(wholesale_date) == false)
			return SetError(400, "API", "receipt::edit error", "The wholesaledate is not in correct date format YYYY-MM-DD");
	}
	if (retail_date.size() != 0)
	{
		if (is_timestamp(retail_date) == false)
			return SetError(400, "API", "receipt::edit error", "The retaildate is not in correct date format YYYY-MM-DD");
	}
	if (is_decimal(wholesale_price) == false)
		return SetError(400, "API", "receipt::edit error", "The wholesaleprice is not a decimal value");
	if (is_decimal(retail_price) == false)
		return SetError(400, "API", "receipt::edit error", "The retailprice is not a decimal value");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "receipt::edit error", "The invtype is not not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "receipt::edit error", "The invtype must bet between 1-5");
	if (metadata_onadd.size() != 0)
	{
		if (is_alphanum(metadata_onadd) == false)
			return SetError(400, "API", "receipt::edit error", "The metadata is not alphanumeric");
	}

	if (product_type.size() != 0)
	{
		if (is_number(product_type) == false)
			return SetError(400, "API", "receipt::edit error", "The producttype is not not numeric");
	}

	// Check valid userid //
	CceUser ceuser(m_pDB, CezJson::m_Origin);
	if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", user_id) == false)
		return SetError(400, "API", "receipt::edit error", "The userid is not valid");

	list <string> unique; // Nothing //
	map <string, int> mask;

	std::stringstream ss1;
	std::string usertype = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT usertype FROM ce_users WHERE user_id='" << user_id << "'");

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["receipt_id"] = receipt_id;
	columns["user_id"] = user_id;
	columns["wholesale_price"] = wholesale_price;
	columns["retail_price"] = retail_price;
	columns["inv_type"] = inv_type;
	columns["commissionable"] = commissionable;
	columns["usertype"] = usertype;

	if (metadata_onadd.size() != 0)
		columns["metadata_onadd"] = metadata_onadd;
	if (wholesale_date.size() != 0)
		columns["wholesale_date"] = wholesale_date;
	if (retail_date.size() != 0)
		columns["retail_date"] = retail_date;
	if (product_type.size() != 0)
		columns["product_type"] = product_type;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, receipt_id, "receipt_id", unique, columns, mask);
}

////////////////////////////////
// Allow editing of a receipt //
////////////////////////////////
const char *CceReceipt::EditWID(int socket, int system_id, string id, string receipt_id, string user_id, string wholesale_price, string retail_price, string wholesale_date, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	wholesale_date = FixDate(wholesale_date);
	retail_date = FixDate(retail_date);

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::edit error", "A database connection needs to be made first");
	if ((commissionable != "true") && (commissionable != "false"))
		return SetError(400, "API", "receipt::edit error", "The commissionable is not true or false");
	if (is_number(id) == false)
		return SetError(400, "API", "receipt::edit error", "The id is not numeric");
	if (is_number(receipt_id) == false)
		return SetError(400, "API", "receipt::edit error", "The receipt_id is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "receipt::edit error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (wholesale_date.size() != 0)
	{
		if (is_timestamp(wholesale_date) == false)
			return SetError(400, "API", "receipt::edit error", "The wholesaledate is not in correct date format YYYY-MM-DD");
	}
	if (retail_date.size() != 0)
	{
		if (is_timestamp(retail_date) == false)
			return SetError(400, "API", "receipt::edit error", "The retaildate is not in correct date format YYYY-MM-DD");
	}
	if (is_decimal(wholesale_price) == false)
		return SetError(400, "API", "receipt::edit error", "The wholesaleprice is not a decimal value");
	if (is_decimal(retail_price) == false)
		return SetError(400, "API", "receipt::edit error", "The retailprice is not a decimal value");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "receipt::edit error", "The invtype is not not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "receipt::edit error", "The invtype must bet between 1-5");
	if (metadata_onadd.size() != 0)
	{
		if (is_alphanum(metadata_onadd) == false)
			return SetError(400, "API", "receipt::edit error", "The metadata is not alphanumeric");
	}

	if (product_type.size() != 0)
	{
		if (is_number(product_type) == false)
			return SetError(400, "API", "receipt::edit error", "The producttype is not not numeric");
	}
	
	// Check valid userid //
	CceUser ceuser(m_pDB, CezJson::m_Origin);
	if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", user_id) == false)
		return SetError(400, "API", "receipt::edit error", "The userid is not valid");

	list <string> unique; // Nothing //
	map <string, int> mask;

	std::stringstream ss1;
	std::string usertype = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT usertype FROM ce_users WHERE user_id='" << user_id << "'");

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["id"] = id;
	columns["receipt_id"] = receipt_id;
	columns["user_id"] = user_id;
	columns["wholesale_price"] = wholesale_price;
	columns["retail_price"] = retail_price;
	columns["inv_type"] = inv_type;
	columns["commissionable"] = commissionable;
	columns["usertype"] = usertype;
	
	if (metadata_onadd.size() != 0)
		columns["metadata_onadd"] = metadata_onadd;
	if (wholesale_date.size() != 0)
		columns["wholesale_date"] = wholesale_date;
	if (retail_date.size() != 0)
		columns["retail_date"] = retail_date;
	if (product_type.size() != 0)
		columns["product_type"] = product_type;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

////////////////////
// Query Receipts //
////////////////////
const char *CceReceipt::Query(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("receipt", "ce_receipts");
	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("system_id");
	columns.push_back("id");
	columns.push_back("receipt_id");
	columns.push_back("user_id");
	columns.push_back("wholesale_price");
	columns.push_back("retail_price");
	columns.push_back("wholesale_date");
	columns.push_back("retail_date");
	columns.push_back("inv_type");
	columns.push_back("commissionable");
	columns.push_back("metadata_onadd");
	columns.push_back("metadata_onupdate");
	columns.push_back("product_type");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	//Debug(DEBUG_TRACE, "CceReceipt::Query - Before CDbPlus::QueryDB");
	//Debug(DEBUG_TRACE, "CceReceipt::Query - m_Tablename", CDbPlus::m_Tablename);

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

////////////////////////////
// Query Receipt Sumation //
////////////////////////////
const char *CceReceipt::QuerySum(int socket, int system_id, string search, string orderby, string orderdir, string offset, string limit)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::querysum error", "A database connection needs to be made first");

	// Handle pagination values //
	if ((search.length()!=0) && (is_alphanum(search) == false))
		return SetError(400, "API", "user::querysum error", "The search string needs to be in a date format: 1-1-2000");
	if ((orderdir != "asc") && (orderdir != "desc"))
		return SetError(409, "API", "user::querysum error", "The orderdir variable needs to be either asc or desc");
	if (is_number(offset) == false)
		return SetError(400, "API", "user::querysum error", "The offset is not numeric");
	if (is_number(limit) == false)
		return SetError(400, "API", "user::querysum error", "The limit is not numeric");
	
	if ((orderby != "id") && 
		(orderby != "systemid") && 
		(orderby != "userid") && 
		(orderby != "count") && 
		(orderby != "amount") && 
		(orderby != "createdat") && 
		(orderby != "updatedat"))
		return SetError(409, "API", "user::querysum error", "The orderby variable needs to be either id, systemid, userid, count, amount, createdat or updatedat");

	// Repair columns cause underscore (_) not allowed in HTTP header section //
	if (orderby == "systemid")
		orderby = "system_id";
	else if (orderby == "userid")
		orderby = "user_id";
	else if (orderby == "createdat")
		orderby = "created_at";
	else if (orderby == "updatedat")
		orderby = "updated_at";

	// Handle search string //
	string searchsql;
	if (search.length()!=0)
	{
		// Handle dates and timestamps differently //
		if (orderby == "id")
			searchsql = " AND "+orderby+"='"+search+"'";
		else if ((orderby == "signup_date") || (orderby == "created_at") || (orderby == "updated_at"))
			searchsql = " AND "+orderby+"::DATE='"+search+"%'";
		else
			searchsql = " AND "+orderby+"::TEXT ILIKE '"+search+"'";
	}

	// Build SQL ending sorting/limit/offset here //
	string sqlend = searchsql+" ORDER BY "+orderby+" "+orderdir+" OFFSET "+offset+" LIMIT "+limit;

	return m_pDB->QueryReceiptSum(socket, system_id, searchsql, sqlend);
}

////////////////////////////////////////////
// Delete a receipt. Maybe void or refund //
////////////////////////////////////////////
const char *CceReceipt::Disable(int socket, int system_id, string id)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receipt::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

/////////////////////////
// Re-enable a receipt //
/////////////////////////
const char *CceReceipt::Enable(int socket, int system_id, string id)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receipt::enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

/////////////////////////
// Re-enable a receipt //
/////////////////////////
const char *CceReceipt::Get(int socket, int system_id, string id)
{
	CDbPlus::Setup("receipt", "ce_receipts");
	
	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receipt::get error", "The id is not numeric");

	CDbPlus::Setup("receipt", "ce_receipts");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("system_id");
	columns.push_back("id");
	columns.push_back("receipt_id");
	columns.push_back("user_id");
	columns.push_back("wholesale_price");
	columns.push_back("retail_price");
	columns.push_back("wholesale_date");
	columns.push_back("retail_date");
	columns.push_back("inv_type");
	columns.push_back("commissionable");
	columns.push_back("metadata_onadd");
	columns.push_back("metadata_onupdate");
	columns.push_back("product_type");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}

/////////////////////////////////////////////////////
// Grab a breakdown of the receipt with pagination //
/////////////////////////////////////////////////////
const char *CceReceipt::QueryBreakdown(int socket, int system_id, string search, string sort)
{
	CDbPlus::Setup("breakdown", "ce_breakdown");

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::querybreakdownalt error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("receipt_id");
	columns.push_back("user_id");
	columns.push_back("amount");
	columns.push_back("commrule_id");
	columns.push_back("generation");
	columns.push_back("percent");
	columns.push_back("dollar");
	columns.push_back("infinitybonus");
	columns.push_back("comm_type");
	columns.push_back("receipt_id_internal");
	columns.push_back("metadata_onadd");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

/////////////////////////////////////////////////
// AddBulk needed for ControlPad compatibility //
/////////////////////////////////////////////////
const char *CceReceipt::AddBulk(int socket, int system_id, string qty, string receipt_id, string user_id, string wholesale_price, string wholesale_date, string retail_price, string retail_date, string inv_type, string commissionable, string metadata_onadd, string product_type)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	// Overrride for now //
	//CDbPlus::SetDisplay(3);
	//CDbPlus::SetLevel(7);

	stringstream teststr;
	teststr << "(" << wholesale_date << ")";
	CDbPlus::Debug(DEBUG_ERROR, "receipt::addbulk - BulkAdd Error - teststr", teststr.str().c_str());

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::addbulk error", "A database connection needs to be made first");
	if (is_number(qty) == false)
		return SetError(400, "API", "receipt::addbulk error", "The qty is not numeric");
	if (atoi(qty.c_str()) < 1)
		return SetError(400, "API", "receipt::addbulk error", "The qty must be greator than 1");
	if (is_number(receipt_id) == false)
		return SetError(400, "API", "receipt::addbulk error", "The receipt_id is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "receipt::addbulk error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (wholesale_date.size() != 0)
	{
		if (is_timestamp(wholesale_date) == false)
			return SetError(400, "API", "receipt::addbulk error", "The wholesaledate is not in correct date format YYYY-MM-DD");
	}
	if (is_decimal(wholesale_price) == false)
		return SetError(400, "API", "receipt::addbulk error", "The wholesaleprice is not a decimal value");
	if (retail_date.size() != 0)
	{
		if (is_timestamp(retail_date) == false)
			return SetError(400, "API", "receipt::addbulk error", "The retaildate is not in correct date format YYYY-MM-DD");
	}
	if (retail_price.size() != 0)
	{
		if (is_decimal(retail_price) == false)
			return SetError(400, "API", "receipt::addbulk error", "The retailprice is not a decimal value");
	}
	if (is_number(inv_type) == false)
		return SetError(400, "API", "receipt::addbulk error", "The invtype is not not numeric");
	if ((atoi(inv_type.c_str()) < 1) || (atoi(inv_type.c_str()) > 5))
		return SetError(400, "API", "receipt::addbulk error", "The invtype must bet between 1-5");
	if ((commissionable != "true") && (commissionable != "false"))
		return SetError(400, "API", "receipt::addbulk error", "The commissionable is not true or false");
	if (metadata_onadd.size() != 0)
	{
		if (is_alphanum(metadata_onadd) == false)
			return SetError(400, "API", "receipt::addbulk error", "The metadata is not alphanumeric");
	}

	if (product_type.size() != 0)
	{
		if (is_number(product_type) == false)
			return SetError(400, "API", "receipt::addbulk error", "The producttype is not not numeric");
	}

	wholesale_date = FixDate(wholesale_date);
	
	// Check valid userid //
	CceUser ceuser(m_pDB, CezJson::m_Origin);
	if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", user_id) == false)
		return SetError(400, "API", "receipt::addbulk error", "The userid is not valid");

	stringstream ss1;
	string usertype = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT usertype FROM ce_users WHERE user_id='" << user_id << "'");

	// AddBulk starts here //
	int maxqty = atoi(qty.c_str());
	int insert_count = 0;
	string strSQL;
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["receipt_id"] = receipt_id;
	columns["user_id"] = user_id;
	columns["usertype"] = usertype;
	if (wholesale_price.size() != 0)
		columns["wholesale_price"] = wholesale_price;
	if (wholesale_date.size() != 0)
		columns["wholesale_date"] = wholesale_date;
	if (retail_price.size() != 0)
		columns["retail_price"] = retail_price;
	if (retail_date.size() != 0)
		columns["retail_date"] = retail_date;
	if (product_type.size() != 0)
		columns["product_type"] = product_type;
	columns["inv_type"] = inv_type;
	columns["commissionable"] = commissionable;
	columns["metadata_onadd"] = metadata_onadd;
	while (maxqty > insert_count) // Make sure we have exact count //
	{
		if ((insert_count = BulkAdd(m_pDB, socket, "ce_receipts", columns, &strSQL, insert_count)) == -1)
		{
			CDbPlus::Debug(DEBUG_ERROR, "receipt::addbulk - BulkAdd Error");
			CDbPlus::Debug(DEBUG_ERROR, "receipt::addbulk - Error SQL", strSQL.c_str());
			return SetError(400, "API", "receipt::addbulk error", "Problems with BulkAdd SQL. Check Error logs");
		}
	}

	// Finish last remaining records //
	if (BulkFinish(m_pDB, socket, &strSQL) == false)
	{
		CDbPlus::Debug(DEBUG_ERROR, "receipt::addbulk - BulkFinish Error");
		CDbPlus::Debug(DEBUG_ERROR, "receipt::addbulk - Error SQL", strSQL.c_str());
		return SetError(400, "API", "receipt::addbulk error", "Problems with BulkFinish SQL. Check Error logs");
	}

	return SetJson(200, "");
}

////////////////////////////////////////////////////
// UpdateBulk needed for ControlPad compatibility //
////////////////////////////////////////////////////
const char *CceReceipt::UpdateBulk(int socket, int system_id, string qty, string receipt_id, string user_id, string wholesale_price, string wholesale_date, string retail_price, string retail_date, string metadata_onupdate, string product_type)
{
	CDbPlus::Setup("receipt", "ce_receipts");

	// Overrride for now //
	//CDbPlus::SetDisplay(3);
	//CDbPlus::SetLevel(7);


	if (metadata_onupdate == "O3XVAS-812")
	{
		stringstream ssTest;
		ssTest << "O3XVAS-812... qty=" << qty << ", receiptid=" << receipt_id << ", user_id=" << user_id << ", wholesale_price=" << wholesale_price << ", wholesale_date=" << wholesale_date << ", retail_price=" << retail_price << ", retail_date=" << retail_date << ", metadata_onupdate=" << metadata_onupdate << ", product_type=" << product_type;

		CDbPlus::Debug(DEBUG_ERROR, ssTest.str().c_str());
	}

	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::updatebulk error", "A database connection needs to be made first");
	if (is_number(qty) == false)
		return SetError(400, "API", "receipt::updatebulk error", "The qty is not numeric");
	if (atoi(qty.c_str()) < 1)
		return SetError(400, "API", "receipt::updatebulk error", "The qty must be greator than 1");
	if (is_number(receipt_id) == false)
		return SetError(400, "API", "receipt::updatebulk error", "The receipt_id is not numeric");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "receipt::updatebulk error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (wholesale_date.size() != 0)
	{
		if (is_timestamp(wholesale_date) == false)
			return SetError(400, "API", "receipt::updatebulk error", "The wholesaledate is not in correct date format YYYY-MM-DD");

		wholesale_date = FixDate(wholesale_date);
	}
	if (wholesale_price.size() != 0)
	{
		if (is_decimal(wholesale_price) == false)
			return SetError(400, "API", "receipt::updatebulk error", "The wholesaleprice is not a decimal value");
	}
	if (retail_date.size() != 0)
	{
		if (is_timestamp(retail_date) == false)
			return SetError(400, "API", "receipt::updatebulk error", "The retaildate is not in correct date format YYYY-MM-DD");

		retail_date = FixDate(retail_date);
	}

	if (is_decimal(retail_price) == false)
		return SetError(400, "API", "receipt::updatebulk error", "The retailprice is not a decimal value");
	if (metadata_onupdate.size() != 0)
	{
		if (is_alphanum(metadata_onupdate) == false)
			return SetError(400, "API", "receipt::addbulk error", "The metadata is not alphanumeric");
	}

	if (product_type.size() != 0)
	{
		if (is_number(product_type) == false)
			return SetError(400, "API", "receipt::addbulk error", "The producttype is not not numeric");
	}
	
	// Check valid userid //
	CceUser ceuser(m_pDB, CezJson::m_Origin);
	if (ceuser.IsPresent(m_pDB, socket, system_id, "user_id", user_id) == false)
		return SetError(400, "API", "receipt::updatebulk error", "The userid is not valid");

	// Do a qty check //
	int maxqty = atoi(qty.c_str());
	stringstream ssSQLcount;
	ssSQLcount << "SELECT count(*) FROM ce_receipts WHERE system_id=" << system_id << " AND user_id='" << user_id << "' AND receipt_id='" << receipt_id << "' AND retail_date IS NULL";
	int retcount = m_pDB->GetFirstDB(socket, ssSQLcount.str().c_str());

	// Overall count //
	stringstream ssSQLOverallcount;
	ssSQLOverallcount << "SELECT count(*) FROM ce_receipts WHERE system_id=" << system_id << " AND user_id='" << user_id << "' AND receipt_id='" << receipt_id << "' AND retail_date IS NULL";
	int overallcount = m_pDB->GetFirstDB(socket, ssSQLOverallcount.str().c_str());
	if (overallcount == 0)
	{
		stringstream ssIns;
		m_pDB->ExecDB(socket, ssIns << "INSERT INTO ce_receipts (system_id, user_id, receipt_id, wholesale_date, metadata_onadd, commissionable, usertype) VALUES ('" << system_id << "', '" << user_id << "', '" << receipt_id << "', '" << retail_date << "', 'MISSING', 'true', '1')");

		retcount = maxqty;
		usleep(50000); // 50 milli second sleep //

		//CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::UpdateBulk - overallcount == overallcount. metadata_onupdate=", metadata_onupdate.c_str());
	}

	// Handle maxqty exceeds available //
	bool qtylimithit = false;
	if (maxqty > retcount)
	{
		// Theortically it should never get here, but just in case log the error for later date inspection //
		stringstream ssError;
		ssError << "Danger (maxqty > retcount). system_id=" << system_id << ", user_id=" << user_id << ", receipt_id=" << receipt_id << ", maxqty=" << maxqty << ", retcount=" << retcount;
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::UpdateBulk - Error:", ssError.str().c_str());
		maxqty = retcount;
		qtylimithit = true;
	}

	// Finally update the qty //
	stringstream ssSQL;
	if (product_type.size() != 0)
		ssSQL << "UPDATE ce_receipts SET retail_price='" << retail_price << "', retail_date='" << retail_date << "', metadata_onupdate='" << metadata_onupdate << "', product_type='" << product_type << "' WHERE id IN (SELECT id FROM ce_receipts WHERE system_id=" << system_id << " AND user_id='" << user_id << "' AND receipt_id='" << receipt_id << "' AND retail_date IS NULL ORDER BY id LIMIT " << maxqty << ")";
	else
		ssSQL << "UPDATE ce_receipts SET retail_price='" << retail_price << "', retail_date='" << retail_date << "', metadata_onupdate='" << metadata_onupdate << "' WHERE id IN (SELECT id FROM ce_receipts WHERE system_id=" << system_id << " AND user_id='" << user_id << "' AND receipt_id='" << receipt_id << "' AND retail_date IS NULL ORDER BY id LIMIT " << maxqty << ")";

	if (m_pDB->ExecDB(socket, ssSQL.str().c_str()) == NULL)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::UpdateBulk - Error m_pDB->ExecDB");
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::UpdateBulk - Error", ssSQL.str().c_str());
		return SetError(400, "API", "receipt::updatebulk error", "There was a problem with an UPDATE database command");
	}

	// Send an error cause kelly said he logs it in the database... and I think rollbar picks it up //
//	if (qtylimithit == true)
//		return SetError(400, "API", "receipt::updatebulk error", "You tried to update more qty than you had. Only updated the maximum amount");

	// Return 200 successful //
	return SetJson(200, "");
}

/////////////////////////////////////////////////////////
// Handle update to turn commissionable on/off by bulk //
/////////////////////////////////////////////////////////
const char *CceReceipt::CommissionableBulk(int socket, int system_id, string userid, string startdate, string enddate, string commissionable)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receipt::commissionablebulk error", "A database connection needs to be made first");
	if (is_userid(userid) == false)
		return SetError(400, "API", "receipt::commissionablebulk error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	if (startdate.size() == 10)
		startdate = FixDate(startdate);
	if (enddate.size() == 10)
		enddate = FixDate(enddate);
	if (is_timestamp(startdate) == false)
		return SetError(400, "API", "receipt::commissionablebulk error", "The startdate is not in correct date format YYYY-MM-DD");
	if (is_timestamp(enddate) == false)
		return SetError(400, "API", "receipt::commissionablebulk error", "The enddate is not in correct date format YYYY-MM-DD");

	if ((commissionable != "true") && (commissionable != "false"))
		return SetError(400, "API", "receipt::commissionablebulk error", "The commissionable needs to be true or false");

	stringstream ssTZ;
	string timezone = m_pDB->GetFirstCharDB(socket, ssTZ << "SELECT value FROM ce_settings WHERE system_id=0 AND varname='timezone'");
	
	stringstream ss;
	ss << "UPDATE ce_receipts SET commissionable=" << commissionable << " WHERE system_id=" << system_id << " AND user_id='" << userid << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE >= '" << startdate << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <= '" << enddate << "'";

	if ((m_pDB->ExecDB(true, socket, ss.str().c_str())) == NULL)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::CommissionableBulk - ExecDB Error");
		return SetError(400, "API", "receipt::commissionablebulk error", "Internal database error");
	}

	// Return 200 successful //
	return SetJson(200, "");
}

////////////////////////////////////
// Handle Receipts Order Sum data //
////////////////////////////////////
const char *CceReceipt::OrderSumWholesale(int socket, int system_id, string batch_id, string userid)
{
	if (!is_number(batch_id))
		return SetError(400, "API", "receipt::ordersumwholesale error", "batch_id is not numeric");

	if (userid.size() != 0)
	{
		if (is_userid(userid) == false)
			return SetError(400, "API", "receipt::ordersumwholesale error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	}

	string timezone = "MST"; //"UTC";

	stringstream ss1;
	stringstream ss2;
	string start_date = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT start_date FROM ce_batches WHERE id='" << batch_id << "'");
	string end_date = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT end_date FROM ce_batches WHERE id='" << batch_id << "'");

	// Handle record count //
	stringstream ss;
	int reccordcount = m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "'))");
	if (reccordcount == 0)
		return SetError(400, "API", "ordersumwholesale error", "There are no records in the system for given start and end dates");

	string useridsql;
	if (userid.size() != 0)
		useridsql = " AND user_id='"+userid+"' ";

	CConn *conn;
	stringstream ssRecords;
	ssRecords << "SELECT user_id, metadata_onadd, SUM(wholesale_price), SUM(retail_price), inv_type, count(*) FROM ce_receipts WHERE system_id=" << system_id << " AND disabled=false AND (((wholesale_date AT TIME ZONE '" << timezone << "')::DATE >='" << start_date << "' AND (wholesale_date AT TIME ZONE '" << timezone << "')::DATE <='" << end_date << "')) " << useridsql << " GROUP BY metadata_onadd, user_id, inv_type ORDER BY user_id, metadata_onadd";
	if ((conn = m_pDB->ExecDB(socket, ssRecords.str().c_str())) == NULL)
		return SetError(503, "API", "ordersumwholesale error", "There was an internal error that prevented an SELECT from the database");

	std::stringstream ss3;
	ss3 << ",\"count\":\"" << reccordcount << "\"";
	ss3 << ",\"orders\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ss3 << "{\"userid\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss3 << "\"order\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss3 << "\"wholesale\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss3 << "\"retail\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss3 << "\"invtype\":\"" << conn->m_RowMap[4].c_str() << "\",";
		ss3 << "\"count\":\"" << conn->m_RowMap[5].c_str() << "\"},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::OrderSumWholesale - ThreadReleaseConn == false");
		return SetError(503, "API", "ordersumwholesale error", "Could not release the database connection");
	}

	std::string json;
    json = ss3.str();
    json = json.substr(0, json.size()-1);
    json += "]";
	return SetJson(200, json.c_str());
}

//////////////////////////////////
// Handle canceling of receipts //
//////////////////////////////////
const char *CceReceipt::CancelReceipt(int socket, int system_id, string receipt_id, string metadata_onadd)
{
	if ((receipt_id.size() == 0) && (metadata_onadd.size() == 0))
		return SetError(400, "API", "receipt::cancelreceipt error", "receipt_id or metadata_onadd required");

	if (receipt_id.size() > 0)
	{
		if (atoi(receipt_id.c_str()) <= 0)
			return SetError(400, "API", "receipt::cancelreceipt error", "receiptid <= 0 is not allowed");

		// Has a commission been run on the receipt already? //
		stringstream ss1;
		if (m_pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_breakdown WHERE system_id='" << system_id << "' AND receipt_id='" << receipt_id << "'") == 0)
		{
			// Then just disable the receipt in the system //
			stringstream ss2;
			if (m_pDB->ExecDB(socket, ss2 << "UPDATE ce_receipts SET disabled='true' WHERE system_id='" << system_id << "' AND receipt_id='" << receipt_id << "'") == NULL)
			{
				CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::CancelReceipt - ExecDB Error #1");
				return SetError(400, "API", "receipt::cancelreceipt error", "Internal database error #1");
			}

			return SetJson(200, "");	
		}
		// Return 200 successful //
		return SetJson(200, "");
	}
	else if (metadata_onadd.size() > 0)
	{
		// Has a commission been run on the receipt already? //
		stringstream ss1;
		if (m_pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM ce_breakdown WHERE system_id='" << system_id << "' AND metadata_onadd='" << metadata_onadd << "'") == 0)
		{
			// Then just disable the receipt in the system //
			stringstream ss2;
			if (m_pDB->ExecDB(socket, ss2 << "UPDATE ce_receipts SET disabled='true' WHERE system_id='" << system_id << "' AND metadata_onadd='" << metadata_onadd << "'") == NULL)
			{
				CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::CancelReceipt - ExecDB Error #2");
				return SetError(400, "API", "receipt::cancelreceipt error", "Internal database error #2");
			}

			return SetJson(200, "");	
		}
	}

	// Add entries in ledger for each user for cancelled receipt // 
	CConn *conn;
	std::stringstream ss;
	if ((conn = m_pDB->ExecDB(socket, ss << "SELECT user_id, batch_id, sum(amount) FROM ce_breakdown WHERE system_id='" << system_id << "' AND metadata_onadd='" << metadata_onadd << "' GROUP BY user_id, batch_id ORDER BY sum(amount) DESC")) == NULL)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::CancelReceipt - ExecDB user Error #3");
		return SetError(400, "API", "receipt::cancelreceipt error", "Internal database error #3");
	}

	/////////////////////////////////////////////////////////////////////////////////////////////
	// Maybe add a failsafe to prevent retract of payment ledger entry from having a duplicate //
	/////////////////////////////////////////////////////////////////////////////////////////////

	// Add negative entries on ledger for refund //
	string commrefundSQL;
	int commrefundCount = 0;
	while (m_pDB->FetchRow(conn) == true)
    {
    	string user_id = conn->m_RowMap[0].c_str();
    	string batch_id = conn->m_RowMap[1].c_str();
    	int amount = -1*atoi(conn->m_RowMap[2].c_str());
    	string newamount = IntToStr(amount);
    	
    	string date = "now()";
    	map <string, string> columns;
    	columns["system_id"] = IntToStr(system_id);
		columns["batch_id"] = batch_id;
		columns["ref_id"] = metadata_onadd;
		columns["user_id"] = user_id;
		columns["ledger_type"] = IntToStr(LEDGER_COMMREFUND); // 3
		columns["amount"] = newamount;
		columns["event_date"] = date;
		if ((commrefundCount = BulkAdd(m_pDB, socket, "ce_ledger", columns, &commrefundSQL, commrefundCount)) == -1)
		{
			CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::CancelReceipt - Error SQL", commrefundSQL.c_str());
			return SetError(400, "API", "receipt::cancelreceipt error", "Internal database error #4");
		}
    }

    // Finish Month Records //
	if (BulkFinish(m_pDB, socket, &commrefundSQL) == false)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CceUserStats::FinishBulk - Error SQL", commrefundSQL.c_str());
		return SetError(400, "API", "receipt::cancelreceipt error", "Internal database error #5");
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		CDbPlus::Debug(DEBUG_ERROR, "CceReceipt::CancelReceipt - ThreadReleaseConn == false");
		return SetError(400, "API", "receipt::cancelreceipt error", "Internal database error #6");
	}

	// Return 200 successful //
	return SetJson(200, "");
}