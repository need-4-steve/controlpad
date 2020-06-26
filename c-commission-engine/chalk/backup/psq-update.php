#!/usr/bin/php
<?php

include "../tests/includes/inc.ce-comm.php";
include "../tests/includes/inc.commrules.php";
include "../tests/includes/inc.tests.php";

// Generate Random Email //
$sysuser_email = "info@chalkcouture.com";
$sysuser_password = "Aasdfasdf1";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

// Wholesale //
$systemid = 1;
$event = 1;
$invtype = 1;
$paytype = 1;
$result = EditCommRule($systemid, 85, 2, -1, false, 3, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #1");
$result = EditCommRule($systemid, 87, 3, -1, false, 3, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #2");
$result = EditCommRule($systemid, 90, 4, -1, false, 4, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #3");
$result = EditCommRule($systemid, 93, 5, -1, false, 4, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #4");
$result = EditCommRule($systemid, 97, 6, -1, false, 4, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #5");
$result = EditCommRule($systemid, 101, 7, -1, false, 5, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #6");
$result = EditCommRule($systemid, 105, 8, -1, false, 5, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #7");
$result = EditCommRule($systemid, 109, 9, -1, false, 5, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #8");

// Switch to affiliate on corporate //
$invtype = 5;
$result = EditCommRule($systemid, 113, 2, -1, false, 3, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #1");
$result = EditCommRule($systemid, 115, 3, -1, false, 3, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #2");
$result = EditCommRule($systemid, 118, 4, -1, false, 4, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #3");
$result = EditCommRule($systemid, 121, 5, -1, false, 4, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #4");
$result = EditCommRule($systemid, 125, 6, -1, false, 4, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #5");
$result = EditCommRule($systemid, 129, 7, -1, false, 5, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #6");
$result = EditCommRule($systemid, 133, 8, -1, false, 5, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #7");
$result = EditCommRule($systemid, 137, 9, -1, false, 5, $invtype, $event, $paytype);
TestCheck("true", $result, "EditCommRule #8");
?>