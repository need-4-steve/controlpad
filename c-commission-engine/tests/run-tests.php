#!/usr/bin/php
<?php

if (empty($argv[1]))
{
	echo "run-tests.php: A base needs to be defined. Example: ./run-tests.php test-live\n";
	return;
}

$base = $argv[1];
$_SERVER["SCRIPT_NAME"] = "/".$base."/run-tests.php";

include "includes/inc.ce-comm.php";
include "includes/inc.tests.php";

include "includes/inc.systemusers.php";
include "includes/inc.systems.php";
include "includes/inc.apikey.php";
include "includes/inc.users.php";
include "includes/inc.receipts.php";
include "includes/inc.rankgenbonusrules.php";
include "includes/inc.rankrules.php";
include "includes/inc.extqualify.php";
include "includes/inc.basiccommrules.php";
include "includes/inc.commrules.php";
include "includes/inc.cmcommrules.php";
include "includes/inc.cmrankrules.php";
include "includes/inc.pools.php";
include "includes/inc.poolrules.php";
include "includes/inc.bonus.php";
include "includes/inc.signupbonus.php";
include "includes/inc.commissions.php";
include "includes/inc.my-affiliate.php";
include "includes/inc.grandpayouts.php";
include "includes/inc.reports.php";
include "includes/inc.ledger.php";
include "includes/inc.simulations.php";
include "includes/inc.rankrulesmissed.php";
include "includes/inc.chalkatour.php";
include "includes/inc.settings.php";

global $g_failcount;
global $g_passcount;
$g_failcount = 0;
$g_passcount = 0;

$starttime = time();

// Generate Random Email //
$sysuseremail = rand(1, 9999999)."wanderson@controlpad.com";
$password = "Aasdfasdf1";

//////////////////////////
// Run systemuser tests //
//////////////////////////
echo "[ SystemUsers:: ]\n";
$result = ValidCheckSysUser($sysuseremail);
TestCheck("true", $result, "ValidCheckSysUser");

$sysuser = AddSystemUser($sysuseremail);
TestCheck("true", $sysuser, "AddSystemUser");

$result = EditSystemUser($sysuser["id"], "###testsystemuserpassword.com/GUESS###");
TestCheck("true", $result, "EditSystemUser");

$result = QuerySystemUser($sysuser["id"]);
TestCheck("true", $result, "QuerySystemUsers");

$result = DisableSystemUser($sysuser["id"]);
TestCheck("true", $result, "DisableSystemUser");

$result = EditSystemUser($sysuser["id"], "###testsystemuserpassword.com/GUESS###"); // Try to edit systemuser while disabled //
if ($result == "false")
	TestTrue("EditSystemUser #2");
else if ($result == "true")
	TestFalse("EditSystemUser #2");

$result = EnableSystemUser($sysuser["id"]);
TestCheck("true", $result, "EnableSystemUser");

$result = ValidCheckSysUser($sysuseremail);
TestCheck("true", $result, "ValidCheckSysUser #2");

$sysuserhash = PassHashSysUserGen($sysuseremail);
TestCheck("true", $sysuserhash, "PassHashSysUserGen");

$result = PassHashSysUserValid($sysuserhash);
TestCheck("true", $result, "PassHashSysUserValid");

$result = PassHashSysUserUpdate($sysuserhash);
TestCheck("true", $result, "PassHashSysUserUpdate");

$result = PassResetSysUser($sysuser["id"], $password);
TestCheck("true", $result, "PassResetSysUser");

$result = LogoutSysUserLog($sysuseremail);
TestCheck("true", $result, "LogoutSysUserLog");

///////////////////////
// Run systems tests //
///////////////////////
echo "[ Systems:: ]\n";
$systemname = "APITest".rand(1, 100000);
$system = AddSystem($systemname, 1, 1, 15, 5, 1, 3, 0, 100, "true");
TestCheck("true", $system, "AddSystem");

$result = EditSystem($system["id"]);
TestCheck("true", $result, "EditSystem");

$result = QuerySystem($system["id"]);
TestCheck("true", $result, "QuerySystem");

$result = DisableSystem($system["id"]);
TestCheck("true", $result, "DisableSystem");

$result = EnableSystem($system["id"]);
TestCheck("true", $result, "EnableSystem");

$result = GetSystemVals($system["id"]);
TestCheck("true", $result, "GetSystemVals");

$result = CountSystem($sysuser["id"]);
TestCheck("true", $result, "CountSystem");

$result = StatsSystem($system["id"]);
TestCheck("true", $result, "StatsSystem");

//////////////////////
// Run apikey tests //
//////////////////////
echo "[ ApiKey:: ]\n";
$apikey = ReissueApiKey();
TestCheck("true", $apikey, "ReissueApiKey");

$result = AddApiKey($system["id"]);
TestCheck("true", $result, "AddApiKey");

$result = EditApiKey($system["id"]);
TestCheck("true", $result, "EditApiKey");

$result = QueryApiKey($system["id"]);
TestCheck("true", $result, "QueryApiKey");

$result = EnableApiKey($system["id"]);
TestCheck("true", $result, "EnableApiKey");

$result = DisableApiKey($system["id"]);
TestCheck("true", $result, "DisableApiKey");

////////////////////
// Run User tests //
////////////////////
echo "[ Users:: ]\n";
$user = AddUser($system["id"], "1", "0", "0", "1", "ce-test@controlpad.com");
TestCheck("true", $user, "AddUser");

$usertest = AddUser($system["id"], "1-testaddr", "0", "0", "1", "ce-test-addr@controlpad.com", "123 Mocking Bird Lane", "Orem", "UT", "84555");
TestCheck("true", $usertest, "AddUser - Address Optional");

$useraddress = UpdateUserAddress($system["id"], "1-testaddr", "359 Bluebird Lane", "Silverton", "CO", "55555");
TestCheck("true", $useraddress, "UpdateUserAddress");

$result = EditUser($system["id"], $user);
TestCheck("true", $result, "EditUser");

$result = QueryUsers($system["id"]);
TestCheck("true", $result, "QueryUsers");

$result = DisableUser($system["id"], $user["user_id"]);
TestCheck("true", $result, "DisableUser");

$result = EnableUser($system["id"], $user["user_id"]);
TestCheck("true", $result, "EnableUser");

$result = GetUser($system["id"], $user["user_id"]);
TestCheck("true", $result, "GetUser");

$result = SeedUsers($system["id"]);
TestCheck("true", $result, "SeedUsers");

///////////////////////
// Run Receipt tests //
///////////////////////
echo "[ Receipts:: ]\n";
$receipt = AddReceipt($system["id"], $user["user_id"], 1, 10.01, 50.05);
TestCheck("true", $receipt, "AddReceipt");

$result = EditReceipt($system["id"], $user["user_id"], 1, 10.29, 50.19);
TestCheck("true", $result, "EditReceipt");

$result = EditReceiptWID($system["id"], $user["user_id"], $receipt["id"]);
TestCheck("true", $result, "EditReceiptWID");

$receiptquery = QueryReceipts($system["id"]);
TestCheck("true", $receiptquery, "QueryReceipts");

if ($receiptquery["wholesaleprice"] != 11.06)
	TestFalse("WholesalePrice");
else
	TestTrue("WholesalePrice");

if ($receiptquery["retailprice"] != 22.17)
	TestFalse("RetailPrice");
else
	TestTrue("RetailPrice");

$result = QueryReceiptSum($system["id"]);
TestCheck("true", $result, "QueryReceiptSum");

$result = DisableReceipt($system["id"], $receipt["id"]);
TestCheck("true", $result, "DisableReceipt");

$result = EnableReceipt($system["id"], $receipt["id"]);
TestCheck("true", $result, "EnableReceipt");

$result = GetReceipt($system["id"], $receipt["id"]);
TestCheck("true", $result, "GetReceipt");

$result = QueryBreakdownReceipt($system["id"]);
TestCheck("true", $result, "QueryBreakdownReceipt");



/*
$result = AddReceiptBulk($system["id"], 5, $user["user_id"], 6, "10.99", "2019-01-15 23:11:21", 1, "true", "STUFF-001");

//$result = AddReceiptBulk($system["id"], $user["user_id"], 6);
TestCheck("true", $result, "AddReceiptBulk");

$result = UpdateReceiptBulk($system["id"], $user["user_id"], 4);
TestCheck("true", $result, "UpdateReceiptBulk #1");

$result = UpdateReceiptBulk($system["id"], $user["user_id"], 3); 
//TestCheck("false", $result, "UpdateReceiptBulk #2"); // Accommodate for the error //
if ($result == "false")
	TestTrue("UpdateReceiptBulk #2");
else if ($result == "true")
	TestFalse("UpdateReceiptBulk #2");
*/



$result = SeedReceipts($system["id"]);
TestCheck("true", $result, "SeedReceipts");

////////////////////////
// Run RankRule tests //
////////////////////////
echo "[ RankRules:: ]\n"; 
$rankrule = AddRankRule($system["id"], "Squire", "1", 1, 5, 1, 0, 0, 0, 0, 0);
TestCheck("true", $rankrule, "AddRankRule");

// Add additional rankrules for commission run later //
AddRankRule($system["id"], "Knight", "2", 1, 50, 3, 0, 0, 0, 0, 0);
AddRankRule($system["id"], "Paladin", "3", 1, 100, 9, 0, 0, 0, 0, 0);

$result = EditRankRule($system["id"], $rankrule['id'], "1", "Squire1");
TestCheck("true", $result, "EditRankRule");

$rankrulequery = QueryRankRule($system["id"]);
TestCheck("true", $rankrulequery, "QueryRankRule");

if ($rankrulequery["label"] != "Squire1")
	TestFalse("RankRule Compare Results #1");
else
	TestTrue("RankRule Compare Results #1");

if ($rankrulequery["rank"] != "1")
	TestFalse("RankRule Compare Results #2");
else
	TestTrue("RankRule Compare Results #2");

$result = DisableRankRule($system["id"], $rankrule["id"]);
TestCheck("true", $result, "DisableRankRule");

$result = EnableRankRule($system["id"], $rankrule["id"]);
TestCheck("true", $result, "EnableRankRule");

$result = GetRankRule($system["id"], $rankrule["id"]);
TestCheck("true", $result, "GetRankRule");

if ($rankrulequery["qualifythreshold"] != "5")
	TestFalse("RankRule Compare Results #3");
else
	TestTrue("RankRule Compare Results #3");

if ($rankrulequery["qualifytype"] != "1")
	TestFalse("RankRule Compare Results #4");
else
	TestTrue("RankRule Compare Results #4");

//////////////////////////
// Run ExtQualify Tests //
//////////////////////////
echo "[ ExtQualify:: ]\n"; 

//AddExtQualify($system["id"], $userid, $varid, $value, $eventdate)
$extqualify = AddExtQualify($system["id"], "5", "9", "12", "2018-10-20");
TestCheck("true", $extqualify, "AddExtQualify");

$result = EditExtQualify($system["id"], $extqualify['id'], "5", "9", "13", "2018-10-21");
TestCheck("true", $result, "EditExtQualify");

$extqualifyquery = QueryExtQualify($system["id"]);
TestCheck("true", $extqualifyquery, "QueryExtQualify");

$result = DisableExtQualify($system["id"], $extqualify["id"]);
TestCheck("true", $result, "DisableExtQualify");

$result = EnableExtQualify($system["id"], $extqualify["id"]);
TestCheck("true", $result, "EnableExtQualify");

$result = GetExtQualify($system["id"], $extqualify["id"]);
TestCheck("true", $result, "GetExtQualify");

////////////////////////
// Run CommRule Tests //
////////////////////////
echo "[ CommRules:: ]\n"; 
$commrule = AddCommRule($system["id"], 1, 1, "false", 10, 1, 1, 1);
TestCheck("true", $commrule, "AddCommRule"); 

// Add commission rules for commission run later //
AddCommRule($system["id"], 2, 1, "false", 10, 1, 1, 1);
AddCommRule($system["id"], 2, 2, "false", 5, 1, 1, 1);
AddCommRule($system["id"], 3, 1, "false", 10, 1, 1, 1);
AddCommRule($system["id"], 3, 2, "false", 5, 1, 1, 1);
AddCommRule($system["id"], 3, 3, "false", 2, 1, 1, 1);
AddCommRule($system["id"], 3, 4, "false", 1, 1, 1, 1); // Test infinity bonus. NO for now //

$result = EditCommRule($system["id"], $commrule["id"], 1, 1, "false", 9, 1, 1, 1);
TestCheck("true", $result, "EditCommRule");

$querycommrules = QueryCommRule($system["id"]);
TestCheck("true", $querycommrules, "QueryCommRule");

$result = DisableCommRule($system["id"], $commrule['id']);
TestCheck("true", $result, "DisableCommRule");

$result = EnableCommRule($system["id"], $commrule['id']);
TestCheck("true", $result, "EnableCommRule");

$result = GetCommRule($system["id"], $commrule['id']);
TestCheck("true", $result, "GetCommRule");

////////////////////////
// Run CommRule Tests //
////////////////////////
echo "[ BasicCommRules:: ]\n"; 

$basiccommrule = AddBasicCommRule($system["id"], 1, 0, 0, 0, 1, 1, 10, 0, 0, "false", 1);
TestCheck("true", $basiccommrule, "AddBasicCommRule");

$result = EditBasicCommRule($system["id"], $basiccommrule["id"], 1, 0, 0, 0, 1, 1, 25, 10, 0, "false", 1);
TestCheck("true", $result, "EditBasicCommRule");

$querybasiccommrules = QueryBasicCommRule($system["id"]);
TestCheck("true", $querybasiccommrules, "QueryBasicCommRule");

$result = DisableBasicCommRule($system["id"], $basiccommrule['id']);
TestCheck("true", $result, "DisableBasicCommRule");

$result = EnableBasicCommRule($system["id"], $basiccommrule['id']);
TestCheck("true", $result, "EnableBasicCommRule");

$result = GetBasicCommRule($system["id"], $basiccommrule['id']);
TestCheck("true", $result, "GetBasicCommRule");

////////////////////////
// Run CMRankRule tests //
////////////////////////
echo "[ CMRankRules:: ]\n"; 
$cmrankrule = AddCMRankRule($system["id"], "Squire", "1", 1, 5, 1, 0, 0, 0, 0, 0);
TestCheck("true", $cmrankrule, "AddCMRankRule");

// Add additional rankrules for commission run later //
AddCMRankRule($system["id"], "Knight", "2", 1, 50, 3, 0, 0, 0, 0, 0);
AddCMRankRule($system["id"], "Paladin", "3", 1, 100, 9, 0, 0, 0, 0, 0);

$result = EditCMRankRule($system["id"], $cmrankrule['id'], "1", "Squire1");
TestCheck("true", $result, "EditCMRankRule");

$cmrankrulequery = QueryCMRankRule($system["id"]);
TestCheck("true", $cmrankrulequery, "QueryCMRankRule");

if ($cmrankrulequery["label"] != "Squire1")
	TestFalse("CMRankRule Compare Results #1");
else
	TestTrue("CMRankRule Compare Results #1");

if ($cmrankrulequery["rank"] != "1")
	TestFalse("CMRankRule Compare Results #2");
else
	TestTrue("CMRankRule Compare Results #2");

$result = DisableCMRankRule($system["id"], $cmrankrule["id"]);
TestCheck("true", $result, "DisableCMRankRule");

$result = EnableCMRankRule($system["id"], $cmrankrule["id"]);
TestCheck("true", $result, "EnableCMRankRule");

$result = GetCMRankRule($system["id"], $cmrankrule["id"]);
TestCheck("true", $result, "GetCMRankRule");

if ($cmrankrulequery["qualifythreshold"] != "5")
	TestFalse("RankRule Compare Results #3");
else
	TestTrue("RankRule Compare Results #3");

if ($cmrankrulequery["qualifytype"] != "1")
	TestFalse("RankRule Compare Results #4");
else
	TestTrue("RankRule Compare Results #4");

//////////////////////////////////
// Check Match Commission Rules //
//////////////////////////////////
echo "[ CheckMatch CommRules:: ]\n"; 
$cmcommrule = AddCMCommRule($system["id"], 1, 3, 20);
TestCheck("true", $result, "AddCMCommRule");

$result = EditCMCommRule($system["id"], $cmcommrule["id"], 1, 3, 21);
TestCheck("true", $result, "EditCMCommRule");

$querycmcommrule = QueryCMCommRule($system["id"]);
TestCheck("true", $querycmcommrule, "QueryCMCommRule");

$result = DisableCMCommRule($system["id"], $cmcommrule["id"]);
TestCheck("true", $result, "DisableCMCommRule");

$result = EnableCMCommRule($system["id"], $cmcommrule["id"]);
TestCheck("true", $result, "EnableCMCommRule");

// How are going to test checkmatch? //

///////////
// Pools //
///////////
echo "[ Pools:: ]\n";
$pool = AddPool($system["id"], 2020, "2017-6-1", "2017-6-30");
TestCheck("true", $pool, "AddPool");

$result = EditPool($system["id"], $pool['id'], 2021, "2017-6-1", "2017-6-30");
TestCheck("true", $result, "EditPool");

$querypools = QueryPools($system["id"]);
TestCheck("true", $querypools, "QueryPools");

$result = DisablePool($system["id"], $pool['id']);
TestCheck("true", $result, "DisablePool");

$result = EnablePool($system["id"], $pool['id']);
TestCheck("true", $result, "EnablePool");

$result = GetPool($system["id"], $pool['id']);
TestCheck("true", $result, "GetPool");

////////////////
// Pool rules //
////////////////
echo "[ PoolRules:: ]\n";
$poolrule = AddPoolRule($system["id"], $pool['id'], 2, 3, 1, 100);
TestCheck("true", $poolrule, "AddPoolRule");

$result = EditPoolRule($system["id"], $poolrule['id'], $pool['id'], 2, 3, 1, 101);
TestCheck("true", $result, "EditPoolRule");

$result = QueryPoolRules($system["id"], $pool['id']);
TestCheck("true", $result, "QueryPoolRules");

$result = DisablePoolRule($system["id"], $poolrule['id']);
TestCheck("true", $result, "DisablePoolRule");

$result = EnablePoolRule($system["id"], $poolrule['id']);
TestCheck("true", $result, "EnablePoolRule");

$result = GetPoolRule($system["id"], $poolrule['id']);
TestCheck("true", $result, "GetPoolRule");

///////////
// Bonus //
///////////
echo "[ Bonus:: ]\n";
$bonus = AddBonus($system["id"], $user["user_id"], "99.23", "2017-6-1");
TestCheck("true", $bonus, "AddBonus");

$result = EditBonus($system["id"], $bonus['id'], $user["user_id"], "99.24", "2017-6-1");
TestCheck("true", $result, "EditBonus");

$result = QueryBonus($system["id"]);
TestCheck("true", $result, "QueryBonus");

$result = QueryUserBonus($system["id"], $user["user_id"]);
TestCheck("true", $result, "QueryUserBonus");

$result = DisableBonus($system["id"], $bonus['id']);
TestCheck("true", $result, "DisableBonus");

$result = EnableBonus($system["id"], $bonus['id']);
TestCheck("true", $result, "EnableBonus");

$result = GetBonus($system["id"], $bonus['id']);
TestCheck("true", $result, "GetBonus");

//////////////////////
// RankGenBonusRule //
//////////////////////
echo "[ RankGenBonusRule:: ]\n";
$rankgenbonus = AddRankGenBonusRule($system["id"], "7", "4", "1", "1000");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule");

$result = EditRankGenBonusRule($system["id"], $rankgenbonus['id'], "7", "4", "1", "1001");
TestCheck("true", $result, "EditRankGenBonusRule");

$result = QueryRankGenBonusRule($system["id"]);
TestCheck("true", $result, "QueryRankGenBonusRule");

$result = DisableRankGenBonusRule($system["id"], $rankgenbonus['id']);
TestCheck("true", $result, "DisableRankGenBonusRule");

$result = EnableRankGenBonusRule($system["id"], $rankgenbonus['id']);
TestCheck("true", $result, "EnableRankGenBonusRule");

$result = GetRankGenBonusRule($system["id"], $rankgenbonus['id']);
TestCheck("true", $result, "GetRankGenBonusRule");

$search = "userid=1";
$sort = "orderby=id&orderdir=desc&limit=10&offset=0";
$result = QueryRankGenBonus($system["id"], $search, $sort);
TestCheck("true", $result, "QueryRankGenBonus");

//////////////////
// Signup Bonus //
//////////////////
// Call signup bonus again after a commissin run //
echo "[ Signup Bonus:: ]\n";
$querysignupbonus = QuerySignupBonus($system["id"]);
if ($result == "false")
	TestFalse("QuerySignupBonus");
else if ($result == "true")
	TestTrue("QuerySignupBonus");

/////////////////
// Commissions //
/////////////////
echo "[ Commissions:: ]\n";
$result = PredictCommissions($system["id"], "2017-6-1", "2017-6-30");
TestCheck("true", $result, "PredictCommissions");
//file_put_contents("json/predict-payout.json", json_encode($result));

$result = PredictFullCompare($result);
TestCheck("true", $result, "PredictFullCompare");

$result = PredictGrandTotals($system["id"], "2017-6-1", "2017-6-30");
TestCheck("true", $result, "PredictGrandTotals");

$result = PredictGrandCompare($result);
TestCheck("true", $result, "PredictGrandCompare");

$result = CalcCommissions($system["id"], "2017-6-1", "2017-6-30");
TestCheck("true", $result, "CalcCommissions");

$result = PredictGrandCompare($result);
TestCheck("true", $result, "CalcCommGrandCompare");

$batch = QueryBatches($system["id"]);
TestCheck("true", $batch, "QueryBatches");

$result = BatchCompare($batch);
TestCheck("true", $result, "BatchCompare");

$usercomm = QueryUserComm($system["id"], 50);
TestCheck("true", $usercomm, "QueryUserComm");

$result = UserCommCompare($usercomm);
TestCheck("true", $result, "UserCommCompare");

$batchcomm = QueryBatchComm($system["id"], $batch['id']);
TestCheck("true", $batchcomm, "QueryBatchComm");

$result = BatchCommCompare($batchcomm);
TestCheck("true", $result, "BatchCommCompare");

////////////////////////
// My/Affiliate Tools //
////////////////////////
echo "[ My/Affiliate:: ]\n";
$result = MyUserValidCheck($system["id"], "testceapi@controlpad.com");
TestCheck("true", $result, "MyUserValidCheck");

$passhash = MyPassHashGen($system["id"], "testceapi@controlpad.com", "1.1.1.1");
TestCheck("true", $passhash, "MyPassHashGen");

$result = MyPassHashValid($passhash);
TestCheck("true", $result, "MyPassHashValid");

$hashuserid = MyPassHashUpdate($passhash);
TestCheck("true", $hashuserid, "MyPassHashUpdate");

$result = MyPassReset($system["id"], $hashuserid, "NanooNanoo");
TestCheck("true", $hashuserid, "MyPassReset");

$result = MyLogout($system["id"], "testceapi@controlpad.com");
TestCheck("true", $result, "MyLogout");

$result = MyLogin($system["id"], "testceapi@controlpad.com", "NanooNanoo", "2.2.2.2");
TestCheck("true", $result, "MyLogin");

// Remove for now cause we might not need it //
//$myprojections = MyProjections($system["id"], $user["user_id"], "2017-6-1", "2017-6-30");
//TestCheck("true", $result, "MyProjections");
//if ($myprojections["commission"] != 1.80)
//	TestFalse("MyProjections Compare");
//else
//	TestTrue("MyProjections Compare");

$mycommissions = MyCommissions($system["id"], $user["user_id"]);
TestCheck("true", $mycommissions, "MyCommissions");

if ($mycommissions["amount"] != 1.80)
	TestFalse("MyCommissions Compare");
else
	TestTrue("MyCommissions Compare");

$myachvbonus = MyAchvBonus($system["id"], $user["user_id"]);
TestCheck("true", $myachvbonus, "MyAchvBonus");

if ($myachvbonus["amount"] != 1) // 1 dollar //
	TestFalse("MyAchvBonus Compare");
else
	TestTrue("MyAchvBonus Compare");

$mybonus = MyBonus($system["id"], $user["user_id"]);	
TestCheck("true", $mybonus, "MyAchvBonus");

if ($mybonus["amount"] != 99.2400) // 1 dollar //
	TestFalse("MyBonus Compare");
else
	TestTrue("MyBonus Compare");

$myledger = MyLedger($system["id"], $user["user_id"]);	
TestCheck("true", $myledger, "MyLedger");
//file_put_contents("json/myledger.json", json_encode($myledger));

$result = VerifyMyLedger($myledger, "json/myledger.json");
TestCheck("true", $result, "VerifyMyLedger Compare");

$mystats = MyStats($system["id"], $user["user_id"]);	
TestCheck("true", $mystats, "MyStats");

if ($mystats["groupwholesalesales"] == 51500)
	TestTrue("MyStats Compare");
else
	TestFalse("MyStats Compare");

$mystatslvl1 = MyStatsLvl1($system["id"], $user["user_id"]);
TestCheck("true", $mystatslvl1, "MyStatsLvl1");

if (($mystatslvl1[0]['userid'] == 1) && ($mystatslvl1[0]['personalsales'] == 20) && ($mystatslvl1[0]['signupcount'] == 1))
	TestTrue("MyStatsLvl1 Compare #1");
else
	TestFalse("MyStatsLvl1 Compare #1");
            
$mydownlinestats = MyDownlineStats($system["id"], $user["user_id"]);
TestCheck("true", $mydownlinestats, "MyDownlineStats");
//file_put_contents("json/mydownlinestats.json", json_encode($mydownlinestats));
$result = StatCompare($mydownlinestats, "json/mydownlinestats.json");
TestCheck("true", $result, "MyStatCompare Verify");

$mydownlinestatslvl1 = MyDownlineStatsLvl1($system["id"], $user["user_id"]);
TestCheck("true", $mydownlinestatslvl1, "MyDownlineStatsLvl1");
//file_put_contents("json/mydownlinestatslvl1.json", json_encode($mydownlinestatslvl1));

$result = VerifyMyDownStatsLvl1($mydownlinestatslvl1, "json/mydownlinestatslvl1.json");
TestCheck("true", $result, "MyDownlineStatsLvl1 Verify");

///////////////////////////////////////////////////////////////////////////////////////////////////////
// This test fails because I need to add a leg rank ruleset similiar to chalkcouters commission plan //
///////////////////////////////////////////////////////////////////////////////////////////////////////
// Can we test this against a chalkcouture database dump? //
//$mydownlinestatsfull = MyDownlineStatsFull($system["id"], $user["user_id"], $batch['id']);
//TestCheck("true", $mydownlinestatsfull, "MyDownlineStatsFull");

$mybreakdown = MyBreakdown($system["id"], 96);
TestCheck("true", $mybreakdown, "MyBreakdown");

$mybreakdowngen = MyBreakdownGen($system["id"], 96, $batch['id']);
TestCheck("true", $mybreakdowngen, "MyBreakdownGen");

$mybreakdownusers = MyBreakdownUsers($system["id"], 96, 1, $batch['id']);
TestCheck("true", $mybreakdownusers, "MyBreakdownUsers");

$mybreakdownorders = MyBreakdownOrders($system["id"], 96, 97, $batch['id']);
TestCheck("true", $mybreakdownorders, "MyBreakdownOrders");

$mydownline = MyDownlineLvl1($system["id"], 33);
TestCheck("true", $mydownline, "MyDownlineLvl1");

if (($mydownline[0]['userid'] == 34) && 
	($mydownline[0]['parentid'] == 33))
	TestTrue("MyDownlineLvl1 Compare #1");
else
	TestFalse("MyDownlineLvl1 Compare #1");

$myupline = MyUpline($system["id"], 4);
TestCheck("true", $myupline, "MyUpline");

if (($myupline[1]['userid'] == 1) && 
	($myupline[1]['lastname'] == "TesterEdit"))
	TestTrue("MyUpline Compare #1");
else
	TestFalse("MyUpline Compare #1");

if (($myupline[3]['userid'] == 3) && 
	($myupline[3]['lastname'] == ""))
	TestTrue("MyUpline Compare #2");
else
	TestFalse("MyUpline Compare #2");

// This hasn't been finished yet //
$mytopclose = MyTopClose($system["id"], $user["user_id"]);
TestCheck("true", $mytopclose, "MyTopClose");

$rank = 1;
$retval = MyDownRankSumLvl1($system["id"], $user["user_id"], $batch['id'], $rank);
TestCheck("true", $retval, "MyDownRankSumLvl1");

$retval = MyDownRankSum($system["id"], $user["user_id"], $batch['id'], $rank, 1);
TestCheck("true", $retval, "MyDownRankSum");

$retval = MyTitle($system["id"], $user["user_id"], $batch['id']);
TestCheck("true", $retval, "MyTitle");

//////////////////
// GrandPayouts //
//////////////////
echo "[ GrandPayouts:: ]\n";

$grandpayouts = QueryGrandPayouts($system["id"], "false");
TestCheck("true", $grandpayouts, "QueryGrandPayouts - authorized=false");

if (($grandpayouts[8]['userid'] == 16) && 
	($grandpayouts[8]['amount'] == 84.8000))
	TestTrue("MyUpline Compare");
else
	TestFalse("MyUpline Compare");

$result = QueryGrandPayouts($system["id"], "true");
TestCheck("false", $result, "QueryGrandPayouts - authorized=true"); // There should be no records //

$result = AuthGrandPayouts($system["id"], $grandpayouts[0]["id"], "true");
TestCheck("true", $result, "AuthGrandPayouts");

$result = AuthGrandBulk($system["id"]);
TestCheck("true", $result, "AuthGrandBulk");

$result = DisableGrandPayout($system["id"], $grandpayouts[0]["id"]);
TestCheck("true", $result, "DisableGrandPayout");

$result = EnableGrandPayout($system["id"], $grandpayouts[0]["id"]);
TestCheck("true", $result, "EnableGrandPayout");

/////////////
// Reports //
/////////////
echo "[ Reports:: ]\n";

$auditranks = QueryAuditRanksReport($system["id"], $batch['id']);
TestCheck("true", $auditranks, "QueryAuditRanksReport");

if (($auditranks[0]["rank"] == 1) && ($auditranks[0]["total"] == 18.1000))
	TestTrue("QueryAuditRanksReport Compare #1");
else
	TestFalse("QueryAuditRanksReport Compare #1");

if (($auditranks[1]["rank"] == 2) && ($auditranks[1]["total"] == 125.0000))
	TestTrue("QueryAuditRanksReport Compare #2");
else
	TestFalse("QueryAuditRanksReport Compare #2");

if (($auditranks[2]["rank"] == 3) && ($auditranks[2]["total"] == 21598.3000))
	TestTrue("QueryAuditRanksReport Compare #3");
else
	TestFalse("QueryAuditRanksReport Compare #3");

$result = QueryAuditUsersReport($system["id"], $batch['id']);
TestCheck("true", $result, "QueryAuditUsers");

// Checking values doesn't work very well here cause of random user problem //

$result = QueryAuditGenReport($system["id"], $batch['id']);
TestCheck("true", $result, "QueryAuditGen");

if (($result[0]['generation'] == 1) && ($result[0]['total'] == 17649.1000))
	TestTrue("QueryAuditGen Compare Gen 1");
else
	TestFalse("QueryAuditGen Compare Gen 1");

if (($result[1]['generation'] == 2) && ($result[1]['total'] == 2568.0000))
	TestTrue("QueryAuditGen Compare Gen 2");
else
	TestFalse("QueryAuditGen Compare Gen 2");

if (($result[2]['generation'] == 3) && ($result[2]['total'] == 1017.0000))
	TestTrue("QueryAuditGen Compare Gen 3");
else
	TestFalse("QueryAuditGen Compare Gen 3");

if (($result[3]['generation'] == 4) && ($result[3]['total'] == 507.3000))
	TestTrue("QueryAuditGen Compare Gen 4");
else
	TestFalse("QueryAuditGen Compare Gen 4");

$result = QueryAuditRanks($system["id"], $batch['id']);
TestCheck("true", $result, "QueryAuditRanks");

$result = QueryAchvBonus($system["id"], $batch['id']);
TestCheck("true", $result, "QueryAchvBonus");

if (($result[0]["userid"] == 1) && ($result[0]["rank"] == 1) && ($result[0]["amount"] == 1))
	TestTrue("QueryAchvBonus Compare");
else
	TestFalse("QueryAchvBonus Compare");

$result = QueryCommissions($system["id"], $batch['id']);
TestCheck("true", $result, "QueryCommissions");

if (($result[0]["userid"] == 1) && ($result[0]["amount"] == 1.80))
	TestTrue("QueryCommissions Compare");
else
	TestFalse("QueryCommissions Compare");

$userstats = QueryUserStats($system["id"], $batch['id']);
TestCheck("true", $userstats, "QueryUserStats");
//file_put_contents("json/userstats.json", json_encode($userstats));

$result = StatCompare($userstats, "json/userstats.json");
TestCheck("true", $result, "UserStatsCompare Verify");

$userstatslvl1 = QueryUserStatsLvl1($system["id"], $batch['id']);
TestCheck("true", $userstatslvl1, "QueryUserStatsLvl1");
//file_put_contents("json/userstatslvl1.json", json_encode($userstatslvl1));

$result = VerifyMyDownStatsLvl1($userstatslvl1, "json/userstatslvl1.json");
TestCheck("true", $result, "MyDownlineStatsLvl1 Verify");

$ledgerbatch = QueryLedgerBatch($system["id"], $batch["id"]);
TestCheck("true", $ledgerbatch, "QueryLedgerBatch");

$ledgerbalance = QueryLedgerBalance($system["id"]);
TestCheck("true", $ledgerbalance, "QueryLedgerBalance");
//file_put_contents("json/ledger-balance.json", json_encode($ledgerbalance));

$result = VerifyLedgerBalance($ledgerbalance);
TestCheck("true", $result, "VerifyLedgerBalance FULL");

$result = AddLedger($system["id"], "-1", "1", 8, "-101.20", "2017-8-1");
TestCheck("true", $result, "AddLedger");

$result = EditLedger($system["id"], $result[0]['id'], "-2", "1", 8, "-102.23", "2017-8-2");
TestCheck("true", $result, "EditLedger");

$result = QueryLedger($system["id"]);
TestCheck("true", $result, "QueryLedger");

$result = QueryLedgerUser($system["id"], $user["user_id"]);
TestCheck("true", $result, "QueryLedgerUser");
//file_put_contents("json/userledger.json", json_encode($result));

$result = VerifyLedgerUser($result, "json/userledger.json");
TestCheck("true", $result, "VerifyLedgerUser Verify");

// Come back and verify ALL receipt breakdown values //
$result = QueryBreakdownReceipt($system["id"]);
TestCheck("true", $result, "QueryBreakdownReceipt #2");
//file_put_contents("json/all-breakdown.json", json_encode($result));

$result = VerifyBreakdownReceipts($result, "json/all-breakdown.json");
TestCheck("true", $result, "VerifyBreakdownReceipts FULL");

/*
/////////////////
// Simulations //
/////////////////
echo "[ Simulations:: ]\n"; 
$result = SimSeedData("test-sim1", $system["id"], 3, "", "", "", "", "", "2017-6-2", "2017-6-30");
TestCheck("true", $result, "SimSeedData #1");

$result = SimRun("test-sim1", $system["id"], "2017-6-1", "2017-6-30");
TestCheck("true", $result, "SimRun #1");
*/
TestCheck("true", "false", "Simulations Disabled for now");

//////////////////////
// RankRules Missed //
//////////////////////
echo "[ RankRulesMissed:: ]\n"; 
$result = QueryRankRulesMissed($system["id"]);
TestCheck("true", $result, "QueryRankRulesMissed");
//file_put_contents("json/rankrules-missed.json", json_encode($result['rankrulesmissed']));

$result = VerifyRankRulesMissed($result['rankrulesmissed'], "json/rankrules-missed.json");
TestCheck("true", $result, "VerifyRankRulesMissed FULL");

$result = MyRankRulesMissed($system["id"], $user["user_id"]);
TestCheck("true", $result, "MyRankRulesMissed");

$result = DownRankRulesMissed($system["id"], $user["user_id"]);
TestCheck("true", $result, "DownRankRulesMissed");
//file_put_contents("json/downline-rankrules-missed.json", json_encode($result['rankrulesmissed']));

$result = VerifyRankRulesMissed($result['rankrulesmissed'], "json/downline-rankrules-missed.json");
TestCheck("true", $result, "VerifyDownlineRankRulesMissed FULL");

////////////////////////////
// Chalkatour Rank Rules  //
////////////////////////////
echo "[ Chalkatour Rank Rules:: ]\n";

$systemname = "APITest".rand(1, 100000);
$chalk_system = AddSystem($systemname, 1, 1, 15, 5, 1, 3, 0, 100, "true");
TestCheck("true", $chalk_system, "Chalk AddSystem");

$user = AddUser($chalk_system["id"], "1", "0", "0", "1", "ce-test@controlpad.com");
TestCheck("true", $user, "Chalk AddUser");

// Seed Test Users //
$result = SeedUsers($chalk_system["id"]);
TestCheck("true", $result, "Chalk SeedUsers");

// Seed Test Receipts //
$result = SeedReceipts($chalk_system["id"]);
TestCheck("true", $result, "Chalk SeedReceipts");

//////////////
// Designer //
//////////////
$result = AddRankRule($chalk_system["id"], "Designer", "1", 1, 100, 0, 0, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #1.0");

//////////////////////
// Leading Designer //
//////////////////////
$result = AddRankRule($chalk_system["id"], "Leading Designer", "2", 1, 200, 0, 1, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #2.0");
$result = AddRankRule($chalk_system["id"], "Leading Designer", "2", 17, 1, 0, 1, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #2.1");

/////////////////////
// Master Designer //
/////////////////////
$result = AddRankRule($chalk_system["id"], "Master Designer", "3", 1, 200, 0, 2, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #3.0");
$result = AddRankRule($chalk_system["id"], "Master Designer", "3", 17, 2, 0, 2, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #3.1");

////////////
// Mentor //
////////////
$result = AddRankRule($chalk_system["id"], "Mentor", "4", 1, 400, 0, 3, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #4.0");
$result = AddRankRule($chalk_system["id"], "Mentor", "4", 17, 3, 0, 3, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #4.1");
$result = AddRankRule($chalk_system["id"], "Mentor", "4", 14, 2000, 0, 3, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #4.2");

////////////////////
// Leading Mentor //
////////////////////
$result = AddRankRule($chalk_system["id"], "Leading Mentor", "5", 1, 400, 0, 4, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #5.0");
$result = AddRankRule($chalk_system["id"], "Leading Mentor", "5", 17, 4, 0, 4, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #5.1");
$result = AddRankRule($chalk_system["id"], "Leading Mentor", "5", 14, 5000, 0, 4, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #5.2");

///////////////////
// Master Mentor //
///////////////////
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 1, 400, 500, 5, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #6.0");
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 17, 5, 0, 5, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #6.1");
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 14, 8000, 0, 5, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #6.2");
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 17, 1, 0, 5, 4, 9);
TestCheck("true", $result, "Chalk AddRankRule #6.3");

///////////////
// Couturier //
///////////////
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 1, 600, 0, 6, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #7.0");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 17, 5, 0, 6, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #7.1");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 14, 10000, 0, 6, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #7.2");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 17, 2, 0, 6, 4, 9);
TestCheck("true", $result, "Chalk AddRankRule #7.3");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 16, 1, 0, 6, 6, 9);
TestCheck("true", $result, "Chalk AddRankRule #7.4");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 2, 25000, 0, 6, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #7.5");

/////////////////////////
// Executive Couturier //
/////////////////////////
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 1, 600, 0, 7, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #8.0");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 17, 7, 0, 7, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #8.1");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 14, 20000, 0, 7, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #8.2");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 17, 3, 0, 7, 4, 9);
TestCheck("true", $result, "Chalk AddRankRule #8.3");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 16, 2, 0, 7, 6, 9);
TestCheck("true", $result, "Chalk AddRankRule #8.4");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 16, 1, 0, 7, 7, 9);
TestCheck("true", $result, "Chalk AddRankRule #8.5");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 2, 100000, 0, 7, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #8.6");

//////////////////////
// Master Couturier //
//////////////////////
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 1, 600, 0, 8, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #9.0");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 17, 10, 0, 8, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #9.1");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 14, 50000, 0, 8, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #9.2");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 17, 5, 0, 8, 4, 9);
TestCheck("true", $result, "Chalk AddRankRule #9.3");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 16, 5, 0, 8, 6, 9);
TestCheck("true", $result, "Chalk AddRankRule #9.4");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 16, 2, 0, 8, 7, 9);
TestCheck("true", $result, "Chalk AddRankRule #9.5");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 16, 1, 0, 8, 8, 9);
TestCheck("true", $result, "Chalk AddRankRule #9.6");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 2, 250000, 0, 8, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #9.7");

//////////////////////////////////
// Chalkatour Commission Rules  //
//////////////////////////////////
echo "[ Chalkatour Commission Rules:: ]\n";

//AddChakatourCommRules($chalk_system["id"], 1);
AddChalkatourCommRules($chalk_system["id"], 5);

// Do a commission run //
$result = CalcCommissions($chalk_system["id"], "2017-6-1", "2017-6-30");
TestCheck("true", $result, "Chalk CalcCommissions");



// Get the most recent batch_id //
$batch = QueryBatches($chalk_system["id"]);
TestCheck("true", $batch, "QueryBatches #2");

$result = MyPassReset($chalk_system["id"], 1, "Aasdfasdf1");
TestCheck("true", $hashuserid, "MyPassReset #2");

$_SESSION['useremail'] = "ce-test@controlpad.com";
$_SESSION['userpass'] = "Aasdfasdf1";

$mydownlinestatsfull = MyDownlineStatsFull($chalk_system["id"], 1, $batch['id']);
TestCheck("true", $mydownlinestatsfull, "MyDownlineStatsFull");

if (count($mydownlinestatsfull) != 10)
	TestFalse("MyDownlineStatsFull count");
else
	TestTrue("MyDownlineStatsFull count");

////////////////////////////////////
// Basic Commission Rules CommRun //
////////////////////////////////////
echo "[ Basic Commission Rules CommRun:: ]\n";

$systemname = "APITest".rand(1, 100000);
$basic_system = AddSystem($systemname, 1, 1, 15, 5, 1, 3, 0, 100, "true");  
TestCheck("true", $basic_system, "BasicComm AddSystem");

$basiccommrule = AddBasicCommRule($basic_system["id"], 1, 0, 0, 0, 1, 2, 10, 3, 0, "false", 1);
TestCheck("true", $basiccommrule, "BasicComm AddBasicCommRule");

$user = AddUser($basic_system["id"], "1", "0", "0", "1", "ce-test@controlpad.com");
TestCheck("true", $user, "BasicComm AddUser");

$result = SeedUsers($basic_system["id"]);
TestCheck("true", $result, "BasicComm SeedUsers");

$result = SeedReceipts($basic_system["id"]);
TestCheck("true", $result, "BasicComm SeedReceipts");

// Allow testing for negative receipts //
$receipt = AddReceipt($basic_system["id"], "50", 121212121, -135.99, -1136.95);
TestCheck("true", $receipt, "BasicComm AddReceipt");

// Do a commission run //
$result = CalcCommissions($basic_system["id"], "2017-6-1", "2017-6-30");
TestCheck("true", $result, "BasicComm CalcCommissions");

if (($result["receiptswholesale"] == 51364.01) && 
	($result["receiptsretail"] == 256363.05) && 
	($result["achvbonuses"] == 0) && 
	($result["bonuses"] == 0) && 
	($result["signupbonuses"] == 100.00))
	TestTrue("BasicComm CalcCommissions Compare");
else
	TestFalse("BasicComm CalcCommissions Compare");

//////////////
// Settings //
//////////////
echo "[ Settings:: ]\n";
$result = SettingsSetSystem(CLIENT, $system["id"], "user-index.php", "1", "Fluffy-Pancake123", "Done.Just.Right-33");
TestCheck("true", $result, "SettingsSetSystem");

$result = SettingsQuerySystem(CLIENT, $system["id"], "varname=Fluffy-Pancake123", "", "");
TestCheck("true", $result, "SettingsQuerySystem");

$result = SettingsSet(CLIENT, "user-index.php", "1", "Wierdo.1.0", "BLACK");
TestCheck("true", $result, "SettingsSet");

$result = SettingsQuery(CLIENT, "", "");
TestCheck("true", $result, "SettingsQuery #1");

$result = SettingsQuery(CLIENT, "userid=1", "");
TestCheck("true", $result, "SettingsQuery #2");

$sort = "orderby=name&orderdir=asc&offset=0&limit=10";
$result = SettingsGetTimezones(CLIENT, $sort);
TestCheck("true", $result, "SettingsGetTimezones");

// ADD test for mysponsoredstats and mysponsoredstatslvl1

// We still need to compare sim data ABOVE //

// TEST POOLS CALC //

// TEST CHECKMATCH //

// Write test to double check breakage //

// Write test for shopwaxwell type commission plan //

// Write tests for Breakaway Type Commissions //

// Write test for Binary Type Commissions //

// Terminal back to normal //
echo "\x1b[0m";
echo $g_passcount." Tests Passed\n";
echo $g_failcount." Tests Failed\n";

$teststime = time()-$starttime;
echo "Testing Time = ".$teststime." Seconds\n";
/*

// Bank Account //
#define POST_ADDBANKACCOUNT			"addbankaccount"
#define POST_QUERYBANKACCOUNTS		"querybankaccounts"
#define POST_EDITBANKACCOUNT		"editbankaccount"
#define POST_DISABLEBANKACCOUNT		"disablebankaccount"
#define POST_ENABLEBANKACCOUNT		"enablebankaccount"
#define POST_GETBANKACCOUNT			"getbankaccount"

// Validate Account //
#define POST_INITIATEVALIDATION		"initiatevalidation"
#define POST_VALIDATEACCOUNT		"validateaccount"

// Payments //
#define POST_PROCESSPAYMENTS		"processpayments"
#define POST_QUERYUSERPAYMENTS		"queryuserpayments"
#define POST_QUERYBATCHPAYMENTS		"querybatchpayments"
#define POST_QUERYNOPAYUSERS		"querynopayusers"
#define POST_QUERYPAYMENTSTOTAL		"querypaymentstotal"
#define	POST_QUERYPAYMENTS 			"querypayments"

// Simulations //
#define POST_SIM_COPYSEED			"copyseedsim"
#define POST_RUNSIM					"runsim"

// Exit - Only enable for testing purposes //
#define POST_EXIT					"exit"
*/

?>