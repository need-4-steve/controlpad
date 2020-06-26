#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/chalk-live/addrankgenbonusrules.php";

include "../tests/includes/inc.ce-comm.php";
include "../tests/includes/inc.rankgenbonusrules.php";
include "../tests/includes/inc.tests.php";

$starttime = time();

// Generate Random Email //
$sysuser_email = "info@chalkcouture.com";
$sysuser_password = "Aasdfasdf1";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

$chalk_system["id"] = 1;

//////////////////////
// RankGenBonusRule //
//////////////////////
echo "[ Chalk RankGenBonusRule:: ]\n";
$rankgenbonus = AddRankGenBonusRule($chalk_system["id"], "7", "6", "1", "1000");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule #1");

$rankgenbonus = AddRankGenBonusRule($chalk_system["id"], "8", "6", "1", "1500");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule #2");

$rankgenbonus = AddRankGenBonusRule($chalk_system["id"], "8", "6", "2", "600");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule #3");

$rankgenbonus = AddRankGenBonusRule($chalk_system["id"], "9", "6", "1", "2000");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule #4");

$rankgenbonus = AddRankGenBonusRule($chalk_system["id"], "9", "6", "2", "1000");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule #5");

$rankgenbonus = AddRankGenBonusRule($chalk_system["id"], "9", "6", "3", "400");
TestCheck("true", $rankgenbonus, "AddRankGenBonusRule #6");
