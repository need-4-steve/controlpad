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
//$credit_system = AddSystem("Credits", 1, 1, 15, 5, 0, 3, $chalk_system["id"]);

// Handle Designer Dollars //
$invtype = 1; // Ask Mike. Is this for 5 also? // PV 
$result = AddBasicCommRule(2, 0, 0, 0, 0, $invtype, 1, 10, 20, 0, "true", 1);
TestCheck("true", $basiccommrule, "Credit Designer Dollars - AddBasicCommRule #1");

?>
