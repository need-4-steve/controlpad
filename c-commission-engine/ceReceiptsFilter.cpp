#include "ceReceiptsFilter.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceReceiptsFilter::CceReceiptsFilter(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("receiptsfilter", "ce_receipts_filter");
	CezJson::SetOrigin(origin);
}
 
////////////////////////
// Add Receipts Filter//
////////////////////////
const char *CceReceiptsFilter::Add(int socket, int system_id, string inv_type, string product_type)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::add error", "A database connection needs to be made first");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "receiptsfilter::add error", "The invtype is not numeric");
	if (is_number(product_type) == false)
		return SetError(400, "API", "receiptsfilter::add error", "The producttype is not numeric");
	
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["inv_type"] = inv_type;
	columns["product_type"] = product_type;

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, 0);
}

//////////////////////////
// Edit Receipts Filter //
//////////////////////////
const char *CceReceiptsFilter::Edit(int socket, int system_id, string id, string inv_type, string product_type)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::edit error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receiptsfilter::edit error", "The id is not numeric");
	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::edit error", "A database connection needs to be made first");
	if (is_number(inv_type) == false)
		return SetError(400, "API", "receiptsfilter::edit error", "The invtype is not numeric");
	if (is_number(product_type) == false)
		return SetError(400, "API", "receiptsfilter::edit error", "The producttype is not numeric");
	
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["inv_type"] = inv_type;
	columns["product_type"] = product_type;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, id, "id", unique, columns, mask);
}

///////////////////////////
// Query Receipts Filter //
///////////////////////////
const char *CceReceiptsFilter::Query(int socket, int system_id, string search, string sort)
{
	Debug(DEBUG_TRACE, "CceReceiptsFilter::Query - TOP");

	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("inv_type");
	columns.push_back("product_type");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

/////////////////////////////
// Disable Receipts Filter //
/////////////////////////////
const char *CceReceiptsFilter::Disable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::disable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receiptsfilter::disable error", "The id is not numeric");

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, id, "id");
}

////////////////////////////
// Enable Receipts Filter //
////////////////////////////
const char *CceReceiptsFilter::Enable(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::enable error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receiptsfilter::enable error", "The id is not numeric");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, id, "id");
}

/////////////////////////////////////
// Grab individual receipts filter //
/////////////////////////////////////
const char *CceReceiptsFilter::Get(int socket, int system_id, string id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "receiptsfilter::get error", "A database connection needs to be made first");
	if (is_number(id) == false)
		return SetError(400, "API", "receiptsfilter::get error", "The id is not numeric");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("inv_type");
	columns.push_back("product_type");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, id, "id", columns, mask);
}