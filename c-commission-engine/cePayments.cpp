#include "cePayments.h"
#include "dbplus.h"
#include "payments.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CcePayments::CcePayments(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CezJson::SetOrigin(origin);
}

////////////////////////////
// Set the payment method //
////////////////////////////
const char *CcePayments::SetPaymentType(int socket, int system_id, int payment_type)
{
	if ((payment_type != 1) && (payment_type != 2))
		return SetError(409, "API", "payments::setpaymenttype error", "payment_type can only be 1 or 2");

	m_pDB->m_pSettings->m_PayProc = payment_type;

	return SetJson(200, "");
}

///////////////////////////////////
// Do the processing of payments //
///////////////////////////////////
const char *CcePayments::Process(int socket, int system_id, string batch_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payments::process error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "payments::process error", "The batchid is not numeric");

	/////////////////////////////////
	// Process payment with payman //
	/////////////////////////////////
	if (m_pDB->m_pSettings->m_PayProc == PAY_PROC_PAYMAN)
	{
		//Debug(DEBUG_ERROR, "CezRecv::ProcessPayments - m_PayProc == PAY_PROC_PAYMAN");
		m_Json = m_pDB->SyncWithPayman(socket, system_id);
		return m_Json.c_str();
	}

	//////////////////////////////
	// Process payments locally //
	//////////////////////////////
	if (m_pDB->m_pSettings->m_PayProc == PAY_PROC_LOCAL)
	{
		//Debug(DEBUG_DEBUG, "CezRecv::ProcessPayments - m_PayProc == PAY_PROC_LOCAL");
		std::list <CPayUser> PayUsersLL;
		if (m_pDB->BuildPayUserList(socket, system_id, atoi(batch_id.c_str()), &PayUsersLL) == false)
			return SetError(503, "API", "payments::process error", "There was an error initiating validation");
			
		CPayments payment;
		m_Json = payment.ProcessPayments(socket, m_pDB->m_pSettings->m_PayProc, system_id, atoi(batch_id.c_str()), &PayUsersLL, m_pDB);
		return m_Json.c_str();
	}

	return SetError(400, "API", "payments::process error", "No Process Payment method selected on error. Notify sys-admin");
}

////////////////////////////////////////
// Grab all payment paid to user bank //
////////////////////////////////////////
const char *CcePayments::QueryUser(int socket, int system_id, string user_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payments::queryuser error", "A database connection needs to be made first");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "payments::queryuser error", "The user_id can only be a-z, A-Z, 1-9, -(minus) and .(period)");

	return m_pDB->QueryUserPayments(socket, system_id, user_id.c_str());
} 

///////////////////////////////////////////////
// Grab all the payments in ref to the batch //
///////////////////////////////////////////////
const char *CcePayments::QueryBatch(int socket, int system_id, string batch_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payments::querybatch error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "payments::querybatch error", "The batchid is not numeric");

	return m_pDB->QueryBatchPayments(socket, system_id, atoi(batch_id.c_str()));
}

/////////////////////////////////////////////
// Get List of users that couldn't be paid //
/////////////////////////////////////////////
const char *CcePayments::QueryNoPay(int socket, int system_id, string batch_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payments::querynopayuser error", "A database connection needs to be made first");
	if (is_number(batch_id) == false)
		return SetError(400, "API", "payments::querynopayuser error", "The batchid is not numeric");

	return m_pDB->GetNoPayUsers(socket, system_id, atoi(batch_id.c_str()));
}

/////////////////////////////////////////////
// Get List of users that couldn't be paid //
/////////////////////////////////////////////
const char *CcePayments::QueryPaymentsTotal(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payments::querypaymentstotal error", "A database connection needs to be made first");

	stringstream ss1;
	string minpay = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT minpay FROM ce_systems WHERE id=" << system_id);

	//stringstream ss2;
	//string grandtotal = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT sum(amount) FROM ce_grandtotals WHERE system_id=" << system_id << " AND authorized=true AND amount >='" << minpay << "'");

	stringstream ssTablename;
	ssTablename << "tmp_ledger_" << system_id;
	CConn *conn;
	stringstream ss2;
	if ((conn = m_pDB->ExecDB(socket, ss2 << "SELECT user_id, SUM(amount) INTO TEMP " << ssTablename.str() << " FROM ce_ledger WHERE system_id='" << system_id << "' GROUP by user_id")) == NULL)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with SUM(amount) INTO tmp_ledger_");
		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with SUM(amount) INTO tmp_ledger_ ThreadReleaseConn");

		return SetError(503, "API", "payments::querypaymentstotal error", "Problems with SUM(amount) OR ThreadReleaseConn");
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with ThreadReleaseConn");
		return SetError(503, "API", "payments::querypaymentstotal error", "Problems with ThreadReleaseConn");
	}

	stringstream ss3;
	ss3 << "SELECT SUM(sum) FROM " << ssTablename.str();
	if (m_pDB->ExecDB(conn, false, ss3.str()) == false)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with SUM");
		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with SUM ThreadReleaseConn");

		return SetError(503, "API", "payments::querypaymentstotal error", "Problems with SUM OR ThreadReleaseConn");
	}

	string total;
	if (m_pDB->FetchRow(conn) == true)
	{
		total = conn->m_RowMap[0];
	}
	else
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with FetchRow");
	}


	stringstream ss4;
	ss4 << "DROP TABLE " << ssTablename.str();
	if (m_pDB->ExecDB(conn, false, ss4.str()) == false)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with SUM");
		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with SUM ThreadReleaseConn");

		return SetError(503, "API", "payments::querypaymentstotal error", "Problems with SUM OR ThreadReleaseConn");
	}


	if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, "CcePayments::QueryPaymentsTotal - Problems with ThreadReleaseConn");

	double grandtotal = atof(total.c_str());

	stringstream ssEnd;
	return SetJson(200, ssEnd << ",\"grandtotal\":{\"amount\":\"" << grandtotal << "\"}");
}

/////////////////////////////////////////////
// Get List of users that couldn't be paid //
/////////////////////////////////////////////
const char *CcePayments::QueryPayments(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "payments::querypayments error", "A database connection needs to be made first");

	stringstream ss1;
	string minpay = m_pDB->GetFirstCharDB(socket, ss1 << "SELECT minpay FROM ce_systems WHERE id=" << system_id);
/*
	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //

	list<string> columns;
	columns.push_back("user_id");
	columns.push_back("amount");

	stringstream searchsql;
	stringstream sqlend;

	//searchsql << " authorized=true AND amount >='" << minpay << "'";
	//searchsql << " amount >='" << minpay << "'";
	//sqlend << searchsql.str() << " ORDER BY user_id ASC";
	sqlend << "orderby=user_id&orderdir=asc&offset=0&limit=99999999";

	Debug(DEBUG_TRACE, "CcePayments::QueryPayments - Before dbplus");

	CDbPlus dbplus;
	dbplus.Setup("payout", "ce_grandtotals");
	m_Json = dbplus.QueryDB(m_pDB, socket, 0, system_id, columns, mask, searchsql.str(), sqlend.str());
	return m_Json.c_str();
*/
	// Create the tmp table //
	CConn *conn;
	stringstream ssTmp;
	ssTmp << "SELECT user_id, SUM(amount) INTO TEMP tmp_ledger_" << system_id << " FROM ce_ledger WHERE system_id='" << system_id << "' GROUP by user_id";
	if ((conn = m_pDB->ExecDB(socket, ssTmp)) == NULL)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with SELECT statement");
		return SetError(503, "API", "payments::querypayments error", "Problems with SELECT statement");
	}

	stringstream ssCount;
	ssCount << "SELECT count(*) FROM tmp_ledger_" << system_id;
	if (m_pDB->ExecDB(conn, false, ssCount.str()) == false)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with COUNT");
		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with COUNT ThreadReleaseConn");

		return SetError(503, "API", "payments::querypayments error", "Problems with COUNT OR ThreadReleaseConn");
	}

	string count;
	if (m_pDB->FetchRow(conn) == true)
	{
		count = conn->m_RowMap[0];
	}
	else
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with FetchRow");
	}

	stringstream ssSQL;
	ssSQL << "SELECT u.user_id, u.firstname, u.lastname, u.email, l.sum FROM ce_users u INNER JOIN tmp_ledger_" << system_id << " l ON u.user_id=l.user_id WHERE u.system_id=" << system_id << " AND l.sum >= '" << minpay << "' ORDER BY l.sum DESC";
	if (m_pDB->ExecDB(conn, false, ssSQL.str()) == false)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with inner join select");
		if (ThreadReleaseConn(conn->m_Resource) == false)
			Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with ThreadReleaseConn");

		return SetError(503, "API", "payments::querypayments error", "Problems with inner join select OR ThreadReleaseConn");
	}

	// Build the return Json //
	std::stringstream ss2;
	ss2 << ",\"count\":\"" << count << "\"";
	ss2 << ",\"payout\":[";
	while (m_pDB->FetchRow(conn) == true)
	{
		ss2 << "{\"userid\":\"" << conn->m_RowMap[0].c_str() << "\",";
		ss2 << "\"firstname\":\"" << conn->m_RowMap[1].c_str() << "\",";
		ss2 << "\"lastname\":\"" << conn->m_RowMap[2].c_str() << "\",";
		ss2 << "\"email\":\"" << conn->m_RowMap[3].c_str() << "\",";
		ss2 << "\"amount\":\"" << conn->m_RowMap[4].c_str() << "\"},";
	}

	std::string json;
    json = ss2.str();
    json = json.substr(0, json.size()-1);
    json += "]";

	stringstream ssDrop;
	ssDrop << "DROP TABLE tmp_ledger_" << system_id;
	if (m_pDB->ExecDB(conn, true, ssDrop.str()) == false)
	{
		Debug(DEBUG_ERROR, "CcePayments::QueryPayments - Problems with SQL DROP");
		return SetError(503, "API", "payments::querypayments error", "Problems with SQL DROP");
	}

	return SetJson(200, json.c_str());
}