#!/usr/bin/php
<?php
include "../tests/includes/inc.ce-comm.php";
include "../tests/includes/inc.tests.php";

include "../tests/includes/inc.systemusers.php";
include "../tests/includes/inc.systems.php";
include "../tests/includes/inc.apikey.php";
include "../tests/includes/inc.users.php";
include "../tests/includes/inc.receipts.php";
include "../tests/includes/inc.rankrules.php";
include "../tests/includes/inc.commrules.php";
include "../tests/includes/inc.cmcommrules.php";
include "../tests/includes/inc.pools.php";
include "../tests/includes/inc.poolrules.php";
include "../tests/includes/inc.bonus.php";
include "../tests/includes/inc.signupbonus.php";
include "../tests/includes/inc.commissions.php";
include "../tests/includes/inc.my-affiliate.php";
include "../tests/includes/inc.grandpayouts.php";
include "../tests/includes/inc.reports.php";
include "../tests/includes/inc.ledger.php";
include "../tests/includes/inc.simulations.php";
include "../tests/includes/inc.rankrulesmissed.php";
include "../tests/includes/inc.chalkatour.php";
include "../tests/includes/inc.basiccommrules.php";

global $g_failcount;
global $g_passcount;
$g_failcount = 0;
$g_passcount = 0;

$starttime = time();

// Generate Random Email //
$sysuser_email = "info@chalkcouture.com";
$sysuser_password = "Aasdfasdf1";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

$chalk_system["id"] = 1;

//////////////
// Designer //
//////////////
$result = AddRankRule($chalk_system["id"], "Designer", "1", 12, 100, 0, 0, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #1.0");

//////////////////////
// Leading Designer //
//////////////////////
$result = AddRankRule($chalk_system["id"], "Leading Designer", "2", 12, 200, 0, 1, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #2.0");
$result = AddRankRule($chalk_system["id"], "Leading Designer", "2", 17, 1, 0, 1, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #2.1");

/////////////////////
// Master Designer //
/////////////////////
$result = AddRankRule($chalk_system["id"], "Master Designer", "3", 12, 200, 0, 2, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #3.0");
$result = AddRankRule($chalk_system["id"], "Master Designer", "3", 17, 2, 0, 2, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #3.1");

////////////
// Mentor //
////////////
$result = AddRankRule($chalk_system["id"], "Mentor", "4", 12, 400, 0, 3, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #4.0");
$result = AddRankRule($chalk_system["id"], "Mentor", "4", 17, 3, 0, 3, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #4.1");
$result = AddRankRule($chalk_system["id"], "Mentor", "4", 20, 2000, 0, 3, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #4.2");

////////////////////
// Leading Mentor //
////////////////////
$result = AddRankRule($chalk_system["id"], "Leading Mentor", "5", 12, 400, 0, 4, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #5.0");
$result = AddRankRule($chalk_system["id"], "Leading Mentor", "5", 17, 4, 0, 4, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #5.1");
$result = AddRankRule($chalk_system["id"], "Leading Mentor", "5", 20, 5000, 0, 4, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #5.2");

///////////////////
// Master Mentor //
///////////////////
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 12, 400, 500, 5, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #6.0");
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 17, 5, 500, 5, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #6.1");
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 20, 8000, 500, 5, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #6.2");
$result = AddRankRule($chalk_system["id"], "Master Mentor", "6", 17, 1, 500, 5, 4, 9);
TestCheck("true", $result, "Chalk AddRankRule #6.3");

///////////////
// Couturier //
///////////////
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 12, 600, 0, 6, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #7.0");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 17, 5, 0, 6, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #7.1");
$result = AddRankRule($chalk_system["id"], "Couturier", "7", 20, 10000, 0, 6, 0, 0);
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
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 12, 600, 0, 7, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #8.0");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 17, 7, 0, 7, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #8.1");
$result = AddRankRule($chalk_system["id"], "Executive Couturier", "8", 20, 20000, 0, 7, 0, 0);
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
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 12, 600, 0, 8, 0, 0);
TestCheck("true", $result, "Chalk AddRankRule #9.0");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 17, 10, 0, 8, 1, 9);
TestCheck("true", $result, "Chalk AddRankRule #9.1");
$result = AddRankRule($chalk_system["id"], "Master Couturier", "9", 20, 50000, 0, 8, 0, 0);
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

// Add all chalkatour comm rules //
AddChalkatourCommRules($chalk_system["id"], 5);
AddChalkatourCommRules($chalk_system["id"], 1);

//////////////////////////////
// Handle Savy Seller Rules //
//////////////////////////////

// Add Savvy Seller Rule //
$basiccommrule = AddBasicCommRule($chalk_system["id"], 0, 12, 2000, 4000, 1, 1, 10, 0, 0, "false", 1);
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #1");

$basiccommrule = AddBasicCommRule($chalk_system["id"], 0, 12, 4000, 0, 1, 1, 20, 0, 0, "false", 1);
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #2");

$basiccommrule = AddBasicCommRule($chalk_system["id"], 0, 12, 2000, 4000, 5, 1, 10, 0, 0, "false", 1);
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #3");

$basiccommrule = AddBasicCommRule($chalk_system["id"], 0, 12, 4000, 0, 5, 1, 20, 0, 0, "false", 1);
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #4");

// 25% on all affiliate orders //
$basiccommrule = AddBasicCommRule($chalk_system["id"], 0, 12, 1, 0, 5, 1, 25, 0, 0, "false", 2);
TestCheck("true", $basiccommrule, "Commission - AddBasicCommRule #5");

///////////////////
// Credit System //
///////////////////
$credit_system = AddSystem("Credits", 1, 1, 15, 5, 0, 3, $chalk_system["id"]);

// Handle Designer Dollars //
$invtype = 1; // Ask Mike. Is this for 5 also? // PV 
$result = AddBasicCommRule($credit_system['id'], 0, 0, 0, 0, $invtype, 1, 10, 20, 0, "true", 1);
TestCheck("true", $basiccommrule, "Credit Designer Dollars - AddBasicCommRule #1");

// Handle Designer Debut - First Quarter... including part first month? //
//AddBasicCommRule($credit_system["id"], 1, 1, 600, 0, $invtype, 1, 10, 60, 60);

/*
// Do a commission run //
$result = CalcCommissions($chalk_system["id"], "2017-7-1", "2017-7-31");
TestCheck("true", $result, "Chalk CalcCommissions");
*/
