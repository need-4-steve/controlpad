#include "ezCopy.h"

/////////////////
// Constructor //
/////////////////
ezCopy::ezCopy(CDb *pFromDB, CDb *pToDB)
{
	m_pFromDB = pFromDB;
	m_pToDB = pToDB;
}

//////////////////////////
// Copy the users table //
//////////////////////////
bool ezCopy::Users(int socket, int from_system_id, int to_system_id)
{
	if ((from_system_id == to_system_id) && (m_pFromDB == m_pToDB))
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::Users - Either systemids need to be different or databases need to be difference", "ce_users");

	list <string> columns;
	//columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("user_id");
	columns.push_back("usertype");
	columns.push_back("parent_id");
	columns.push_back("sponsor_id");
	columns.push_back("breakage");
	columns.push_back("signup_date");
	columns.push_back("upline_parent");
	columns.push_back("upline_sponsor");
	columns.push_back("password_hash");
	columns.push_back("salt");
	columns.push_back("email");
	columns.push_back("cell");
	columns.push_back("firstname");
	columns.push_back("lastname");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");
	if (CopyTable(socket, from_system_id, to_system_id, "ce_users", "ce_users_id_seq", "system_id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::Users - Problems with CopyTable ", "ce_users");

	return true;
}

/////////////////////////////
// Copy the Receipts table //
/////////////////////////////
bool ezCopy::Receipts(int socket, int from_system_id, int to_system_id) //, const char *startdate, const char *enddate)
{
	if ((from_system_id == to_system_id) && (m_pFromDB == m_pToDB))
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::Receipts - Either systemids need to be different or databases need to be difference", "ce_users");

	list <string> columns;
	columns.push_back("system_id");
	columns.push_back("receipt_id");
	columns.push_back("user_id");
	columns.push_back("usertype");
	columns.push_back("wholesale_price");
	columns.push_back("wholesale_date");
	columns.push_back("commissionable");
	columns.push_back("inv_type");
	columns.push_back("retail_date");
	columns.push_back("retail_price");
	columns.push_back("metadata_onadd");
	columns.push_back("metadata_onupdate");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	if (CopyTable(socket, from_system_id, to_system_id, "ce_receipts", "ce_receipts_id_seq", "system_id", columns, "") == false)
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::Receipts - Problems with CopyTable ", "ce_users");

	return true;
}

///////////////////////////////////////////////////////
// Copy table information from one system to another //
///////////////////////////////////////////////////////
bool ezCopy::CopyTable(int socket, int from_system_id, int to_system_id, string tablename, string seqname, string system_id_column, list <string> columns, string endsql)
{
	CDbPlus::Debug(DEBUG_TRACE, "ezCopy::CopyTable - TOP");

	// Build a string from column names //
	stringstream ss1;
	list <string>::iterator c;
	for (c=columns.begin(); c != columns.end(); ++c) 
	{
		ss1 << (*c) << ", ";
	}
	stringstream ssCols;
	ssCols << ss1.str().substr(0, ss1.str().size()-2); // Remove last comma //

	CConn *conn;
	stringstream ss2;
	if ((conn = m_pFromDB->ExecDB(socket, ss2 << "SELECT " << ssCols.str() << " FROM " << tablename << " WHERE " << system_id_column << "=" << from_system_id << " " << endsql)) == NULL)
	{
		stringstream ss3;
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - SELECT Error ", ss3 << " from " << tablename << " failed");
	}

	// Grab the values //
	list <map <string, string> > allrecords;
	while (m_pFromDB->FetchRow(conn) == true)
	{	
		map <string, string> values;
		int index = 0;
		for (c=columns.begin(); c != columns.end(); ++c) 
		{
			//values[(*c)] = pLiveDB->RowMap(index);
			if ((*c) == "system_id")
				values[(*c)] = IntToStr(to_system_id);
			else
				values[(*c)] = conn->m_RowMap[index];
			index++;
		}

		allrecords.push_back(values);
	}

	CDbPlus::Debug(DEBUG_TRACE, "ezCopy::CopyTable - allrecords.size()", allrecords.size());

	if (ThreadReleaseConn(conn->m_Resource) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - ThreadReleaseConn == false");

	string strInsertSQL;
	int recordcount = 0;
	list <map <string, string> >::iterator i;
	for (i=allrecords.begin(); i != allrecords.end(); ++i) 
	{
		if ((recordcount = BulkAdd(m_pToDB, socket, tablename, (*i), &strInsertSQL, recordcount)) == -1)
			return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - BulkAdd == -1");
	}

	CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - Before BulkFinish");

	if (BulkFinish(m_pToDB, socket, &strInsertSQL) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - BulkFinish == false");

	// Only if different databases //
	if (m_pFromDB != m_pToDB)
	{
		// Set the SEQUENCE count to prevent errors //
		stringstream ss4;
		int seqcount;
		if ((seqcount = m_pToDB->GetFirstDB(socket, ss4 << "SELECT id+1 FROM " << tablename << " ORDER BY id DESC LIMIT 1")) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - Grab SEQUENCE count == false");

		CConn *conn2;
		stringstream ss5;
		if ((conn2 = m_pToDB->ExecDB(socket, ss5 << "ALTER SEQUENCE " << seqname << " RESTART WITH " << seqcount)) == NULL)
			return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - ALTER SEQUENCE == false");

		if (ThreadReleaseConn(conn2->m_Resource) == false)
			return CDbPlus::Debug(DEBUG_ERROR, "ezCopy::CopyTable - #2 ThreadReleaseConn == false");
	}

	return true;
}