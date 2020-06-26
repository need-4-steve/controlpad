#ifndef _MIGRATIONS_H
#define _MIGRATIONS_H

#include "db.h"
#include "debug.h"
#include "ezSettings.h"

///////////////////////
// Manage Migrations //
///////////////////////
class CMigrations : CDebug
{
public:
	CMigrations(CDb *pDB); // Only for Indexes //
	CMigrations(CDb *pDB, const char *hashpass, const char *command); // 

	bool AddIndexes();
	bool DropIndexes();

private:

	void Migrate(const char *current_version);
	bool Rollback(const char *current_version);
	
	double Init_1_00(); // Do Initial creating of tables //
	bool VersionRepair();
	double Migrate_1_01(); 
	double Migrate_1_02();
	double Migrate_1_03(); 
	double Migrate_1_04(); 
	double Migrate_1_05(); 
	double Migrate_1_06();
	double Migrate_1_07();
	double Migrate_1_08();
	double Migrate_1_09();
	double Migrate_1_10();
	double Migrate_1_11();
	double Migrate_1_12();
	double Migrate_1_13();
	double Migrate_1_14();
	double Migrate_1_15();
	double Migrate_1_16();
	double Migrate_1_17();
	double Migrate_1_18();
	double Migrate_1_19();
	double Migrate_1_20();
	double Migrate_1_21();
	double Migrate_1_22();
	double Migrate_1_23();
	double Migrate_1_24();
	double Migrate_1_25();
	double Migrate_1_26();
	double Migrate_1_27();
	double Migrate_1_28();
	double Migrate_1_29();
	double Migrate_1_30();
	double Migrate_1_31();
	double Migrate_1_32();
	double Migrate_1_33();
	double Migrate_1_34();
	double Migrate_1_35();
	double Migrate_1_36();
	double Migrate_1_37();
	double Migrate_1_38();
	double Migrate_1_39();
	double Migrate_1_40();
	double Migrate_1_41();
	double Migrate_1_42();

	bool Rollback_1_00(); // Rollback Initial creating of tables //
	bool Rollback_1_01();
	bool Rollback_1_02();
	bool Rollback_1_03();
	bool Rollback_1_04();
	bool Rollback_1_05();
	bool Rollback_1_06();
	bool Rollback_1_07();
	bool Rollback_1_08();
	bool Rollback_1_09();
	bool Rollback_1_10();
	bool Rollback_1_11();
	bool Rollback_1_12();
	bool Rollback_1_13();
	bool Rollback_1_14();
	bool Rollback_1_15();
	bool Rollback_1_16();
	bool Rollback_1_17();
	bool Rollback_1_18();
	bool Rollback_1_19();
	bool Rollback_1_20();
	bool Rollback_1_21();
	bool Rollback_1_22();
	bool Rollback_1_23();
	bool Rollback_1_24();
	bool Rollback_1_25();
	bool Rollback_1_26();
	bool Rollback_1_27();
	bool Rollback_1_28();
	bool Rollback_1_29();
	bool Rollback_1_30();
	bool Rollback_1_31();
	bool Rollback_1_32();
	bool Rollback_1_33();
	bool Rollback_1_34();
	bool Rollback_1_35();
	bool Rollback_1_36();
	bool Rollback_1_37();
	bool Rollback_1_38();
	bool Rollback_1_39();
	bool Rollback_1_40();
	bool Rollback_1_41();
	bool Rollback_1_42();

	// Helper function at the end of every migration //
	bool Update(double version, const char *label);

	CDb *m_pDB;
	
	std::string m_HashPass;

	// Duplicate records discovered. Clean them up on migration //
	// Duplicate in code has been fixed. There is no rollback // 
	bool CleanupCeRanks(void); // This is for only the mistake on united. Maybe hope5000 //
};

#endif