#!/usr/bin/php
<?php

if (empty($argv[1]))
{
	echo "run-affil-sims.php: A base needs to be defined. Example: ./run-affil-sims.php test-live\n";
	return;
}

$base = $argv[1];
$_SERVER["SCRIPT_NAME"] = "/".$base."/run-affil-sims.php";

include "/var/www/c-commission-engine/tests/includes/inc.ce-comm.php";
include "/var/www/c-commission-engine/tests/includes/inc.settings.php";
include "/var/www/c-commission-engine/tests/includes/inc.commissions.php";

if (empty($g_pghost))
{
	echo "g_pghost needs to be defined in inc.settings.php\n";
}
else if ($argv[1] == "help")
{
	echo "Example: ./run-affil-sims.php test-live\n";
}
else
{
	///////////////////////////////////////////////////
	// Web interface to finalize live commission run //
	///////////////////////////////////////////////////

	// Current date //
	$curr_month = date("n"); // Current month //
	$curr_year = date("Y"); // Current Year //
	$curr_day = date("d"); // Current day //

	// What was the date for the last batch // 
	$batch = QueryBatchesMore(MASTER, "1", "", "orderby=id&orderdir=desc&limit=1&offset=0");
	$last_batchdate = $batch['batches'][0]['startdate'];
	
	if (empty($last_batchdate))
	{
		// Run only this month //
		$last_batchyear = $curr_year;
		$last_batchmonth = $curr_month;
		$calc_year = $curr_year;
		$calc_month = $curr_month;
	}
	else
	{
		$last_batchyear = strtok($last_batchdate, "-");
		$last_batchmonth = strtok("-");

		// Define starting month //
		if ($last_batchmonth == 12)
		{
			$calc_month = 1;
			$calc_year = $last_batchyear+1;
		}
		else
		{
			$calc_month = $last_batchmonth+1;
			$calc_year = $last_batchyear;
		}
	}

	// Figure out which sim database switch to //
	//echo "g_siminuse = ".$g_siminuse."\n";
	if ($g_siminuse == "sim1")
		$newsim = "sim2";
	else
		$newsim = "sim1";

	// Do actual simdb //
	$basedb = strtok($base, "-");
	$simdb = $basedb."-".$newsim;

	// pg_dump -U pgadmin -h commieng.c0ieugu68fz5.us-west-1.rds.amazonaws.com -d chalk-sim1 > chalk-sim.sql

	// Copy live database to sim //
	$command = "rm /var/spool/ceapi/".$base."-1hour-dump.sql";
	echo $command."\n";
	system($command); // Remove the backup from last hour //

	$command = "rm /tmp/output.sql";
	echo $command."\n";
	system($command); // Remove the backup from last hour //

	//system("pg_dump ".$base." > /var/spool/ceapi/".$base."-1hour-dump.sql");
	if (($g_pghost == "localhost") || ($g_pghost == "127.0.0.1"))
	{
		$command = "pg_dump ".$base." > /var/spool/ceapi/".$base."-1hour-dump.sql";
		echo $command."\n";
		system($command);
		$command = "psql -o /tmp/output.sql -d ".$simdb." -f /var/www/c-commission-engine/2hour/purge.sql";
		echo $command."\n";
		system($command);
		$command = "psql -o /tmp/output.sql -d ".$simdb." -f /var/spool/ceapi/".$base."-1hour-dump.sql";
		system($command);
		echo $command."\n";
	}
	else
	{
		$command = "pg_dump ".$base." -U ".$g_pguser." -h ".$g_pghost." > /var/spool/ceapi/".$base."-1hour-dump.sql";
		echo $command."\n";
		system($command);
		$command = "psql -o /tmp/output.sql -U ".$g_pguser." -h ".$g_pghost." -d ".$simdb." -f /var/www/c-commission-engine/2hour/purge.sql";
		echo $command."\n";
		system($command);
		$command = "psql -o /tmp/output.sql -U ".$g_pguser." -h ".$g_pghost." -d ".$simdb." -f /var/spool/ceapi/".$base."-1hour-dump.sql";
		system($command);
		echo $command."\n";
	}

	// Sim Run all commissions from previous uncalculated months //
	while (1)
	{
		// Define given dates for calculations //
		$max_days = cal_days_in_month(CAL_GREGORIAN, $calc_month, $calc_year);
		$startdate = $calc_year."-".$calc_month."-1";
		$enddate = $calc_year."-".$calc_month."-".$max_days;
		$now = $today = date("Y-m-d H:i:s");

		echo "(".$now.") Running commissions on ".$simdb." (".$startdate." to ".$enddate.")\n";

		///////////////////////////////////////////////////////////////////////
		// Warning!! The system defined 1 and 2 is specific to chalcouture 	 //
		// This was done to finish up ASAP 									 //
		// If it's ever sold to another clients, then this needs to be fixed //
		///////////////////////////////////////////////////////////////////////

		// Run commission on sim database //
		system("/var/www/c-commission-engine/ceapi ".$simdb." commrun 1 ".$startdate." ".$enddate);
		//system("/var/www/c-commission-engine/ceapi ".$simdb." commrun 2 ".$startdate." ".$enddate);

		if (($calc_year == $curr_year) && ($calc_month == $curr_month))
		{
			break;
		}

		$calc_month++;
		if ($calc_month == 13)
		{
			$calc_month = 1;
			$calc_year++;
		}
	}

	// Allow affiliates to see results on website //
	$result = SettingsSet(MASTER, "", "", "sim-inuse", $newsim);
}

?>			