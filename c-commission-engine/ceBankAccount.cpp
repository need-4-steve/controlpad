#include "ceBankAccount.h"
#include "db.h"
#include <stdlib.h> // atoi //
#include "payments.h"

/////////////////
// Constructor //
/////////////////
CceBankAccount::CceBankAccount(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("bankaccount", "ce_bankaccounts");
	CezJson::SetOrigin(origin);
}

////////////////////////
// Add a bank account //
////////////////////////
const char *CceBankAccount::Add(int socket, int system_id, string user_id, string account_type, string routing_number, string account_number, string holder_name)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount::add error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount::add error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(account_type) == false)
		return SetError(400, "API", "bankaccount::add error", "The accounttype is not numeric");
	if ((account_type != "1") && (account_type != "2"))
		return SetError(400, "API", "bankaccount::add error", "The accounttype needs to either be 1 (checking) or 2 (savings)");
	if (is_number(routing_number) == false)
		return SetError(400, "API", "bankaccount::add error", "The routingnumber is not numeric");
	if (is_number(account_number) == false)
		return SetError(400, "API", "bankaccount::add error", "The accountnumber is not numeric");
	if (is_alpha(holder_name) == false)
		return SetError(400, "API", "bankaccount::add error", "The holdername is not an alpha string");
	if (routing_number.size() > 9)
		return SetError(400, "API", "bankaccount::add error", "The routing_number cannot be longer that 9 characters");
	if (account_number.size() > 17)
		return SetError(400, "API", "bankaccount::add error", "The account_number cannot be longer that 17 characters");
 
 	// Unique //
 	list <string> unique;
	unique.push_back("user_id");

	// Mask //
	map <string, int> mask;
	mask["routing_number"] = 4;
	mask["account_number"] = 4;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["user_id"] = user_id;
	columns["account_type"] = account_type;
	columns["routing_number"] = routing_number;
	columns["account_number"] = account_number;
	columns["holder_name"] = holder_name;

	m_Json = CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, -1);
	return m_Json.c_str();
}

/////////////////////////////////////
// Update bank account information //
/////////////////////////////////////
const char *CceBankAccount::Edit(int socket, int system_id, string user_id, string account_type, string routing_number, string account_number, string holder_name)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount::edit error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount::edit error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_number(account_type) == false)
		return SetError(400, "API", "bankaccount::edit error", "The accounttype is not numeric");
	if ((account_type != "1") && (account_type != "2"))
		return SetError(400, "API", "bankaccount::add error", "The accounttype needs to either be 1 (checking) or 2 (savings)");
	if (is_number(routing_number) == false)
		return SetError(400, "API", "bankaccount::edit error", "The routingnumber is not numeric");
	if (is_number(account_number) == false)
		return SetError(400, "API", "bankaccount::edit error", "The accountnumber is not numeric");
	if (is_alpha(holder_name) == false)
		return SetError(400, "API", "bankaccount::edit error", "The holdername is not an alpha string");

	list <string> unique;
	map <string, int> mask;
	mask["routing_number"] = 4;
	mask["account_number"] = 4;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	//columns["user_id"] = user_id;
	columns["account_type"] = account_type;
	columns["routing_number"] = routing_number;
	columns["account_number"] = account_number;
	columns["holder_name"] = holder_name;

	return CDbPlus::EditDB(m_pDB, socket, 0, system_id, user_id, "user_id", unique, columns, mask);
}

/////////////////////////////////////////////////////////
// Grab a list of bank accounts associated to the user //
/////////////////////////////////////////////////////////
const char *CceBankAccount::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount.query error", "A database connection needs to be made first");
	
	map <string, int> mask;
	mask["routing_number"] = 4;
	mask["account_number"] = 4;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("account_type");
	columns.push_back("routing_number");
	columns.push_back("account_number");
	columns.push_back("holder_name");
	columns.push_back("validated");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

//////////////////////////////////////
// Disable bank account information //
//////////////////////////////////////
const char *CceBankAccount::Disable(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount.disable error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount.disable error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	list <string> unique; // Nothing? //
	map <string, int> mask;
	mask["routing_number"] = 4;
	mask["account_number"] = 4;

	// Prepare the columns //
	map <string, string> columns;
	columns["user_id"] = user_id;
	columns["account_type"] = "0";
	columns["routing_number"] = " ";
	columns["account_number"] = " ";
	columns["holder_name"] = " ";

	CDbPlus::EditDB(m_pDB, socket, 0, system_id, user_id, "user_id", unique, columns, mask);

	return CDbPlus::DisableDB(m_pDB, socket, 0, system_id, user_id, "user_id");
}

/////////////////////////////
// Enable the bank account //
/////////////////////////////
const char *CceBankAccount::Enable(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount.enable error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount.enable error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	return CDbPlus::EnableDB(m_pDB, socket, 0, system_id, user_id, "user_id");
}

////////////////////////////////
// Grab a bank account record //
////////////////////////////////
const char *CceBankAccount::Get(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount::get error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount::get error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	map <string, int> mask;
	mask["routing_number"] = 4;
	mask["account_number"] = 4;

	list<string> columns;
	columns.push_back("user_id");
	columns.push_back("account_type");
	columns.push_back("holder_name");
	columns.push_back("routing_number");
	columns.push_back("account_number");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, system_id, user_id, "user_id", columns, mask);
}

////////////////////////////////////////
// Make entries for inital validation //
////////////////////////////////////////
const char *CceBankAccount::InitiateValidation(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount.initiatevalidation error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount.initiatevalidation error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	CBankAccount *paccount;
	if ((paccount = m_pDB->GetBankAccount(socket, system_id, user_id.c_str())) == 0)
		return SetError(503, "API", "bankaccount.initiatevalidation error", "There was an error retrieving the bank account");
	
	CPayments payment;
	if (payment.InitiateValidation(m_pDB->m_pSettings->m_PayProc, paccount) == false)
		return SetError(503, "API", "bankaccount.initiatevalidation error", "There was an error initiating validation");
	
	m_Json = m_pDB->InitiateValidation(socket, system_id, user_id.c_str(), paccount->m_Amount1, paccount->m_Amount2);
	return m_Json.c_str();
}

////////////////////////////////////
// Verify the entries are correct //
////////////////////////////////////
const char *CceBankAccount::Validate(int socket, int system_id, string user_id, string amount1, string amount2)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "bankaccount.validate error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "bankaccount.validate error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (is_decimal(amount1) == false)
		return SetError(400, "API", "bankaccount.validate error", "The amount1 is not a decimal value");
	if (is_decimal(amount2) == false)
		return SetError(400, "API", "bankaccount.validate error", "The amount2 is not a decimal value");

	m_Json = m_pDB->ValidateBankAccount(socket, system_id, user_id.c_str(), amount1.c_str(), amount2.c_str());	
	return m_Json.c_str();
}