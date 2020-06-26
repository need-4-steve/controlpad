<?php

/////////////////////////////
// Query rank rules Missed //
/////////////////////////////
function QueryRankRulesMissed($systemid)
{
	$fields[] = "systemid";
	$values["systemid"] = $systemid;
	$headers = BuildHeader(CLIENT, "queryrankrulemissed", $fields, "", $values);
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

/////////////////////////////
// Query rank rules Missed //
/////////////////////////////
function MyRankRulesMissed($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "myrankrulesmissed", $fields, "", $values);
	$json = PostURL($headers);
	if (HandleResponse($json, SUCCESS_NOTHING) == true)
	{
		return $json["rankrulesmissed"][0];
	}
	else
	{
		return "false";
	}
}

/////////////////////////////
// Query rank rules Missed //
/////////////////////////////
function DownRankRulesMissed($systemid, $userid)
{
	$fields[] = "systemid";
	$fields[] = "userid";
	$values["systemid"] = $systemid;
	$values["userid"] = $userid;
	$headers = BuildHeader(AFFILIATE, "downrankrulesmissed", $fields, "", $values);
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

/////////////////////////////////////
// Verify all the rankrules missed //
/////////////////////////////////////
function VerifyRankRulesMissed($results, $filename)
{
	$jsonstr = file_get_contents($filename);
	$json = json_decode($jsonstr, true);

	foreach ($json as $record)
	{
		$actualrecord = LookupRankRulesMissed($results, $record['userid']);
		if (($actualrecord['rank'] != $record['rank']) || 
			($actualrecord['qualifytype'] != $record['qualifytype']) ||
			($actualrecord['qualifythreshold'] != $record['qualifythreshold']) ||
			($actualrecord['actualvalue'] != $record['actualvalue']) ||
			($actualrecord['diff'] != $record['diff']))
		{
			echo "VerifyRankRulesMissed() - record: userid(".$record['userid'].") - VALUES DO NOT MATCH\n";
			return "false";
		}
	}

	return "true";
}

////////////////////////////////////////////
// Lookup the user commission in the json //
////////////////////////////////////////////
function LookupRankRulesMissed($json, $userid)
{
	foreach ($json as $record)
	{
		if ($record["userid"] == $userid)
			return $record;
	}

	echo "LookupRankRulesMissed() - record: userid(".$userid."), userid(".$userid.") not found\n";
	return "None";
}
?>