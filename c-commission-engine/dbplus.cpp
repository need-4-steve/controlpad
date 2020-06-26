#include "dbplus.h"
#include "convert.h"
#include "ConnPool.h"
//#include "ezNetwork.h"

/////////////////
// Constructor //
/////////////////
CDbPlus::CDbPlus()
{
	m_IsSetup = false;
}

/////////////////////////
// Handle inital setup //
/////////////////////////
bool CDbPlus::Setup(string classref, string tablename)
{
	m_ClassRef = classref;
	m_Tablename = tablename;
	m_IsSetup = true;

	return true;
}	

///////////////////////////////////////////
// Hande adding a record to the database //
///////////////////////////////////////////
const char *CDbPlus::AddDB(CDb *pDB, int socket, int sysuser_id, int system_id, list <string> unique, map <string, string> columns, map <string, int> mask, int maxlimit)
{
	//Debug(DEBUG_TRACE, "CDbPlus::AddDB - TOP");

	string errorstr = m_ClassRef+"::add error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	// Handle WHERE //
	string ssID = PagBuildSearch(sysuser_id, system_id);

	if (maxlimit > 0)
	{
		stringstream ss1;
		if (pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM " << m_Tablename << " " << ssID) > maxlimit)
		{
			stringstream ss2;
			ss2 << "The max limit for " << m_ClassRef << " has been met";
			return SetError(400, "API", errorstr.c_str(), ss2.str().c_str());
		}
	}

	// Handle unique fields to check for duplicates //
	list<string>::iterator i;
	for (i=unique.begin(); i != unique.end(); ++i)
	{
		stringstream ss1;
		if (pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM " << m_Tablename << " " << ssID << " AND " << (*i) << "='" << columns[(*i)] << "'") == 1)
		{
			stringstream ss2;
			ss2 << (*i) << " is already in the system";
			return SetError(400, "API", errorstr.c_str(), ss2.str().c_str());
		}
	}

	// Build the SQL statment //
	stringstream ss3;
	ss3 << "INSERT INTO " << m_Tablename << "(";

	// Loop through the columns //
	std::map <std::string, string>::iterator q;
	for (q=columns.begin(); q != columns.end(); ++q) 
	{
		ss3 << q->first << ", ";
	}
	stringstream ss4;
	ss4 << ss3.str().substr(0, ss3.str().size()-2); // Remove last comma //
	ss4 << ") VALUES (";

	// Loop through values //
	for (q=columns.begin(); q != columns.end(); ++q) 
	{
		ss4 << "'" << q->second << "', ";
	}
	stringstream ss5;
	ss5 << ss4.str().substr(0, ss4.str().size()-2); // Remove last comma //
	ss5 << ")";//" RETURNING id";

	Debug(DEBUG_TRACE, "CDbPlus::AddDB - Right Before pDB->ExecDB");

	if (pDB->ExecDB(socket, ss5) == NULL)
		return SetError(503, "API", errorstr.c_str(), "There was an internal error that prevented an INSERT in the database");
	//int newid = pDB->GetFirstDB(ss5); // Use RETURNING id so we only need to make one database call to get id back out //

	stringstream ss6;
	ss6 << ",\"" << m_ClassRef << "\":[{";

	// Build the response string of all values added //
	for (q=columns.begin(); q != columns.end(); ++q) 
	{
		// Mask //
		int masklen = mask[q->first];
		string second;
		if (masklen > 0)
			second = Mask(q->second.c_str(), masklen);
		else
			second = q->second;

		ss6 << "\"" << q->first << "\":\"" << second << "\",";
	}

	// Do we need a 18ms delay to accommodate for Read/Write database? Matt knows the exact number //

	stringstream ssNew;
	int newid = pDB->GetFirstDB(socket, ssNew << "SELECT id FROM " << m_Tablename << " ORDER BY id DESC LIMIT 1");
	ss6 << "\"id\":\"" << newid << "\","; // Add the newid in //

	stringstream ss7;
	ss7 << ss6.str().substr(0, ss6.str().size()-1); // Remove last comma //
	ss7 << "}]";

	return SetJson(200, ss7.str().c_str());
}

/////////////////////////////
// Handle editing a record //
/////////////////////////////
const char *CDbPlus::EditDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column, list <string> unique, map <string, string> columns, map <string, int> mask)
{
	Debug(DEBUG_TRACE, "CDbPlus::EditDB - TOP");

	string errorstr = m_ClassRef+"::edit error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	// Handle no system_id //
	string ssID = PagBuildSearch(sysuser_id, system_id);

	// Handle unique fields to check for duplicates //
	list<string>::iterator i;
	for (i=unique.begin(); i != unique.end(); ++i)
	{
		stringstream ss1;
		if (pDB->GetFirstDB(socket, ss1 << "SELECT count(*) FROM " << m_Tablename << " " << ssID << " AND " << (*i) << "='" << columns[(*i)] << "'") == 1)
		{
			stringstream ss2;
			ss2 << (*i) << " is already in the system";
			return SetError(400, "API", errorstr.c_str(), ss2.str().c_str());
		}
	}

	// Build the SQL statment //
	stringstream ss3;
	ss3 << "UPDATE " << m_Tablename << " SET ";

	// Loop through the columns //
	std::map <std::string, string>::iterator q;
	for (q=columns.begin(); q != columns.end(); ++q) 
	{
		ss3 << q->first << "='" << q->second << "', ";
	}
	stringstream ss4;
	ss4 << ss3.str().substr(0, ss3.str().size()-2); // Remove last comma //
	if (ssID.size() > 0)
		ss4 << " " << ssID << " AND " << id_column << "='" << id << "'";
	else
		ss4 << " WHERE " << id_column << "='" << id << "'";

	if (pDB->ExecDB(socket, ss4) == NULL)
		return SetError(503, "API", errorstr.c_str(), "There was an internal error that prevented an UPDATE in the database");

	stringstream ss6;
	ss6 << ",\"" << m_ClassRef << "\":[{";

	// Build the response string of all values added //
	for (q=columns.begin(); q != columns.end(); ++q) 
	{
		// Mask //
		int masklen = mask[q->first];
		string second;
		if (masklen > 0)
			second = Mask(q->second.c_str(), masklen);
		else
			second = q->second;

		ss6 << "\"" << q->first << "\":\"" << second << "\",";
	}
	stringstream ss7;
	ss7 << ss6.str().substr(0, ss6.str().size()-1); // Remove last comma //
	ss7 << "}]";

	return SetJson(200, ss7.str().c_str());
}

///////////////////////////
// Build the sort string //
///////////////////////////
string CDbPlus::ValidateSort(string sort)
{
	Debug(DEBUG_TRACE, "CDbPlus::ValidateSort - sort", sort);

	// Handle sprt values //
	string orderby = QStrValue(sort, "orderby");
	string orderdir = QStrValue(sort, "orderdir");
	string offset = QStrValue(sort, "offset");
	string limit = QStrValue(sort, "limit");
	if ((orderdir != "asc") && (orderdir != "desc"))
		return SetError(409, "API", "cdbplus::ValidateSort error", "The orderdir variable needs to be either asc or desc");
	if (is_number(offset) == false)
		return SetError(400, "API", "cdbplus::ValidateSort error", "The offset is not numeric");
	if (is_number(limit) == false)
		return SetError(400, "API", "cdbplus::ValidateSort error", "The limit is not numeric");
	if (is_qstring(orderby) == false)
		return SetError(400, "API", "cdbplus::ValidateSort error", "The orderby has invalid characters");

	return "";
}

/////////////////////////////////////
// Build the end SQL based on sort //
/////////////////////////////////////
string CDbPlus::BuildSQLEnd(string sort)
{
	// Values already checked in ValidateSort //
	Debug(DEBUG_DEBUG, "CDbPlus::BuildSQLEnd - TOP");

	string orderby = QStrValue(sort, "orderby");
	string orderdir = QStrValue(sort, "orderdir");
	string offset = QStrValue(sort, "offset");
	string limit = QStrValue(sort, "limit");

	// Handle all pagination related stuff //
	orderby = CDbPlus::AddUnderScore(orderby);

	string sqlend = " ORDER BY "+orderby+" "+orderdir+" OFFSET "+offset+" LIMIT "+limit;
	return sqlend;
}

/////////////////////////////////
// Simplify the Query Function //
/////////////////////////////////
const char *CDbPlus::QueryDB(CDb *pDB, int socket, int sysuser_id, int system_id, list<string> columns, map <string, int> mask, string search, string sort)
{
	Debug(DEBUG_TRACE, "CDbPlus::QueryDB - TOP");

	if ((m_Json = ValidateSort(sort)) != "")
		return m_Json.c_str();

	if ((m_Json = PagCheck(columns, search, sort)) != "success")
		return m_Json.c_str();

	search = StrReplace(search, "*", "%");
	string searchsql = PagBuildSearch(sysuser_id, system_id, columns, search);

	string sqlend = BuildSQLEnd(sort);

	Debug(DEBUG_TRACE, "CDbPlus::QueryDB - search", search.c_str());
	Debug(DEBUG_TRACE, "CDbPlus::QueryDB - searchsql", searchsql.c_str());
	Debug(DEBUG_TRACE, "CDbPlus::QueryDB - sqlend", sqlend.c_str());

	// Do the final last step query //
	return QueryInternalDB(pDB, socket, sysuser_id, system_id, columns, mask, searchsql, sqlend);
}

///////////////////////////
// Query with raw search //
///////////////////////////
const char *CDbPlus::QuerySearchRawDB(CDb *pDB, int socket, int sysuser_id, int system_id, list<string> columns, map <string, int> mask, string searchraw, string sort)
{
	// Handle sort values //
	string orderby = QStrValue(sort, "orderby");
	string orderdir = QStrValue(sort, "orderdir");
	string offset = QStrValue(sort, "offset");
	string limit = QStrValue(sort, "limit");
	if ((orderdir != "asc") && (orderdir != "desc"))
		return SetError(409, "API", "cdbplus::querydb error", "The orderdir variable needs to be either asc or desc");
	if (is_number(offset) == false)
		return SetError(400, "API", "cdbplus::querydb error", "The offset is not numeric");
	if (is_number(limit) == false)
		return SetError(400, "API", "cdbplus::querydb error", "The limit is not numeric");

	// Handle all pagination related stuff //
	orderby = CDbPlus::AddUnderScore(orderby);

	string search;
	if ((m_Json = PagCheck(columns, search, sort)) != "success")
		return m_Json.c_str();

	//string sqlend = searchraw+" ORDER BY "+orderby+" "+orderdir+" OFFSET "+offset+" LIMIT "+limit;
	string sqlend = " ORDER BY "+orderby+" "+orderdir+" OFFSET "+offset+" LIMIT "+limit;

	//Debug(DEBUG_TRACE, "CDbPlus::QueryDB - searchsql", searchraw.c_str());
	//Debug(DEBUG_TRACE, "CDbPlus::QueryDB - sqlend", sqlend.c_str());

	// Do the final last step query //
	return QueryInternalDB(pDB, socket, sysuser_id, system_id, columns, mask, searchraw, sqlend);
}

/////////////////////////////
// Handle query of records //
/////////////////////////////
const char *CDbPlus::QueryInternalDB(CDb *pDB, int socket, int sysuser_id, int system_id, list<string> columns, map <string, int> mask, string searchsql, string sqlend)
{
	string errorstr = m_ClassRef+"::query error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	// Handle no system_id //
	string ssID = PagBuildSearch(sysuser_id, system_id);

	//Debug(DEBUG_WARN, "CDbPlus::QueryInternalDB - #1 - searchsql", searchsql.c_str());
	//Debug(DEBUG_ERROR, "CDbPlus::QueryInternalDB - #1 - sqlend", sqlend.c_str());

	// Prevent problems with embedded select override //
	string front;
	string back;
	const char *tmpbrack = strchr(searchsql.c_str(), '(');
	if (tmpbrack != NULL)
	{
		int frontstrpos = searchsql.find('(');
		if (frontstrpos > 0)
		{
			front = searchsql.substr(0, frontstrpos);
			//Debug(DEBUG_ERROR, "CDbPlus::QueryInternalDB - front", front.c_str());
			StrReplace(searchsql, front, "");
		}

		int backstrpos = searchsql.find(')');
		if (backstrpos > 0)
		{
			back = searchsql.substr(backstrpos, searchsql.size());
			//Debug(DEBUG_ERROR, "CDbPlus::QueryInternalDB - back", back.c_str());
			StrReplace(searchsql, back, "");
		}

		//Debug(DEBUG_ERROR, "CDbPlus::QueryInternalDB - frontstrpos", frontstrpos);
	}
	else
	{
		StrReplace(searchsql, " id", " t.id");
		StrReplace(searchsql, " parent_id", " t.parent_id");
		StrReplace(searchsql, " user_id", " t.user_id");
		StrReplace(searchsql, " system_id", " t.system_id");
	}

	// Handle adding t. to search string //
	std::list<string>::iterator t;
	for (t=columns.begin(); t != columns.end(); ++t)
	{
		if (strstr(front.c_str(), (*t).c_str()) != NULL)
		{
			//Debug(DEBUG_TRACE, "(*t)", (*t));

			string tmpstr1 = " "+(*t);
			string tmpstr2 = " t."+(*t);
			StrReplace(front, tmpstr1.c_str(), tmpstr2.c_str());
			StrReplace(sqlend, tmpstr1.c_str(), tmpstr2.c_str());
			StrReplace(back, tmpstr1.c_str(), tmpstr2.c_str());

			//if (tmpbrack == NULL)
			//	StrReplace(searchsql, tmpstr1.c_str(), tmpstr2.c_str());
		}
	}

	stringstream finalsearchsql;
	finalsearchsql << front << searchsql << back;  

	//Debug(DEBUG_ERROR, "CDbPlus::QueryInternalDB - #2 - finalsearchsql", finalsearchsql.str().c_str());

	// Verify count(*) of records //
	stringstream ss;
	int count = pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM " << m_Tablename << " t " << finalsearchsql.str()); //<< searchsql); ssID
	if (count == 0)
		return SetError(400, "API", errorstr.c_str(), "There are no records");

	// Build the select string //
	bool useridflag = false;
	stringstream ss2;
	ss2 << "SELECT DISTINCT ";
	std::list<string>::iterator i;
	for (i=columns.begin(); i != columns.end(); ++i)
	{
		if (((*i) == "user_id") && (m_Tablename != "ce_users") && (m_Tablename != "ce_settings"))
			useridflag = true;
		
		ss2 << "t." << (*i) << ", ";
	}

	// Handle user information columns //
	if (useridflag == true)
		ss2 << "u.firstname, u.lastname, ";

	stringstream ss3;
	ss3 << ss2.str().substr(0, ss2.str().size()-2); // Remove last comma //
	ss3 << " FROM " << m_Tablename << " t ";

	// Handle user information columns //
	stringstream ssExcept;
	if (useridflag == true)
		ss3 << " LEFT JOIN ce_users u ON u.user_id=t.user_id AND u.system_id=" << system_id;
	else if ((m_Tablename == "ce_settings") && (finalsearchsql.str().size() == 0))
		ssExcept << " WHERE t.system_id=" << system_id;
	else if ((m_Tablename == "ce_settings") && (strstr(finalsearchsql.str().c_str(), " WHERE  t.user_id=") != NULL))
		ssExcept << " AND t.system_id=" << system_id;
	
	//Debug(DEBUG_TRACE, "CDbPlus::QueryInternalDB - finalsearchsql", finalsearchsql.str().c_str());
	//Debug(DEBUG_TRACE, "CDbPlus::QueryInternalDB - ssID", ssID.c_str());

	// Continue with the rest of the statement //
	ss3 << finalsearchsql.str() << ssExcept.str() << sqlend; // ssID <<

	CConn *conn;
	if ((conn = pDB->ExecDB(socket, ss3)) == NULL)
		return SetError(503, "API", errorstr.c_str(), "There was an internal error that prevented a SELECT from the database");

	std::stringstream ss4;
	ss4 << ",\"count\":\"" << count << "\"";
	ss4 << ",\"" << m_ClassRef << "\":[";
	while (pDB->FetchRow(conn) == true)
	{
		ss4 << "{";
		int index = 0;
		for (i=columns.begin(); i != columns.end(); ++i)
		{
			if (index !=0)
				ss4 << ",";

			// Mask //
			int masklen = mask[*i];
			string second;
			if (masklen > 0)
				second = Mask(conn->m_RowMap[index].c_str(), masklen);
			else
				second = conn->m_RowMap[index];

			/*
			if (masklen > 0)
				second = Mask(pDB->RowMap(index), masklen);
			else
				second = pDB->RowMap(index);
			*/

			// Write out json //
			ss4 << "\""<< RemoveUnderScore(*i) << "\":\"" << second << "\"";
			index++;
		}

		if (useridflag == true)
		{
			ss4 << ",\"firstname\":\"" << conn->m_RowMap[index] << "\",";
			ss4 << "\"lastname\":\"" << conn->m_RowMap[index+1] << "\"";
		}

		ss4 << "},";
	}

	if (ThreadReleaseConn(conn->m_Resource) == false)
	{
		Debug(DEBUG_ERROR, "CDbPlus::QueryInternalDB - ThreadReleaseConn == false");
		return SetError(503, "API", errorstr.c_str(), "Could not release the database connection");
	}

	stringstream json;
    json << ss4.str().substr(0, ss4.str().size()-1); // Remove last comma //
    json << "]";
    
	m_Json = SetJson(200, json.str().c_str());
	return m_Json.c_str();
}

/////////////////////////////////////
// Generic way of enabling records //
/////////////////////////////////////
const char *CDbPlus::EnableDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column)
{
	Debug(DEBUG_TRACE, "CDbPlus::EnableDB - TOP");

	string errorstr = m_ClassRef+"::enable error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	string ssID = PagBuildSearch(sysuser_id, system_id);

	stringstream ss0;
	if (pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM " << m_Tablename << ssID << " AND " << id_column << "='" << id << "'") != 1)
		return SetError(503, "API", errorstr.c_str(), "The id is not found in database");

	stringstream ss1;
	if (pDB->ExecDB(socket, ss1 << "UPDATE " << m_Tablename << " SET disabled=false, updated_at='now()' " << ssID << " AND " << id_column << "='" << id << "'") == NULL)
		return SetError(503, "API", errorstr.c_str(), "Database error prevented an UPDATE");

	return SetJson(200, "");
}

//////////////////////////////////////
// Generic way of disabling records //
//////////////////////////////////////
const char *CDbPlus::DisableDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column)
{
	Debug(DEBUG_TRACE, "CDbPlus::DisableDB - TOP");

	string errorstr = m_ClassRef+"::disable error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	string ssID = PagBuildSearch(sysuser_id, system_id);

	stringstream ss0;
	if (pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM " << m_Tablename << ssID << " AND " << id_column << "='" << id << "'") != 1)
		return SetError(503, "API", errorstr.c_str(), "The id is not found in database");

	stringstream ss1;
	if (pDB->ExecDB(socket, ss1 << "UPDATE " << m_Tablename << " SET disabled=true, updated_at='now()' " << ssID << " AND " << id_column << "='" << id << "'") == NULL)
		return SetError(503, "API", errorstr.c_str(), "Database error prevented an UPDATE");

	return SetJson(200, "");
}

///////////////////////////////////////
// Delete a record from the database //
///////////////////////////////////////
const char *CDbPlus::DeleteDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column)
{
	Debug(DEBUG_TRACE, "CDbPlus::DeleteDB - TOP");

	string errorstr = m_ClassRef+"::disable error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	string ssID = PagBuildSearch(sysuser_id, system_id);

	stringstream ss0;
	if (pDB->GetFirstDB(socket, ss0 << "SELECT count(*) FROM " << m_Tablename << " WHERE " << ssID << " AND " << id_column << "='" << id << "'") != 1)
		return SetError(503, "API", errorstr.c_str(), "The id is not found in database");

	stringstream ss1;
	if (pDB->ExecDB(socket, ss1 << "DELETE FROM " << m_Tablename << " WHERE " << ssID << " AND " << id_column << "='" << id << "'") == NULL)
		return SetError(503, "API", errorstr.c_str(), "Database error prevented an UPDATE");

	return SetJson(200, "");
}

/////////////////////////
// Get a single record //
/////////////////////////
const char *CDbPlus::GetDB(CDb *pDB, int socket, int sysuser_id, int system_id, string id, string id_column, list <string> columns, map <string, int> mask)
{
	Debug(DEBUG_TRACE, "CDbPlus::GetDB - TOP");

	string errorstr = m_ClassRef+"::get error";
	if (m_IsSetup == false)
		return SetError(503, "API", errorstr.c_str(), "issetup is false");
	if ((sysuser_id > 0) && (system_id > 0))
		return SetError(503, "API", errorstr.c_str(), "sysuser_id > 0 AND system_id > 0. It cannot be both");

	stringstream ssearch;
	ssearch << id_column << "=" << id;
	string sort = "orderby="+id_column+"&orderdir=asc&offset=0&limit=1000";

	return QueryDB(pDB, socket, sysuser_id, system_id, columns, mask, ssearch.str(), sort);
}

////////////////////////////////////
// Return count number of records //
////////////////////////////////////
const char *CDbPlus::CountDB(CDb *pDB, int socket, string column, string value)
{
	Debug(DEBUG_TRACE, "CDbPlus::CountDB - TOP");

	stringstream ss;
	int count = pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM " << m_Tablename << " WHERE " << column << "='" << value << "'");
	stringstream json;
	json << ",\"count\":\"" << count << "\"";

	return SetJson(200, json);
}

/////////////////////////////////////////
// Is the value present in the column? //
/////////////////////////////////////////
bool CDbPlus::IsPresent(CDb *pDB, int socket, int system_id, string column, string value)
{	
	stringstream ss;
	if (pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM " << m_Tablename << " WHERE system_id=" << system_id << " AND " << column << "='" << value << "'") == false)
		return false;

	return true;
}

///////////////////////////////////////
// Handle checking Pagination values //
///////////////////////////////////////
const char *CDbPlus::PagCheck(list <string> columns, string search, string sort)
{
	string errorstr = m_ClassRef+"::pagcheck error";
	string orderby = AddUnderScore(QStrValue(sort, "orderby"));
	string orderdir = QStrValue(sort, "orderdir");
	string offset = QStrValue(sort, "offset");
	string limit = QStrValue(sort, "limit");

	//search = trim(search);

	// Handle pagination values //
	if (orderby.length() == 0)
		return SetError(400, "API", errorstr.c_str(), "The orderby is empty");
	if (orderdir.length() == 0)
		return SetError(400, "API", errorstr.c_str(), "The orderdir is empty");
	if ((orderdir != "asc") && (orderdir != "desc"))
		return SetError(409, "API", errorstr.c_str(), "The orderdir variable needs to be either asc or desc");
	if (is_number(offset) == false)
		return SetError(400, "API", errorstr.c_str(), "The offset is not numeric");
	if (is_number(limit) == false)
		return SetError(400, "API", errorstr.c_str(), "The limit is not numeric");

	string tmpsearch = StrReplace(search, "_", ""); // Strip out underscore //
	if ((tmpsearch.length() != 0) && (is_qstring(tmpsearch) == false))
	{
		Debug(DEBUG_WARN, "CDbPlus::PagCheck - tmpsearch", tmpsearch);
		return SetError(400, "API", errorstr.c_str(), "The search string needs to be in querystring format");
	}

	// Build error string //
	stringstream ss;
	ss << "The sort variable needs to be either ";
	list<string>::iterator i;
	for (i=columns.begin(); i != columns.end(); ++i)
	{
		ss << (*i) << ", ";
	}
	stringstream ss2;
	ss2 << ss.str().substr(0, ss.str().size()-2); // Remove last comma //

	//Debug(DEBUG_ERROR, "CDbPlus::PagCheck - orderby", orderby.c_str());

	// Do actual check //
	bool found = false;
	for (i=columns.begin(); i != columns.end(); ++i)
	{
		//Debug(DEBUG_ERROR, "CDbPlus::PagCheck - (*i)", (*i).c_str());

		if (orderby == (*i))
			found = true;
	}

	if (found == false)
		return SetError(409, "API", errorstr.c_str(), ss2.str().c_str());

	return "success";
}

////////////////////////
// Fix the underscore //
////////////////////////
string CDbPlus::AddUnderScore(string orderby)
{
	// Repair columns cause underscore (_) not allowed in HTTP header section //
	// This list will need to be for all columns for all tables //
	if (orderby == "createdat") 
		return "created_at";
	else if (orderby == "updatedat")
		return "updated_at";
	else if (orderby == "systemid")
		return "system_id";
	else if (orderby == "startdate")
		return "start_date";
	else if (orderby == "enddate")
		return "end_date";
	else if (orderby == "userid")
		return "user_id";
	else if (orderby == "receiptid")
		return "receipt_id";
	else if (orderby == "startgen")
		return "start_gen";
	else if (orderby == "endgen")
		return "end_gen";
	else if (orderby == "qualifytype")
		return "qualify_type";
	else if (orderby == "qualifythreshold")
		return "qualify_threshold";
	else if (orderby == "systemname")
		return "system_name";
	else if (orderby == "payouttype")
		return "payout_type";
	else if (orderby == "payoutmonthday")
		return "payout_monthday";
	else if (orderby == "payoutweekday")
		return "payout_weekday";
	else if (orderby == "updatedurl")
		return "updated_url";
	else if (orderby == "updatedusername")
		return "updated_username";
	else if (orderby == "updatedpassword")
		return "updated_password";
	else if (orderby == "batchid")
		return "batch_id";
	else if (orderby == "personalsales")
		return "personal_sales";
	else if (orderby == "groupsales")
		return "group_sales";
	else if (orderby == "signupcount")
		return "signup_count";
	else if (orderby == "affiliatecount")
		return "affiliate_count";
	else if (orderby == "customercount")
		return "customer_count";
	else if (orderby == "ledgertype")
		return "ledger_type";
	else if (orderby == "invtype")
		return "inv_type";
	else if (orderby == "parentid")
		return "parent_id";
	else if (orderby == "sponsorid")
		return "sponsor_id";
	else if (orderby == "signupdate")
		return "signup_date";
	else if (orderby == "wholesaledate")
		return "wholesale_date";
	else if (orderby == "retaildate")
		return "retail_date";
	else if (orderby == "bonusdate")
		return "bonus_date";
	else if (orderby == "groupsales")
		return "group_sales";
	else if (orderby == "customersales")
		return "customer_sales";
	else if (orderby == "affiliatesales")
		return "affiliate_sales";
	else if (orderby == "signupcount")
		return "signup_count";
	else if (orderby == "affiliatecount")
		return "affiliate_count";
	else if (orderby == "customercount")
		return "customer_count";
	else if (orderby == "rankruleid")
		return "rankrule_id";
	else if (orderby == "commruleid")
		return "commrule_id";
	else if (orderby == "wholesaleprice")
		return "wholesale_price";
	else if (orderby == "retailprice")
		return "retail_price";
	else if (orderby == "actualvalue")
		return "actual_value";
	else if (orderby == "ruleid")
		return "rule_id";
	else if (orderby == "resellercount")
		return "reseller_count";
	else if (orderby == "mywholesalesales")
		return "my_wholesale_sales";
	else if (orderby == "myretailsales")
		return "my_retail_sales";
	else if (orderby == "groupwholesalesales")
		return "group_wholesale_sales";
	else if (orderby == "customerwholesalesales")
		return "customer_wholesale_sales";
	else if (orderby == "affiliatewholesalesales")
		return "affiliate_wholesale_sales";
	else if (orderby == "resellerwholesalesales")
		return "reseller_wholesale_sales";
	else if (orderby == "teamandmywholesale")
		return "team_and_my_wholesale";
	else if (orderby == "pvoverride")
		return "pv_override";
	else if (orderby == "metadataonadd")
		return "metadata_onadd";

	return orderby;
}

////////////////////////////
// Replace the underscore //
////////////////////////////
string CDbPlus::RemoveUnderScore(string column)
{
	CConvert conv;
	return conv.StrReplace(column, "_", "");
}

///////////////////////
// Simplified search //
///////////////////////
string CDbPlus::PagBuildSearch(int sysuser_id, int system_id)
{
	list <string> columns;
	string search;
	return PagBuildSearch(sysuser_id, system_id, columns, search);
}


/////////////////////////////////////
// Build the pagination search SQL //
/////////////////////////////////////
string CDbPlus::PagBuildSearch(int sysuser_id, int system_id, list<string> columns, string search)
{
	//Debug(DEBUG_TRACE, "DbPlus::PagBuildSearch - TOP search", search.c_str());

	stringstream ss;
	if ((columns.size() > 0) || (sysuser_id > 0) || (system_id > 0))
		ss << " WHERE ";
	else
	{
		// It can get here if accessing ce_settings values //
		//string errorstr = m_ClassRef+"::pagbuildsearch error - It should never get here";
		//Debug(DEBUG_WARN, errorstr.c_str());
		return ""; // It should never get here //
	}

	// Handle sysuser_id //
	if ((sysuser_id > 0) && (m_Tablename == "ce_systemusers"))
		ss << "id='" << sysuser_id << "' AND ";
	else if ((sysuser_id > 0) && (m_Tablename != "ce_systemusers"))
		ss << "sysuser_id='" << sysuser_id << "' AND ";

	// Handle system_id //
	if ((system_id > 0) && (m_Tablename == "ce_systems"))
		ss << "id='" << system_id << "' AND ";
	else if ((system_id > 0) && (m_Tablename != "ce_systems"))
		ss << "system_id='" << system_id << "' AND ";

	// Handle search params //
	list<string>::iterator i;
	for (i=columns.begin(); i != columns.end(); ++i)
	{
		string varname = (*i);
		string value = QStrValue(RemoveUnderScore(search), RemoveUnderScore((*i)));

		//Debug(DEBUG_TRACE, "CDbPlus::PagBuildSearch - varname", varname.c_str());
		//Debug(DEBUG_TRACE, "CDbPlus::PagBuildSearch - value", value.c_str());

		if (value != "")
		{
			// This is needed to speed things up //
			string likequals;
			if (strstr(value.c_str(), "%") != NULL)
				likequals = "::TEXT ILIKE ";
			else
				likequals = "=";

			// Handle each type a little differently //
			if (varname == "id")
				ss << " " << varname << likequals << "'" << value << "' AND";
			else if ((varname == "created_at") || (varname == "updated_at"))
				ss << " " << varname << likequals << "'" << value << "' AND";
			else
				ss << " " << varname << likequals << "'" << value << "' AND";
		}
	}

	// If nothing, then return an empty search string //
	if (ss.str() == " WHERE ")
		return "";

	// Back off the last 3 letters (AND) //
	stringstream ss2;
	ss2 << ss.str().substr(0, ss.str().size()-4); // Remove AND //

	//Debug(DEBUG_TRACE, "DbPlus::PagBuildSearch - BOTTOM ss2 (search)", ss2.str().c_str());

	// Return searchsql string //
	return ss2.str();
}

////////////////////////////////////////////////////
// Grab the value of a variable off a querystring //
////////////////////////////////////////////////////
string CDbPlus::QStrValue(string qstring, string varname)
{
	//Debug(DEBUG_TRACE, "CDbPlus::QStrValue - qstring", qstring.c_str());

	size_t last = 0; 
	size_t next = 0; 
	while ((next = qstring.find("&", last)) != string::npos) 
	{ 
		string fullkey = qstring.substr(last, next-last);
		size_t next_val = fullkey.find("=", 0);
		string key = fullkey.substr(0, next_val);
		string value = fullkey.substr(next_val+1, fullkey.size());
		//printf("key = %s, value = %s\n", key.c_str(), value.c_str());
		if (varname == key)
			return value;
		last = next + 1; 
	} 

	// Handle the very last one //
	string fullkey = qstring.substr(last);
	int length = fullkey.size();
	if (length != 0)
	{
		size_t next_val = fullkey.find("=", 0);
		string key = fullkey.substr(0, next_val);
		string value = fullkey.substr(next_val+1, fullkey.size());
		//printf("#2 key = %s, value = %s\n", key.c_str(), value.c_str());
		if (varname == key)
			return value;
	}

	return "";
}