#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/piphany-live/newplan.php";

include "../tests/includes/inc.ce-comm.php";
include "../tests/includes/inc.systemusers.php";
include "../tests/includes/inc.systems.php";
include "../tests/includes/inc.rankrules.php";
include "../tests/includes/inc.commrules.php";
include "../tests/includes/inc.basiccommrules.php";
include "../tests/includes/inc.tests.php";

$starttime = time();

// Main Account //
$sysmain_email = "master@commissions.com";
$sysmain_pass = "my.co#5YvhgW34&&.gf:gf*()23oties.com";

// Generate Random Email //
$sysuser_email = "wanderson@controlpad.com";
$sysuser_password = "piphitup";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

// Add a system User //
echo "[ PIPHANY - AddSystemUser:: ]\n";
$result = AddSystemUser($sysuser_email);
TestCheck("true", $result, "PIPHANY - AddSystemUser");
//Pre($result);

// Add a system //
echo "[ PIPHANY - AddSystem:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 0;
$psqlimit = 0; // They must have 350 in personal sales to even qualify //
$compression = "true";
$system = AddSystem("Reps", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system, "PIPHANY - AddSystem");
//Pre($system);

$system["id"] = 1; //
 
$invtype = "5"; // Wholesale //
$event = "2"; // On Retail Date //
$paytype = "2"; // Wholesale //
$max_rank = "7";

////////////////
// Rank Rules //
////////////////

// 6 to 10 units sold //
$result = AddRankRule($system['id'], "6-10", "1", 25, 6, 0, 0, 0, 0);
TestCheck("true", $result, "6-10 - AddRankRule");

// 11 to 20 units sold //
$result = AddRankRule($system['id'], "11-20", "2", 25, 11, 0, 1, 0, 0);
TestCheck("true", $result, "11-20 - AddRankRule");

// 21 to 30 units sold //
$result = AddRankRule($system['id'], "21-30", "3", 25, 21, 0, 2, 0, 0);
TestCheck("true", $result, "21-30 - AddRankRule");

// 31 to 40 units sold //
$result = AddRankRule($system['id'], "31-40", "4", 25, 31, 0, 3, 0, 0);
TestCheck("true", $result, "31-40 - AddRankRule");

// 41 to 50 units sold //
$result = AddRankRule($system['id'], "41-50", "5", 25, 41, 0, 4, 0, 0);
TestCheck("true", $result, "41-50 - AddRankRule");

// 51 to 60 units sold //
$result = AddRankRule($system['id'], "51-60", "6", 25, 51, 0, 5, 0, 0);
TestCheck("true", $result, "51-60 - AddRankRule");

// 61 to 70 units sold //
$result = AddRankRule($system['id'], "61-70", "7", 25, 61, 0, 6, 0, 0);
TestCheck("true", $result, "61-70 - AddRankRule");

// 71+ units sold //
$result = AddRankRule($system['id'], "71+", "8", 25, 71, 0, 7, 0, 0);
TestCheck("true", $result, "71+ - AddRankRule");

//////////////////////
// Commission Rules //
//////////////////////

// Payout rank 1 //
$result = AddCommRule($system["id"], "1", 0, "false", "2.5", $invtype, $event, $paytype);
TestCheck("true", $result, "6-10 - AddCommRule Gen 0");

// Payout rank 2 //
$result = AddCommRule($system["id"], "2", 0, "false", "5", $invtype, $event, $paytype);
TestCheck("true", $result, "11-20 - AddCommRule Gen 0");

// Payout rank 3 //
$result = AddCommRule($system["id"], "3", 0, "false", "7.5", $invtype, $event, $paytype);
TestCheck("true", $result, "21-30 - AddCommRule Gen 0");

// Payout rank 4 //
$result = AddCommRule($system["id"], "4", 0, "false", "10", $invtype, $event, $paytype);
TestCheck("true", $result, "31-40 - AddCommRule Gen 0");

// Payout rank 5 //
$result = AddCommRule($system["id"], "5", 0, "false", "12.5", $invtype, $event, $paytype);
TestCheck("true", $result, "41-50 - AddCommRule Gen 0");

// Payout rank 6 //
$result = AddCommRule($system["id"], "6", 0, "false", "15", $invtype, $event, $paytype);
TestCheck("true", $result, "51-60 - AddCommRule Gen 0");

// Payout rank 7 //
$result = AddCommRule($system["id"], "7", 0, "false", "17.5", $invtype, $event, $paytype);
TestCheck("true", $result, "61-70 - AddCommRule Gen 0");

// Payout rank 8 //
$result = AddCommRule($system["id"], "8", 0, "false", "20", $invtype, $event, $paytype);
TestCheck("true", $result, "71+ - AddCommRule Gen 0");