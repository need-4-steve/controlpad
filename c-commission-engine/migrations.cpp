#include "migrations.h"
#include "ezCrypt.h"
#include "ConnPool.h"

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <string>
#include <sstream>
#include <iomanip> // Precision //

extern char g_DebugLevel;

//////////////////////////////////////////
// Needed for seeding access to indexes //
//////////////////////////////////////////
CMigrations::CMigrations(CDb *pDB)
{
	m_pDB = pDB;
}

///////////////////
// Do Migrations //
///////////////////
CMigrations::CMigrations(CDb *pDB, const char *hashpass, const char *command)
{
    int socket = 0;
	m_pDB = pDB;
	m_HashPass = hashpass;

    // Don't allow pool connections. Messes things up on migrations //
    //pDB->m_pSettings->m_ConnPoolCount = 0;
    pDB->m_ConnPool.Disable();

	if (strcmp(command, "init") == 0)
	{
		Init_1_00();
		Migrate("1.00");
	}
	else if (strcmp(command, "migrate") == 0)
	{
        VersionRepair();

		std::stringstream ss;
		std::string version_str = pDB->GetFirstCharDB(socket, ss << "SELECT version FROM ce_migrations ORDER BY version::FLOAT DESC LIMIT 1");
		Migrate(version_str.c_str());
	}
	else if (strcmp(command, "rollback") == 0)
	{
		std::stringstream ss;
		std::string version_str = pDB->GetFirstCharDB(socket, ss << "SELECT version FROM ce_migrations ORDER BY version::FLOAT DESC LIMIT 1");
		Rollback(version_str.c_str());
	}

    pDB->m_ConnPool.Enable();
}

/////////////////////////
// Do actual migration //
/////////////////////////
void CMigrations::Migrate(const char *current_version)
{
    Debug(DEBUG_MESSAGE, "CMigrations::Migrate - current_version", current_version);

	// Add next migration below //
	double version = atof(current_version);
	if (version == 1)
		version = Migrate_1_01();
	if (version == 1.01)
		version = Migrate_1_02();
    if (version == 1.02)
        version = Migrate_1_03();
    if (version == 1.03)
        version = Migrate_1_04();
    if (version == 1.04)
        version = Migrate_1_05();
    if (version == 1.05)
        version = Migrate_1_06();
    if (version == 1.06)
        version = Migrate_1_07();
    if (version == 1.07)
        version = Migrate_1_08();
    if (version == 1.08)
        version = Migrate_1_09();
    if (version == 1.09)
        version = Migrate_1_10();
    if (version == 1.10)
        version = Migrate_1_11();
    if (version == 1.11)
        version = Migrate_1_12();
    if (version == 1.12)
        version = Migrate_1_13();
    if (version == 1.13)
        version = Migrate_1_14();
    if (version == 1.14)
        version = Migrate_1_15();
    if (version == 1.15)
        version = Migrate_1_16();
    if (version == 1.16)
        version = Migrate_1_17();
    if (version == 1.17)
        version = Migrate_1_18();
    if (version == 1.18)
        version = Migrate_1_19();
    if (version == 1.19)
        version = Migrate_1_20();
    if (version == 1.20)
        version = Migrate_1_21();
    if (version == 1.21)
        version = Migrate_1_22();
    if (version == 1.22)
        version = Migrate_1_23();
    if (version == 1.23)
        version = Migrate_1_24();
    if (version == 1.24)
        version = Migrate_1_25();
    if (version == 1.25)
        version = Migrate_1_26();
    if (version == 1.26)
        version = Migrate_1_27();
    if (version == 1.27)
        version = Migrate_1_28();
    if (version == 1.28)
        version = Migrate_1_29();
    if (version == 1.29)
        version = Migrate_1_30();
    if (version == 1.30)
        version = Migrate_1_31();
    if (version == 1.31)
        version = Migrate_1_32();
    if (version == 1.32)
        version = Migrate_1_33();
    if (version == 1.33)
        version = Migrate_1_34();
    if (version == 1.34)
        version = Migrate_1_35();
    if (version == 1.35)
        version = Migrate_1_36();
    if (version == 1.36)
        version = Migrate_1_37();
    if (version == 1.37)
        version = Migrate_1_38();
    if (version == 1.38)
        version = Migrate_1_39();
    if (version == 1.39)
        version = Migrate_1_40();
    if (version == 1.40)
        version = Migrate_1_41();
    if (version == 1.41)
        version = Migrate_1_42();

	//if ((version == current_version) && (g_DebugLevel != 0))
	Debug(DEBUG_DEBUG, "CMigrations::Migrate - Migration Finished");
}

////////////////////////
// Do actual Rollback //
////////////////////////
bool CMigrations::Rollback(const char *current_version)
{
    int socket = 0;
    bool rollback = true;
	std::string version = current_version;
	if (version == "1.00")
    	rollback = Rollback_1_00();
    else if (version == "1.01")
        rollback = Rollback_1_01();
    else if (version == "1.02")
        rollback = Rollback_1_02();
    else if (version == "1.03")
        rollback = Rollback_1_03();
    else if (version == "1.04")
        rollback = Rollback_1_04();
    else if (version == "1.05")
        rollback = Rollback_1_05();
    else if (version == "1.06")
        rollback = Rollback_1_06();
    else if (version == "1.07")
        rollback = Rollback_1_07();
    else if (version == "1.08")
        rollback = Rollback_1_08();
    else if (version == "1.09")
        rollback = Rollback_1_09();
    else if (version == "1.10")
        rollback = Rollback_1_10();
    else if (version == "1.11")
        rollback = Rollback_1_11();
    else if (version == "1.12")
        rollback = Rollback_1_12();
    else if (version == "1.13")
        rollback = Rollback_1_13();
    else if (version == "1.14")
        rollback = Rollback_1_14();
    else if (version == "1.15")
        rollback = Rollback_1_15();
    else if (version == "1.16")
        rollback = Rollback_1_16();
    else if (version == "1.17")
        rollback = Rollback_1_17();
    else if (version == "1.18")
        rollback = Rollback_1_18();
    else if (version == "1.19")
        rollback = Rollback_1_19();
    else if (version == "1.20")
        rollback = Rollback_1_20();
    else if (version == "1.21")
        rollback = Rollback_1_21();
    else if (version == "1.22")
        rollback = Rollback_1_22();
    else if (version == "1.23")
        rollback = Rollback_1_23();
    else if (version == "1.24")
        rollback = Rollback_1_24();
    else if (version == "1.25")
        rollback = Rollback_1_25();
    else if (version == "1.26")
        rollback = Rollback_1_26();
    else if (version == "1.27")
        rollback = Rollback_1_27();
    else if (version == "1.28")
        rollback = Rollback_1_28();
    else if (version == "1.29")
        rollback = Rollback_1_29();
    else if (version == "1.30")
        rollback = Rollback_1_30();
    else if (version == "1.31")
        rollback = Rollback_1_31();
    else if (version == "1.32")
        rollback = Rollback_1_32();
    else if (version == "1.33")
        rollback = Rollback_1_33();
    else if (version == "1.34")
        rollback = Rollback_1_34();
    else if (version == "1.35")
        rollback = Rollback_1_35();
    else if (version == "1.36")
        rollback = Rollback_1_36();
    else if (version == "1.37")
        rollback = Rollback_1_37();
    else if (version == "1.38")
        rollback = Rollback_1_38();
    else if (version == "1.39")
        rollback = Rollback_1_39();
    else if (version == "1.40")
        rollback = Rollback_1_40();
    else if (version == "1.41")
        rollback = Rollback_1_41();
    else if (version == "1.42")
        rollback = Rollback_1_42();
    
    // Handle bad rollback //
    if (rollback == false)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback - Error rolling back");

    // Delete ce_migratons version entry //
    std::stringstream ss;
    if (m_pDB->ExecDB(true, socket, ss << "DELETE FROM ce_migrations WHERE version='" << current_version << "'") == NULL)
        Debug(DEBUG_DEBUG, "CMigrations::Rollback - Could not DELETE migration entry");

    Debug(DEBUG_MESSAGE, "CMigration::Rollback - Rolled Back Migration", current_version);

    return true;
}

///////////////////////////////////////////////////////////
// Adding Indexes helps speed up commission calculations //
///////////////////////////////////////////////////////////
bool CMigrations::AddIndexes()
{
    int socket = 0;

	// User Indexes //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_system_id ON ce_users(system_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_users_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_user_id ON ce_users(user_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_users_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_usertype ON ce_users(usertype)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_users_usertype");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_sponsor_id ON ce_users(sponsor_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_users_sponsor_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_parent_id ON ce_users(parent_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_users_parent_id");

    // Receipt Indexes //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipts_system_id ON ce_receipts(system_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_receipts_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipts_purchase_date ON ce_receipts(purchase_date)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_receipts_purchase_date");

    // Ledger Indexes //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ledger_system_id ON ce_ledger(system_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_ledger_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ledger_ledger_type ON ce_ledger(ledger_type)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_ledger_ledger_type");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ledger_user_id ON ce_ledger(user_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_ledger_user_id");

    // Breakdown Indexes //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_system_id ON ce_breakdown(system_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_breakdown_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_user_id ON ce_breakdown(user_id)") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_breakdown_user_id");

    // Rankrules Indexes //
	if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_rankrules_system_id ON ce_rankrules(system_id)") == NULL)
		return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_rankrules_system_id");

    // Commrules Indexes //
	if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_commrules_system_id ON ce_commrules(system_id)") == NULL)
		return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_commrules_system_id");

    // Ranks //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ranks_system_id ON ce_ranks(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_ranks_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ranks_batch_id ON ce_ranks(batch_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_ranks_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ranks_user_id ON ce_ranks(user_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_ranks_user_id");

	// Levels //
	if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_levels_system_id ON ce_levels(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_levels_system_id");
   	if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_levels_user_id ON ce_levels(user_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_levels_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_levels_ancestor_id ON ce_levels(ancestor_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_levels_ancestor_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_levels_level ON ce_levels(level)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::AddIndexes - CREATE INDEX idx_ce_levels_level");

    return true;
}

//////////////////
// Drop Indexes //
//////////////////
bool CMigrations::DropIndexes()
{
    int socket = 0;

	// User Indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_system_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_users_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_user_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_users_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_usertype") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_users_usertype");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_sponsor_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_users_sponsor_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_parent_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_users_parent_id");

    // Receipt Indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipts_system_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_receipts_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipts_purchase_date") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_receipts_purchase_date");

    // Ledger Indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ledger_system_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_ledger_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ledger_ledger_type") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_ledger_ledger_type");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ledger_user_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_ledger_user_id");

    // Breakdown Indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_system_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_breakdown_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_user_id") == NULL)
    	return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_breakdown_user_id");

    // Rank Rules //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_rankrules_system_id") == NULL)
		return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_rankrules_system_id");

    // Commrules Indexes //
	if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_commrules_system_id") == NULL)
		return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_commrules_system_id");

    // Ranks //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ranks_system_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_ranks_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ranks_batch_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_ranks_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ranks_user_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_ranks_user_id");

   	// Levels //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_levels_system_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_levels_system_id");
   	if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_levels_user_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_levels_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_levels_ancestor_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_levels_ancestor_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_levels_level") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::DropIndexes - DROP INDEX idx_ce_levels_level");

    return true;
}

///////////////////////////////////////////////////
// Helper function at the end of every migration //
///////////////////////////////////////////////////
bool CMigrations::Update(double version, const char *label)
{
    int socket = 0;

	std::stringstream ss;
	if (m_pDB->ExecDB(true, socket, ss << "INSERT INTO ce_migrations (version, label, created_at) VALUES (" << version << ", '" << label << "', 'now()')") == NULL)
	{
        stringstream message;
        message << "Error Migrating Version " << version;
        Debug(DEBUG_ERROR, message.str().c_str());
		return false;
	}

	std::stringstream ss2;
	ss2 << std::setprecision(2) << std::fixed; // Set precision for decimal //
	ss2 << version;
	std::string ver_str = ss2.str();

	if (g_DebugLevel != 0)
    {
        stringstream message;
        message << "Migrated to Version " << ver_str.c_str();
        Debug(DEBUG_MESSAGE, message.str().c_str());
    }

	return true;
}

//////////////////////////////////////////////
// fix cause versioning was initally broken //
//////////////////////////////////////////////
bool CMigrations::VersionRepair()
{
    int socket = 0;

    stringstream ss;
    if (m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_migrations") <= 9)
    {
        if (m_pDB->ExecDB(true, socket, "BEGIN") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - BEGIN Failed");

        stringstream ss1;
        if (m_pDB->ExecDB(true, socket, ss1 << "UPDATE ce_migrations SET version='1.01' WHERE version='1.10'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.01'");

        stringstream ss2;
        if (m_pDB->ExecDB(true, socket, ss2 << "UPDATE ce_migrations SET version='1.02' WHERE version='1.20'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.02'");

        stringstream ss3;
        if (m_pDB->ExecDB(true, socket, ss3 << "UPDATE ce_migrations SET version='1.03' WHERE version='1.30'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.03'");

        stringstream ss4;
        if (m_pDB->ExecDB(true, socket, ss4 << "UPDATE ce_migrations SET version='1.04' WHERE version='1.40'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.04'");

        stringstream ss5;
        if (m_pDB->ExecDB(true, socket, ss5 << "UPDATE ce_migrations SET version='1.05' WHERE version='1.50'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.05'");

        stringstream ss6;
        if (m_pDB->ExecDB(true, socket, ss6 << "UPDATE ce_migrations SET version='1.06' WHERE version='1.60'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.06'");

        stringstream ss7;
        if (m_pDB->ExecDB(true, socket, ss7 << "UPDATE ce_migrations SET version='1.07' WHERE version='1.70'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.07'");

        stringstream ss8;
        if (m_pDB->ExecDB(true, socket, ss8 << "UPDATE ce_migrations SET version='1.08' WHERE version='1.80'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.08'");

        stringstream ss9;
        if (m_pDB->ExecDB(true, socket, ss9 << "UPDATE ce_migrations SET version='1.09' WHERE version='1.90'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::VersionRepair - UPDATE ce_migrations SET version='1.09'");

        m_pDB->ExecDB(true, socket, "COMMIT");
    }

    return true;
}

///////////////////////////////////
// Do Initial creating of tables //
///////////////////////////////////
double CMigrations::Init_1_00()
{
    int socket = 0;

	m_pDB->ExecDB(true, socket, "BEGIN");

	// Do initialization //
	// systems will belong to a user //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_systemusers(id SERIAL PRIMARY KEY, email VARCHAR(254), password VARCHAR(128), salt VARCHAR(32), api_key VARCHAR(128), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Sessions needed for end user authentication //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_sessions(id SERIAL PRIMARY KEY, sysuser_id BIGINT, sessionkey VARCHAR(128), ipaddress VARCHAR(15), hit_count BIGINT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Keep all commission systems independant //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_systems(id SERIAL PRIMARY KEY, sysuser_id BIGINT, system_name VARCHAR(20), commtype VARCHAR(1), altcore VARCHAR(2) default '0', payout_type VARCHAR(1), payout_monthday VARCHAR(2), payout_weekday VARCHAR(2), autoauthgrand BOOL DEFAULT false, infinitycap VARCHAR(2) DEFAULT '0', updated_url TEXT, updated_username VARCHAR(128), updated_password VARCHAR(128), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())"); //, rest_user VARCHAR(32), rest_pass VARCHAR(32), rest_url VARCHAR(128))");

	// users table //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_users(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, usertype VARCHAR(1), parent_id TEXT, sponsor_id TEXT, breakage BOOL default false, signup_date DATE, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// ranks table //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_ranks(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, batch_id BIGINT, rank VARCHAR(2) default '0', created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// receipts table //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_receipts(id SERIAL PRIMARY KEY, system_id BIGINT, receipt_id BIGINT, user_id TEXT, usertype VARCHAR(1), amount DECIMAL(37,4), purchase_date DATE, commissionable BOOL, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// levels table // Not used.. yet? //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_levels(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, ancestor_id TEXT, level SMALLINT default 0, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// rank rules - this is how someone moves up a rank //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_rankrules(id SERIAL PRIMARY KEY, system_id BIGINT, rank VARCHAR(2), qualify_type VARCHAR(2), qualify_threshold FLOAT, achvbonus INT, breakage BOOL, rulegroup INT, maxdacleg INT, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// commission rules - this is how we handle the dividing of the pie //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_commrules(id SERIAL PRIMARY KEY, system_id BIGINT, rank VARCHAR(3), qualify_type VARCHAR(2), qualify_threshold FLOAT, start_gen BIGINT, end_gen BIGINT, percent FLOAT, infinitybonus BOOL DEFAULT false, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// pool pot //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_poolpots(id SERIAL PRIMARY KEY, system_id BIGINT, qualify_type VARCHAR(2), amount INT, receipts DECIMAL(37,4), start_date DATE, end_date DATE, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// pool rules //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_poolrules(id SERIAL PRIMARY KEY, system_id BIGINT, poolpot_id BIGINT, start_rank VARCHAR(2), end_rank VARCHAR(2), qualify_threshold FLOAT, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// We need to track each batch for processing //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_batches(id SERIAL PRIMARY KEY, system_id BIGINT, start_date DATE, end_date DATE, receipts DECIMAL(37,4) DEFAULT 0, commissions DECIMAL(37,4) DEFAULT 0, bonuses DECIMAL(37,4) DEFAULT 0, pools DECIMAL(37,4) DEFAULT 0, disabled BOOL DEFAULT false, create_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// commission breakdown - each individual divided up amount //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_breakdown(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, paytype VARCHAR(1), receipt_id BIGINT, user_id TEXT, amount DECIMAL(37,4), commrule_id BIGINT, generation BIGINT, percent FLOAT, infinitybonus BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// binary ledger - so we can do auditing //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_binaryledger(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, commission DECIMAL(37,4), firstleg DECIMAL(37,4), secondleg DECIMAL(37,4), groupsales DECIMAL(37,4), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Log the achievement bonus for auditing purposes //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_achvbonus(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, rankrule_id BIGINT, rank VARCHAR(2), amount INT, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// commission payout - sum of breakdown applied to each user //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_commissions(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, amount DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Handle checkpoint on resuming system calculations if an error occurred //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_checkpoint(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, checkpoint SMALLINT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Track the payout of pools //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_poolpayouts(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, poolpot_id BIGINT, user_id TEXT, amount DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Keep a balance of all totals in one uniform place //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_grandtotals(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, amount DECIMAL(37,4) DEFAULT 0.00, authorized BOOL DEFAULT false, syncd_payman BOOL DEFAULT false, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Track userstats //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_month_lvl1(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, personal_sales DECIMAL(37,4), signup_count BIGINT, customer_count BIGINT, affiliate_count BIGINT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_month_legs(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, firstbestleg_sales DECIMAL(37,4), secondbestleg_sales DECIMAL(37,4), firstbestleg_id TEXT, secondbestleg_id TEXT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_month(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, group_sales DECIMAL(37,4), group_used DECIMAL(37,4), customer_sales DECIMAL(37,4), affiliate_sales DECIMAL(37,4), signup_count BIGINT, affiliate_count BIGINT, customer_count BIGINT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_total_lvl1(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, personal_sales DECIMAL(37,4), signup_count BIGINT, customer_count BIGINT, affiliate_count BIGINT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_total_legs(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, firstbestleg_sales DECIMAL(37,4), secondbestleg_sales DECIMAL(37,4), firstbestleg_id TEXT, secondbestleg_id TEXT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_total(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, group_sales DECIMAL(37,4), group_used DECIMAL(37,4), customer_sales DECIMAL(37,4), affiliate_sales DECIMAL(37,4), signup_count BIGINT, affiliate_count BIGINT, customer_count BIGINT, firstbestleg_sales DECIMAL(37,4), secondbestleg_sales DECIMAL(37,4), firstbestleg_id TEXT, secondbestleg_id TEXT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	/////////////////////////////////////
	// Handle the payments tables here //
	/////////////////////////////////////
	//m_pDB->ExecDB(true, socket, "CREATE TABLE ce_bankaccounts(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, account_type VARCHAR(1), routing_number VARCHAR(9), account_number VARCHAR(17), holder_name VARCHAR(22), validated BOOL DEFAULT false, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");
	// Switched to TEXT so we can store the information encrypted //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_bankaccounts(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, account_type VARCHAR(1), routing_number TEXT, account_number TEXT, holder_name TEXT, validated BOOL DEFAULT false, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");


	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_bankpayments(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, amount DECIMAL(37,4), pay_date DATE, payoutfile_id BIGINT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_bankpayoutfile(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, filename VARCHAR(32), file_id BIGINT, filedate TIMESTAMP, submitted_at VARCHAR(19), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_bankvalidation(id SERIAL PRIMARY KEY, system_id BIGINT, account_id BIGINT, amount1 DECIMAL(37,4), amount2 DECIMAL(37,4), payoutfile_id BIGINT, submitted_at VARCHAR(19), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	///////////////////////
	// Handle migrations //
	///////////////////////
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_migrations(id SERIAL PRIMARY KEY, version DECIMAL(4,2), label TEXT, created_at TIMESTAMP DEFAULT now())");

	/////////////////////////////////////////
	// Create master account for user_id=1 //
	/////////////////////////////////////////
	CezCrypt crypt;
	std::string salt = crypt.GenSalt();
	std::string pbkdf2 = crypt.GenPBKDF2(m_HashPass.c_str(), salt.c_str(), INITAL_MASTER_PASSWORD); // No password //

	CezCrypt crypt2;
	std::string apikey = crypt2.GenSha256();
	std::string apikeyhash = crypt2.GenPBKDF2(m_HashPass.c_str(), salt.c_str(), apikey.c_str()); // No password //

    // Don't write the file anymore //
	//FILE *pFile;
  	//pFile = fopen("master.apikey.txt","w");
  	//if (pFile!=NULL)
  	//{
    //	fputs(apikey.c_str(), pFile);
    //	fclose(pFile);
  	//}

  	//if (g_DebugLevel != 0)
	//	printf("master apikey = %s\n", apikey.c_str()); // Allow person installing to see master apikey //
	//	printf("apikeyhash = %s\n", apikeyhash.c_str()); // Allow person installing to see master apikey //

	std::stringstream ss;
	if (m_pDB->ExecDB(true, socket, ss << "INSERT INTO ce_systemusers(email, password, salt, api_key, created_at) VALUES ('" << MASTER_ACCOUNT << "', '" << pbkdf2 << "', '" << salt << "', '" << apikeyhash << "', 'now()')") == NULL)
		Debug(DEBUG_ERROR, "Couldn't create master userid=1 account");

	//////////////////////////////
	// Integrate check matching //
	//////////////////////////////

	// Checkmatch rules //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_cmcommrules(id SERIAL PRIMARY KEY, system_id BIGINT, rank VARCHAR(3), start_gen BIGINT, end_gen BIGINT, percent FLOAT, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Check match needs to be seperate from grandtotals to avoid doing a checkmatch on a checkmatch payment //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_checkmatch(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, match_rule_id BIGINT, user_id TEXT, match_user_id TEXT, amount DECIMAL(37,4), percent FLOAT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_bonus(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, amount DECIMAL(37,4), bonus_date DATE, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Handle transaction types for ledger table //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_transtypes(id SERIAL PRIMARY KEY, transtype TEXT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");
	m_pDB->ExecDB(true, socket, "INSERT INTO ce_transtypes(transtype) VALUES ('NACHA payment')"); // Minus (-) //
	m_pDB->ExecDB(true, socket, "INSERT INTO ce_transtypes(transtype) VALUES ('Purchase (rewards)')"); // Minus (-) //
	m_pDB->ExecDB(true, socket, "INSERT INTO ce_transtypes(transtype) VALUES ('Grandtotals')"); // Plus (+) //
	m_pDB->ExecDB(true, socket, "INSERT INTO ce_transtypes(transtype) VALUES ('Transfer')"); // Plus (+) // From tokens played (United)
	m_pDB->ExecDB(true, socket, "INSERT INTO ce_transtypes(transtype) VALUES ('Checkmatch')"); // Plus (+) //

	// Use a ledger table to track all transactions //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_ledger(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, ref_id BIGINT, user_id TEXT, ledger_type BIGINT, amount DECIMAL(37,4), from_system_id BIGINT, from_user_id TEXT, event_date DATE default 'now()', generation INT4, authorized BOOL DEFAULT false, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Needed for speed performance on multi-processors //
	m_pDB->ExecDB(true, socket, "CREATE TABLE ce_calcused(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, groupused DECIMAL(37,4), start_date DATE, end_date DATE, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())");

	// Add all the indexes //
	AddIndexes();

	m_pDB->ExecDB(true, socket, "COMMIT");
    
    Update(1.0, "Initial database setup 1.0 successful");
	return 1.0;
}

//////////////////
// Rollback 1.0 //
//////////////////
bool CMigrations::Rollback_1_00()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

	// Add all the indexes //
	DropIndexes();

	m_pDB->ExecDB(true, socket, "DROP TABLE ce_systemusers");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_sessions");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_systems");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_users");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_ranks");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_receipts");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_levels");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_rankrules");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_commrules");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_poolpots");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_poolrules");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_batches");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_breakdown");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_binaryledger");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_achvbonus");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_commissions");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_checkpoint");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_poolpayouts");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_grandtotals");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_month_lvl1");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_month_legs");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_month");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_total_lvl1");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_total_legs");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_total");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_bankaccounts");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_bankpayments");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_bankpayoutfile");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_bankvalidation");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_migrations");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_cmcommrules");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_checkmatch");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_transtypes");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_ledger");
	m_pDB->ExecDB(true, socket, "DROP TABLE ce_bonus");

    m_pDB->ExecDB(true, socket, "COMMIT");
    Debug(DEBUG_MESSAGE, "CMigrations::Rollback_1_0 - Successfully Rolled Back 1.0");
    return true;
}

/////////////////////////////////
// Frame out the first upgrade //
/////////////////////////////////
double CMigrations::Migrate_1_01()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

    string notes = "Add ce_ledger_totals table.\n";
    notes += "Add ce_receipts_totals table.\n";
    notes += "Fix ce_batches(create_at to created_at) columns.\n";
    notes += "Fix pools and poolrules.\n";

    if (CleanupCeRanks() == false)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Problems cleaning up ce_ranks");

    // Commission Engine Site Tables //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ces_sysuser_passreset(id SERIAL PRIMARY KEY, sysuser_id BIGINT, hash TEXT, ipaddress TEXT, used BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ces_sysuser_passreset");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ces_sysuser_login(id SERIAL PRIMARY KEY, sysuser_id BIGINT, ipaddress TEXT, login_at TIMESTAMP DEFAULT now(), logout_at TIMESTAMP)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ces_sysuser_login");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ces_user_passreset(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, hash TEXT, ipaddress TEXT, used BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ces_user_passreset");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ces_user_login(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, ipaddress TEXT, login_at TIMESTAMP DEFAULT now(), logout_at TIMESTAMP)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ces_user_login");

    // Commission Engine Tables //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_audit_users(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, generation INT4, total DECIMAL(37,4), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ce_audit_users");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_audit_ranks(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, rank SMALLINT, total DECIMAL(37,4), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ce_audit_ranks");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_audit_generations(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, generation SMALLINT, total DECIMAL(37,4), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ce_audit_generations");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_apikeys(id SERIAL PRIMARY KEY, sysuser_id BIGINT, system_id BIGINT, label TEXT, salt TEXT, apikeyhash TEXT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ce_ledger_totals");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_ledger_totals(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, amount DECIMAL(37,4), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ce_ledger_totals");
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_receipt_totals(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, count BIGINT, amount DECIMAL(37,4), created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not CREATE TABLE ce_receipt_totals");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches RENAME COLUMN create_at TO created_at") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ALTER TABLE ce_batches");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolpots DROP COLUMN qualify_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN qualify_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolrules ADD COLUMN qualify_type VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN qualify_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN minpay DECIMAL(37,2) DEFAULT '0.00'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN minpay");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ledger ADD COLUMN transaction_id BIGINT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN transaction_id");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN upline_parent TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN upline_parent");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN upline_sponsor TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN upline_sponsor");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches RENAME COLUMN bonuses TO achv_bonuses") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN achv_bonuses");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches ADD COLUMN bonuses DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN bonuses");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolpots RENAME TO ce_pools") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not rename TABLE ce_poolpots TO ce_pools");
    if (m_pDB->ExecDB(true, socket, "ALTER SEQUENCE ce_poolpots_id_seq RENAME TO ce_pools_id_seq") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not rename SEQUENCE ce_poolpots_id_seq");
    if (m_pDB->ExecDB(true, socket, "ALTER INDEX ce_poolpots_pkey RENAME TO ce_pools_pkey") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not rename SEQUENCE ce_poolpots_id_seq");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolrules RENAME COLUMN poolpot_id TO pool_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN poolpot_id");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_pools ADD COLUMN pool_type VARCHAR(1)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN pool_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers RENAME COLUMN api_key TO apikey_hash") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN api_key TO apikey_hash");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers RENAME COLUMN password TO password_hash") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN password TO password_hash");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers ADD COLUMN firstname TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN firstname");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers ADD COLUMN lastname TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN lastname");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules RENAME COLUMN start_gen TO generation") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN start_gen");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN end_gen") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not DROP COLUMN end_gen");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN qualify_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not DROP COLUMN qualify_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN qualify_threshold") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not DROP COLUMN qualify_threshold");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN paytype") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - breakdown could not DROP COLUMN paytype");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN event SMALLINT DEFAULT 1") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not ADD COLUMN event");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN inv_type SMALLINT DEFAULT 1") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not ADD COLUMN inv_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ADD COLUMN inv_type SMALLINT DEFAULT 1") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - receipts could not ADD COLUMN inv_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts RENAME COLUMN purchase_date TO wholesale_date") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - receipts could not RENAME COLUMN purchase_date TO wholesale_date");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ADD COLUMN retail_date DATE") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - receipts could not ADD COLUMN retail_date");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_cmcommrules RENAME COLUMN start_gen TO generation") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - cmcommrules could not RENAME COLUMN start_gen");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_cmcommrules DROP COLUMN end_gen") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - cmcommrules could not DROP COLUMN end_gen");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers ADD COLUMN cell VARCHAR(15)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN cell to systemusers");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN firstname TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN firstname to users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN lastname TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN lastname to users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN email VARCHAR(254)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN email to users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN cell VARCHAR(15)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN cell to users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN password_hash VARCHAR(128)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN password to users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN salt VARCHAR(32)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not ADD COLUMN salt to users");

    // Add Indexes for ledger_totals and receipt_totals //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ledger_batch_id ON ce_ledger(batch_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_ledger_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ledger_totals_system_id ON ce_ledger_totals(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX ce_ledger_totals_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_ledger_totals_user_id ON ce_ledger_totals(user_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX ce_ledger_totals_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipt_totals_system_id ON ce_receipt_totals(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX ce_receipt_totals_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipt_totals_user_id ON ce_receipt_totals(user_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_receipt_totals_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipt_totals_count ON ce_receipt_totals(count)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_receipt_totals_count");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_batch_id ON ce_breakdown(batch_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_breakdown_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_generation ON ce_breakdown(generation)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_breakdown_generation");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_receipt_id ON ce_breakdown(receipt_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_breakdown_receipt_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_achvbonus_system_id ON ce_achvbonus(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_achvbonus_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_systemusers_email ON ce_systemusers(email)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_systemusers_email");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipts_amount ON ce_receipts(amount)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_receipts_amount");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_bonus_system_id ON ce_bonus(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_bonus_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_email ON ce_users(email)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - CREATE INDEX idx_ce_users_email");

    m_pDB->ExecDB(true, socket, "COMMIT");
    Update(1.01, notes.c_str());
    //Debug(DEBUG_MESSAGE, "CMigrations::Migrate_1_1 - Successfully Migrated 1.10");
	return 1.01;
}

/////////////////////////////////
// Clean up the ce_ranks table //
/////////////////////////////////
bool CMigrations::CleanupCeRanks(void)
{
    int socket = 0;

    stringstream ssCount;
    ssCount << "SELECT count(*) FROM ce_ranks WHERE system_id=1";
    if (m_pDB->GetFirstDB(socket, ssCount) == 0)
    {
        Debug(DEBUG_TRACE, "CMigrations::CleanupCeRanks - No ranks. Skip this function");
        return true;
    }

    // Select all the ce_rank records //
    CConn *conn;
    stringstream ss;
    if ((conn = m_pDB->ExecDB(true, socket, ss << "SELECT id, batch_id, user_id, rank FROM ce_ranks WHERE system_id=1")) == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::CleanupCeRanks - There was a problem with a SELECT statement");

    string strSQL = "DELETE FROM ce_ranks WHERE";

    // Allow user_id and batch_id to pass once //
    map <string, string> UsersMap;
    while (m_pDB->FetchRow(conn) == true)
    {
        string id = conn->m_RowMap[0];
        string batch_id = conn->m_RowMap[1];
        string user_id = conn->m_RowMap[2];
        string rank = conn->m_RowMap[3];

        string searchstr = user_id+"-"+batch_id+"-"+rank;
        if (UsersMap[searchstr].size() == 0)
        {
            //UsersMap[searchstr+"-"+id] = "found";
            UsersMap[searchstr] = "found";
        }
        else
        {
            UsersMap[searchstr+"-"+id] = id;
        }
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
        return Debug(DEBUG_ERROR, "CMigrations::CleanupCeRanks - ThreadReleaseConn == false");

    // Loop through all records //
    int count = 0;
    for (map<string, string>::iterator i=UsersMap.begin(); i!=UsersMap.end(); ++i)
    {
        if (i->second != "found")
        {
            if (count == 0)
                strSQL += " id="+i->second;
            else
                strSQL += " OR id="+i->second;

            count++;
            // It got too big. Clear it out //
            if (count > MAX_SQL_APPEND)
            {
                Debug(DEBUG_DEBUG, "CMigrations::CleanupCeRanks - duplicate_count", count);

                if (m_pDB->ExecDB(true, socket, strSQL.c_str()) == NULL)
                    return Debug(DEBUG_ERROR, "CMigrations::CleanupCeRanks - There was a problem with a DELETE statment #1");
                strSQL.clear();
                strSQL = "DELETE FROM ce_ranks WHERE";
                count = 0;
            }
        }
    }

    // Run DELETE statement //
    if (count > 0)
    {
        Debug(DEBUG_DEBUG, "CMigrations::CleanupCeRanks - #2 - duplicate_count", count);
        if (m_pDB->ExecDB(true, socket, strSQL.c_str()) == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::CleanupCeRanks - There was a problem with a DELETE statment #2");
    }

    return true;
}

//////////////////////////
// Rollback 1.1 changes //
//////////////////////////
bool CMigrations::Rollback_1_01()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ledger_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_ledger_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ledger_totals_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_ledger_totals_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_ledger_totals_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_ledger_totals_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipt_totals_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_receipt_totals_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipt_totals_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_receipt_totals_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipt_totals_count") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_receipt_totals_count");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_breakdown_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_generation") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_breakdown_generation");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_receipt_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_breakdown_receipt_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_achvbonus_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_achvbonus_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_systemusers_email") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_systemusers_email");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipts_amount") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_receipts_amount");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_bonus_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_bonus_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_email") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - DROP INDEX idx_ce_users_email");
    
    // Keep commission engine site separate //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ces_sysuser_passreset") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ces_user_passreset");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ces_sysuser_login") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ces_user_login");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ces_user_passreset") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ces_user_passreset");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ces_user_login") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ces_user_login");

    // Handle Tables //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_pools ADD COLUMN qualify_type VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not ADD COLUMN qualify_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_pools DROP COLUMN pool_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN pool_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_pools RENAME TO ce_poolpots") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not rename TABLE ce_pools TO ce_poolpots");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_audit_users") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ce_audit_users");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_apikeys") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ce_apikeys");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_ledger_totals") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ce_ledger_totals");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_receipt_totals") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP TABLE ce_receipts_totals");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches RENAME COLUMN created_at TO create_at") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not RENAME COLUMN created_at");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolrules DROP COLUMN qualify_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN qualify_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN minpay") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN minpay");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ledger DROP COLUMN transaction_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN transaction_id");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN upline_parent") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN upline_parent");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN upline_sponsor") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN upline_sponsor");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches DROP COLUMN bonuses") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN bonuses");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches RENAME COLUMN achv_bonuses TO bonuses") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not RENAME achv_bonuses");
    if (m_pDB->ExecDB(true, socket, "ALTER SEQUENCE ce_pools_id_seq RENAME TO ce_poolpots_id_seq") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not rename SEQUENCE ce_pools_id_seq");
    if (m_pDB->ExecDB(true, socket, "ALTER INDEX ce_pools_pkey RENAME TO ce_poolpots_pkey") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not rename SEQUENCE ce_pools_pkey");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolrules RENAME COLUMN pool_id TO poolpot_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not RENAME COLUMN pool_id");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers RENAME COLUMN apikey_hash TO api_key") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not RENAME COLUMN apikey_hash TO api_key");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers RENAME COLUMN password_hash TO password") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not RENAME COLUMN password_hash TO password");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers DROP COLUMN firstname") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN firstname");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers DROP COLUMN lastname") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_1 - Could not DROP COLUMN lastname");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules RENAME COLUMN generation TO start_gen") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN generation");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN end_gen SMALLINT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not RENAME COLUMN generation");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN event") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not DROP COLUMN event");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN inv_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not DROP COLUMN inv_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts DROP COLUMN inv_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - receipts could not DROP COLUMN inv_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts RENAME COLUMN retail_date TO purchase_date") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - receipts could not RENAME COLUMN retail_date");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts DROP COLUMN wholesale_date") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - receipts could not DROP COLUMN wholesale_date");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_cmcommrules RENAME COLUMN generation TO start_gen") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - cmcommrules could not RENAME COLUMN generation");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_cmcommrules ADD COLUMN end_gen SMALLINT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - cmcommrules could not AND COLUMN end_gen");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN qualify_type VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not ADD COLUMN qualify_type");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN qualify_threshold FLOAT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - commrules could not ADD COLUMN qualify_threshold");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN paytype VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - breakdown could not ADD COLUMN paytype");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systemusers DROP COLUMN cell") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN cell from systemusers");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN firstname") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN firstname from users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN lastname") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN lastname from users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN email") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN email from users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN cell") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN cell from users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN password_hash") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN password from users");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN salt") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP COLUMN salt");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_audit_ranks") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP TABLE ce_audit_ranks");
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_audit_generations") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_1 - Could not DROP TABLE ce_audit_generations");

    m_pDB->ExecDB(true, socket, "COMMIT");
    Debug(DEBUG_MESSAGE, "CMigrations::Rollback_1_1 - Successfully Rolled Back 1.01");
    return true;
}

//////////////////////////////////
// Frame out the second upgrade //
//////////////////////////////////
double CMigrations::Migrate_1_02()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

	// All these migrations are after the intial demo server setup //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ADD COLUMN label VARCHAR(64);") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_rankrules ADD COLUMN label");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts RENAME COLUMN amount TO wholesale_price") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_receipts RENAME COLUMN amount TO wholesale_price");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ADD COLUMN retail_price DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_receipts ADD COLUMN retail_price");

    // Change ce_userstats_month //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN group_sales TO group_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_month RENAME COLUMN group_sales TO group_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN customer_sales TO customer_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_month RENAME COLUMN customer_sales TO customer_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN affiliate_sales TO affiliate_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_month RENAME COLUMN affiliate_sales TO affiliate_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN group_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_month ADD COLUMN group_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN customer_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_month ADD COLUMN customer_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN affiliate_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_month ADD COLUMN affiliate_retail_sales");

    // Change ce_userstats_total //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total RENAME COLUMN group_sales TO group_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_total RENAME COLUMN group_sales TO group_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total RENAME COLUMN customer_sales TO customer_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_total RENAME COLUMN customer_sales TO customer_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total RENAME COLUMN affiliate_sales TO affiliate_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_total RENAME COLUMN affiliate_sales TO affiliate_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total ADD COLUMN group_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_total ADD COLUMN group_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total ADD COLUMN customer_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_total ADD COLUMN customer_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total ADD COLUMN affiliate_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_userstats_total ADD COLUMN affiliate_retail_sales");

    // Change ce_batches //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches RENAME COLUMN receipts TO receipts_wholesale") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_batches RENAME COLUMN receipts TO receipts_wholesale");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches ADD COLUMN receipts_retail DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_2 - Could not ALTER TABLE ce_batches ADD COLUMN receipts_retail");

    m_pDB->ExecDB(true, socket, "COMMIT");
    Update(1.02, "Add label to ce_rankrules\n");
    return 1.02;
}

//////////////////
// Rollback 1.2 //
//////////////////
bool CMigrations::Rollback_1_02()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

    // All these migrations are after the intial demo server setup //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules DROP COLUMN label") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_rankrules DROP COLUMN label");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts RENAME COLUMN wholesale_price TO amount") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_receipts RENAME COLUMN wholesale_price TO amount");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts DROP COLUMN retail_price") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_receipts DROP COLUMN retail_price");
    
    // Change ce_userstats_month //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN group_wholesale_sales TO group_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_month RENAME COLUMN group_wholesale_sales TO group_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN customer_wholesale_sales TO customer_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_month RENAME COLUMN customer_wholesale_sales TO customer_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN affiliate_wholesale_sales TO affiliate_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_month RENAME COLUMN affiliate_wholesale_sales TO affiliate_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN group_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_month DROP COLUMN group_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN customer_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_month DROP COLUMN customer_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN affiliate_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_month DROP COLUMN affiliate_retail_sales");

    // Change ce_userstats_total //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total RENAME COLUMN group_wholesale_sales TO group_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_total RENAME COLUMN group_wholesale_sales TO group_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total RENAME COLUMN customer_wholesale_sales TO customer_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_total RENAME COLUMN customer_wholesale_sales TO customer_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total RENAME COLUMN affiliate_wholesale_sales TO affiliate_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_total RENAME COLUMN affiliate_wholesale_sales TO affiliate_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total DROP COLUMN group_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_total DROP COLUMN group_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total DROP COLUMN customer_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_total DROP COLUMN customer_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_total DROP COLUMN affiliate_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_userstats_total DROP COLUMN affiliate_retail_sales");

    // Change ce_batches //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches RENAME COLUMN receipts_wholesale TO receipts") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_batches RENAME COLUMN receipts_wholesale TO receipts");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_batches DROP COLUMN receipts_retail") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_2 - Could not ALTER TABLE ce_batches DROP COLUMN receipts_retail");

    m_pDB->ExecDB(true, socket, "COMMIT");
    Debug(DEBUG_MESSAGE, "CMigrations::Rollback_1_02 - Successfully Rolled Back 1.02");
    return true;
}

////////////////////
// Migrate to 1.2 //
////////////////////
double CMigrations::Migrate_1_03()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

    // Receipts //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ADD COLUMN metadata_onadd TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_3 - Could not ALTER TABLE ce_receipts ADD COLUMN metadata_onadd"); 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ADD COLUMN metadata_onupdate TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_3 - Could not ALTER TABLE ce_receipts ADD COLUMN metadata_onupdate");

    // Breakdown //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN receipt_id_internal BIGINT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_3 - Could not ALTER TABLE ce_breakdown ADD COLUMN receipt_id_internal");   

    m_pDB->ExecDB(true, socket, "COMMIT");
    Update(1.03, "Add both metadata fields to ce_receipts for controlpad compatibility\n");
    return 1.03;
}

//////////////////
// Rollback 1.3 //
//////////////////
bool CMigrations::Rollback_1_03()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

    // Receipts //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts DROP COLUMN metadata_onadd") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_3 - Could not ALTER TABLE ce_receipts DROP COLUMN metadata_onadd"); 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts DROP COLUMN metadata_onupdate") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_3 - Could not ALTER TABLE ce_receipts DROP COLUMN metadata_onupdate"); 

    // Breakdown //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN receipt_id_internal") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_3 - Could not ALTER TABLE ce_breakdown DROP COLUMN receipt_id_internal"); 

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

////////////////////
// Migrate to 1.4 //
////////////////////
double CMigrations::Migrate_1_04()
{
    int socket = 0;

    m_pDB->ExecDB(true, socket, "BEGIN");

    // Systems //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN signupbonus DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_4 - Could not ALTER TABLE ce_systems ADD COLUMN signupbonus");   

    // Signup Bonus //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_signupbonus(id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, from_user_id TEXT, batch_id BIGINT, signupbonus DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_4 - Could not CREATE TABLE ce_signupbonus");

    // Fix checkpoint values //
    if (m_pDB->ExecDB(true, socket, "UPDATE ce_checkpoint SET checkpoint=23 WHERE checkpoint=22") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_4 - Could not UPDATE ce_checkpoint SET checkpoint=23");
    if (m_pDB->ExecDB(true, socket, "UPDATE ce_checkpoint SET checkpoint=22 WHERE checkpoint=21") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_4 - Could not UPDATE ce_checkpoint SET checkpoint=22");
    if (m_pDB->ExecDB(true, socket, "UPDATE ce_checkpoint SET checkpoint=21 WHERE checkpoint=20") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_4 - Could not UPDATE ce_checkpoint SET checkpoint=21");

    // This leaves 20 open for signup bonuses //

    Update(1.04, "Add signup bonus to ce_systems\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.04;
}

//////////////////
// Rollback 1.4 //
//////////////////
bool CMigrations::Rollback_1_04()
{
    int socket = 0;
    
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Systems //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN signupbonus") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_4 - Could not ALTER TABLE ce_systems DROP COLUMN signupbonus"); 
   
    // Signup Bonus //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_signupbonus") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_4 - Could not DROP TABLE ce_signupbonus");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

/////////////////
// Migrate 1.5 //
/////////////////
double CMigrations::Migrate_1_05()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // ce_userstats_month and lvl1 //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN reseller_wholesale_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_userstats_month ADD COLUMN reseller_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN reseller_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_userstats_month ADD COLUMN reseller_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN reseller_count INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_userstats_month ADD COLUMN reseller_count");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN reseller_count INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_userstats_month ADD COLUMN reseller_count");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN my_wholesale_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN my_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN my_retail_sales DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN my_retail_sales");

    // Add stack type to ce_systems //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN stack_type VARCHAR(1) DEFAULT '1'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_5 - Could not ALTER TABLE ce_systems ADD COLUMN stack_type");

    Update(1.05, "Add more to user statistics\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.05;
}

//////////////////
// Rollback 1.5 //
//////////////////
bool CMigrations::Rollback_1_05()
{
    int socket = 0;
     m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN reseller_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_5 - Could not ALTER TABLE ce_userstats_month DROP COLUMN reseller_wholesale_sales"); 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN reseller_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_5 - Could not ALTER TABLE ce_userstats_month DROP COLUMN reseller_retail_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN reseller_count") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_5 - Could not ALTER TABLE ce_userstats_month DROP COLUMN reseller_count");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN reseller_count") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_5 - Could not ALTER TABLE ce_userstats_month DROP COLUMN reseller_count");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN my_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_5 - Could not ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN my_wholesale_sales"); 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN my_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_5 - Could not ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN my_retail_sales");

    // Drop stack type to ce_systems //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN stack_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_05 - Could not ALTER TABLE ce_systems DROP COLUMN stack_type");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

/////////////////
// Migrate 1.6 //
/////////////////
double CMigrations::Migrate_1_06()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Add index to ce_receipts table //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_receipts_receipt_id ON ce_receipts(receipt_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - CREATE INDEX idx_ce_receipts_receipt_id");

    // ce_rankrules_missed //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_rankrules_missed(id SERIAL PRIMARY KEY, system_id INT4, batch_id INT4, user_id TEXT, rule_id INT4, rank VARCHAR(2) default '0', qualify_type VARCHAR(2), qualify_threshold FLOAT, actual_value FLOAT, diff FLOAT, created_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not CREATE TABLE ce_rankrules_missed");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN teamgenmax VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not ALTER TABLE ce_systems ADD COLUMN teamgenmax");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN team_wholesale_sales NUMERIC(37, 4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not ALTER TABLE ce_userstats_month ADD COLUMN team_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN team_retail_sales NUMERIC(37, 4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not ALTER TABLE ce_userstats_month ADD COLUMN team_retail_sales");

    // ce_userstats_month_rank //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_month_rank(id SERIAL PRIMARY KEY, system_id INT4, batch_id INT4, user_id TEXT, rank VARCHAR(2) default '0', total INT4, created_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not CREATE TABLE ce_userstats_month_rank");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ADD COLUMN sumrankstart VARCHAR(2) default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not ALTER TABLE ce_rankrules ADD COLUMN sumrankstart");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ADD COLUMN sumrankend VARCHAR(2) default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_6 - Could not ALTER TABLE ce_rankrules ADD COLUMN sumrankend");

    string update = "Added index receipt_id to ce_receipts\n";
    update += "Added table and functionality to ce_rankrules_missed\n";
    update += "Added teamgenmax to ce_systems table\n";
    update += "Added team sales data to ce_userstats_month table\n";
    update += "Added table ce_userstats_month_rank\n";
    update += "Added column sumrankstart and sumrankend to ce_rankrules\n";
    Update(1.06, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.06;
}

//////////////////
// Rollback 1.6 //
//////////////////
bool CMigrations::Rollback_1_06()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_receipts_receipt_id") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - DROP INDEX idx_ce_receipts_receipt_id");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_rankrules_missed") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - DROP TABLE ce_rankrules_missed");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN teamgenmax") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - Could not ALTER TABLE ce_systems DROP COLUMN teamgenmax");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN team_wholesale_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - Could not ALTER TABLE ce_userstats_month DROP COLUMN team_wholesale_sales");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN team_retail_sales") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - Could not ALTER TABLE ce_userstats_month DROP COLUMN team_retail_sales");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_month_rank") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - DROP TABLE ce_userstats_month_rank");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules DROP COLUMN sumrankstart") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - Could not ALTER TABLE ce_rankrules DROP COLUMN sumrankstart");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules DROP COLUMN sumrankend") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_6 - Could not ALTER TABLE ce_rankrules DROP COLUMN sumrankend");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

/////////////////
// Migrate 1.7 //
/////////////////
double CMigrations::Migrate_1_07()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle United League Database change to reseller //
    stringstream ss;
    if (m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_systemusers WHERE id=2 AND email='master@unitedleague.com'") == 1)
    {
        stringstream ssUpdate;
        ssUpdate << "UPDATE ce_rankrules SET qualify_type='19' WHERE qualify_type='9' AND system_id='1'";
        if (m_pDB->ExecDB(socket, ssUpdate) == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_7 - UPDATE ce_rankrules Error");
    }

    // Needed for Chalkatour - Combined totals //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN group_and_my_wholesale NUMERIC(37, 4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_7 - Could not ALTER TABLE ce_userstats_month ADD COLUMN group_and_my_wholesale");

    /*
    // ce_userstats_month_rank //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_month_rank(id SERIAL PRIMARY KEY, system_id INT4, batch_id INT4, user_id TEXT, rank VARCHAR(2) default '0', total INT4, created_at TIMESTAMP DEFAULT now())") == false)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_7 - Could not CREATE TABLE ce_userstats_month_rank");

    */
    string update = "Added coumn group_and_my_wholesale to ce_userstats_month\n";
    /*
    update += "Added table and functionality to ce_rankrules_missed\n";
    update += "Added teamgenmax to ce_systems table\n";
    update += "Added team sales data to ce_userstats_month table\n";
    update += "Added table ce_userstats_month_rank\n";
    update += "Added column sumrankstart and sumrankend to ce_rankrules\n";
    */
    
    Update(1.07, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.07;
}

//////////////////
// Rollback 1.7 //
//////////////////
bool CMigrations::Rollback_1_07()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    stringstream ss;
    if (m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM ce_systemusers WHERE id=2 AND email='master@unitedleague.com'") == 1)
    {
        stringstream ssUpdate;
        ssUpdate << "UPDATE ce_rankrules SET qualify_type='9' WHERE qualify_type='19' AND system_id='1'";
        if (m_pDB->ExecDB(socket, ssUpdate) == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_7 - UPDATE ce_rankrules Error");
    }

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN group_and_my_wholesale") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_7 - Could not ALTER TABLE ce_userstats_month DROP COLUMN group_and_my_wholesale");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

/////////////////
// Migrate 1.8 //
/////////////////
double CMigrations::Migrate_1_08()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Needed for Chalkatour - Combined totals //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN team_and_my_wholesale NUMERIC(37, 4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_8 - Could not ALTER TABLE ce_userstats_month ADD COLUMN team_and_my_wholesale");

    // ce_settings needed for ever 2 hour calc system //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_settings(id SERIAL PRIMARY KEY, system_id INT4, user_id TEXT, varname TEXT, value TEXT, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_8 - Could not CREATE TABLE ce_userstats_month_rank");

    string update = "Added column team_and_my_wholesale to ce_userstats_month\n";
    update += "Dropped column group_and_my_wholesale to ce_userstats_month\n";
    update += "Added ce_settings table\n";

    Update(1.08, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.08;
}

//////////////////
// Rollback 1.8 //
//////////////////
bool CMigrations::Rollback_1_08()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN team_and_my_wholesale") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_08 - Could not ALTER TABLE ce_userstats_month DROP COLUMN team_and_my_wholesale");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_settings") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_08 - Could not DROP TABLE ce_settings");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.09 //
//////////////////
double CMigrations::Migrate_1_09()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // ce_basic_commrules needed rankless type commissions //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_basic_commrules(id SERIAL PRIMARY KEY, system_id INT4, generation INT4, qualify_type VARCHAR(2), start_threshold DOUBLE PRECISION default 0, end_threshold DOUBLE PRECISION default 0, inv_type SMALLINT, event SMALLINT, percent FLOAT, modulus INT4, paylimit INT4, pv_override BOOL DEFAULT 'false', paytype VARCHAR(1) DEFAULT '1', disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not CREATE TABLE ce_basic_commrules");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_settings ALTER COLUMN user_id DROP DEFAULT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_breakdown ADD COLUMN comm_type");

    // Needed for Chalkatour - Combined totals //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN comm_type VARCHAR(1) DEFAULT '1'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_breakdown ADD COLUMN comm_type");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_settings ADD COLUMN webpage TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_settings ADD COLUMN webpage");

    if (m_pDB->ExecDB(true, socket, "DELETE FROM ce_checkpoint") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not DELETE FROM ce_checkpoint");

    // Set Default Timezone //
    if (m_pDB->ExecDB(true, socket, "INSERT INTO ce_settings(system_id, varname, value) VALUES (0, 'timezone', 'America/Denver')") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not add default timezone into ce_settings");

     // Set Default System //
    if (m_pDB->ExecDB(true, socket, "INSERT INTO ce_settings(system_id, varname, value) VALUES (0, 'defaultsystem', '1')") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not add default defaultsystem into ce_settings");

    // Allow a piggyback_id - This is a system_id that we pull the users and receipts from. Perfect for points/rewards systems //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN piggy_id INT4 DEFAULT 0") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_systems ADD COLUMN piggy_id");

    // Allow basic comm rule to allow personal volume calculations and not receipt based //
    // This needed for chalkatour cause the combine two types on inventories and some people //
    // would be shorted on their designer dollars //

    // wholesale_date needs timestamp //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ALTER COLUMN wholesale_date TYPE TIMESTAMP") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_receipts ALTER COLUMN wholesale_date TYPE TIMESTAMP");

    // retail_date needs timestamp //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ALTER COLUMN retail_date TYPE TIMESTAMP") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_receipts ALTER COLUMN retail_date TYPE TIMESTAMP");

    // Use the created_at timestamp to do the best we can at correcting this problem //
    stringstream ssDate;
    ssDate << "UPDATE ce_receipts SET wholesale_date=wholesale_date::DATE+(interval '1 hour' * EXTRACT(HOURS FROM created_at))+(interval '1 minutes' * EXTRACT(MINUTES FROM created_at))+(interval '1 seconds' * EXTRACT(SECONDS FROM created_at)) WHERE wholesale_date IS NOT NULL";
    if (m_pDB->ExecDB(true, socket, ssDate) == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Problems updating wholesale_date with timestamp");

    stringstream ssDate2;
    ssDate2 << "UPDATE ce_receipts SET retail_date=retail_date::DATE+(interval '1 hour' * EXTRACT(HOURS FROM created_at))+(interval '1 minutes' * EXTRACT(MINUTES FROM created_at))+(interval '1 seconds' * EXTRACT(SECONDS FROM created_at)) WHERE retail_date IS NOT NULL";
    if (m_pDB->ExecDB(true, socket, ssDate2) == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Problems updating wholesale_date with timestamp");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN paytype VARCHAR(1) DEFAULT '1'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Problems ALTER TABLE ce_commrules ADD COLUMN paytype");



    // Breakdown needs basic_id //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN basic_id INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Problems ALTER TABLE ce_commrules ADD COLUMN paytype");

    // Allow Breakdown to have master receipt information //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN metadata_onadd TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Problems ALTER TABLE ce_breakdown ADD COLUMN metadata_onadd");

    // ce_downline_bonusrules //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_downline_bonusrules(id SERIAL PRIMARY KEY, system_id INT4, rank VARCHAR(3), amount DECIMAL(37,4), generation INT4, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not CREATE TABLE ce_basic_commrules");

    string update = "Added table ce_basic_commrules\n";
    update += "Added column comm_type to ce_breakdown\n";
    update += "Added column webpage to ce_settings\n";
    update += "Added default timezone entry into ce_settings\n";
    update += "Added piggy_id into ce_systems\n";
    update += "Adjusted ce_receipts wholesale_date and retail_date to timestamps\n";

    Update(1.09, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.09;
}

///////////////////
// Rollback 1.09 //
///////////////////
bool CMigrations::Rollback_1_09()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_basic_commrules") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_09 - Could not DROP TABLE ce_basic_commrules");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN comm_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_09 - Could not ALTER TABLE ce_breakdown DROP COLUMN comm_type");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_settings DROP COLUMN webpage") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_09 - Could not ALTER TABLE ce_settings DROP COLUMN webpage");

    if (m_pDB->ExecDB(true, socket, "DELETE FROM ce_settings WHERE system_id=0 AND varname='timezone'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_09 - Could not DELETE timezone FROM ce_settings");

    if (m_pDB->ExecDB(true, socket, "DELETE FROM ce_settings WHERE system_id=0 AND varname='defaultsystem'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_09 - Could not DELETE defaultsystem FROM ce_settings");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN piggy_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_09 - Could not ALTER TABLE ce_systems DROP COLUMN piggy_id");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ALTER COLUMN wholesale_date TYPE DATE") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_receipts ALTER COLUMN wholesale_date TYPE DATE");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ALTER COLUMN retail_date TYPE DATE") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_receipts ALTER COLUMN retail_date TYPE DATE");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN paytype") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_commrules DROP COLUMN paytype");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN basic_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE ce_breakdown DROP COLUMN basic_id");
 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN metadata_onadd") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not ALTER TABLE metadata_onadd DROP COLUMN metadata_onadd");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_downline_bonusrules") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_09 - Could not DROP TABLE ce_downline_bonusrules");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.10 //
//////////////////
double CMigrations::Migrate_1_10()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN psq INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_10 - Could not ALTER TABLE ce_userstats_month_lvl1 ADD COLUMN psq");

    string update = "Added psq column to ce_userstats_month_lvl1 TABLE\n";

    Update(1.10, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.10;
}

///////////////////
// Rollback 1.09 //
///////////////////
bool CMigrations::Rollback_1_10()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN psq") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_10 - Could not ALTER TABLE ce_userstats_month_lvl1 DROP COLUMN psq");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.11 //
//////////////////
double CMigrations::Migrate_1_11()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN carrer_rank INT4 DEFAULT 0") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_11 - Could not ALTER TABLE ce_users ADD COLUMN carrer_rank");

    string update = "Added carrer_rank column to ce_users TABLE\n";

    Update(1.11, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.11;
}

///////////////////
// Rollback 1.11 //
///////////////////
bool CMigrations::Rollback_1_11()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN carrer_rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_11 - Could not ALTER TABLE ce_users DROP COLUMN carrer_rank");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.12 //
//////////////////
double CMigrations::Migrate_1_12()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    //////////////////////////////////////////
    // UPDATE carrer_rank on ce_users table //
    //////////////////////////////////////////
    map <string, string> AllUsersMap;
    CConn *conn;
    stringstream ssSQL;
    if ((conn = m_pDB->ExecDB(socket, ssSQL << "SELECT system_id, user_id FROM ce_users ORDER BY id")) == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_12 - Problems with SELECT");

    while (m_pDB->FetchRow(conn) == true)
    {

        string systemid = conn->m_RowMap[0].c_str();
        string userid = conn->m_RowMap[1].c_str();
        string mapindex = userid+"/"+systemid;
        AllUsersMap[mapindex] = userid;
    }

    if (ThreadReleaseConn(conn->m_Resource) == false)
        return Debug(DEBUG_ERROR, "CDb::GetSystemsUsed - ThreadReleaseConn == false");

    // This not needed anymore. Used for Chalk. Causes extremely long migration time for United. 
    // New companies don't need to worry about this //

    // Loop through all users and update carrer_rank //
//    map <string, string>::iterator j;
//    for (j=AllUsersMap.begin(); j != AllUsersMap.end(); ++j) 
//    {
//        string useridandsystem = j->first;
//        string userid = j->second;

//        char *psystem = strstr((char *)useridandsystem.c_str(), "/");
//        psystem = &psystem[1];
//        int systemid = atoi(psystem);

//        stringstream ssCount;
//        int count = m_pDB->GetFirstDB(socket, ssCount << "SELECT count(*) FROM ce_ranks WHERE system_id=" << systemid << " AND user_id='" << userid << "'");
//        if (count > 0)
//        {
//            stringstream ssSELECT;
//            int rank = m_pDB->GetFirstDB(socket, ssSELECT << "SELECT rank FROM ce_ranks WHERE system_id=" << systemid << " AND user_id='" << userid << "' ORDER BY rank DESC");
//            stringstream ssUPDATE;
//            if (m_pDB->ExecDB(socket, ssUPDATE << "UPDATE ce_users SET carrer_rank=" << rank << " WHERE user_id='" << userid << "' AND system_id=" << systemid) == false)
//                return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_12 - UPDATE ce_users (carrer_rank) ERROR");
//        }
//    }

    // 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_rank RENAME TO ce_userstats_month_leg_rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_12 - Could not CREATE TABLE ce_userstats_month_leg_rank");

    // ce_userstats_month_rank //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_userstats_month_lvl1_rank(id SERIAL PRIMARY KEY, system_id INT4, batch_id INT4, user_id TEXT, rank VARCHAR(2) default '0', total INT4, created_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_12 - Could not CREATE TABLE ce_userstats_month_lvl1_rank");

    string update = "Repaired carrer_rank historical values. Added and renamed rank stat tracking tables \n";

    Update(1.12, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.12;
}

///////////////////
// Rollback 1.12 //
///////////////////
bool CMigrations::Rollback_1_12()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank RENAME TO ce_userstats_month_rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_12 - Could not ALTER TABLE ce_userstats_month_leg_rank");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_userstats_month_lvl1_rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_12 - Could not DROP TABLE ce_userstats_month_lvl1_rank");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}


//////////////////
// Migrate 1.13 //
//////////////////
double CMigrations::Migrate_1_13()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank ADD COLUMN userdata TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_13 - Could not ALTER TABLE ce_userstats_month_leg_rank ADD COLUMN userdata");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1_rank ADD COLUMN userdata TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_13 - Could not ALTER TABLE ce_userstats_month_lvl1_rank ADD COLUMN userdata");

    string update = "Added userdata column to rank tracking stats tables TABLE\n";

    Update(1.13, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.13;
}

///////////////////
// Rollback 1.13 //
///////////////////
bool CMigrations::Rollback_1_13()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank DROP COLUMN userdata") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_13 - Could not ALTER TABLE ce_userstats_month_leg_rank DROP COLUMN userdata");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1_rank DROP COLUMN userdata") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_13 - Could not ALTER TABLE ce_userstats_month_lvl1_rank DROP COLUMN userdata");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.14 //
//////////////////
double CMigrations::Migrate_1_14()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN advisor_id TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_14 - Could not ALTER TABLE ce_users ADD COLUMN advisor_id");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN upline_advisor TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_14 - Could not ALTER TABLE ce_users ADD COLUMN upline_advisor");

    string update = "Added columns to track advisor for chalkatour TABLE\n";

    Update(1.14, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.14;
}

///////////////////
// Rollback 1.14 //
///////////////////
bool CMigrations::Rollback_1_14()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN advisor_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_14 - Could not ALTER TABLE ce_users DROP COLUMN advisor_id");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN upline_advisor") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_14 - Could not ALTER TABLE ce_users DROP COLUMN upline_advisor");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.15 //
//////////////////
double CMigrations::Migrate_1_15()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN psq_limit INT4 DEFAULT 0") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - Could not ALTER TABLE ce_users ADD COLUMN advisor_id");

    // Create indexes to speed up mydownlinereport //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_advisor_id ON ce_users(advisor_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_users_advisor_id");

    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_rank_user_id ON ce_userstats_month_lvl1_rank(user_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_lvl1_rank_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_rank_system_id ON ce_userstats_month_lvl1_rank(system_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_lvl1_rank_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_rank_batch_id ON ce_userstats_month_lvl1_rank(batch_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_lvl1_rank_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_rank_rank ON ce_userstats_month_lvl1_rank(rank)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_lvl1_rank_rank");

    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_leg_rank_user_id ON ce_userstats_month_leg_rank(user_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_leg_rank_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_leg_rank_system_id ON ce_userstats_month_leg_rank(system_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_leg_rank_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_leg_rank_batch_id ON ce_userstats_month_leg_rank(batch_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_leg_rank_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_leg_rank_rank ON ce_userstats_month_leg_rank(rank)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_15 - CREATE INDEX idx_ce_userstats_month_leg_rank_rank");

    string update = "Added psq_limit to ce_systems TABLE\n";
    update += "Added tons of indexes for mydownlinereport query\n";

    Update(1.15, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.15;
}

///////////////////
// Rollback 1.15 //
///////////////////
bool CMigrations::Rollback_1_15()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN psq_limit") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_14 - Could not ALTER TABLE ce_users DROP COLUMN advisor_id");

    // Drop indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_advisor_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_users_advisor_id");

    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_rank_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_lvl1_rank_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_rank_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_lvl1_rank_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_rank_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_lvl1_rank_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_rank_rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_lvl1_rank_rank");

    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_leg_rank_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_leg_rank_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_leg_rank_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_leg_rank_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_leg_rank_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_leg_rank_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_leg_rank_rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_15 - DROP INDEX idx_ce_userstats_month_leg_rank_rank");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}


//////////////////
// Migrate 1.16 //
//////////////////
double CMigrations::Migrate_1_16()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Create indexes to speed up mydownlinereport //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_user_id ON ce_userstats_month(user_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_16 - CREATE INDEX idx_ce_userstats_month_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_batch_id ON ce_userstats_month(batch_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_16 - CREATE INDEX idx_ce_userstats_month_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_system_id ON ce_userstats_month(system_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_16 - CREATE INDEX idx_ce_userstats_month_system_id");

    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_user_id ON ce_userstats_month_lvl1(user_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_16 - CREATE INDEX idx_ce_userstats_month_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_batch_id ON ce_userstats_month_lvl1(batch_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_16 - CREATE INDEX idx_ce_userstats_month_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_userstats_month_lvl1_system_id ON ce_userstats_month_lvl1(system_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_16 - CREATE INDEX idx_ce_userstats_month_system_id");

    string update = "Added tons of indexes for mydownlinereport query\n";

    Update(1.16, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.16;
}

///////////////////
// Rollback 1.16 //
///////////////////
bool CMigrations::Rollback_1_16()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Drop indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_16 - DROP INDEX idx_ce_userstats_month_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_16 - DROP INDEX idx_ce_userstats_month_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_16 - DROP INDEX idx_ce_userstats_month_system_id");

    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_16 - DROP INDEX idx_ce_userstats_month_lvl1_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_16 - DROP INDEX idx_ce_userstats_month_lvl1_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_userstats_month_lvl1_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_16 - DROP INDEX idx_ce_userstats_month_lvl1_system_id");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.17 //
//////////////////
double CMigrations::Migrate_1_17()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Allow address fields in ce_users table //
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users ADD COLUMN address TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_17 - ALTER table ce_users ADD COLUMN address");
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users ADD COLUMN city TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_17 - ALTER table ce_users ADD COLUMN city");
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users ADD COLUMN state VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_17 - ALTER table ce_users ADD COLUMN state");
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users ADD COLUMN zip VARCHAR(5)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_17 - ALTER table ce_users ADD COLUMN zip");

    string update = "Allow address info stored in ce_users table\n";

    Update(1.17, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.17;
}

///////////////////
// Rollback 1.17 //
///////////////////
bool CMigrations::Rollback_1_17()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Remove address fields in ce_users table //
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users DROP COLUMN address") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_17 - ALTER table ce_users DROP COLUMN address");
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users DROP COLUMN city") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_17 - ALTER table ce_users DROP COLUMN city");
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users DROP COLUMN state") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_17 - ALTER table ce_users DROP COLUMN state");
    if (m_pDB->ExecDB(true, socket, "ALTER table ce_users DROP COLUMN zip") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_17 - ALTER table ce_users DROP COLUMN zip");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.18 //
//////////////////
double CMigrations::Migrate_1_18()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Allow address fields in ce_users table //
    if (m_pDB->ExecDB(true, socket, "INSERT INTO ce_settings (system_id, varname, value) VALUES (0, 'sim-inuse', 'sim1')") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_18 - INSERT INTO ce_settings");

    string update = "Add sim-inuse entry in ce_settings so we can altername between commission run simulations\n";

    Update(1.18, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.18;
}

///////////////////
// Rollback 1.18 //
///////////////////
bool CMigrations::Rollback_1_18()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Remove address fields in ce_users table //
    if (m_pDB->ExecDB(true, socket, "DELETE FROM ce_settings WHERE system_id=0 AND varname='sim-inuse'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_18 - ALTER table ce_users DROP COLUMN address");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.19 //
//////////////////
double CMigrations::Migrate_1_19()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Allow rankgenbonusrules table //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_rankgenbonusrules(id SERIAL PRIMARY KEY, system_id BIGINT, my_rank VARCHAR(3), user_rank VARCHAR(3), generation VARCHAR(3), bonus DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_19 - Create table ce_users ADD COLUMN address");

    // Table needed to store all rankgenbonus values //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_rankgenbonus(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, amount DECIMAL(37,4), event_date DATE, my_rank INT4, user_rank INT4, generation VARCHAR(3), userdata TEXT, rule_id INT4, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_19 - Create table ce_users ADD COLUMN address");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank ADD COLUMN generation INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_19 - ALTER table ADD COLUMN generation INT4");

    if (m_pDB->ExecDB(true, socket, "UPDATE ce_userstats_month_leg_rank SET generation='1'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_19 - UPDATE ce_userstats_month_leg_rank SET generation='1'");

    // Add indexes to speed the ce_checkpoint up //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_checkpoint_system_id ON ce_checkpoint(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_19 - CREATE INDEX idx_ce_checkpoint_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_checkpoint_batch_id ON ce_checkpoint(batch_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_19 - CREATE INDEX idx_ce_checkpoint_batch_id");

    string update = "Create the ce_rankgenbonus table\n";
    update += "Add generation column to ce_userstats_month_leg_rank\n";
    update += "Add ce_checkpoint indexes\n";

    Update(1.19, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.19;
}

///////////////////
// Rollback 1.19 //
///////////////////
bool CMigrations::Rollback_1_19()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Remove rankgenbonusrules table //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_rankgenbonusrules") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_19 - DROP TABLE ce_rankgenbonusrules");

    // Remove rankgenbonus table //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_rankgenbonus") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_19 - DROP TABLE ce_rankgenbonus");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank DROP COLUMN generation") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_19 - ALTER table RENAME TO ce_userstats_month_leg_rank");

    // Drop the ce checkpoint indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_checkpoint_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_19 - DROP INDEX idx_ce_checkpoint_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_checkpoint_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_19 - DROP INDEX idx_ce_checkpoint_batch_id");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.20 //
//////////////////
double CMigrations::Migrate_1_20()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Allow ce_breakdown_gen table //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_breakdown_gen(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, parent_id TEXT, generation VARCHAR(3), amount DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - Create table ce_breakdown_gen");

    // Allow ce_breakdown_users table //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_breakdown_users(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, parent_id TEXT, user_id TEXT, generation VARCHAR(3), amount DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - Create table ce_breakdown_users");

    // Allow ce_breakdown_users table //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_breakdown_orders(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, parent_id TEXT, user_id TEXT, ordernum TEXT, generation VARCHAR(3), amount DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - Create table ce_breakdown_users");

    // Add indexes to speed the ce_checkpoint up //
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_users_system_id ON ce_breakdown_users(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_users_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_users_batch_id ON ce_breakdown_users(batch_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_users_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_users_parent_id ON ce_breakdown_users(parent_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_users_parent_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_users_user_id ON ce_breakdown_users(user_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_users_user_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_users_generation ON ce_breakdown_users(generation)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_users_generation");

    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_orders_system_id ON ce_breakdown_orders(system_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_orders_system_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_orders_batch_id ON ce_breakdown_orders(batch_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_orders_batch_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_orders_parent_id ON ce_breakdown_orders(parent_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_orders_parent_id");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_breakdown_orders_user_id ON ce_breakdown_orders(user_id)") == NULL)
       return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_20 - CREATE INDEX idx_ce_breakdown_orders_user_id");

    string update = "Create the ce_breakdown_users table\n";
    update += "Create the ce_breakdown_orders table\n";
    //update += "Add ce_checkpoint indexes\n";

    Update(1.20, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.20;
}

///////////////////
// Rollback 1.20 //
///////////////////
bool CMigrations::Rollback_1_20()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");
    
    // Remove ce_breakdown_users table //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_breakdown_gen") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP TABLE ce_breakdown_gen");

    // Remove ce_breakdown_users table //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_breakdown_users") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP TABLE ce_breakdown_users");

    // Remove ce_breakdown_orders table //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_breakdown_orders") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP TABLE ce_breakdown_orders");

    // Drop the ce checkpoint indexes //
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_users_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_users_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_users_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_users_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_users_parent_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_users_parent_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_users_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_users_user_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_users_generation") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_users_generation");

    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_orders_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_orders_system_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_orders_batch_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_orders_batch_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_orders_parent_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_orders_parent_id");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_breakdown_orders_user_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_20 - DROP INDEX idx_ce_breakdown_orders_user_id");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.21 //
//////////////////
double CMigrations::Migrate_1_21()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_basic_commrules ADD COLUMN rank VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_21 - ALTER TABLE ce_basic_commrules ADD rank");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_21 - ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(3)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_21 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(3)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_21 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(3)");

    string update = "Alter table ce_basic_commrules add columns rank\n";
    update += "ce_rankrules ALTER COLUMN rank TYPE VARCHAR(3)\n";
    update += "ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(3)\n";
    update += "ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(3)\n";
    //update += "Add ce_checkpoint indexes\n";

    Update(1.21, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.21;
}

///////////////////
// Rollback 1.21 //
///////////////////
bool CMigrations::Rollback_1_21()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");
    
    // Remove the ranks columns from basic_commrules //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_basic_commrules DROP COLUMN rank") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_21 - ALTER TABLE ce_basic_commrules DROP COLUMN rank");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_21 - ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(2)");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_21 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(2)");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_21 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(2)");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.22 //
//////////////////
double CMigrations::Migrate_1_22()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(6)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ranks ALTER COLUMN rank TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_ranks ALTER COLUMN rank TYPE VARCHAR(6)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_basic_commrules  ALTER COLUMN rank TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_basic_commrules ALTER COLUMN rank TYPE VARCHAR(6)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(6)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(6)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(6)");

    string update = "Alter many tables ALTER COLUMN rank TYPE VARCHAR(6)\n";
    //update += "Add ce_checkpoint indexes\n";

    Update(1.22, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.22;
}

///////////////////
// Rollback 1.22 //
///////////////////
bool CMigrations::Rollback_1_22()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_22 - ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(2)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ranks ALTER COLUMN rank TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_ranks ALTER COLUMN rank TYPE VARCHAR(2)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_rankrules ALTER COLUMN rank TYPE VARCHAR(3)");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankstart TYPE VARCHAR(3)");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(3)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_22 - ALTER TABLE ce_rankrules ALTER COLUMN sumrankend TYPE VARCHAR(3)");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.23 //
//////////////////
double CMigrations::Migrate_1_23()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1_rank ALTER COLUMN rank TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_23 - ALTER TABLE ce_userstats_month_lvl1_rank ALTER COLUMN rank TYPE VARCHAR(6)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules_missed ALTER COLUMN rank TYPE VARCHAR(6)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_23 - ALTER TABLE ce_rankrules_missed ALTER COLUMN rank TYPE VARCHAR(6)");

    //if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_rank ALTER COLUMN rank TYPE VARCHAR(6)") == false)
    //    return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_23 - ALTER TABLE ce_rankrules_missed ALTER COLUMN rank TYPE VARCHAR(6)");

    string update = "Alter more tables ALTER COLUMN rank TYPE VARCHAR(6)\n";
    //update += "Add ce_checkpoint indexes\n";

    Update(1.23, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.23;
}

///////////////////
// Rollback 1.23 //
///////////////////
bool CMigrations::Rollback_1_23()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_lvl1_rank ALTER COLUMN rank TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_23 - ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(2)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules_missed ALTER COLUMN rank TYPE VARCHAR(2)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_23 - ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(2)");

    //if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month_rank ALTER COLUMN rank TYPE VARCHAR(2)") == false)
    //    return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_23 - ALTER TABLE ce_userstats_month_leg_rank ALTER COLUMN rank TYPE VARCHAR(2)");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.24 //
//////////////////
double CMigrations::Migrate_1_24()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN date_last_earned DATE") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_24 - ALTER TABLE ce_users ADD COLUMN date_last_earned DATE");

    string update = "Alter ce_user table ADD COLUMN date_last_earned DATE\n";
    //update += "Add ce_checkpoint indexes\n";

    Update(1.24, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.24;
}

///////////////////
// Rollback 1.24 //
///////////////////
bool CMigrations::Rollback_1_24()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN date_last_earned") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_24 - ALTER TABLE ce_users DROP COLUMN date_last_earned");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.25 //
//////////////////
double CMigrations::Migrate_1_25()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN compression BOOL default 'true'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_25 - ALTER TABLE ce_systems ADD COLUMN disable_compression BOOL");

    string update = "Alter ce_systems table ADD COLUMN disable_compression BOOL\n";

    Update(1.25, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.25;
}

///////////////////
// Rollback 1.25 //
///////////////////
bool CMigrations::Rollback_1_25()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN compression") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_25 - ALTER TABLE ce_systems DROP COLUMN disable_compression");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

//////////////////
// Migrate 1.26 //
//////////////////
double CMigrations::Migrate_1_26()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ALTER COLUMN zip TYPE TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_26 - ALTER TABLE ce_systems ADD COLUMN disable_compression BOOL");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_audit_ranks ADD COLUMN usercount INT4 default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_26 - ALTER TABLE ce_audit_ranks ADD COLUMN usercount INT4");

    string update = "Alter ce_audit_ranks table ADD COLUMN usercount INT4. Alter ce_users table ALTER COLUMN zip TYPE TEXT\n";
    Update(1.26, update.c_str());
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.26;
}

///////////////////
// Rollback 1.26 //
///////////////////
bool CMigrations::Rollback_1_26()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ALTER COLUMN zip TYPE VARCHAR(5)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_26 - ALTER TABLE ce_users ALTER COLUMN zip TYPE VARCHAR(5)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_audit_ranks DROP COLUMN usercount") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_26 - ALTER TABLE ce_audit_ranks DROP COLUMN usercount");

    m_pDB->ExecDB(true, socket, "COMMIT");
    return true;
}

// Handle United migration of pools changes //

/////////////////////////////////
// Frame out the first upgrade //
/////////////////////////////////
double CMigrations::Migrate_1_27()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle CM RankRules //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_cmrankrules(id SERIAL PRIMARY KEY, system_id BIGINT, rank VARCHAR(6), qualify_type VARCHAR(2), qualify_threshold FLOAT, achvbonus INT4, breakage BOOL, rulegroup INT4, maxdacleg INT4, label VARCHAR(64), sumrankstart VARCHAR(6), sumrankend VARCHAR(6), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_27 - Create table ce_cmrankrules");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_cmrankrules_system_id ON ce_cmrankrules(system_id)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_27 - Could not CREATE INDEX idx_ce_cmrankrules_system_id");

    Update(1.27, "Add cmrankrules to users table.\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.27;
}

//////////////////
// Rollback 1.27 //
//////////////////
bool CMigrations::Rollback_1_27()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_cmrankrules") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_27 - ce_users could not DROP COLUMN poolshares");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_cmrankrules_system_id") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_27 - Could not DROP INDEX idx_ce_cmrankrules_system_id");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.28 //
////////////////////
double CMigrations::Migrate_1_28()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ADD COLUMN poolshares INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_28 - Could not ADD COLUMN poolshares");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolpayouts ADD COLUMN poolshares INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_28 - Could not ADD COLUMN poolshares");
    if (m_pDB->ExecDB(true, socket, "CREATE INDEX idx_ce_users_poolshares ON ce_users(poolshares)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_28 - Could not CREATE INDEX idx_ce_users_poolshares");

    Update(1.28, "Add poolshares to users table.\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.28;
}

//////////////////
// Rollback 1.28 //
//////////////////
bool CMigrations::Rollback_1_28()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users DROP COLUMN poolshares") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_28 - ce_users could not DROP COLUMN poolshares");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_poolpayouts DROP COLUMN poolshares") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_28 - ce_poolpayouts could not DROP COLUMN poolshares");
    if (m_pDB->ExecDB(true, socket, "DROP INDEX idx_ce_users_poolshares") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_28 - Could not DROP INDEX idx_ce_users_poolshares");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.28 //
////////////////////
double CMigrations::Migrate_1_29()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN forcepay BOOL DEFAULT false") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_29 - Could not ALTER TABLE ce_commrules ADD COLUMN forcepay");

    Update(1.29, "Add forcepayout to ce_commrules.\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.29;
}

///////////////////
// Rollback 1.29 //
///////////////////
bool CMigrations::Rollback_1_29()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN forcepay") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_29 - ce_users could not ALTER TABLE ce_commrules DROP COLUMN forcepay");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.30 //
////////////////////
double CMigrations::Migrate_1_30()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN inv_type INT4 DEFAULT 1") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_30 - Could not ALTER TABLE ce_breakdown ADD COLUMN inv_type INT4");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems DROP COLUMN stack_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_30 - Could not ALTER TABLE ce_systems DROP COLUMN stack_type");

    Update(1.30, "Add inv_type to ce_breakdown.\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.30;
}

///////////////////
// Rollback 1.30 //
///////////////////
bool CMigrations::Rollback_1_30()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN inv_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_30 - ce_users could not ALTER TABLE ce_breakdown DROP COLUMN inv_type");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_systems ADD COLUMN stack_type VARCHAR(1) DEFAULT '1'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_30 - Could not ALTER TABLE ce_systems DROP COLUMN stack_type");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.31 //
////////////////////
double CMigrations::Migrate_1_31()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle pregeneration of data //   
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_pre_legrankgen (system_id BIGINT, batch_id BIGINT, user_id TEXT, lvl1_rank_1 INT4 DEFAULT 0, lvl1_rank_2 INT4 DEFAULT 0, lvl1_rank_3 INT4 DEFAULT 0, lvl1_rank_4 INT4 DEFAULT 0, lvl1_rank_5 INT4 DEFAULT 0, lvl1_rank_6 INT4 DEFAULT 0, lvl1_rank_7 INT4 DEFAULT 0, lvl1_rank_8 INT4 DEFAULT 0, lvl1_rank_9 INT4 DEFAULT 0, lvl1_rank_10 INT4 DEFAULT 0, lvl1_rank_11 INT4 DEFAULT 0, lvl1_rank_12 INT4 DEFAULT 0, lvl1_rank_13 INT4 DEFAULT 0, lvl1_rank_14 INT4 DEFAULT 0, lvl1_rank_15 INT4 DEFAULT 0, lvl1_rank_16 INT4 DEFAULT 0, lvl1_rank_17 INT4 DEFAULT 0, lvl1_rank_18 INT4 DEFAULT 0, lvl1_rank_19 INT4 DEFAULT 0, lvl1_rank_20 INT4 DEFAULT 0, lvl1_rank_21 INT4 DEFAULT 0, lvl1_rank_22 INT4 DEFAULT 0, lvl1_rank_23 INT4 DEFAULT 0, lvl1_rank_24 INT4 DEFAULT 0, lvl1_rank_25 INT4 DEFAULT 0, lvl1_rank_26 INT4 DEFAULT 0, lvl1_rank_27 INT4 DEFAULT 0, lvl1_rank_28 INT4 DEFAULT 0, lvl1_rank_29 INT4 DEFAULT 0, lvl1_rank_30 INT4 DEFAULT 0, lvl1_rank_31 INT4 DEFAULT 0, lvl1_rank_32 INT4 DEFAULT 0, lvl1_rank_33 INT4 DEFAULT 0, lvl1_rank_34 INT4 DEFAULT 0, lvl1_rank_35 INT4 DEFAULT 0, lvl1_rank_36 INT4 DEFAULT 0, lvl1_rank_37 INT4 DEFAULT 0, lvl1_rank_38 INT4 DEFAULT 0, lvl1_rank_39 INT4 DEFAULT 0, lvl1_rank_40 INT4 DEFAULT 0, lvl1_rank_41 INT4 DEFAULT 0, lvl1_rank_42 INT4 DEFAULT 0, lvl1_rank_43 INT4 DEFAULT 0, lvl1_rank_44 INT4 DEFAULT 0, lvl1_rank_45 INT4 DEFAULT 0, lvl1_rank_46 INT4 DEFAULT 0, lvl1_rank_47 INT4 DEFAULT 0, lvl1_rank_48 INT4 DEFAULT 0, lvl1_rank_49 INT4 DEFAULT 0, lvl1_rank_50 INT4 DEFAULT 0, gen1_rank_1 INT4 DEFAULT 0, gen1_rank_2 INT4 DEFAULT 0, gen1_rank_3 INT4 DEFAULT 0, gen1_rank_4 INT4 DEFAULT 0, gen1_rank_5 INT4 DEFAULT 0, gen1_rank_6 INT4 DEFAULT 0, gen1_rank_7 INT4 DEFAULT 0, gen1_rank_8 INT4 DEFAULT 0, gen1_rank_9 INT4 DEFAULT 0, gen1_rank_10 INT4 DEFAULT 0, gen1_rank_11 INT4 DEFAULT 0, gen1_rank_12 INT4 DEFAULT 0, gen1_rank_13 INT4 DEFAULT 0, gen1_rank_14 INT4 DEFAULT 0, gen1_rank_15 INT4 DEFAULT 0, gen1_rank_16 INT4 DEFAULT 0, gen1_rank_17 INT4 DEFAULT 0, gen1_rank_18 INT4 DEFAULT 0, gen1_rank_19 INT4 DEFAULT 0, gen1_rank_20 INT4 DEFAULT 0, gen1_rank_21 INT4 DEFAULT 0, gen1_rank_22 INT4 DEFAULT 0, gen1_rank_23 INT4 DEFAULT 0, gen1_rank_24 INT4 DEFAULT 0, gen1_rank_25 INT4 DEFAULT 0, gen1_rank_26 INT4 DEFAULT 0, gen1_rank_27 INT4 DEFAULT 0, gen1_rank_28 INT4 DEFAULT 0, gen1_rank_29 INT4 DEFAULT 0, gen1_rank_30 INT4 DEFAULT 0, gen1_rank_31 INT4 DEFAULT 0, gen1_rank_32 INT4 DEFAULT 0, gen1_rank_33 INT4 DEFAULT 0, gen1_rank_34 INT4 DEFAULT 0, gen1_rank_35 INT4 DEFAULT 0, gen1_rank_36 INT4 DEFAULT 0, gen1_rank_37 INT4 DEFAULT 0, gen1_rank_38 INT4 DEFAULT 0, gen1_rank_39 INT4 DEFAULT 0, gen1_rank_40 INT4 DEFAULT 0, gen1_rank_41 INT4 DEFAULT 0, gen1_rank_42 INT4 DEFAULT 0, gen1_rank_43 INT4 DEFAULT 0, gen1_rank_44 INT4 DEFAULT 0, gen1_rank_45 INT4 DEFAULT 0, gen1_rank_46 INT4 DEFAULT 0, gen1_rank_47 INT4 DEFAULT 0, gen1_rank_48 INT4 DEFAULT 0, gen1_rank_49 INT4 DEFAULT 0, gen1_rank_50 INT4 DEFAULT 0)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_31 - Could not CREATE TABLE ce_pre_legrankgen");

    // Change refid in ce_ledger to TEXT //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ledger ALTER COLUMN ref_id TYPE TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_31 - Could not ALTER TABLE ce_ledger ALTER COLUMN ref_id TYPE TEXT");

    // Add product_type to ce_receipts //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ADD COLUMN product_type SMALLINT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_31 - Could not ALTER TABLE ce_ledger ADD COLUMN product_type SMALLINT");

    Update(1.31, "Create table ce_pre_legrankgen. Speeds up affiliate downline report. Change ce_ledger ref_id to TEXT. Added column product_type to ce_receipts\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.31;
}

///////////////////
// Rollback 1.31 //
///////////////////
bool CMigrations::Rollback_1_31()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Handle Pool Shares //   
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_pre_legrankgen") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_31 - Could not DROP TABLE ce_pre_legrankgen");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ledger ALTER COLUMN ref_id TYPE BIGINT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_31 - Could not ALTER TABLE ce_ledger ALTER COLUMN ref_id BIGINT");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_ledger drop COLUMN product_type") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_31 - Could not ALTER TABLE ce_ledger drop COLUMN product_type");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.32 //
////////////////////
double CMigrations::Migrate_1_32()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Add product_type to ce_receipts //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_settings ADD COLUMN disabled BOOL DEFAULT false") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_32 - Could not ALTER TABLE ce_settings ADD COLUMN disabled BOOL");

    Update(1.32, "Add disable column to ce_settings\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.32;
}

///////////////////
// Rollback 1.32 //
///////////////////
bool CMigrations::Rollback_1_32()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_settings drop COLUMN disabled") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_32 - Could not ALTER TABLE ce_ledger drop COLUMN product_type");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.33 //
////////////////////
double CMigrations::Migrate_1_33()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Repair problem where United has column and all others don't //
    // This is postgresql specific //
    stringstream ss;
    if (m_pDB->GetFirstDB(socket, ss << "SELECT count(*) FROM information_schema.columns WHERE table_name='ce_audit_ranks' AND column_name='usercount'") == 0)
    {
        if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_audit_ranks ADD COLUMN usercount INT4 default '0'") == NULL)
            return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_33 - ALTER TABLE ce_audit_ranks ADD COLUMN usercount INT4 default '0'");
    }

    Update(1.33, "Add usercount column to ce_audit_ranks\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.33;
}

///////////////////
// Rollback 1.33 //
///////////////////
bool CMigrations::Rollback_1_33()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_audit_ranks DROP COLUMN usercount") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_33 - Could not ALTER TABLE ce_audit_ranks DROP COLUMN usercount");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.34 //
////////////////////
double CMigrations::Migrate_1_34()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN item_count INT4 default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_34 - ALTER TABLE ce_userstats_month ADD COLUMN itemcount INT4 default '0'");
    

    Update(1.34, "Add itemcount to ce_userstats_month\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.34;
}

///////////////////
// Rollback 1.34 //
///////////////////
bool CMigrations::Rollback_1_34()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN item_count") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_34 - Could not ALTER TABLE ce_userstats_month DROP COLUMN itemcount");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.35 //
////////////////////
double CMigrations::Migrate_1_35()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "UPDATE ce_receipts SET product_type='0' WHERE product_type::TEXT IS NULL") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - ALTER TABLE ce_receipts ALTER COLUMN product_type SET DEFAULT '0'");

    // 
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ALTER COLUMN product_type SET DEFAULT '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - ALTER TABLE ce_receipts ALTER COLUMN product_type SET DEFAULT '0'");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN item_count TO item_count_wholesale") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - ALTER TABLE ce_userstats_month RENAME COLUMN item_count TO item_count_wholesale");
    
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN item_count_retail INT4 default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - ALTER TABLE ce_userstats_month ADD COLUMN item_count_retail INT4 default '0'");

    Update(1.35, "Alter ce_receipts alter product_type Default 0\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.35;
}

///////////////////
// Rollback 1.35 //
///////////////////
bool CMigrations::Rollback_1_35()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // How do we even turn an empty product_type to a number then back to empty? //

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_receipts ALTER COLUMN product_type DROP DEFAULT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - Could not ALTER TABLE ce_receipts ALTER COLUMN product_type DROP DEFAULT");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month RENAME COLUMN item_count_wholesale TO item_count") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - ALTER TABLE ce_userstats_month RENAME COLUMN item_count_wholesale TO item_count");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN item_count_retail") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_35 - ALTER TABLE ce_userstats_month ADD COLUMN item_count_retail");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.36 //
////////////////////
double CMigrations::Migrate_1_36()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_receipts_filter(id SERIAL PRIMARY KEY, system_id INT4, inv_type INT4, product_type INT4, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not CREATE TABLE ce_receipt_filter");

    // Allow rankbonusrules table //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_rankbonusrules(id SERIAL PRIMARY KEY, system_id BIGINT, rank VARCHAR(3), bonus DECIMAL(37,4), disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Create TABLE ce_rankbonusrules");

    // Table needed to store all rankbonus values //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_rankbonus(id SERIAL PRIMARY KEY, system_id BIGINT, batch_id BIGINT, user_id TEXT, amount DECIMAL(37,4), event_date DATE, rank INT4, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not CREATE TABLE ce_rankbonus");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules ADD COLUMN dollar DECIMAL(37,4) default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - ALTER TABLE ce_commrules ADD COLUMN dollar");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown ADD COLUMN dollar DECIMAL(37,4) default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - ALTER TABLE ce_breakdown ADD COLUMN dollar");

    // Add receipt filter for all inv_type //
    // Currently only for system_id=1 //
    // Interface will need to be updated //
    if (m_pDB->ExecDB(true, socket, "INSERT INTO ce_receipts_filter (system_id, inv_type, product_type) VALUES (1, 0, 0), (1, 1, 0), (1, 2, 0), (1, 3, 0), (1, 4, 0), (1, 5, 0), (1, 6, 0)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - ALTER TABLE ce_breakdown ADD COLUMN dollar");

    Update(1.36, "Added table ce_receipt_filter. Added ce_rankbonus and ce_rankbonusrules table. Added dollar amount to ce_commrules\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.36;
}

///////////////////
// Rollback 1.36 //
///////////////////
bool CMigrations::Rollback_1_36()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_receipts_filter") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not DROP TABLE ce_receipts_filter");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_rankbonusrules") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not DROP TABLE ce_rankbonusrules");

    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_rankbonus") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not DROP TABLE ce_rankbonus");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_commrules DROP COLUMN dollar") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not ALTER TABLE ce_commrules DROP COLUMN dollar");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_breakdown DROP COLUMN dollar") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not ALTER TABLE ce_breakdown DROP COLUMN dollar");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.37 //
////////////////////
double CMigrations::Migrate_1_37()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN unique_users_receipts BIGINT default '0'") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_37 - ALTER TABLE ce_userstats_month ADD COLUMN unique_users_receipts");

    Update(1.37, "Added table ce_userstats_month\n");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.37;
}

///////////////////
// Rollback 1.37 //
///////////////////
bool CMigrations::Rollback_1_37()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN unique_users_receipts") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_36 - Could not ALTER TABLE ce_userstats_month DROP COLUMN unique_users_receipts");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.38 //
////////////////////
double CMigrations::Migrate_1_38()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Fix the zipcode length problem //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ALTER COLUMN zip TYPE VARCHAR(10)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_38 - ALTER TABLE ce_users ALTER COLUMN zip VARCHAR(10)");

    // Add varid to rankrules //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules ADD COLUMN varid TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_38 - ALTER TABLE ce_rankrules ADD COLUMN varid TEXT");

    // Add table ce_extqualify //
    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_extqualify (id SERIAL PRIMARY KEY, system_id BIGINT, user_id TEXT, varid TEXT, value INT4, event_date TIMESTAMP, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_38 - ALTER TABLE ce_rankrules ADD COLUMN varid TEXT");

    Update(1.38, "Added table ce_users ALTER COLUMN zip VARCHAR(10)\nAdd varid to ce_rankrules\nCreate table ce_extqualify");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.38;
}

///////////////////
// Rollback 1.38 //
///////////////////
bool CMigrations::Rollback_1_38()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Revert back zipcode problem //
    //if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_users ALTER COLUMN zip TYPE VARCHAR(5)") == false)
    //    return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_38 - Could not ALTER TABLE ce_users ALTER COLUMN zip VARCHAR(5)");
    // VALUE TOO LARGE FOR VARCHAR(5) AFTER MIGRATONS. WE CAN'T ROLLBACK //

    // Revert back the rankrules varid //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_rankrules DROP COLUMN varid") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_38 - ALTER TABLE ce_rankrules DROP COLUMN varid");

    // Drop the ce_extqualify table //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_extqualify") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_38 - DROP TABLE ce_extqualify");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.39 //
////////////////////
double CMigrations::Migrate_1_39()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // cmrankrules piggy backs onto rankrules code. 
    // This extra precaution of adding varid is needed to prevent SQL errors //

    // Add varid to rankrules //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_cmrankrules ADD COLUMN varid TEXT") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_39 - ALTER TABLE ce_cmrankrules ADD COLUMN varid TEXT");

    Update(1.39, "Add varid to ce_cmrankrules");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.39;
}

///////////////////
// Rollback 1.39 //
///////////////////
bool CMigrations::Rollback_1_39()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Revert back the rankrules varid //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_cmrankrules DROP COLUMN varid") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_39 - ALTER TABLE ce_rankrules DROP COLUMN varid");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.40 //
////////////////////
double CMigrations::Migrate_1_40()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // PV, TV, Rank, PV over ++days (90 days) //

    if (m_pDB->ExecDB(true, socket, "CREATE TABLE ce_faststart(id SERIAL PRIMARY KEY, system_id BIGINT, rank VARCHAR(2), qualify_type VARCHAR(2), qualify_threshold FLOAT, days_count INT, bonus INT, rulegroup INT, disabled BOOL DEFAULT false, created_at TIMESTAMP DEFAULT now(), updated_at TIMESTAMP DEFAULT now())") == NULL)
    {
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_40 - ALTER TABLE CREATE TABLE ce_faststart");
    }

    Update(1.40, "Add ce_faststart table");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.40;
}

///////////////////
// Rollback 1.40 //
///////////////////
bool CMigrations::Rollback_1_40()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Revert back the rankrules varid //
    if (m_pDB->ExecDB(true, socket, "DROP TABLE ce_faststart") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_40 - DROP TABLE ce_faststart");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.41 //
////////////////////
double CMigrations::Migrate_1_41()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN item_count_wholesale_ev INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_41 - ALTER TABLE ce_userstats_month ADD COLUMN item_count_wholesale_ev INT4");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN item_count_retail_ev INT4") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_41 - ALTER TABLE ce_userstats_month ADD COLUMN item_count_retail_ev INT4");

    Update(1.41, "Add item_count_wholesale_ev and item_count_retail_ev to ce_userstats_month");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.41;
}

///////////////////
// Rollback 1.41 //
///////////////////
bool CMigrations::Rollback_1_41()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Revert back the rankrules varid //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN item_count_wholesale_ev") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_41 - ALTER TABLE ce_userstats_month DROP COLUMN item_count_wholesale_ev");

    // Revert back the rankrules varid //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN item_count_retail_ev") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_41 - ALTER TABLE ce_userstats_month DROP COLUMN item_count_retail_ev");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}

////////////////////
// Migration 1.42 //
////////////////////
double CMigrations::Migrate_1_42()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN corp_wholesale_price DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_42 - ALTER TABLE ce_userstats_month ADD COLUMN corp_wholesale_price DECIMAL(37,4)");

    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month ADD COLUMN corp_retail_price DECIMAL(37,4)") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Migrate_1_42 - ALTER TABLE ce_userstats_month ADD COLUMN corp_retail_price DECIMAL(37,4)");

    Update(1.42, "Add corp_wholesale_price and corp_retail_price to ce_userstats_month");
    m_pDB->ExecDB(true, socket, "COMMIT");
    return 1.42;
}

///////////////////
// Rollback 1.42 //
///////////////////
bool CMigrations::Rollback_1_42()
{
    int socket = 0;
    m_pDB->ExecDB(true, socket, "BEGIN");

    // Revert back the rankrules varid //
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN corp_wholesale_price") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_42 - ALTER TABLE ce_userstats_month DROP COLUMN corp_wholesale_price");
    if (m_pDB->ExecDB(true, socket, "ALTER TABLE ce_userstats_month DROP COLUMN corp_retail_price") == NULL)
        return Debug(DEBUG_ERROR, "CMigrations::Rollback_1_42 - ALTER TABLE ce_userstats_month DROP COLUMN corp_retail_price");

    return m_pDB->ExecDB(true, socket, "COMMIT");
}