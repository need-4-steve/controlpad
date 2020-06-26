#include "dbbulk.h"
#include "db.h"
#include <sstream>

/////////////////
// Constructor //
/////////////////
CDbBulk::CDbBulk()
{

}

//////////////////////
// Add Bulk Records //
//////////////////////
int CDbBulk::BulkAdd(CDb *pDB, int socket, string tablename, map <string, string> columns, string *strSQL, int count)
{
	stringstream ssColumns;
	stringstream ssValues;

	// Build columns and values string //
	std::map <std::string, string>::iterator i;
	for (i=columns.begin(); i != columns.end(); ++i) 
	{
		ssColumns << i->first << ", ";
		if (((i->first == "wholesale_date") || (i->first == "retail_date")) && (i->second.size() == 0))
			ssValues << "NULL, "; // Date columns need to be handled differently //
		else if (((i->first == "wholesale_price") || (i->first == "retail_price")) && (i->second.size() == 0))
			ssValues << "'0', "; // Numeric columns need to be handled differently //
		else	
			ssValues << "'" << i->second << "', ";
	}

	stringstream ssCols;
	ssCols << ssColumns.str().substr(0, ssColumns.str().size()-2); // Remove last comma //

	stringstream ssVals;
	ssVals << ssValues.str().substr(0, ssValues.str().size()-2); // Remove last comma //

	// INSERT  //
	std::stringstream ss;
	if (strSQL->size() == 0)
		ss << "INSERT INTO " << tablename << " (" << ssCols.str() << ") VALUES (" << ssVals.str() << ")";
	else
    	ss << ",(" << ssVals.str() << ")";
    
    *strSQL += ss.str();
    count++;

	if (count >= MAX_SQL_APPEND)
	{
		if (BulkFinish(pDB, socket, strSQL) == false)
		{
			Debug(DEBUG_ERROR, "CSimulations::BulkAdd - BulkFinish Error");
			return -1;
		}
		count = 0;
	}

	return count;
}

////////////////////////////////////////
// Finish last amount of bulk records //
////////////////////////////////////////
bool CDbBulk::BulkFinish(CDb *pDB, int socket, string *strSQL)
{
	if (strSQL->size() == 0)
		return true; // Not really an error //

	if (pDB->ExecDB(socket, strSQL->c_str()) == NULL)
		return Debug(DEBUG_ERROR, "CSimulations::BulkFinish - ExecDB Systems Error");
	
	strSQL->clear();
	*strSQL = "";
	return true;
}

/////////////////
// Bulk Update //
///////////////// 
int CDbBulk::BulkUpdate(CDb *pDB, int socket, string tablename, string column, string value, string condition, string *strSQL, int count)
{
	count++;
	if (strSQL->size() == 0)
		*strSQL += "UPDATE "+tablename+" SET "+column+"="+value+" WHERE "+condition;
	else
		*strSQL += " OR "+condition;

	if (count >= MAX_SQL_APPEND)
	{
		if (pDB->ExecDB(socket, strSQL->c_str()) == NULL)
			return Debug(DEBUG_ERROR, "CDbBulk::BulkUpdate - ExecDB == false");
		
		*strSQL = "";
		count = 0;
	}

	return count;
}