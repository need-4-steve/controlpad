#!/usr/bin/php
<?php

$_SERVER["SCRIPT_NAME"] = "/maverick-live/newplan.php";

include "../../tests/includes/inc.ce-comm.php";
include "../../tests/includes/inc.systemusers.php";
include "../../tests/includes/inc.systems.php";
include "../../tests/includes/inc.rankrules.php";
include "../../tests/includes/inc.commrules.php";
include "../../tests/includes/inc.basiccommrules.php";
include "../../tests/includes/inc.faststart.php";
include "../../tests/includes/inc.tests.php";

$starttime = time();

// Main Account //
$sysmain_email = "master@commissions.com";
$sysmain_pass = "my.co#5YvhgW34&&.gf:gf*()23oties.com";

// Generate Random Email //
$sysuser_email = "wanderson@controlpad.com";
$sysuser_password = "easypass";
$_SESSION['authemail'] = $sysuser_email;
$_SESSION['authpass'] = $sysuser_password;

$systemid = 1;
$receiptid = 718;
$userid = 223;
$qty = 1;
$result = AddReceiptBulk($systemid, $receiptid, $userid, $qty, $wholesaleprice, $wholesaledate, $invtype, $commissionable, $metadata)


$result = UpdateReceiptBulk($systemid, $receiptid, $userid, $qty, $retailprice, $retaildate, $metadata)

/*

[

	{

		"authemail": "",

		"apikey": "",

		"systemid": "",

		"qty": 1,

		"commissionable": "true",

		"invtype": 2,

		"metadata": "OQASFT-210",

		"producttype": 1,

		"command": "updatereceiptbulk",

		"retailprice": 48,

		"retaildate": "2019-01-17 20:51:11",

		"receiptid": 718,

		"userid": 223

	},

	{

		"command": "addreceiptbulk",

		"authemail": "",

		"apikey": "",

		"systemid": "",

		"qty": 1,

		"metadata": "OQASFT-210",

		"receiptid": -1,

		"userid": 223,

		"retaildate": "2019-01-17 20:51:11",

		"retailprice": -35,

		"wholesaledate": "2019-01-17 20:51:11",

		"wholesaleprice": -35,

		"commissionable": "true",

		"invtype": 2

	}

]
*/

