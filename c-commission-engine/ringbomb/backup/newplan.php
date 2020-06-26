#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/ringbomb-live/newplan.php";

include "../tests/includes/inc.ce-comm.php";
include "../tests/includes/inc.systemusers.php";
include "../tests/includes/inc.systems.php";
include "../tests/includes/inc.rankrules.php";
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
echo "[ Ringbomb - AddSystemUser:: ]\n";
$result = AddSystemUser($sysuser_email);
TestCheck("true", $result, "Ringbomb - AddSystemUser");
//Pre($result);

// Add a system //
echo "[ Ringbomb - AddSystem:: ]\n";
$commtype = 1;
$payouttype = 1;
$payoutmonthday = 15;
$minpay = 5;
$signupbonus = 0;
$teamgenmax = 3;
$piggyid = 0;
$psqlimit = 350; // They must have 350 in personal sales to even qualify //
$system = AddSystem("Reps", $commtype, $payouttype, $payoutmonthday, $minpay, $signupbonus, $teamgenmax, $piggyid, $psqlimit);
TestCheck("true", $system, "Ringbomb - AddSystem");
//Pre($system);

$system["id"] = 1; //
 
$invtype = "1"; // Wholesale //
$event = "1"; // On Retail Date //
$paytype = "1";
$max_rank = "210";

// Create the rankrules //
// Citrine - Senior Leader //
echo "[ Citrine - Party Leader:: ]\n";
$rank['citrine-PL'] = 20;
$result = AddRankRule($system['id'], "Citrine - Senior Leader", $rank['citrine-PL'], 12, 350, 0, $rank['citrine-PL'], 0, 0);
TestCheck("true", $result, "Citrine - Senior Leader - AddRankRule #1");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['citrine-PL']);
//TestCheck("true", $result, "Citrine - Senior Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-PL']);
TestCheck("true", $result, "Citrine - Senior Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-PL']);
TestCheck("true", $result, "Citrine - Senior Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-PL']);
TestCheck("true", $result, "Citrine - Senior Leader - AddBasicCommRule #4");

// Citrine - Party Hostess //
echo "[ Citrine - Senior Hostess:: ]\n";
$rank['citrine-SH'] = 30;
$result = AddRankRule($system['id'], "Citrine - Party Hostess", $rank['citrine-SH'], 12,   525, 0, $rank['citrine-SH'], 0, 0);
TestCheck("true", $result, "Citrine - Party Hostess - AddRankRule #1");
$result = AddRankRule($system['id'], "Citrine - Party Hostess", $rank['citrine-SH'], 21,    2, 0, $rank['citrine-SH'], 0, 0);
TestCheck("true", $result, "Citrine - Party Hostess - AddRankRule #2");
$result = AddRankRule($system['id'], "Citrine - Party Hostess", $rank['citrine-SH'], 20, 2000, 0, $rank['citrine-SH'], 0, 0);
TestCheck("true", $result, "Citrine - Party Hostess - AddRankRule #3");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['citrine-SH']);
//TestCheck("true", $result, "Citrine - Party Hostess - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-SH']);
TestCheck("true", $result, "Citrine - Party Hostess - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-SH']);
TestCheck("true", $result, "Citrine - Party Hostess - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-SH']);
TestCheck("true", $result, "Citrine - Party Hostess - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['citrine-SH']);
TestCheck("true", $result, "Citrine - Party Hostess - AddBasicCommRule #5");

// Amethyst - Party Leader //
echo "[ Amethyst - Party Leader:: ]\n";
$rank['amethyst-PL'] = 40;
$result = AddRankRule($system['id'], "Amethyst - Party Leader", $rank['amethyst-PL'], 12,   620, 0, $rank['amethyst-PL'], 0, 0);
TestCheck("true", $result, "Amethyst - Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Amethyst - Party Leader", $rank['amethyst-PL'], 21,    4, 0, $rank['amethyst-PL'], 0, 0);
TestCheck("true", $result, "Amethyst - Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Amethyst - Party Leader", $rank['amethyst-PL'], 20, 3000, 0, $rank['amethyst-PL'], 0, 0);
TestCheck("true", $result, "Amethyst - Party Leader - AddRankRule #3");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['amethyst-PL']);
//TestCheck("true", $result, "Amethyst - Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-PL']);
TestCheck("true", $result, "Amethyst - Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-PL']);
TestCheck("true", $result, "Amethyst - Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-PL']);
TestCheck("true", $result, "Amethyst - Party Leader - AddBasicCommRule #4");;
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-PL']);
TestCheck("true", $result, "Amethyst - Party Leader - AddBasicCommRule #5");

// Amethyst - Senior Party Leader //
echo "[ Amethyst - Senior Party Leader:: ]\n";
$rank['amethyst-SPL'] = 50;
$result = AddRankRule($system['id'], "Amethyst - Senior Party Leader", $rank['amethyst-SPL'], 12,   620, 0, $rank['amethyst-SPL'], 0, 0);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Amethyst - Senior Party Leader", $rank['amethyst-SPL'], 21,    6, 0, $rank['amethyst-SPL'], 0, 0);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Amethyst - Senior Party Leader", $rank['amethyst-SPL'], 16,    2, 0, $rank['amethyst-SPL'], $rank['citrine-SH'], $max_rank);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Amethyst - Senior Party Leader", $rank['amethyst-SPL'], 20, 3000, 0, $rank['amethyst-SPL'], 0, 0);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddRankRule #4");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['amethyst-SPL']);
//TestCheck("true", $result, "Amethyst - Senior Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-SPL']);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-SPL']);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-SPL']);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-SPL']);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  2, "", 0, "", $paytype, $rank['amethyst-SPL']);
TestCheck("true", $result, "Amethyst - Senior Party Leader - AddBasicCommRule #6");

// Amethyst - Executive Party Leader //
echo "[ Amethyst - Executive Party Leader:: ]\n";
$rank['amethyst-EPL'] = 60;
$result = AddRankRule($system['id'], "Amethyst - Executive Party Leader", $rank['amethyst-EPL'], 12,   350, 0, $rank['amethyst-EPL'], 0, 0);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Amethyst - Executive Party Leader", $rank['amethyst-EPL'], 21,    6, 0, $rank['amethyst-EPL'], 0, 0);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Amethyst - Executive Party Leader", $rank['amethyst-EPL'], 16,    3, 0, $rank['amethyst-EPL'], $rank['citrine-SH'], $max_rank);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Amethyst - Executive Party Leader", $rank['amethyst-EPL'], 20, 3000, 0, $rank['amethyst-EPL'], 0, 0);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddRankRule #4");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['amethyst-EPL']);
//TestCheck("true", $result, "Amethyst - Executive Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-EPL']);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-EPL']);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-EPL']);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['amethyst-EPL']);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  2, "", 0, "", $paytype, $rank['amethyst-EPL']);
TestCheck("true", $result, "Amethyst - Executive Party Leader - AddBasicCommRule #6");

// Emerald - Party Leader //
echo "[ Emerald - Party Leader:: ]\n";
$rank['emerald-PL'] = 70;
$result = AddRankRule($system['id'], "Emerald - Party Leader", $rank['emerald-PL'], 12,   715, 0, $rank['emerald-PL'], 0, 0);
TestCheck("true", $result, "Emerald - Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Emerald - Party Leader", $rank['emerald-PL'], 21,    8, 0, $rank['emerald-PL'], 0, 0);
TestCheck("true", $result, "Emerald - Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Emerald - Party Leader", $rank['emerald-PL'], 16,    1, 0, $rank['emerald-PL'], $rank['amethyst-PL'], $max_rank);
TestCheck("true", $result, "Emerald - Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Emerald - Party Leader", $rank['emerald-PL'], 20, 6000, 0, $rank['emerald-PL'], 0, 0);
TestCheck("true", $result, "Emerald - Party Leader - AddRankRule #4");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['emerald-PL']);
//TestCheck("true", $result, "Emerald - Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-PL']);
TestCheck("true", $result, "Emerald - Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-PL']);
TestCheck("true", $result, "Emerald - Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-PL']);
TestCheck("true", $result, "Emerald - Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-PL']);
TestCheck("true", $result, "Emerald - Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  3, "", 0, "", $paytype, $rank['emerald-PL']);
TestCheck("true", $result, "Emerald - Party Leader - AddBasicCommRule #6");

// Emerald - Senior Party Leader //
echo "[ Emerald - Senior Party Leader:: ]\n";
$rank['emerald-SPL'] = 80;
$result = AddRankRule($system['id'], "Emerald - Senior Party Leader", $rank['emerald-SPL'], 12,   715, 0, $rank['emerald-SPL'], 0, 0);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Emerald - Senior Party Leader", $rank['emerald-SPL'], 21,   10, 0, $rank['emerald-SPL'], 0, 0);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Emerald - Senior Party Leader", $rank['emerald-SPL'], 16,    1, 0, $rank['emerald-SPL'], $rank['amethyst-SPL'], $max_rank);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Emerald - Senior Party Leader", $rank['emerald-SPL'], 20, 8000, 0, $rank['emerald-SPL'], 0, 0);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddRankRule #4");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['emerald-SPL']);
//TestCheck("true", $result, "Emerald - Senior Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-SPL']);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-SPL']);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-SPL']);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-SPL']);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  3, "", 0, "", $paytype, $rank['emerald-SPL']);
TestCheck("true", $result, "Emerald - Senior Party Leader - AddBasicCommRule #6");

// Emerald - Executive Party Leader //
echo "[ Emerald - Executive Party Leader:: ]\n";
$rank['emerald-EPL'] = 90;
$result = AddRankRule($system['id'], "Emerald - Executive Party Leader", $rank['emerald-EPL'], 12,    715, 0, $rank['emerald-EPL'], 0, 0);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Emerald - Executive Party Leader", $rank['emerald-EPL'], 21,    10, 0, $rank['emerald-EPL'], 0, 0);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Emerald - Executive Party Leader", $rank['emerald-EPL'], 16,     1, 0, $rank['emerald-EPL'], $rank['amethyst-SPL'], $max_rank);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Emerald - Executive Party Leader", $rank['emerald-EPL'], 20, 10000, 0, $rank['emerald-EPL'], 0, 0);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddRankRule #4");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['emerald-EPL']);
//TestCheck("true", $result, "Emerald - Executive Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-EPL']);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-EPL']);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-EPL']);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['emerald-EPL']);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 1, 0, 999999999, $invtype, $event,  3, "", 0, "", $paytype, $rank['emerald-EPL']);
TestCheck("true", $result, "Emerald - Executive Party Leader - AddBasicCommRule #6");

// Sapphire - Party Leader //
echo "[ Sapphire - Party Leader:: ]\n";
$rank['sapphire-PL'] = 100;
$result = AddRankRule($system['id'], "Sapphire - Party Leader", $rank['sapphire-PL'], 12,    800, 0, $rank['sapphire-PL'], 0, 0);
TestCheck("true", $result, "Sapphire - Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Sapphire - Party Leader", $rank['sapphire-PL'], 21,    12, 0, $rank['sapphire-PL'], 0, 0);
TestCheck("true", $result, "Sapphire - Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Sapphire - Party Leader", $rank['sapphire-PL'], 16,     2, 0, $rank['sapphire-PL'], $rank['emerald-PL'], $max_rank);
TestCheck("true", $result, "Sapphire - Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Sapphire - Party Leader", $rank['sapphire-PL'], 20, 11000, 0, $rank['sapphire-PL'], 0, 0);
TestCheck("true", $result, "Sapphire - Party Leader - AddRankRule #4");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['sapphire-PL']);
//TestCheck("true", $result, "Sapphire - Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-PL']);
TestCheck("true", $result, "Sapphire - Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-PL']);
TestCheck("true", $result, "Sapphire - Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-PL']);
TestCheck("true", $result, "Sapphire - Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-PL']);
TestCheck("true", $result, "Sapphire - Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  4, "", 0, "", $paytype, $rank['sapphire-PL']);
TestCheck("true", $result, "Sapphire - Party Leader - AddBasicCommRule #6");

// Sapphire - Senior Party Leader //
echo "[ Sapphire - Senior Party Leader:: ]\n";
$rank['sapphire-SPL'] = 110;
$result = AddRankRule($system['id'], "Sapphire - Senior Party Leader", $rank['sapphire-SPL'], 12,    800, 0, $rank['sapphire-SPL'], 0, 0);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Sapphire - Senior Party Leader", $rank['sapphire-SPL'], 21,    14, 0, $rank['sapphire-SPL'], 0, 0);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Sapphire - Senior Party Leader", $rank['sapphire-SPL'], 16,     2, 0, $rank['sapphire-SPL'], $rank['emerald-SPL'], $max_rank);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Sapphire - Senior Party Leader", $rank['sapphire-SPL'], 16,     1, 0, $rank['sapphire-SPL'], $rank['amethyst-PL'], $max_rank);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddRankRule #4");
$result = AddRankRule($system['id'], "Sapphire - Senior Party Leader", $rank['sapphire-SPL'], 20, 13000, 0, $rank['sapphire-SPL'], 0, 0);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['sapphire-SPL']);
//TestCheck("true", $result, "Sapphire - Senior Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-SPL']);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-SPL']);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-SPL']);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-SPL']);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  4, "", 0, "", $paytype, $rank['sapphire-SPL']);
TestCheck("true", $result, "Sapphire - Senior Party Leader - AddBasicCommRule #6");

// Sapphire - Executive Party Leader  //
echo "[ Sapphire - Executive Party Leader:: ]\n";
$rank['sapphire-EPL'] = 120;
$result = AddRankRule($system['id'], "Sapphire - Executive Party Leader", $rank['sapphire-EPL'], 12,    800, 0, $rank['sapphire-EPL'], 0, 0);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddRankRule #1");
$result = AddRankRule($system['id'], "Sapphire - Executive Party Leader", $rank['sapphire-EPL'], 21,    14, 0, $rank['sapphire-EPL'], 0, 0);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddRankRule #2");
$result = AddRankRule($system['id'], "Sapphire - Executive Party Leader", $rank['sapphire-EPL'], 16,     2, 0, $rank['sapphire-EPL'], $rank['emerald-SPL'], $max_rank);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddRankRule #3");
$result = AddRankRule($system['id'], "Sapphire - Executive Party Leader", $rank['sapphire-EPL'], 16,     1, 0, $rank['sapphire-EPL'], $rank['amethyst-EPL'], $max_rank);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddRankRule #4");
$result = AddRankRule($system['id'], "Sapphire - Executive Party Leader", $rank['sapphire-EPL'], 20, 13000, 0, $rank['sapphire-EPL'], 0, 0);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['sapphire-EPL']);
//TestCheck("true", $result, "Sapphire - Executive Party Leader - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-EPL']);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-EPL']);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-EPL']);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['sapphire-EPL']);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  4, "", 0, "", $paytype, $rank['sapphire-EPL']);
TestCheck("true", $result, "Sapphire - Executive Party Leader - AddBasicCommRule #6");

// Garnet - Party Director //
echo "[ Garnet - Party Director:: ]\n";
$rank['garnet-PD'] = 130;
$result = AddRankRule($system['id'], "Garnet - Party Director", $rank['garnet-PD'], 12,    890, 0, $rank['garnet-PD'],  0,  0);
TestCheck("true", $result, "Garnet - Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Garnet - Party Director", $rank['garnet-PD'], 21,    16, 0, $rank['garnet-PD'],  0,  0);
TestCheck("true", $result, "Garnet - Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Garnet - Party Director", $rank['garnet-PD'], 16,     2, 0, $rank['garnet-PD'],  $rank['emerald-EPL'],  $max_rank);
TestCheck("true", $result, "Garnet - Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Garnet - Party Director", $rank['garnet-PD'], 16,     1, 0, $rank['garnet-PD'], $rank['sapphire-SPL'], $max_rank);
TestCheck("true", $result, "Garnet - Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Garnet - Party Director", $rank['garnet-PD'], 20, 16000, 0, $rank['garnet-PD'],  0,  0);
TestCheck("true", $result, "Garnet - Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['garnet-PD']);
//TestCheck("true", $result, "Garnet - Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-PD']);
TestCheck("true", $result, "Garnet - Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-PD']);
TestCheck("true", $result, "Garnet - Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-PD']);
TestCheck("true", $result, "Garnet - Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-PD']);
TestCheck("true", $result, "Garnet - Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  4, "", 0, "", $paytype, $rank['garnet-PD']);
TestCheck("true", $result, "Garnet - Party Director - AddBasicCommRule #6");

// Garnet - Senior Party Director //
echo "[ Garnet - Senior Party Director:: ]\n";
$rank['garnet-SPD'] = 140;
$result = AddRankRule($system['id'], "Garnet - Senior Party Director", $rank['garnet-SPD'], 12,    890, 0, $rank['garnet-SPD'],  0,  0);
TestCheck("true", $result, "Garnet - Senior Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Garnet - Senior Party Director", $rank['garnet-SPD'], 21,    18, 0, $rank['garnet-SPD'],  0,  0);
TestCheck("true", $result, "Garnet - Senior Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Garnet - Senior Party Director", $rank['garnet-SPD'], 16,     3, 0, $rank['garnet-SPD'], $rank['emerald-EPL'], $max_rank);
TestCheck("true", $result, "Garnet - Senior Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Garnet - Senior Party Director", $rank['garnet-SPD'], 16,     1, 0, $rank['garnet-SPD'], $rank['sapphire-EPL'], $max_rank);
TestCheck("true", $result, "Garnet - Senior Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Garnet - Senior Party Director", $rank['garnet-SPD'], 20, 18000, 0, $rank['garnet-SPD'],  0,  0);
TestCheck("true", $result, "Garnet - Senior Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['garnet-SPD']);
//TestCheck("true", $result, "Garnet - Senior Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-SPD']);
TestCheck("true", $result, "Garnet - Senior Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-SPD']);
TestCheck("true", $result, "Garnet - Senior Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-SPD']);
TestCheck("true", $result, "Garnet - Senior Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-SPD']);
TestCheck("true", $result, "Garnet - Senior Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  4, "", 0, "", $paytype, $rank['garnet-SPD']);
TestCheck("true", $result, "Garnet - Senior Party Director - AddBasicCommRule #6");

// Garnet - Executive Party Director //
echo "[ Garnet - Executive Party Director:: ]\n";
$rank['garnet-EPD'] = 150;
$result = AddRankRule($system['id'], "Garnet - Executive Party Director", $rank['garnet-EPD'], 12,    890, 0, $rank['garnet-EPD'],  0,  0);
TestCheck("true", $result, "Garnet - Executive Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Garnet - Executive Party Director", $rank['garnet-EPD'], 21,    18, 0, $rank['garnet-EPD'],  0,  0);
TestCheck("true", $result, "Garnet - Executive Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Garnet - Executive Party Director", $rank['garnet-EPD'], 16,     3, 0, $rank['garnet-EPD'],  $rank['emerald-EPL'],  $max_rank);
TestCheck("true", $result, "Garnet - Executive Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Garnet - Executive Party Director", $rank['garnet-EPD'], 16,     1, 0, $rank['garnet-EPD'], $rank['emerald-EPL'], $max_rank);
TestCheck("true", $result, "Garnet - Executive Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Garnet - Executive Party Director", $rank['garnet-EPD'], 20, 20000, 0, $rank['garnet-EPD'],  0,  0);
TestCheck("true", $result, "Garnet - Executive Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['garnet-EPD']);
//TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-EPD']);
TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-EPD']);
TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-EPD']);
TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['garnet-EPD']);
TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  4, "", 0, "", $paytype, $rank['garnet-EPD']);
TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  2, "", 0, "", $paytype, $rank['garnet-EPD']);
TestCheck("true", $result, "Garnet - Executive Party Director - AddBasicCommRule #7");

// Aquamarine - Party Director //
echo "[ Aquamarine - Party Director:: ]\n";
$rank['aquamarine-PD'] = 160;
$result = AddRankRule($system['id'], "Aquamarine - Party Director", $rank['aquamarine-PD'], 12,    985, 0, $rank['aquamarine-PD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Aquamarine - Party Director", $rank['aquamarine-PD'], 21,    20, 0, $rank['aquamarine-PD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Aquamarine - Party Director", $rank['aquamarine-PD'], 16,     2, 0, $rank['aquamarine-PD'], $rank['sapphire-EPL'], $max_rank);
TestCheck("true", $result, "Aquamarine - Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Aquamarine - Party Director", $rank['aquamarine-PD'], 16,     1, 0, $rank['aquamarine-PD'], $rank['garnet-PD'], $max_rank);
TestCheck("true", $result, "Aquamarine - Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Aquamarine - Party Director", $rank['aquamarine-PD'], 20, 27000, 0, $rank['aquamarine-PD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['aquamarine-PD']);
//TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-PD']);
TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-PD']);
TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-PD']);
TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-PD']);
TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-PD']);
TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  2, "", 0, "", $paytype, $rank['aquamarine-PD']);
TestCheck("true", $result, "Aquamarine - Party Director - AddBasicCommRule #7");

// Aquamarine - Senior Party Director //
echo "[ Aquamarine - Senior Party Director:: ]\n";
$rank['aquamarine-SPD'] = 170;
$result = AddRankRule($system['id'], "Aquamarine - Senior Party Director", $rank['aquamarine-SPD'], 12,    985, 0, $rank['aquamarine-SPD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Aquamarine - Senior Party Director", $rank['aquamarine-SPD'], 21,    22, 0, $rank['aquamarine-SPD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Aquamarine - Senior Party Director", $rank['aquamarine-SPD'], 16,     2, 0, $rank['aquamarine-SPD'], $rank['sapphire-EPL'], $max_rank);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Aquamarine - Senior Party Director", $rank['aquamarine-SPD'], 16,     1, 0, $rank['aquamarine-SPD'], $rank['garnet-SPD'], $max_rank);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Aquamarine - Senior Party Director", $rank['aquamarine-SPD'], 20, 35000, 0, $rank['aquamarine-SPD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['aquamarine-SPD']);
//TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-SPD']);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-SPD']);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-SPD']);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-SPD']);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-SPD']);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  2, "", 0, "", $paytype, $rank['aquamarine-SPD']);
TestCheck("true", $result, "Aquamarine - Senior Party Director - AddBasicCommRule #7");

// Aquamarine - Executive Party Director //
echo "[ Aquamarine - Executive Party Director:: ]\n";
$rank['aquamarine-EPD'] = 180;
$result = AddRankRule($system['id'], "Aquamarine - Executive Party Director", $rank['aquamarine-EPD'], 12,    985, 0, $rank['aquamarine-EPD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Aquamarine - Executive Party Director", $rank['aquamarine-EPD'], 21,    22, 0, $rank['aquamarine-EPD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Aquamarine - Executive Party Director", $rank['aquamarine-EPD'], 16,     3, 0, $rank['aquamarine-EPD'], $rank['sapphire-EPL'], $max_rank);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Aquamarine - Executive Party Director", $rank['aquamarine-EPD'], 16,     2, 0, $rank['aquamarine-EPD'], $rank['garnet-SPD'], $max_rank);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Aquamarine - Executive Party Director", $rank['aquamarine-EPD'], 20, 40000, 0, $rank['aquamarine-EPD'],  0,  0);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['aquamarine-EPD']);
//TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-EPD']);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-EPD']);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-EPD']);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-EPD']);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['aquamarine-EPD']);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  3, "", 0, "", $paytype, $rank['aquamarine-EPD']);
TestCheck("true", $result, "Aquamarine - Executive Party Director - AddBasicCommRule #7");

// Opal - Party Director //
echo "[ Opal - Party Director:: ]\n";
$rank['opal-PD'] = 190;
$result = AddRankRule($system['id'], "Opal - Party Director", $rank['opal-PD'], 12,    1500, 0, $rank['opal-PD'],  0,  0);
TestCheck("true", $result, "Opal - Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Opal - Party Director", $rank['opal-PD'], 21,     24, 0, $rank['opal-PD'],  0,  0);
TestCheck("true", $result, "Opal - Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Opal - Party Director", $rank['opal-PD'], 16,      2, 0, $rank['opal-PD'], $rank['garnet-SPD'], $max_rank);
TestCheck("true", $result, "Opal - Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Opal - Party Director", $rank['opal-PD'], 16,      1, 0, $rank['opal-PD'], $rank['aquamarine-PD'], $max_rank);
TestCheck("true", $result, "Opal - Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Opal - Party Director", $rank['opal-PD'], 20,  50000, 0, $rank['opal-PD'],  0,  0);
TestCheck("true", $result, "Opal - Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['opal-PD']);
//TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-PD']);
TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-PD']);
TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-PD']);
TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-PD']);
TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-PD']);
TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  3, "", 0, "", $paytype, $rank['opal-PD']);
TestCheck("true", $result, "Opal - Party Director - AddBasicCommRule #7");

// Opal - Senior Party Director //
echo "[ Opal - Senior Party Director:: ]\n";
$rank['opal-SPD'] = 200;
$result = AddRankRule($system['id'], "Opal - Senior Party Director", $rank['opal-SPD'], 12,    1500, 0, $rank['opal-SPD'],  0,  0);
TestCheck("true", $result, "Opal - Senior Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Opal - Senior Party Director", $rank['opal-SPD'], 21,     26, 0, $rank['opal-SPD'],  0,  0);
TestCheck("true", $result, "Opal - Senior Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Opal - Senior Party Director", $rank['opal-SPD'], 16,      3, 0, $rank['opal-SPD'], $rank['garnet-SPD'], $max_rank);
TestCheck("true", $result, "Opal - Senior Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Opal - Senior Party Director", $rank['opal-SPD'], 16,      1, 0, $rank['opal-SPD'], $rank['aquamarine-PD'], $max_rank);
TestCheck("true", $result, "Opal - Senior Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Opal - Senior Party Director", $rank['opal-SPD'], 20,  65000, 0, $rank['opal-SPD'],  0,  0);
TestCheck("true", $result, "Opal - Senior Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['opal-SPD']);
//TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-SPD']);
TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-SPD']);
TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-SPD']);
TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-SPD']);
TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-SPD']);
TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  3, "", 0, "", $paytype, $rank['opal-SPD']);
TestCheck("true", $result, "Opal - Senior Party Director - AddBasicCommRule #7");

// Opal - Executive Party Director //
echo "[ Opal - Executive Party Director:: ]\n";
$rank['opal-EPD'] = 210;
$result = AddRankRule($system['id'], "Opal - Executive Party Director", $rank['opal-EPD'], 12,    1500, 0, $rank['opal-EPD'],  0,  0);
TestCheck("true", $result, "Opal - Executive Party Director - AddRankRule #1");
$result = AddRankRule($system['id'], "Opal - Executive Party Director", $rank['opal-EPD'], 21,     26, 0, $rank['opal-EPD'],  0,  0);
TestCheck("true", $result, "Opal - Executive Party Director - AddRankRule #2");
$result = AddRankRule($system['id'], "Opal - Executive Party Director", $rank['opal-EPD'], 16,      1, 0, $rank['opal-EPD'], $rank['aquamarine-EPD'], $max_rank);
TestCheck("true", $result, "Opal - Executive Party Director - AddRankRule #3");
$result = AddRankRule($system['id'], "Opal - Executive Party Director", $rank['opal-EPD'], 16,      1, 0, $rank['opal-EPD'], $rank['aquamarine-PD'], $max_rank);
TestCheck("true", $result, "Opal - Executive Party Director - AddRankRule #4");
$result = AddRankRule($system['id'], "Opal - Executive Party Director", $rank['opal-EPD'], 20,  80000, 0, $rank['opal-EPD'],  0,  0);
TestCheck("true", $result, "Opal - Executive Party Director - AddRankRule #5");
//$result = AddBasicCommRule($system['id'], 0, 12,  350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, $rank['opal-EPD']);
//TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #1");
$result = AddBasicCommRule($system['id'], 0, 12, 1000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-EPD']);
TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #2");
$result = AddBasicCommRule($system['id'], 0, 12, 1500, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-EPD']);
TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #3");
$result = AddBasicCommRule($system['id'], 0, 12, 2000, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-EPD']);
TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #4");
$result = AddBasicCommRule($system['id'], 1, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-EPD']);
TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #5");
$result = AddBasicCommRule($system['id'], 2, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-EPD']);
TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #6");
$result = AddBasicCommRule($system['id'], 3, 12, 0, 999999999, $invtype, $event,  5, "", 0, "", $paytype, $rank['opal-EPD']);
TestCheck("true", $result, "Opal - Executive Party Director - AddBasicCommRule #7");

//AddRankRule($systemid, $label, $rank, $qualifytype, $threshold, $achvbonus, $rulegroup, $sumrankstart, $sumrankend);

//AddBasicCommRule($systemid, $generation, $qualifytype, $startthreshold, $endthreshold, $invtype, $event, $percent, $modulus, $paylimit, $pvoverride, $paytype, $rank)

//AddBasicCommRule($system['id'], 0, 1, 350, 999999999, $invtype, $event, 20, "", 0, "", $paytype, 0);
