/////////////
// Tests.h //
/////////////

#include "date.h"
#include "ezCrypt.h"
#include "ezCurl.h"
#include "packets.h"

#define TEST_DEBUG_OVERRIDE		6 //4 // DEBUG //6 // SQL //

/////////////////////////////////////////
// All the Unit Tests are handled here //
/////////////////////////////////////////
extern char g_DebugLevel;

//////////////////////////////////////////
// Handle generating test database data //
//////////////////////////////////////////
#ifdef COMPILE_TESTDATA
TEST(CCommissionEngine, GenTestData)
{
	CCommissionEngine ce;
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //
//	ce.United();
	//int retval2 = system("rm dump/ce.united.sql"); // Make backup //
	//int retval3 = system("pg_dump ce > dump/ce.united.sql"); // Make backup //

	//int retval1 = system("dropdb ce");
	//int retval2 = system("createdb ce");
    //int retval3 = system("psql ce < dump/ce.united.sql");
    //CCommissionEngine ce;

    //ce.ConnectDB();
    //g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

    ce.Test();
    //ce.m_DB.CronCommissions();

//	ce.Test();

    //CCommissions comm;
	//comm.Run(&ce.m_DB, 485, 1, false, true, "2016-9-2", "2016-10-2");

    //int retval2 = system("rm dump/ce.united.sql"); // Make backup //
	//int retval3 = system("pg_dump ce > dump/ce.united.sql"); // Make backup //
}
#endif

/*
////////////////////////////
// CommissionEngine Tests //
////////////////////////////
TEST(CCommissionEngine, Public)
{
	CCommissionEngine ce;

	ASSERT_EQ(false, ce.LoadINI("settings/test1.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test2.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test3.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test4.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test5.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test6.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test7.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test8.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test9.ini"));
	ASSERT_EQ(true, ce.LoadINI("settings/test10.ini"));
	ASSERT_EQ(true, ce.LoadINI("settings/test11.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test12.ini"));
	ASSERT_EQ(true, ce.LoadINI("settings/test13.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test14.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test15.ini"));
	ASSERT_EQ(true, ce.LoadINI("settings/test16.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test17.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test18.ini"));
	ASSERT_EQ(true, ce.LoadINI("settings/test19.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test20.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test21.ini"));
	ASSERT_EQ(true, ce.LoadINI("settings/test22.ini"));
	ASSERT_EQ(false, ce.LoadINI("settings/test23.ini"));

    ASSERT_EQ(true, ce.LoadINI(INI_FILENAME));
  	
  	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //
    //ASSERT_EQ(true, ce.Startup()); // Not sure is needed, cause it blocks on listening to sockets when not a daemon //
    // CreateMaster();
    ce.CronCommissions();
    ce.Migrate();
}

////////////////////////////////
// Test the commissions class //
////////////////////////////////
TEST(CCommissions, public)
{
	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

	CDb db;
	db.Connect(&ce.m_Settings);

	CCommissions comm;
	const char *pcomm1 = comm.Run(&db, 1, 1, false, true, "2016-7-1", "2016-7-31");
	char compstr1[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandpayouts\":{\"receipts\":\"505000.00\",\"commissions\":\"0.00\",\"achvbonuses\":\"0.00\"}}";
	ASSERT_EQ(true, db.TestJson(compstr1, pcomm1));

	// Pools //

	// Test different types of commission structures //
}

///////////
// Dates //
///////////
TEST(CDate, Public)
{
	CDateCompare comp("2016-7-1", "2016-7-31");
	ASSERT_EQ(true, comp.IsBetween("2016-7-2"));
	ASSERT_EQ(1470117600, comp.ConvDateToSec(2016, 7, 2));
}

///////////
// Debug //
///////////
TEST(CDebug, public)
{	
	CDebug debug;

	double value1 = 1.234;
	debug.Debug(1, "test=", value1);

	double value2 = 1.234;
	debug.Debug(1, "test=", value2);

	char test[] = "test 123 ";
	trim(test);
	rtrim(test, ' ');

	std::string s = "test";
	toupper(s);
}

////////////////////////
// Database functions //
////////////////////////
TEST(DB, PublicGeneral)
{
	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

	CDb db;
	ASSERT_EQ(true, db.Connect(&ce.m_Settings));
	ASSERT_EQ(100, db.GetUserCount()); // 100 users from test data //

	std::list <CRulesRank> RulesRank;
	ASSERT_EQ(true, db.GetRankRules(1, &RulesRank));

	std::list <CRulesComm> RulesComm;
	ASSERT_EQ(true, db.GetCommRules(1, &RulesComm));

	std::map <std::string, CUser> UsersMap;
	//ASSERT_EQ(false, db.GetUsers(1, NULL, true, UPLINE_PARENT_ID, NULL, NULL));
	ASSERT_EQ(false, db.GetUsers(1, UsersMap, true, UPLINE_PARENT_ID, NULL, NULL));
	ASSERT_EQ(false, db.GetUsers(1, UsersMap, true, UPLINE_PARENT_ID, "2016-1-1", NULL));
	ASSERT_EQ(true, db.GetUsers(1, UsersMap, true, UPLINE_PARENT_ID, "2016-1-1", "2100-1-1"));
	ASSERT_EQ(true, db.GetReceipts(1, UsersMap, UPLINE_PARENT_ID, "2016-1-1", "2100-1-1"));

	std::list <CRulesPool> RulesPool;
	ASSERT_EQ(true, db.GetPoolRules(1, 1, &RulesPool));

	ASSERT_EQ(3, db.AddBatch(1, "2000-1-1", "2000-1-31"));
	ASSERT_EQ(true, db.AddRank(1, 2, "99", 1, false, 19, 1));
	ASSERT_EQ(true, db.IsAchvBonusPaid(1, "99", 1)); // Check bonus just added //
	ASSERT_EQ(false, db.IsAchvBonusPaid(1, "98", 1)); // There should be no bouns for user 98 //
	ASSERT_EQ(true, db.SetSyncGrand(1));
	//ASSERT_EQ(2, db.GetInfinityCap(1));
	ASSERT_EQ(0, db.GetInfinityCap(1));
	//ASSERT_EQ(6, db.GetGenLimit(1));
	ASSERT_EQ(9, db.GetGenLimit(1));	
	ASSERT_EQ(true, db.AddReceiptBreakdown(1, 1, 1, 1, "99", 0.01, 1, 19, 99, false));
	ASSERT_EQ(true, db.AddCommission(1, 2, "99", 19.99));
	ASSERT_EQ(true, db.AddBinaryLedger(1, 2, "99", 19.99, 5.01, 4.99, 19.99));
	ASSERT_EQ(true, db.AddPoolPayout(1, 1, "99", 4.99));
	ASSERT_EQ(1, db.GetSystemCommType(1));
	//ASSERT_EQ(5, db.GetGrandID(1, "99"));
	ASSERT_EQ(2, db.GetGrandID(1, "99"));
	ASSERT_EQ(true, db.UpdateGrandTotal(2, 1, "99", 3.01));
	ASSERT_EQ(false, db.UpdateGrandTotal(199, 1, "98", 10000.99));
	ASSERT_EQ(true, db.CronCommissions());
	ASSERT_EQ(true, db.CronProcLoop("2016-6-1", "2016-6-31")); // Uses external requests to process //
	ASSERT_EQ(false, db.CurlUpdatedURL(1, "", "", "")); // Uses external requests to process //
	ASSERT_EQ(true, db.IsRightsSystem(1, 1));
	ASSERT_EQ(false, db.IsRightsSystem(1, 9));
	ASSERT_EQ(false, db.IsRightsSystem(9, 1));
	ASSERT_EQ(-1, db.AuthAPIUser("testemail@wontwork.com", "This key should fail"));
	ASSERT_EQ(-1, db.AuthAPIUser(MASTER_ACCOUNT, "This key should fail"));

	// Read in the master api key //
	FILE *pFile = fopen("master.apikey.txt", "r");
	if (pFile==NULL)
	{
		fputs("File error", stderr); 
		exit (1);
	}
	// obtain file size:
	long lSize;
	fseek(pFile, 0, SEEK_END);
	lSize = ftell(pFile);
 	rewind(pFile);
	char masterapikey[1024];
	memset(masterapikey, 0, 1024);
	int result = fread(masterapikey, 1, lSize, pFile);
	fclose (pFile);

	// Test the master API key //
	ASSERT_EQ(1, db.AuthAPIUser(MASTER_ACCOUNT, masterapikey));
	
	// Test authorization of session //
	const char *pjson = db.AuthSessionUser(MASTER_ACCOUNT, INITAL_MASTER_PASSWORD, "127.0.0.1");
	char compstr[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"authuser\":{\"sessionkey\":\"";
	ASSERT_EQ(true, db.TestJson(compstr, pjson));

	// Check the session //
	char sessionkey[1024];
	memset(sessionkey, 0, 1024);
	int complen = strlen(compstr);
	int sesslen = strlen(&pjson[complen]);
	memcpy(sessionkey, &pjson[complen], sesslen-3);
	// Set to -1 while they are currently disabled. 
	// It will switch to 1 when they are turned back on //
	ASSERT_EQ(-1, db.CheckSessionUser(MASTER_ACCOUNT, sessionkey));
}

/////////////////////////////////
// Handle the public API calls //
/////////////////////////////////
TEST(DB, PublicAPI)
{
	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

	CDb db;
	db.Connect(&ce.m_Settings);

	// System Users Tests //
	const char *pjson1 = db.AddSystemUser("random@testuser.com", "RandomPassword", "5.5.5.5");
	char compstr1[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systemuser\":[{\"id\":\"3\"";
	ASSERT_EQ(true, db.TestJson(compstr1, pjson1));

	const char *pjson2 = db.EditSystemUser(3, "random@testuser.com", "randompass", "4.4.4.4");
	char compstr2[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systemuser\":[{\"id\":\"3\"";
	ASSERT_EQ(true, db.TestJson(compstr2, pjson2));

	const char *pjson3 = db.QuerySystemUsers();
	char compstr3[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systemusers\":[{\"id\":\"1\",\"email\":\"master@commissions.com\",\"disabled\":\"f\",\"createdat\":\"";
	ASSERT_EQ(true, db.TestJson(compstr3, pjson3));

	const char *pjson4 = db.DisableSystemUser(3);
	char compstr4[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr4, pjson4));

	const char *pjson5 = db.EnableSystemUser(3);
	char compstr5[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr5, pjson5));

	// System Tests //
	const char *pjson6 = db.AddSystem("randomsystemname", 1, 1, 1, 15, 6, "false", 2, "", "", "");
	char compstr6[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"system\":[{\"id\"";
	ASSERT_EQ(true, db.TestJson(compstr6, pjson6));
	const char *pjson7 = db.AddSystem("randomsystemname2", 2, 1, 1, 15, 6, "false", 2, "", "", "");
	char compstr7[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"system\":[{\"id\"";
	ASSERT_EQ(true, db.TestJson(compstr7, pjson7));
	const char *pjson8 = db.AddSystem("randomsystemname3", 3, 1, 1, 15, 6, "false", 2, "", "", "");
	char compstr8[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"system\":[{\"id\"";
	ASSERT_EQ(true, db.TestJson(compstr8, pjson8));

	const char *pjson9 = db.UpdateSystem(1, "randomsystemname1", 1, 1, 5, 2, "false", 2, "", "", "");
	char compstr9[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr9, pjson9));

	const char *pjson10 = db.QuerySystems(1);
	char compstr10[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systems\":[{\"id\":\"1\",\"systemname\":\"randomsystemname1\",\"commtype\":\"1\",\"payouttype\":\"1\",\"payoutmonthday\":\"5\",\"payoutweekday\":\"2\",\"disabled\":\"f\"";
	ASSERT_EQ(true, db.TestJson(compstr10, pjson10));

	const char *pjson11 = db.DisableSystem(3);
	char compstr11[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr11, pjson11));

	const char *pjson12 = db.EnableSystem(3);
	char compstr12[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr12, pjson12));

	const char *pjson13 = db.DisableSystem(99);
	char compstr13[] = "HTTP/1.1 503 Service Unavailable\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"503\",\"source\":\"API\",\"title\":\"disablesystem error\",\"detail\":\"systemid not found in database\"}}";
	ASSERT_EQ(true, db.TestJson(compstr13, pjson13));

	// Users Tests //
	const char *pjson14 = db.AddUser(2, "1", "0", "2016-7-25", 1);
	char compstr14[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr14, pjson14));

	const char *pjson15 = db.EditUser(2, "1", "0", "2016-7-26", 0);
	char compstr15[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr15, pjson15));

	const char *pjson16 = db.QueryUsers(2);
	char compstr16[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"users\":[{\"id\":\"101\",\"userid\":\"1\",\"sponsorid\":\"0\",\"rank\":\"0\",\"signupdate\":\"2016-07-26\",\"disabled\":\"f\"";
	ASSERT_EQ(true, db.TestJson(compstr16, pjson16));

	const char *pjson17 = db.DisableUser(2, "1");
	char compstr17[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr17, pjson17));

	const char *pjson18 = db.EnableUser(2, "1");
	char compstr18[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr18, pjson18));

	// Receipt tests //
	const char *pjson19 = db.AddReceipt(3, 1, "1", 49.99, "2016-7-27", "true");
	char compstr19[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr19, pjson19));

	const char *pjson20 = db.EditReceipt(3, 1, "1", 49.99, "2016-7-27", "true");
	char compstr20[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr20, pjson20));

	const char *pjson21 = db.QueryReceipts(3, "2016-7-1", "2016-7-31");
	//char compstr21[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"receipts\":[{\"id\":\"101\",\"receiptid\":\"1\",\"userid\":\"1\",\"amount\":\"49.9900\",\"purchasedate\":\"2016-07-27\",\"commissionable\":\"t\"";
	char compstr21[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"receipts\":[{\"id\":\"101\",\"receiptid\":\"1\",\"userid\":\"1\",\"amount\":\"49.9900\",\"purchasedate\":\"2016-07-27\",\"commissionable\":\"t\",\"disabled\":\"f\",\"createdat";
	ASSERT_EQ(true, db.TestJson(compstr21, pjson21));

	const char *pjson22 = db.DisableReceipt(3, 1);
	char compstr22[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr22, pjson22));

	const char *pjson23 = db.EnableReceipt(3, 1);
	char compstr23[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr23, pjson23));

	const char *pjson24 = db.QueryBreakdown(2, 1);
	char compstr24[] = "HTTP/1.1 400 Bad Request\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"400\",\"source\":\"API\",\"title\":\"querybreakdown error\",\"detail\":\"There are no breakdown entries for the given receipt\"}}";
	ASSERT_EQ(true, db.TestJson(compstr24, pjson24));

	const char *pjson25 = db.QueryBreakdown(1, 1);
	//char compstr25[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"breakdown\":[{\"id\":\"395\",\"batchid\":\"1\",\"paytype\":\"1\",\"userid\":\"99\",\"amount\":\"0.0100\",\"createdat\"";
	char compstr25[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"breakdown\":[{\"id\":\"1\",\"batchid\":\"1\",\"paytype\":\"1\",\"userid\":\"99\",\"amount\":\"0.0100";
	ASSERT_EQ(true, db.TestJson(compstr25, pjson25));

	// Rank Rules Tests //
	const char *pjson26 = db.AddRankRule(3, 1, 1, 2, 3, "false", 0, 0);
	char compstr26[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr26, pjson26));

	//const char *pjson27 = db.EditRankRule(2, 38, 1, 1, 2, 4, "false", 0, 0);
	const char *pjson27 = db.EditRankRule(3, 9, 1, 1, 2, 4, "false", 0, 0);
	char compstr27[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr27, pjson27));

	const char *pjson28 = db.QueryRankRules(1);
	char compstr28[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"rankrules\":[{\"id\"";
	ASSERT_EQ(true, db.TestJson(compstr28, pjson28));

	const char *pjson29 = db.DisableRankRule(3, 9);
	char compstr29[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr29, pjson29));

	const char *pjson30 = db.EnableRankRule(3, 9);
	char compstr30[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr30, pjson30));

	// Commission Rules Tests //
	const char *pjson31 = db.AddCommRule(3, 1, 1, 1, 1, 1, "false", 3.5);
	char compstr31[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr31, pjson31));

	const char *pjson32 = db.EditCommRule(3, 18, 1, 1, 1, 1, 1, "false", 3.5);
	char compstr32[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr32, pjson32));

	const char *pjson33 = db.QueryCommRule(3);
	char compstr33[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"commrules\":[{\"id\"";
	ASSERT_EQ(true, db.TestJson(compstr33, pjson33));

	const char *pjson34 = db.DisableCommRule(3, 18);
	char compstr34[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr34, pjson34));

	const char *pjson35 = db.EnableCommRule(3, 18);
	char compstr35[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr35, pjson35));

	// Pool Pot Rules //
	const char *pjson36 = db.AddPoolPot(2, 1, 2000, "2016-7-1", "2016-7-31");
	char compstr36[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr36, pjson36));

	const char *pjson37 = db.EditPoolPot(2, 2, 2, 1999, "2016-7-1", "2016-7-31");
	char compstr37[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr37, pjson37));

	const char *pjson38 = db.QueryPoolPots(2);
	char compstr38[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"poolpots\":[{\"id\":\"2\",\"systemid\":\"2\",\"qualifytype\":\"2\",\"amount\":\"1999\"";
	ASSERT_EQ(true, db.TestJson(compstr38, pjson38));

	const char *pjson39 = db.DisablePoolPot(2, 2);
	char compstr39[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr39, pjson39));

	const char *pjson40 = db.EnablePoolPot(2, 2);
	char compstr40[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr40, pjson40));

	// Pool Rules Tests //
	const char *pjson41 = db.AddPoolRule(2, 2, 1, 1, 500);
	char compstr41[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr41, pjson41));

	const char *pjson42 = db.EditPoolRule(2, 2, 1, 1, 500);
	char compstr42[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr42, pjson42));

	const char *pjson43 = db.QueryPoolRules(2, 2);
	char compstr43[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"poolrules\":[{\"id\":\"2\",\"startrank\":\"1\",\"endrank\":\"1\",\"qualifythreshold\":\"500\"";
	ASSERT_EQ(true, db.TestJson(compstr43, pjson43));

	const char *pjson44 = db.DisablePoolRule(2, 2);
	char compstr44[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr44, pjson44));

	const char *pjson45 = db.EnablePoolRule(2, 2);
	char compstr45[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr45, pjson45));

	// Commissions calculated //
	const char *pjson46 = db.QueryBatches(1);
	char compstr46[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"batches\":[{";
	ASSERT_EQ(true, db.TestJson(compstr46, pjson46));

	const char *pjson47 = db.QueryUserComm(1, "1");
	//char compstr47[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"commission\":[{\"user_id\":\"1\",\"amount\":\"15.3000\"}]}";
	char compstr47[] = "HTTP/1.1 412 Precondition Failed\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"412\",\"source\":\"API\",\"title\":\"queryusercomm error\",\"detail\":\"There are no commission";
	ASSERT_EQ(true, db.TestJson(compstr47, pjson47));

	const char *pjson48 = db.QueryBatchComm(1, 1);
	//char compstr48[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"commissions\":[{\"user_id\":\"1\",\"amount\":\"15.3000\"},{\"user_id\":\"12\",\"amount\":\"612.9900\"},{\"user_id\":\"2\",\"amount\":\"295.0500\"},{\"user_id\":\"3\",\"amount\":\"127.4000\"}]}";
	char compstr48[] = "HTTP/1.1 412 Precondition Failed\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"412\",\"source\":\"API\",\"title\":\"querybatchcomm";
	ASSERT_EQ(true, db.TestJson(compstr48, pjson48));

	// Final functions //
	const char *pjson49 = db.QueryGrandPayout(1, "true");
	//char compstr49[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandtotals\":[{\"id\":\"2\",\"systemid\":\"1\",\"userid\":\"12\",\"amount\":\"772.9900\"";
	char compstr49[] = "HTTP/1.1 412 Precondition Failed\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors";
	ASSERT_EQ(true, db.TestJson(compstr49, pjson49));

	const char *pjson50 = db.QueryGrandPayout(1, "false");
	//char compstr50[] = "HTTP/1.1 412 Precondition Failed\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"412\",\"source\":\"API\",\"title\":\"querygrandpayout error\",\"detail\":\"There are no records for given criteria\"}}";
	char compstr50[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandtotals\":[{\"id\":\"2\",\"systemid\":\"1\",\"userid\":\"99\",\"amount\":\"45.0100";
	ASSERT_EQ(true, db.TestJson(compstr50, pjson50));

	const char *pjson51 = db.AuthGrandPayout(1, 1, "false");
	char compstr51[] = "HTTP/1.1 503 Service Unavailable\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"503\",\"source\":\"API\",\"title\":\"authgrandpayout error\",\"detail\":\"The record is unavailable for authorization\"}}";
	ASSERT_EQ(true, db.TestJson(compstr51, pjson51));

	const char *pjson52 = db.AuthGrandBulk(1);
	char compstr52[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr52, pjson52));

	const char *pjson53 = db.DisableGrandPayout(1, 1);
	char compstr53[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr53, pjson53));

	const char *pjson54 = db.EnableGrandPayout(1, 1);
	char compstr54[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr54, pjson54));

	// Payment functions //
	const char *pjson55 = db.AddBankAccount(1, "1", 1, "123456789", "98765432198765432", "TestName TestLast");
	char compstr55[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr55, pjson55));

	const char *pjson56 = db.EditBankAccount(1, "1", 1, "987654321", "12345678912345678", "Name TestLast");
	char compstr56[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr56, pjson56));

	const char *pjson57 = db.QueryBankAccounts(1);
	char compstr57[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"bankaccounts\":[{\"id\":\"1\",\"userid\":\"1\",\"accounttype\":\"1\",\"routingnumber\":\"XXXXX6789\"";
	ASSERT_EQ(true, db.TestJson(compstr57, pjson57));

	const char *pjson58 = db.DisableBankAccount(1, "1");
	char compstr58[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr58, pjson58));

	const char *pjson59 = db.EnableBankAccount(1, "1");
	char compstr59[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr59, pjson59));

	const char *pjson60 = db.InitiateValidation(1, "1", 0.19, 0.03);
	char compstr60[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"validation\":{\"amount1\":\"0.19\",\"amount2\":\"0.03\"}}";
	ASSERT_EQ(true, db.TestJson(compstr60, pjson60));

	const char *pjson61 = db.ValidateBankAccount(1, "1", "0.19", "0.03");
	char compstr61[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr61, pjson61));

	const char *pjson62 = db.QueryUserPayments(1, "1"); 
	char compstr62[] = "HTTP/1.1 400 Bad Request\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"400\",\"source\":\"API\",\"title\":\"queryuserpayments error\",\"detail\":\"There are no payments in ref to the userid\"}}";
	ASSERT_EQ(true, db.TestJson(compstr62, pjson62));

	const char *pjson63 = db.QueryBatchPayments(1, 1);
	char compstr63[] = "HTTP/1.1 400 Bad Request\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"400\",\"source\":\"API\",\"title\":\"querybatchpayments error\",\"detail\":\"There are no payments in ref to the batchid\"}}";
	ASSERT_EQ(true, db.TestJson(compstr63, pjson63));

	const char *pjson64 = db.GetNoPayUsers(1, 1);
	//char compstr64[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"commissions\":[{\"user_id\":\"12\",\"amount\":\"612.9900\"},{\"user_id\":\"2\",\"amount\":\"295.0500\"},{\"user_id\":\"3\",\"amount\":\"127.4000\"}]}";
	char compstr64[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"commissions\"";
	ASSERT_EQ(true, db.TestJson(compstr64, pjson64));
}

//////////////////////////////
// Handle private functions //
//////////////////////////////
TEST(DB, Private)
{
	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

	CDb db;
	db.Connect(&ce.m_Settings);

	//const char *pjson7 = db.SyncWithPayman(1);
	//char compstr7[] = "HTTP/1.1 503 Service Unavailable\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"503\",\"source\":\"API\",\"title\":\"SyncWithPayman error\",\"detail\":\"response json missing data\"}}";
	//ASSERT_EQ(true, db.TestJson(compstr7, pjson7));

	const char *pjson1 = db.SyncWithNacha(1);
	char compstr1[] = "Needs to be finished";
	ASSERT_EQ(true, db.TestJson(compstr1, pjson1));

	ASSERT_EQ(1, db.AddBankPayoutFile(1, 1, "test.filename.txt"));
	ASSERT_EQ(true, db.AddBankPayment(1, 1, "1", 22.99, 1));
	ASSERT_EQ(true, db.Flush());
	ASSERT_EQ(true, db.Clear());

	const char *pjson55 = db.AddBankAccount(2, "1", 1, "123456789", "98765432198765432", "TestName TestLast");
	char compstr55[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(compstr55, pjson55));

	CBankAccount *pBank = db.GetBankAccount(2, "1");
	ASSERT_EQ(2, pBank->m_ID);
	ASSERT_EQ(1, pBank->m_AccountType);
	ASSERT_STREQ("123456789", pBank->m_RoutingNumber.c_str());
	ASSERT_STREQ("98765432198765432", pBank->m_AccountNumber.c_str());
	ASSERT_STREQ("TestName TestLast", pBank->m_HolderName.c_str());

	std::list <CPayUser> PayUsersLL;
	ASSERT_EQ(true, db.BuildPayUserList(1, 1, &PayUsersLL));
	ASSERT_STREQ("XXXXX6789", db.Mask("123456789"));
	ASSERT_EQ(true, db.ExecDB("SELECT count(*) FROM users"));
		
	std::stringstream ss;
	ASSERT_EQ(true, db.ExecDB(ss << "SELECT count(*) FROM users"));
	ASSERT_EQ(1, db.GetFirstDB("SELECT id FROM users ORDER BY id"));
	std::stringstream ss1;
	ASSERT_EQ(1, db.GetFirstDB(ss1 << "SELECT id FROM users ORDER BY id"));

	ASSERT_STREQ("1", db.GetFirstCharDB("SELECT id FROM users ORDER BY id"));
	std::stringstream ss2;
	ASSERT_STREQ("1", db.GetFirstCharDB(ss2 << "SELECT id FROM users ORDER BY id"));

	//printf("%d\n", retval);
}

//////////////////////
// Commission Class //
//////////////////////
TEST(CCommissions, Public)
{
	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

	CDb db;
	db.Connect(&ce.m_Settings);

	CCommissions comm;

	const char *pjson1 = comm.Run(&db, 1, 1, false, true, "2016-7-1", "2016-7-31");
	//char compstr1[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandpayouts\":{\"receipts\":\"5050.00\",\"commissions\":\"1050.74\",\"achvbonuses\":\"280.00\"}}";
	char compstr1[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandpayouts\":{\"receipts\":\"1010000.00\",\"commissions\":\"0.00\",\"achvbonuses\":\"0.00\"}}";
	ASSERT_EQ(true, db.TestJson(compstr1, pjson1));
	ASSERT_EQ(true, comm.RunPool(&db, 1, 1, 1, 2000, "2016-7-1", "2016-7-31"));
}


/////////////////////////
// Test the encryption //
/////////////////////////
TEST(CezCrypt, public)
{
	CezCrypt crypt;
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //
	const char *salt = crypt.GenSalt();
	ASSERT_EQ(32, strlen(salt));

	const char *pbj = crypt.GenPBKDF2("This is a pretend hash pass", salt, "This is a pretend password");
	ASSERT_EQ(80, strlen(pbj));

	// Leave these in for reference //
	const char *sha512 = crypt.GenSha512();
	ASSERT_LT(110, strlen(sha512));

	const char *sha256 = crypt.GenSha256();
	ASSERT_GT(65, strlen(sha256));
}

/////////////////////////
// Test our curl class //
/////////////////////////
TEST(CezCurl, public)
{
	//CezCurl curl;

	//CDb db;

	//const char *pval = curl.SendRaw("http://www.google.com", "q=test");
	//char comstr1[] = "<!DOCTYPE html>\r\n<html lang=en>\r\n  <meta charset=utf-8>\r\n  <meta name=viewport content=\"initial-scale=1, minimum-scale=1, width=device-width\">\r\n  <title>Error 405 (Method Not Allowed)!!1</title>"; 
	//ASSERT_EQ(true, db.TestJson(comstr1, pval));
	//ASSERT_TRUE(curl.SetHeader("test: thetest"));
}

///////////////////////////
// Test our ezJson class //
///////////////////////////
TEST (CezJson, public)
{
	CezJson json;
	CDb db;

	const char *pval = json.SetJson(200, ",test");

	char comstr1[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{test}";
	ASSERT_EQ(true, db.TestJson(comstr1, pval));

	std::stringstream ss;
    const char *pval2 = json.SetJson(404, ss << ",test");
	char comstr2[] = "HTTP/1.1 404 Not Found\r\nContent-Type: application/vnd.api+json\r\n\r\n{test}";
	ASSERT_EQ(true, db.TestJson(comstr2, pval2));

	const char *pval3 = json.SetError(503, "source test", "title test", "detail test");
	char comstr3[] = "HTTP/1.1 503 Service Unavailable\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"503\",\"source\":\"source test\",\"title\":\"title test\",\"detail\":\"detail test\"}}";
    ASSERT_EQ(true, db.TestJson(comstr3, pval3));
    
    const char *pval4 = json.SetAngResp("test-access_headers", "test-origin");
	char comstr4[] = "HTTP/1.1 200 OK\r\nAccess-Control-Allow-Origin: test-origin\r\nAccess-Control-Allow-Methods: POST\r\nAccess-Control-Allow-Headers: test-access_headers\r\nAccess-Control-Allow-Credentials: true\r\nContent-Type: text/plain";
	ASSERT_EQ(true, db.TestJson(comstr4, pval4));
}

/////////////////////////
// Do nacha file tests //
/////////////////////////
TEST (CezNacha, public)
{
	
}

//////////////////////////////////////
// Handle testing the CezRecv class //
//////////////////////////////////////
TEST (CezRecv, public)
{
	CDb db;

	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //
	
	CezRecv recv;
	ASSERT_EQ(true, recv.Clear());
	ASSERT_EQ(true, recv.Startup(&ce.m_Settings));
	ASSERT_EQ(true, recv.StartupRuby(ce.m_Settings.m_DatabaseName, ce.m_Settings.m_Username, ce.m_Settings.m_Password, ce.m_Settings.m_Hostname));
*/
	//const char data[] = "POST / HTTP/1.1\r\nUser-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)\r\nHost: nospy.mobi\r\nAccept: */*\r\ncommand: calccommissions\r\nauthemail: master@commissions.com\r\napikey: 44cfb3d2a282a1268a2c68619f8adbe1934917239c82ceac49a8d2b58d8ae0\r\nstartdate: 2016-1-1\r\nenddate: 2016-12-31\r\nContent-Type: application/x-www-form-urlencoded\r\nExpect: 100-continue";
/*	ASSERT_EQ(true, recv.SocketParse(data)); // Parse the incoming data //
	ASSERT_EQ(true, recv.SetPostVar("testkey", "testval")); // Set map hash values for all post variables //
	//ASSERT_EQ(false, recv.SetPostVar(NULL, "testval")); // Set map hash values for all post variables //
	ASSERT_EQ(true, recv.SetPostVar("HTTP_TEST", "testval")); // Set map hash values for all post variables //
	ASSERT_EQ(true, recv.SetPostVar("http_test", "testval")); // Set map hash values for all post variables //
	ASSERT_EQ(true, recv.SetHeadVar("testkey", "testval")); // Set map hash values for all head variables //
		
	std::string key = "testkey";
	std::string value = "testvalue";
	ASSERT_EQ(true, recv.SetVar(key, value));
	
	//const char *retstr = recv.Process(); // Process incoming communication //
*/
	//const char compstr1[] = "POST / HTTP/1.1\r\nUser-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)\r\nHost: nospy.mobi\r\nAccept: */*\r\ncommand: calccommissions\r\nauthemail: master@commissions.com\r\napikey: 44cfb3d2a282a1268a2c68619f8adbe1934917239c82ceac49a8d2b58d8ae0\r\nstartdate: 2016-1-1\r\nenddate: 2016-12-31\r\nContent-Type: application/x-www-form-urlencoded\r\nExpect: 100-continue";
	//ASSERT_EQ(true, db.TestJson(compstr1, retstr));
/*	ASSERT_EQ(true, recv.DumpVars()); // Display all vars set earlier //

	ASSERT_STREQ("testval", recv.GetHeadVar("testkey")); // Grab from out map hash values //
	ASSERT_EQ(true, recv.ClearHeadVar("testkey"));
	ASSERT_EQ(true, recv.SetOrigin("127.0.0.1"));
	ASSERT_EQ(true, recv.SetSocket(1));
}

TEST (CezRecv, private)
{
	CCommissionEngine ce;
	ce.LoadINI(INI_FILENAME);
	g_DebugLevel = TEST_DEBUG_OVERRIDE; // Turn off debug print outs //

	CDb db;
	CezRecv recv;
	recv.Startup(&ce.m_Settings);

	std::string key = "testkey";
	std::string value = "testvalue";
	ASSERT_EQ(true, recv.SetVar(key, value));
	ASSERT_STREQ("testvalue", recv.GetPostVar("testkey"));

	ASSERT_EQ(CMD_AUTHSESSIONUSER, recv.CheckCommands(POST_AUTHSESSIONUSER));
	ASSERT_EQ(CMD_ADDSYSTEMUSER, recv.CheckCommands(POST_ADDSYSTEMUSER));
	ASSERT_EQ(CMD_EDITSYSTEMUSER, recv.CheckCommands(POST_EDITSYSTEMUSER));
	ASSERT_EQ(CMD_QUERYSYSTEMUSERS, recv.CheckCommands(POST_QUERYSYSTEMUSERS));
	ASSERT_EQ(CMD_DISABLESYSTEMUSER, recv.CheckCommands(POST_DISABLESYSTEMUSER));
	ASSERT_EQ(CMD_ENABLESYSTEMUSER, recv.CheckCommands(POST_ENABLESYSTEMUSER));
	ASSERT_EQ(CMD_ADDSYSTEM, recv.CheckCommands(POST_ADDSYSTEM));
	ASSERT_EQ(CMD_EDITSYSTEM, recv.CheckCommands(POST_EDITSYSTEM));
	ASSERT_EQ(CMD_QUERYSYSTEMS, recv.CheckCommands(POST_QUERYSYSTEMS));
	ASSERT_EQ(CMD_DISABLESYSTEM, recv.CheckCommands(POST_DISABLESYSTEM));
	ASSERT_EQ(CMD_ENABLESYSTEM, recv.CheckCommands(POST_ENABLESYSTEM));
	ASSERT_EQ(CMD_ADDUSER, recv.CheckCommands(POST_ADDUSER));
	ASSERT_EQ(CMD_EDITUSER, recv.CheckCommands(POST_EDITUSER));
	ASSERT_EQ(CMD_QUERYUSERS, recv.CheckCommands(POST_QUERYUSERS));
	ASSERT_EQ(CMD_DISABLEUSER, recv.CheckCommands(POST_DISABLEUSER));
	ASSERT_EQ(CMD_ENABLEUSER, recv.CheckCommands(POST_ENABLEUSER));
	ASSERT_EQ(CMD_ADDRECEIPT, recv.CheckCommands(POST_ADDRECEIPT));
	ASSERT_EQ(CMD_EDITRECEIPT, recv.CheckCommands(POST_EDITRECEIPT));
	ASSERT_EQ(CMD_QUERYRECEIPTS, recv.CheckCommands(POST_QUERYRECEIPTS));
	ASSERT_EQ(CMD_DISABLERECEIPT, recv.CheckCommands(POST_DISABLERECEIPT));
	ASSERT_EQ(CMD_ENABLERECEIPT, recv.CheckCommands(POST_ENABLERECEIPT));
	ASSERT_EQ(CMD_QUERYBREAKDOWN, recv.CheckCommands(POST_QUERYBREAKDOWN));
	ASSERT_EQ(CMD_ADDRANKRULE, recv.CheckCommands(POST_ADDRANKRULE));
	ASSERT_EQ(CMD_EDITRANKRULE, recv.CheckCommands(POST_EDITRANKRULE));
	ASSERT_EQ(CMD_QUERYRANKRULES, recv.CheckCommands(POST_QUERYRANKRULES));
	ASSERT_EQ(CMD_DISABLERANKRULE, recv.CheckCommands(POST_DISABLERANKRULE));
	ASSERT_EQ(CMD_ENABLERANKRULE, recv.CheckCommands(POST_ENABLERANKRULE));
	ASSERT_EQ(CMD_ADDCOMMRULE, recv.CheckCommands(POST_ADDCOMMRULE));
	ASSERT_EQ(CMD_EDITCOMMRULE, recv.CheckCommands(POST_EDITCOMMRULE));
	ASSERT_EQ(CMD_QUERYCOMMRULES, recv.CheckCommands(POST_QUERYCOMMRULES));
	ASSERT_EQ(CMD_DISABLECOMMRULE, recv.CheckCommands(POST_DISABLECOMMRULE));
	ASSERT_EQ(CMD_ENABLECOMMRULE, recv.CheckCommands(POST_ENABLECOMMRULE));
	ASSERT_EQ(CMD_ADDPOOLPOT, recv.CheckCommands(POST_ADDPOOLPOT));
	ASSERT_EQ(CMD_EDITPOOLPOT, recv.CheckCommands(POST_EDITPOOLPOT));
	ASSERT_EQ(CMD_QUERYPOOLPOTS, recv.CheckCommands(POST_QUERYPOOLPOTS));
	ASSERT_EQ(CMD_DISABLEPOOLPOT, recv.CheckCommands(POST_DISABLEPOOLPOT));
	ASSERT_EQ(CMD_ENABLEPOOLPOT, recv.CheckCommands(POST_ENABLEPOOLPOT));
	ASSERT_EQ(CMD_ADDPOOLRULE, recv.CheckCommands(POST_ADDPOOLRULE));
	ASSERT_EQ(CMD_EDITPOOLRULE, recv.CheckCommands(POST_EDITPOOLRULE));
	ASSERT_EQ(CMD_QUERYPOOLRULES, recv.CheckCommands(POST_QUERYPOOLRULES));
	ASSERT_EQ(CMD_DISABLEPOOLRULE, recv.CheckCommands(POST_DISABLEPOOLRULE));
	ASSERT_EQ(CMD_ENABLEPOOLRULE, recv.CheckCommands(POST_ENABLEPOOLRULE));
	ASSERT_EQ(CMD_PREDICTCOMMISSIONS, recv.CheckCommands(POST_PREDICTCOMMISSIONS));
	ASSERT_EQ(CMD_PREDICTGRANDTOTAL, recv.CheckCommands(POST_PREDICTGRANDTOTAL));
	ASSERT_EQ(CMD_CALCCOMMISSIONS, recv.CheckCommands(POST_CALCCOMMISSIONS));
	ASSERT_EQ(CMD_QUERYBATCHES, recv.CheckCommands(POST_QUERYBATCHES));
	ASSERT_EQ(CMD_QUERYUSERCOMM, recv.CheckCommands(POST_QUERYUSERCOMM));
	ASSERT_EQ(CMD_QUERYBATCHCOMM, recv.CheckCommands(POST_QUERYBATCHCOMM));
	ASSERT_EQ(CMD_QUERYGRANDPAYOUT, recv.CheckCommands(POST_QUERYGRANDPAYOUT));
	ASSERT_EQ(CMD_AUTHGRANDPAYOUT, recv.CheckCommands(POST_AUTHGRANDPAYOUT));
	ASSERT_EQ(CMD_AUTHGRANDBULK, recv.CheckCommands(POST_AUTHGRANDBULK));
	ASSERT_EQ(CMD_DISABLEGRANDPAYOUT, recv.CheckCommands(POST_DISABLEGRANDPAYOUT));
	ASSERT_EQ(CMD_ENABLEGRANDPAYOUT, recv.CheckCommands(POST_ENABLEGRANDPAYOUT));
	ASSERT_EQ(CMD_ADDBANKACCOUNT, recv.CheckCommands(POST_ADDBANKACCOUNT));
	ASSERT_EQ(CMD_QUERYBANKACCOUNTS, recv.CheckCommands(POST_QUERYBANKACCOUNTS));
	ASSERT_EQ(CMD_EDITBANKACCOUNT, recv.CheckCommands(POST_EDITBANKACCOUNT));
	ASSERT_EQ(CMD_DISABLEBANKACCOUNT, recv.CheckCommands(POST_DISABLEBANKACCOUNT));
	ASSERT_EQ(CMD_ENABLEBANKACCOUNT, recv.CheckCommands(POST_ENABLEBANKACCOUNT));
	ASSERT_EQ(CMD_INITIATEVALIDATION, recv.CheckCommands(POST_INITIATEVALIDATION));
	ASSERT_EQ(CMD_VALIDATEACCOUNT, recv.CheckCommands(POST_VALIDATEACCOUNT));
	ASSERT_EQ(CMD_PROCESSPAYMENTS, recv.CheckCommands(POST_PROCESSPAYMENTS));
	ASSERT_EQ(CMD_QUERYUSERPAYMENTS, recv.CheckCommands(POST_QUERYUSERPAYMENTS));
	ASSERT_EQ(CMD_QUERYBATCHPAYMENTS, recv.CheckCommands(POST_QUERYBATCHPAYMENTS));
	ASSERT_EQ(CMD_QUERYNOPAYUSERS, recv.CheckCommands(POST_QUERYNOPAYUSERS));
	ASSERT_EQ(CMD_EXIT, recv.CheckCommands(POST_EXIT));

	ASSERT_STREQ("testkey", recv.ToLower("TESTKEY"));

	//////////////////////////
	// Start function tests //
	//////////////////////////
	const char *pjson1 = recv.AddSystemUser("test@email.com", "testpassword123", "127.0.0.1");
	char testjson1[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systemuser\":[{\"id\":\"4\",\"apikey\":\"";
	ASSERT_EQ(true, db.TestJson(testjson1, pjson1)); 

	const char *pjson2 = recv.EditSystemUser(4, 4, "test2@email.com", "paspaspas", "8.8.8.8");
	char testjson2[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systemuser\":[{\"id\":\"4\",\"apikey";
	ASSERT_EQ(true, db.TestJson(testjson2, pjson2)); 
	
	const char *pjson3 = recv.QuerySystemUsers();
	char testjson3[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systemusers\":[{\"id\":\"1\",\"email\":\"master@commissions.com";
	ASSERT_EQ(true, db.TestJson(testjson3, pjson3));

	const char *pjson4 = recv.DisableSystemUser(1, 4);
	char testjson4[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson4, pjson4));

	const char *pjson5 = recv.EnableSystemUser(1, 4);
	char testjson5[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson5, pjson5));

	/////////////
	// Systems //
	/////////////
	const char *pjson6 = recv.AddSystem(4, "test.system", "2", "2", "10", "5", "false", "0", "", "", "");
	char testjson6[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"system\":[{\"id\":\"7\"}]}";
	ASSERT_EQ(true, db.TestJson(testjson6, pjson6));

	const char *pjson7 = recv.EditSystem(7, "test.2.system", "2", "2", "10", "5", "false", "0", "", "", "");
	char testjson7[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson7, pjson7));

	const char *pjson8 = recv.QuerySystems(4);
	char testjson8[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"systems\":[{\"id\":\"7\",\"systemname\":\"test.2.system";
	ASSERT_EQ(true, db.TestJson(testjson8, pjson8));

	const char *pjson9 = recv.DisableSystem(4);
	char testjson9[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson9, pjson9));

	const char *pjson10 = recv.EnableSystem(4);
	char testjson10[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson10, pjson10));

	///////////
	// Users //
	///////////
	const char *pjson11 = recv.AddUser(4, "1", "0", "2016-7-30", "1");
	char testjson11[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson11, pjson11));

	const char *pjson12 = recv.EditUser(4, "1", "0", "2016-7-29", "2");
	char testjson12[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson12, pjson12));

	const char *pjson13 = recv.QueryUsers(4);
	char testjson13[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"users\":[{\"id\":\"102\",\"userid\":\"1\",\"sponsorid\":\"0";
	ASSERT_EQ(true, db.TestJson(testjson13, pjson13));

	const char *pjson14 = recv.DisableUser(4, "2");
	char testjson14[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson14, pjson14));

	const char *pjson15 = recv.EnableUser(4, "2");
	char testjson15[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson15, pjson15));

	//////////////
	// Receipts //
	//////////////
	const char *pjson16 = recv.AddReceipt(4, "1", "1", "9.99", "2016-7-30", "true");
	char testjson16[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson16, pjson16));

	const char *pjson17 = recv.EditReceipt(4, "1", "1", "9.99", "2016-7-30", "true");
	char testjson17[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson17, pjson17));
	
	const char *pjson18 = recv.QueryReceipts(4, "2016-7-1", "2016-7-31");
	char testjson18[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"receipts\":[{\"id\":\"102\",\"receiptid\":\"1\",\"userid\":\"1\",\"amount\":\"9.9900";
	ASSERT_EQ(true, db.TestJson(testjson18, pjson18));

	const char *pjson19 = recv.DisableReceipt(4, "1");
	char testjson19[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson19, pjson19));
	
	const char *pjson20 = recv.EnableReceipt(4, "1");
	char testjson20[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson20, pjson20));
	
	const char *pjson21 = recv.QueryBreakdown(4, "1"); 
	char testjson21[] = "HTTP/1.1 400 Bad Request\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"400";
	ASSERT_EQ(true, db.TestJson(testjson21, pjson21));

	//////////
	// Rank //
	//////////
	const char *pjson22 = recv.AddRankRule(4, "1", "1", "2", "3", "false", "0", "0");
	char testjson22[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson22, pjson22));

	const char *pjson23 = recv.EditRankRule(4, "11", "1", "1", "2", "3", "false", "0", "0");
	char testjson23[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson23, pjson23));

	const char *pjson24 = recv.QueryRankRules(4);
	char testjson24[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"rankrules\":[{\"id\":\"11\"";
	ASSERT_EQ(true, db.TestJson(testjson24, pjson24));

	const char *pjson25 = recv.DisableRankRule(4, "11");
	char testjson25[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson25, pjson25));

	const char *pjson26 = recv.EnableRankRule(4, "11");
	char testjson26[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson26, pjson26));

	///////////////////////
	// Commissions Rules //
	///////////////////////
	const char *pjson27 = recv.AddCommRule(4, "1", "1", "1", "2", "3", "false", "2");
	char testjson27[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson27, pjson27));
	
	const char *pjson28 = recv.EditCommRule(4, "19", "1", "1", "1", "1", "2", "false", "3");
	char testjson28[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson28, pjson28));

	const char *pjson29 = recv.QueryCommRule(4);
	char testjson29[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"commrules\":[{\"id\":\"19";
	ASSERT_EQ(true, db.TestJson(testjson29, pjson29));
	
	const char *pjson30 = recv.DisableCommRule(4, "19");
	char testjson30[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson30, pjson30));

	const char *pjson31 = recv.EnableCommRule(4, "19");
	char testjson31[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson31, pjson31));

	///////////////
	// Pool Pots //
	///////////////
	const char *pjson32 = recv.AddPoolPot(4, "9999", "2", "2016-7-1", "2016-7-31");
	char testjson32[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson32, pjson32));

	const char *pjson33 = recv.EditPoolPot(4, "3", "9999", "2", "2016-7-1", "2016-7-31");
	char testjson33[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson33, pjson33));

	const char *pjson34 = recv.QueryPoolPots(4);
	char testjson34[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"poolpots\":[{\"id\":\"3\",\"systemid\":\"4\",\"qualifytype\":\"2\",\"amount\":\"9999";
	ASSERT_EQ(true, db.TestJson(testjson34, pjson34));
	
	const char *pjson35 = recv.DisablePoolPot(4, "3");
	char testjson35[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson35, pjson35));
	
	const char *pjson36 = recv.EnablePoolPot(4, "3");
	char testjson36[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson36, pjson36));

	////////////////
	// Pool Rules //
	////////////////
	const char *pjson37 = recv.AddPoolRule(4, "3", "1", "1", "300");
	char testjson37[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson37, pjson37));

	const char *pjson38 = recv.EditPoolRule(4, "3", "1", "2", "350"); 
	char testjson38[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson38, pjson38));

	const char *pjson39 = recv.QueryPoolRules(4, "3");
	char testjson39[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"poolrules\":[{\"id\":\"3\",\"startrank\":\"1\",\"endrank\":\"2\"";
	ASSERT_EQ(true, db.TestJson(testjson39, pjson39));

	const char *pjson40 = recv.DisablePoolRule(4, "3");
	char testjson40[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson40, pjson40));

	const char *pjson41 = recv.EnablePoolRule(4, "3");
	char testjson41[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"}}";
	ASSERT_EQ(true, db.TestJson(testjson41, pjson41));

	/////////////////////////////
	// Commission Calculations //
	/////////////////////////////
	const char *pjson42 = recv.PredictCommissions(1, "2016-7-1", "2016-7-31");
	char testjson42[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"payouts\":[{\"userid\":\"1\",\"commission";
	ASSERT_EQ(true, db.TestJson(testjson42, pjson42));

	const char *pjson43 = recv.PredictGrandTotal(1, "2016-7-1", "2016-7-31");
	char testjson43[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandpayouts\":{\"receipts\":\"505000.00\",\"commissions\":\"0.00\",\"achvbonuses\":\"0.00\"}}";
	ASSERT_EQ(true, db.TestJson(testjson43, pjson43));

	const char *pjson44 = recv.CalcCommissions(1, "2016-7-1", "2016-7-31");
	char testjson44[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"grandpayouts\":{\"receipts\":\"505000.00\",\"commissions\":\"0.00\",\"achvbonuses\":\"0.00\"}}";
	ASSERT_EQ(true, db.TestJson(testjson44, pjson44));

	const char *pjson45 = recv.QueryBatches(1);
	char testjson45[] = "HTTP/1.1 200 OK\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"success\":{\"status\":\"200\"},\"batches\":[{\"id\":\"1\",\"systemid\":\"1\",\"startdate\":\"2016-7-1";
	ASSERT_EQ(true, db.TestJson(testjson45, pjson45));

	const char *pjson46 = recv.QueryUserComm(1, "1");
	char testjson46[] = "HTTP/1.1 412 Precondition Failed\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"412";
	ASSERT_EQ(true, db.TestJson(testjson46, pjson46));

	const char *pjson47 = recv.QueryBatchComm(1, "1");
	printf("%s\n", pjson47);
	char testjson47[] = "HTTP/1.1 412 Precondition Failed\r\nContent-Type: application/vnd.api+json\r\n\r\n{\"errors\":{\"status\":\"412";
	ASSERT_EQ(true, db.TestJson(testjson47, pjson47));
}
*/