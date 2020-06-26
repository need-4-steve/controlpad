<?php

//////////////////////////////////
// Do a check and add up totals //
//////////////////////////////////
function TestCheck($equalfalse, $result, $testname)
{
	global $g_failcount;
	global $g_passcount;

	if ($equalfalse == "true")
	{
		if ($result == "false")
		{
			$g_failcount++;
			echo "[\x1b[31m failed \x1b[0m] ".$testname."\n";
			return false;
		}
		else
		{
			$g_passcount++;
			echo "[\x1b[32m passed \x1b[0m] ".$testname."\n";
			return true;
		}
	}
	else
	{
		if ($result != "false")
		{
			$g_failcount++;
			echo "[\x1b[31m failed \x1b[0m] ".$testname."\n";
			return true;
		}
		else
		{
			$g_passcount++;
			echo "[\x1b[32m passed \x1b[0m] ".$testname."\n";
			return false;
		}
	}
}

////////////////
// Force True //
////////////////
function TestTrue($testname)
{
	global $g_passcount;

	$g_passcount++;
	echo "[\x1b[32m passed \x1b[0m] ".$testname."\n";
}

/////////////////
// Force False //
/////////////////
function TestFalse($testname)
{
	global $g_failcount;

	$g_failcount++;
	echo "[\x1b[31m failed \x1b[0m] ".$testname."\n";
}

?>