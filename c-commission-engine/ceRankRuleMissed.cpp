#include "ceRankRuleMissed.h"
#include "db.h"
#include <stdlib.h> // atoi //

/////////////////
// Constructor //
/////////////////
CceRankRuleMissed::CceRankRuleMissed(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("rankrulesmissed", "ce_rankrules_missed");
	CezJson::SetOrigin(origin);
}

////////////////////////////////////////////////
// Define the rank where user goes up a level //
////////////////////////////////////////////////
const char *CceRankRuleMissed::Add(int socket, int system_id, int batch_id, string user_id, int rule_id, int rank,
	int qualify_type, double qualify_threshold, double actual_value)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "rankrulesmissed::add error", "A database connection needs to be made first");
	if (batch_id < 0)
		return SetError(400, "API", "rankrulesmissed::add error", "The batchid must be greater than 0");
	if (is_userid(user_id) == false)
		return SetError(400, "API", "rankrulesmissed::add error", "The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (rule_id < 0)
		return SetError(400, "API", "rankrulesmissed::add error", "The ruleid must be greater than 0");
	if (rank < 0)
		return SetError(400, "API", "rankrulesmissed::add error", "The rank must be greater than 0");
	if (qualify_type < 0)
		return SetError(400, "API", "rankrulesmissed::add error", "The qualifytype must be greater than 0");
	//if (is_decimal(qualify_threshold) == false)
	//	return SetError(400, "API", "rankrulesmissed::add error", "The qualifythreshold is not a decimal number");
	//if (is_decimal(actual_value) == false)
	//	return SetError(400, "API", "rankrulesmissed::add error", "The actualvalue is not a decimal number");

	list <string> unique; // Nothing //
	map <string, int> mask;

	// Prepare the columns //
	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["batch_id"] = IntToStr(batch_id);
	columns["user_id"] = user_id;
	columns["rule_id"] = IntToStr(rule_id);
	columns["rank"] = IntToStr(rank);
	columns["qualify_type"] = IntToStr(qualify_type);
	columns["qualify_threshold"] = DoubleToStr(qualify_threshold);
	columns["actual_value"] = DoubleToStr(actual_value);
	columns["diff"] = DoubleToStr(qualify_threshold-actual_value);

	return CDbPlus::AddDB(m_pDB, socket, 0, system_id, unique, columns, mask, m_pDB->m_pSettings->m_MaxRankRules);
}

/////////////////////////////////
// Query ranks with pagenation //
/////////////////////////////////
const char *CceRankRuleMissed::Query(int socket, int system_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "rankrulesmissed::query error", "A database connection needs to be made first");

	// Handle pagination values //
	if (search.length() != 0)
	{
		if (is_qstring(search) == false)
			return SetError(400, "API", "rankrulesmissed::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}
	if (sort.length() != 0) 
	{
		if (is_qstring(sort) == false)
			return SetError(400, "API", "rankrulesmissed::query error", "The search string is invalid. Only a-z, A-Z, 1-9, *, =, & are valid characters");
	}

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_id");
	columns.push_back("batch_id");
	columns.push_back("user_id");
	columns.push_back("rule_id");
	columns.push_back("rank");
	columns.push_back("qualify_type");
	columns.push_back("qualify_threshold");
	columns.push_back("actual_value");
	columns.push_back("diff");
	columns.push_back("created_at");

	return CDbPlus::QueryDB(m_pDB, socket, 0, system_id, columns, mask, search, sort);
}

///////////////////////////////////
// Add RankRules Missesd in bulk //
///////////////////////////////////
int CceRankRuleMissed::BulkAdd(int missedcount, string *missedSQL, int socket, int system_id, int batch_id, string user_id, int rule_id, int rank, int qualify_type, double qualify_threshold, double actual_value)
{
	if (m_pDB == NULL)
		return CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - A database connection needs to be made first");
	if (batch_id < 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - The batchid must be greater than 0");
	if (is_userid(user_id) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - The userid can only be a-z, A-Z, 1-9, -(minus) and .(period)");
	if (rule_id < 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - The ruleid must be greater than 0");
	if (rank < 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - The rank must be greater than 0");
	if (qualify_type < 0)
		return CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - The qualifytype must be greater than 0");

	map <string, string> columns;
	columns["system_id"] = IntToStr(system_id);
	columns["batch_id"] = IntToStr(batch_id);
	columns["user_id"] = user_id;
	columns["rule_id"] = IntToStr(rule_id);
	columns["rank"] = IntToStr(rank);
	columns["qualify_type"] = IntToStr(qualify_type);
	columns["qualify_threshold"] = DoubleToStr(qualify_threshold);
	columns["actual_value"] = DoubleToStr(actual_value);
	columns["diff"] = DoubleToStr(qualify_threshold-actual_value);

	if ((missedcount = CDbBulk::BulkAdd(m_pDB, socket, "ce_rankrules_missed", columns, missedSQL, missedcount)) == -1)
		CDbPlus::Debug(DEBUG_ERROR, "CceRankRuleMissed::BulkAdd - missedcount == -1");
	
	return missedcount;
}

/////////////////////////////////////////////
// Finish the rest of the rankrules missed //
/////////////////////////////////////////////
bool CceRankRuleMissed::BulkFinish(int socket, string insertSQL)
{
	if (CDbBulk::BulkFinish(m_pDB, socket, &insertSQL) == false)
		return CDbPlus::Debug(DEBUG_ERROR, "CSimulations::CopyTable - BulkFinish == false");

	return true;
}