#include "CommissionEngine.h"
#include "ceSystem.h"
#include "commissions.h"
#include "db.h"
#include <stdlib.h> // atoi //

extern CCommissionEngine g_RubyEng; // Handle Ruby DB connection //

//////////////////////
// Ruby Constructor //
//////////////////////
CceSystem::CceSystem()
{
	m_pDB = &g_RubyEng.m_DB;
	CDbPlus::Setup("system", "ce_systems");
	CezJson::SetOrigin("na");
}

/////////////////
// Constructor //
/////////////////
CceSystem::CceSystem(CDb *pDB, string origin)
{
	m_pDB = pDB;

	CDbPlus::Setup("system", "ce_systems");
	CezJson::SetOrigin(origin);
}

//////////////////////
// Add Through Ruby //
//////////////////////
string CceSystem::AddRuby(string systemname, string payout_type, string payout_monthday, string payout_weekday, string minpay, string signupbonus, string psqlimit, string compression)
{
	m_Retval = Add(0, 2, "1", systemname, "1", "0", payout_type, payout_monthday, payout_weekday, "true", "0", minpay, "", "", "", signupbonus, "1", "0", psqlimit, compression);
	return m_Retval;
}

///////////////////////
// Edit Through Ruby //
///////////////////////
string CceSystem::EditRuby(int system_id, string systemname, string payout_type, string payout_monthday,
	string payout_weekday, string minpay, string signupbonus, string psqlimit, string compression)
{
	m_Retval = Edit(0, 2, system_id, "1", systemname, "1", "0", payout_type, payout_monthday, payout_weekday, "true", "0", minpay, "", "", "", signupbonus, "1", "0", psqlimit, compression);
	return m_Retval;
}

//////////////////////////
// Disable Through Ruby //
//////////////////////////
string CceSystem::DisableRuby(int system_id)
{
	m_Retval = Disable(0, system_id);
	return m_Retval;
}

/////////////////////////
// Enable Through Ruby //
/////////////////////////
string CceSystem::EnableRuby(int system_id)
{
	m_Retval = Enable(0, system_id);
	return m_Retval;
}

//////////////////////
// Get Through Ruby //
//////////////////////
string CceSystem::GetRuby(int system_id)
{
	m_Retval = Get(0, system_id);
	return m_Retval;
}

/////////////////////////////////////////////////
// Handle initializing a new commission system //
/////////////////////////////////////////////////
const char *CceSystem::Add(int socket, int sysuserid, string stacktype, string systemname, string commtype, string altcore, string payout_type, 
	string payout_monthday, string payout_weekday, string autoauthgrand, string infinitycap, string minpay, 
	string updated_url, string updated_username, string updated_password, string signupbonus, string teamgenmax, 
	string piggyid, string psqlimit, string compression)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.add error", "A database connection needs to be made first");
	if (is_json(systemname) == false)
		return SetError(409, "API", "system.add error", "The systemname can only be A-Z, a-z and .");
	if (systemname.length() > 20)
		return SetError(409, "API", "system.add error", "The systemname can only be 20 characters long");
	if ((commtype != "1") && (commtype != "2") && (commtype != "3"))
		return SetError(400, "API", "system.add error", "The commtype needs to be Hybrid-Uni(1), Breakaway(2) or Binary(3)");
	if (is_number(payout_type) == false)
		return SetError(409, "API", "system.add error", "The payouttype is invalid");

	if ((atoi(payout_type.c_str()) < 1) || (atoi(payout_type.c_str()) > 4))
		return SetError(409, "API", "system.add error", "The payouttype needs to be Monthly(1), Weekly(2), Daily(1) or Custom-API(4)");

	// 1 - monthly
	if ((atoi(payout_type.c_str()) == 1) && (is_number(payout_monthday) == false))
		return SetError(409, "API", "system.add error", "The payoutmonthday is invalid");
	else if (atoi(payout_type.c_str()) == 1)
		payout_weekday.clear();

	// 2 - weekly
	if ((atoi(payout_type.c_str()) == 2) && (is_number(payout_weekday) == false))
		return SetError(409, "API", "system.add error", "The payoutweekday is invalid");
	else if (atoi(payout_type.c_str()) == 2)
		payout_monthday.clear();

	// 3 - daily
	if (atoi(payout_type.c_str()) == 3)
	{
		payout_weekday.clear();
		payout_monthday.clear();
	}

	if (is_boolean(autoauthgrand) == false)
		return SetError(409, "API", "system.add error", "The autoauthgrand needs to be true or false");
	if (infinitycap.size() == 0)
		infinitycap = "0";
	if (is_number(infinitycap) == false)
 		return SetError(409, "API", "system.add error", "The infinitycap needs to be a numeric value. It defines the percentage limit that can be paid out on inifinity bonus");
	if (is_decimal(minpay) == false)
		return SetError(409, "API", "system.add error", "The minpay needs to be a numeric value. It defines the bare minimum need before paying out a commission");
	
	if ((updated_url.size()!=0) && (is_password(updated_url) == false))
		return SetError(400, "API", "system.add error", "The updatedurl has some invalid characters");
	if ((updated_username.size()!=0) && (is_alphanum(updated_username) == false))
		return SetError(409, "API", "system.add error", "The updatedusername can only be A-Z, a-z and 1-9");
	if ((updated_password.size()!=0) && (is_password(updated_password) == false))
		return SetError(400, "API", "system.add error", "The updatedpassword has some invalid characters");

	if (altcore.size() == 0)
		altcore = "0";
	if (is_number(altcore) == false)
		return SetError(409, "API", "system.add error", "The altcore needs to be a numeric value");
	
	if (signupbonus.size() == 0)
		signupbonus = "0";
	if (is_decimal(signupbonus) == false)
		return SetError(409, "API", "system.add error", "The signupbonus needs to be a decimal number");

	if (stacktype.size() == 0)
		stacktype = "1"; // Full stacking //
	if ((stacktype != "1") && (stacktype != "2"))
		return SetError(409, "API", "system.add error", "The stacktype needs to be 1 or 2");

	if (teamgenmax.size() != 0)
	{
		if (is_number(teamgenmax) == false)
			return SetError(400, "API", "system.add error", "The teamgenmax is not numeric");
	}
	else
	{
		teamgenmax = "1";
	}

	if (piggyid.size() != 0)
	{
		if (is_number(piggyid) == false)
			return SetError(400, "API", "system.add error", "The piggyid is not numeric");
	}
	else
		piggyid = "0";

	if (is_number(psqlimit) == false)
 		return SetError(409, "API", "system.add error", "The psqlimit needs to be a numeric value");

 	if ((compression != "true") && (compression != "false"))
 		return SetError(409, "API", "system.add error", "The compression needs to be true of false");

	// Prepare the columns //
	list <string> unique; // Nothing //
	map <string, int> mask;
 
	map <string, string> columns;
	columns["sysuser_id"] = IntToStr(sysuserid);
	columns["system_name"] = systemname;
	columns["commtype"] = commtype;
	columns["altcore"] = altcore;
	columns["payout_type"] = payout_type;
	columns["payout_monthday"] = payout_monthday;
	columns["payout_weekday"] = payout_weekday;
	columns["autoauthgrand"] = autoauthgrand;
	columns["infinitycap"] = infinitycap;
	columns["minpay"] = minpay;
	columns["updated_url"] = updated_url;
	columns["updated_username"] = updated_username;
	columns["updated_password"] = updated_password;
	columns["signupbonus"] = signupbonus;
	columns["teamgenmax"] = teamgenmax;
	columns["piggy_id"] = piggyid;
	columns["psq_limit"] = psqlimit;
	columns["compression"] = compression;

	// This won't work cause we need the new system_id created //
	// Add the receipts_filter entries //
	//if (m_pDB->ExecDB(true, socket, "INSERT INTO ce_receipts_filter (system_id, inv_type, product_type) VALUES (1, 0, 0), (1, 1, 0), (1, 2, 0), (1, 3, 0), (1, 4, 0), (1, 5, 0), (1, 6, 0)") == false)
    //    return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - ALTER TABLE ce_breakdown ADD COLUMN dollar");

	// Run DB add //
	return CDbPlus::AddDB(m_pDB, socket, sysuserid, 0, unique, columns, mask, m_pDB->m_pSettings->m_MaxSystems);
}

///////////////////
// Edit a system //
///////////////////
const char *CceSystem::Edit(int socket, int sysuser_id, int system_id, string stacktype, string systemname, string commtype, string altcore, string payout_type, 
	string payout_monthday, string payout_weekday, string autoauthgrand, string infinitycap, string minpay, string updated_url,
	string updated_username, string updated_password, string signupbonus, string teamgenmax, string piggyid, string psqlimit, string compression)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.edit error", "A database connection needs to be made first");
	if (is_json(systemname) == false)
		return SetError(409, "API", "system.edit error", "The systemname is invalid");
	if ((commtype != "1") && (commtype != "2") && (commtype != "3"))
		return SetError(400, "API", "system.add error", "The commtype need to be 1, 2 or 3");
	if (is_number(payout_type) == false)
		return SetError(409, "API", "system.edit error", "The payouttype is invalid");

	// 1 - monthly
	if ((atoi(payout_type.c_str()) == 1) && (is_number(payout_monthday) == false))
		return SetError(409, "API", "system.edit error", "The payoutmonthday is invalid");
	else if (atoi(payout_type.c_str()) == 1)
		payout_weekday.clear();

	// 2 - weekly
	if ((atoi(payout_type.c_str()) == 2) && (is_number(payout_weekday) == false))
		return SetError(409, "API", "system.edit error", "The payoutweekday is invalid (1)");
	else if (atoi(payout_type.c_str()) == 2)
		payout_monthday.clear();

	// 3 - daily
	if (atoi(payout_type.c_str()) == 3)
	{
		payout_weekday.clear();
		payout_monthday.clear();
	}

	if (is_boolean(autoauthgrand) == false)
		return SetError(409, "API", "system.edit error", "The autoauthgrand needs to be true or false");
	if (infinitycap.size() == 0)
		infinitycap = "0";
	if (is_number(infinitycap) == false)
		return SetError(409, "API", "system.edit error", "The infinitycap needs to be a numeric value. It defines the percentage limit that can be paid out on inifinity bonus");
	if (is_decimal(minpay) == false)
		return SetError(409, "API", "system.edit error", "The minpay needs to be a numeric value. It defines the bare minimum need before paying out a commission");
	
	if ((updated_url.size() != 0) && (is_password(updated_url) == false))
		return SetError(400, "API", "system.edit error", "The updatedurl has some invalid characters");
	if ((updated_username.size() != 0) && (is_alphanum(updated_username) == false))
		return SetError(409, "API", "system.edit error", "The updatedusername can only be A-Z, a-z and 1-9");
	if ((updated_password.size() != 0) && (is_password(updated_password) == false))
		return SetError(400, "API", "system.edit error", "The updatedpassword has some invalid characters");

	if (altcore.size() == 0)
		altcore = "0";
	if (is_number(altcore) == false)
		return SetError(409, "API", "system.edit error", "The altcore needs to be a numeric value");

	if (signupbonus.size() == 0)
		signupbonus = "0";
	if (is_decimal(signupbonus) == false)
		return SetError(409, "API", "system.edit error", "The signupbonus needs to be a decimal number");

	if (stacktype.size() == 0)
		stacktype = "1"; // Full stacking //
	if ((stacktype != "1") && (stacktype != "2"))
		return SetError(409, "API", "system.edit error", "The stacktype needs to be 1 or 2");

	if (teamgenmax.size() != 0)
	{
		if (is_number(teamgenmax) == false)
			return SetError(400, "API", "system.edit error", "The teamgenmax is not numeric");
	}
	else
	{
		teamgenmax = "1";
	}

	if (piggyid.size() != 0)
	{
		if (is_number(piggyid) == false)
			return SetError(400, "API", "system.edit error", "The piggyid is not numeric");
	}
	else
		piggyid = "0";

	if (is_number(psqlimit) == false)
 		return SetError(409, "API", "system.edit error", "The psqlimit needs to be a numeric value");

 	if ((compression != "true") && (compression != "false"))
 		return SetError(409, "API", "system.edit error", "The compression needs to be true of false");

	// Prepare the columns //
	list <string> unique; // Nothing //
	map <string, int> mask;

	map <string, string> columns;;
	columns["system_name"] = systemname;
	columns["commtype"] = commtype;
	columns["altcore"] = altcore;
	columns["payout_type"] = payout_type;
	columns["payout_monthday"] = payout_monthday;
	columns["payout_weekday"] = payout_weekday;
	columns["autoauthgrand"] = autoauthgrand;
	columns["infinitycap"] = infinitycap;
	columns["minpay"] = minpay;
	columns["updated_url"] = updated_url;
	columns["updated_username"] = updated_username;
	columns["updated_password"] = updated_password;
	columns["signupbonus"] = signupbonus;
	columns["teamgenmax"] = teamgenmax;
	columns["piggy_id"] = piggyid;
	columns["psq_limit"] = psqlimit;
	columns["compression"] = compression;

	// Prepare the id //
	stringstream id;
	id << system_id;

	return CDbPlus::EditDB(m_pDB, socket, sysuser_id, 0, id.str(), "id", unique, columns, mask);
}

///////////////////////////////////
// Query systems created by user //
///////////////////////////////////
const char *CceSystem::Query(int socket, int sysuser_id, string search, string sort)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.query error", "A database connection needs to be made first");

	map <string, int> mask;

	list<string> columns;
	columns.push_back("id");
	columns.push_back("system_name");
	columns.push_back("commtype");
	columns.push_back("altcore");
	columns.push_back("payout_type");
	columns.push_back("payout_monthday");
	columns.push_back("payout_weekday");
	columns.push_back("autoauthgrand");
	columns.push_back("infinitycap");
	columns.push_back("minpay");
	columns.push_back("teamgenmax");
	columns.push_back("updated_url");
	columns.push_back("updated_username");
	columns.push_back("updated_password");
	columns.push_back("piggy_id");
	columns.push_back("psq_limit");
	columns.push_back("compression");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::QueryDB(m_pDB, socket, sysuser_id, 0, columns, mask, search, sort);
}

//////////////////////////////////////////////////////////////////////////////
// Delete commission system after done using. i.e. Leagues individual games //
//////////////////////////////////////////////////////////////////////////////
const char *CceSystem::Disable(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.disable error", "A database connection needs to be made first");

	return m_pDB->DisableSystem(socket, system_id);
}

/////////////////////
// Enable a system //
/////////////////////
const char *CceSystem::Enable(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.enable error", "A database connection needs to be made first");

	return m_pDB->EnableSystem(socket, system_id);
}

////////////////////////
// Get a Given record //
////////////////////////
const char *CceSystem::Get(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.get error", "A database connection needs to be made first");

	Debug(DEBUG_TRACE, "CceSystem::Get - TOP");

	map <string, int> mask;

	stringstream ss;
	ss << system_id;

	list <string> columns;
	columns.push_back("id");
	columns.push_back("system_name");
	columns.push_back("commtype");
	columns.push_back("payout_type");
	columns.push_back("payout_monthday");
	columns.push_back("payout_weekday");
	columns.push_back("autoauthgrand");
	columns.push_back("infinitycap");
	columns.push_back("minpay");
	columns.push_back("teamgenmax");
	columns.push_back("updated_url");
	columns.push_back("updated_username");
	columns.push_back("updated_password");
	columns.push_back("signupbonus");
	columns.push_back("piggy_id");
	columns.push_back("psq_limit");
	columns.push_back("compression");
	columns.push_back("disabled");
	columns.push_back("created_at");
	columns.push_back("updated_at");

	return CDbPlus::GetDB(m_pDB, socket, 0, 0, ss.str(), "id", columns, mask);
}

//////////////////////////////////////////////////
// Get the count of record in ref to sysuser_id //
//////////////////////////////////////////////////
const char *CceSystem::Count(int socket, int sysuser_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.count error", "A database connection needs to be made first");

	stringstream ss;
	ss << sysuser_id;

	return CDbPlus::CountDB(m_pDB, socket, "sysuser_id", ss.str());
}

///////////////////////////////////////
// Build stats for system user login //
///////////////////////////////////////
const char *CceSystem::Stats(int socket, int system_id)
{
	if (m_pDB == NULL)
		return SetError(409, "API", "system.count error", "A database connection needs to be made first");

	// Grab the full ledger //
	stringstream ss;
	string ledgerbalance = m_pDB->GetFirstCharDB(socket, ss << "SELECT sum(amount) FROM ce_ledger WHERE system_id='" << system_id << "'");
	stringstream ss2;
	string affiliatecount = m_pDB->GetFirstCharDB(socket, ss2 << "SELECT count(*) FROM ce_users WHERE usertype='1' AND system_id='" << system_id << "'");
	stringstream ss3;
	string customercount = m_pDB->GetFirstCharDB(socket, ss3 << "SELECT count(*) FROM ce_users WHERE usertype='2' AND system_id='" << system_id << "'");

	stringstream ss4;
	string wholesale = m_pDB->GetFirstCharDB(socket, ss4 << "SELECT sum(wholesale_price) FROM ce_receipts WHERE system_id='" << system_id << "'");
	stringstream ss5;
	string retail = m_pDB->GetFirstCharDB(socket, ss5 << "SELECT sum(retail_price) FROM ce_receipts WHERE system_id='" << system_id << "'");
	stringstream ss6;
	string commissions = m_pDB->GetFirstCharDB(socket, ss6 << "SELECT sum(amount) FROM ce_commissions WHERE system_id='" << system_id << "'");
	stringstream ss7;
	string bonus = m_pDB->GetFirstCharDB(socket, ss7 << "SELECT sum(amount) FROM ce_ledger WHERE ledger_type='7' AND system_id='" << system_id << "'");
	stringstream ss8;
	string signupbonus = m_pDB->GetFirstCharDB(socket, ss8 << "SELECT sum(amount) FROM ce_ledger WHERE ledger_type='10' AND system_id='" << system_id << "'");
	stringstream ss9;
	string achvbonus = m_pDB->GetFirstCharDB(socket, ss9 << "SELECT sum(amount) FROM ce_achvbonus WHERE system_id='" << system_id << "'");

	// Build json by hand //
	stringstream json;
	json << ",\"system\":{";
	json << "\"ledgerbalance\":\"" << ledgerbalance << "\"";
	json << ",\"affiliatecount\":\"" << affiliatecount << "\"";
	json << ",\"customercount\":\"" << customercount << "\"";
	json << ",\"wholesale\":\"" << wholesale << "\"";
	json << ",\"retail\":\"" << retail << "\"";
	json << ",\"commissions\":\"" << commissions << "\"";
	json << ",\"bonus\":\"" << bonus << "\"";
	json << ",\"signupbonus\":\"" << signupbonus << "\"";
	json << ",\"achvbonus\":\"" << achvbonus << "\"";
	json << "}";

	return SetJson(200, json.str().c_str());
}

/////////////////////////////////////////////////////////////////////////////////////
// Ruby-rice only allows 10 parameters. Eliminate REST params for now on ruby-rice //
/////////////////////////////////////////////////////////////////////////////////////
const char *CceSystem::AddRuby(int socket, int sysuserid, string stacktype, string systemname, string commtype, string altcore, string payout_type, string payout_monthday, 
	string payout_weekday, string autoauthgrand, string infinitycap, string minpay)
{
	return Add(socket, sysuserid, stacktype, systemname, commtype, altcore, payout_type, payout_monthday, payout_weekday, autoauthgrand, infinitycap, minpay, "", "", "", "", "", "", "", "true");
}

/////////////////////////////////////////////////////////////////////////////////////
// Ruby-rice only allows 10 parameters. Eliminate REST params for now on ruby-rice //
/////////////////////////////////////////////////////////////////////////////////////
const char *CceSystem::EditRuby(int socket, int sysuserid, int system_id, string stacktype, string systemname, string commtype, string altcore, string payout_type, string payout_monthday,
	string payout_weekday, string autoauthgrand, string infinitycap, string minpay)
{
	return Edit(socket, sysuserid, system_id, stacktype, systemname, commtype, altcore, payout_type, payout_monthday, payout_weekday, autoauthgrand, infinitycap, minpay, "", "", "", "", "", "", "", "true");
}
