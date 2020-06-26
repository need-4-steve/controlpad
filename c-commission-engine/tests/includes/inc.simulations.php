<?php

///////////////////////////////
// Seed simulation with data //
///////////////////////////////
function SimSeedData($sim, $systemid, $seedoption, $seedtype, $usersmax, $receiptsmax, $minprice, $maxprice, $startdate, $enddate)
{
	// copyseedoption needs to be 1-4 //
	// #define COPY_ONLY_USERS			1 // Seed receipts //
	// #define COPY_ONLY_RECEIPTS		2 // Seed users //
	// #define COPY_USERS_RECEIPTS		3 // Copy both. Seed neither //
	// #define SEED_BOTH				4 // Seed both receipts and users //
	// #define COPY_SEED_NONE			5 // Neither copy or seed /

	// seedtype //
	// SIM_SEED_WIDE					1
	// SIM_SEED_DEEP					2

	$fields[] = "sim";
	$fields[] = "systemid";
	$fields[] = "copyseedoption";
	$fields[] = "seedtype";
	$fields[] = "usersmax";
	$fields[] = "receiptsmax";
	$fields[] = "minprice";
	$fields[] = "maxprice";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["sim"] = $sim;
	$values["systemid"] = $systemid;
	$values["copyseedoption"] = $seedoption;
	$values["seedtype"] = $seedtype;
	$values["usersmax"] = $usersmax;
	$values["receiptsmax"] = $receiptsmax; // Group sales //
	$values["minprice"] = $minprice;
	$values["maxprice"] = $maxprice;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "copyseedsim", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return "true";
	}
	else
	{
		return "false";
	}
}

//////////////////////
// Run a simulation //
//////////////////////
function SimRun($sim, $systemid, $startdate, $enddate)
{	
	$fields[] = "sim";
	$fields[] = "systemid";
	$fields[] = "startdate";
	$fields[] = "enddate";
	$values["sim"] = $sim;
	$values["systemid"] = $systemid;
	$values["startdate"] = $startdate;
	$values["enddate"] = $enddate;
	$headers = BuildHeader(CLIENT, "runsim", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json;
	}
	else
	{
		return "false";
	}
}