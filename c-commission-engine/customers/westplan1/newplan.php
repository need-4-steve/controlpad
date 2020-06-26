#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/tzin-live/newplan.php";

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
$sysuser_password = "easypass";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

// Add a system User //
echo "[ WestPlan #1 - AddSystemUser:: ]\n";
$result = AddSystemUser($sysuser_email);
TestCheck("true", $result, "WestPlan1 - AddSystemUser");
//Pre($result);

// Add a system //
echo "[ WestPlan #1 - AddSystem:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 0;
$psqlimit = 0; // They must have 350 in personal sales to even qualify //
$compression = "true";
$system_comm = AddSystem("Commissions", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit, $compression);
TestCheck("true", $system, "WestPlan #1 - AddSystem");
//Pre($system);

$system["id"] = 1; //
 
$invtype = "1"; // Wholesale //
$event = "1"; // On Retail Date //
$paytype = "1";
$max_rank = "2";

//////////
// Pawn //
//////////
$result = AddRankRule($system_comm['id'], "Pawn", "1", 12, 200, 0, 1, 0, 0);
TestCheck("true", $result, "Pawn - AddRankRule PV");

////////////
// Bishop //
////////////
$result = AddRankRule($system_comm['id'], "Bishop", "2", 12, 500, 0, 2, 0, 0);
TestCheck("true", $result, "Bishop - AddCommRule PV");

$result = AddRankRule($system_comm['id'], "Bishop", "2", 17, 2, 0, 1, 2, 5); // 2 Pawns //
TestCheck("true", $result, "Bishop - AddCommRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Bishop", "2", 2, 1500, 0, 2, 0, 0);
TestCheck("true", $result, "Bishop - AddCommRule EV");

////////////
// Knight //
////////////
$result = AddRankRule($system_comm['id'], "Knight", "3", 12, 1500, 0, 3, 0, 0);
TestCheck("true", $result, "Knight - AddCommRule PV");

$result = AddRankRule($system_comm['id'], "Knight", "3", 17, 4, 0, 3, 1, 5); // 4 Pawns //
TestCheck("true", $result, "Knight - AddCommRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Knight", "3", 16, 1, 0, 3, 2, 5); // 1 Bishop //
TestCheck("true", $result, "Knight - AddCommRule RANKSUMLEG");

$result = AddRankRule($system_comm['id'], "Knight", "3", 2, 3000, 0, 3, 0, 0);
TestCheck("true", $result, "Knight - AddCommRule EV");

//////////
// Rook //
//////////
$result = AddRankRule($system_comm['id'], "Rook", "4", 12, 3500, 0, 4, 0, 0);
TestCheck("true", $result, "Rook - AddCommRule PV");

$result = AddRankRule($system_comm['id'], "Rook", "4", 17, 9, 0, 4, 1, 5); // 9 Pawns //
TestCheck("true", $result, "Rook - AddCommRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Rook", "4", 16, 2, 0, 4, 3, 5); // 2 Bishops //
TestCheck("true", $result, "Rook - AddCommRule RANKSUMLEG #1");

$result = AddRankRule($system_comm['id'], "Rook", "4", 2, 7000, 0, 4, 0, 0);
TestCheck("true", $result, "Rook - AddCommRule EV");

///////////
// Queen //
///////////
$result = AddRankRule($system_comm['id'], "Queen", "5", 12, 7500, 0, 4, 0, 0);
TestCheck("true", $result, "Queen - AddCommRule PV");

$result = AddRankRule($system_comm['id'], "Queen", "5", 17, 14, 0, 4, 1, 5); // 14 Pawns // 
TestCheck("true", $result, "Queen - AddCommRule RANKSUM_LVL1");

$result = AddRankRule($system_comm['id'], "Queen", "5", 16, 2, 0, 4, 4, 5); // 2 Rooks //  
TestCheck("true", $result, "Queen - AddCommRule RANKSUMLEG #1");

$result = AddRankRule($system_comm['id'], "Queen", "5", 2, 15000, 0, 4, 0, 0);
TestCheck("true", $result, "Queen - AddCommRule EV");

//////////
// King //
//////////

// King left off intentionally for later expansion 
// When someone has achieved Queen level //
// It will also allow to evalulate sales numbers and patterns //

/////////////////////
// Payout for Pawn //
/////////////////////
AddCommRule($system_comm['id'], "1", "-1", "false", "5", "2", "2", "1"); // PSV 1 //
AddCommRule($system_comm['id'], "1",  "1", "false", "5", "2", "2", "1"); // Gen 1 //

///////////////////////
// Payout for Bishop //
///////////////////////
AddCommRule($system_comm['id'], "2", "-1", "false", "8", "2", "2", "1"); // PSV 1 //
AddCommRule($system_comm['id'], "2",  "1", "false", "6", "2", "2", "1"); // Gen 1 //
AddCommRule($system_comm['id'], "2",  "2", "false", "5", "2", "2", "1"); // Gen 2 //

///////////////////////
// Payout for Knight //
///////////////////////
AddCommRule($system_comm['id'], "3", "-1", "false", "10", "2", "2", "1"); // PSV 1 //
AddCommRule($system_comm['id'], "3",  "1", "false",  "7", "2", "2", "1"); // Gen 1 //
AddCommRule($system_comm['id'], "3",  "2", "false",  "5", "2", "2", "1"); // Gen 2 //
AddCommRule($system_comm['id'], "3",  "3", "false",  "3", "2", "2", "1"); // Gen 3 //

/////////////////////
// Payout for Rook //
/////////////////////
AddCommRule($system_comm['id'], "4", "-1", "false", "10", "2", "2", "1"); // PSV 1 //
AddCommRule($system_comm['id'], "4",  "1", "false",  "7", "2", "2", "1"); // Gen 1 //
AddCommRule($system_comm['id'], "4",  "2", "false",  "5", "2", "2", "1"); // Gen 2 //
AddCommRule($system_comm['id'], "4",  "3", "false",  "3", "2", "2", "1"); // Gen 3 //
AddCommRule($system_comm['id'], "4",  "4", "false",  "2", "2", "2", "1"); // Gen 1 //

//////////////////////
// Payout for Queen //
//////////////////////
AddCommRule($system_comm['id'], "5", "-1", "false", "10", "2", "2", "1"); // PSV 1 //
AddCommRule($system_comm['id'], "5",  "1", "false",  "7", "2", "2", "1"); // Gen 1 //
AddCommRule($system_comm['id'], "5",  "2", "false",  "5", "2", "2", "1"); // Gen 2 //
AddCommRule($system_comm['id'], "5",  "3", "false",  "3", "2", "2", "1"); // Gen 3 //
AddCommRule($system_comm['id'], "5",  "4", "false",  "2", "2", "2", "1"); // Gen 1 //
AddCommRule($system_comm['id'], "5",  "5", "false",  "1", "2", "2", "1"); // Gen 1 //