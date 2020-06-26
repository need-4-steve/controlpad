<?php

include_once("includes/inc.global.php");

function DisplayUser($user_id, $batch_id)
{
	$user['id'] = $user_id;
/*	$user['t_bought'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND batch_id='".$batch_id."' AND ledger_type='3' AND user_id='".$user_id."'");
	$user['t_played'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND batch_id='".$batch_id."' AND ledger_type='4' AND user_id='".$user_id."'");
	$user['cm_bought'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND batch_id='".$batch_id."' AND ledger_type='5' AND user_id='".$user_id."'");
	$user['cm_played'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND batch_id='".$batch_id."' AND ledger_type='6' AND user_id='".$user_id."'");

	$user['t_play_gen1'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=1 AND batch_id='".$batch_id."' AND ledger_type='6' AND user_id='".$user_id."'");
	$user['t_play_gen2'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=2 AND batch_id='".$batch_id."' AND ledger_type='6' AND user_id='".$user_id."'");
	$user['t_play_gen3'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=3 AND batch_id='".$batch_id."' AND ledger_type='6' AND user_id='".$user_id."'");
	$user['t_play_gen4'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=4 AND batch_id='".$batch_id."' AND ledger_type='6' AND user_id='".$user_id."'");
*/
	$retval = GenerationTotals("t_bought", $user_id, $batch_id, 3);
	$user = array_merge($user, $retval);
	$retval = GenerationTotals("t_played", $user_id, $batch_id, 4);
	$user = array_merge($user, $retval);
	$retval = GenerationTotals("cm_bought", $user_id, $batch_id, 5);
	$user = array_merge($user, $retval);
	$retval = GenerationTotals("cm_played", $user_id, $batch_id, 6);
	$user = array_merge($user, $retval);

/*
	$user['t_play_gen1'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=1");
	$user['t_play_gen2'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=2");
	$user['t_play_gen3'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=3");
	$user['t_play_gen4'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=4");
*/
	return $user;
}

function GenerationTotals($base, $user_id, $batch_id, $ledger_type)
{
	$user[$base] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND batch_id='".$batch_id."' AND ledger_type='".$ledger_type."' AND user_id='".$user_id."'");

	if ($ledger_type == 3)
	{
		$user[$base.'_gen1'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=1");
		$user[$base.'_gen2'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=2");
		$user[$base.'_gen3'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=3");
		$user[$base.'_gen4'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=4");
		$user[$base.'_gen5'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=5");
		$user[$base.'_gen6'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=6");
		$user[$base.'_gen7'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=7");
		$user[$base.'_gen8'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=8");
		$user[$base.'_gen9'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=9");
	}
	else if ($ledger_type == 4)
	{
		$user[$base.'_gen1'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=1");
		$user[$base.'_gen2'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=2");
		$user[$base.'_gen3'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=3");
		$user[$base.'_gen4'] = GetDB("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND batch_id='".$batch_id."' AND user_id='".$user_id."' AND generation=4");
	}
	else if (($ledger_type == 5) || ($ledger_type == 6))
	{
		$user[$base.'_gen1'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=1 AND batch_id='".$batch_id."' AND ledger_type='".$ledger_type."' AND user_id='".$user_id."'");
		$user[$base.'_gen2'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=2 AND batch_id='".$batch_id."' AND ledger_type='".$ledger_type."' AND user_id='".$user_id."'");
		$user[$base.'_gen3'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=3 AND batch_id='".$batch_id."' AND ledger_type='".$ledger_type."' AND user_id='".$user_id."'");
		$user[$base.'_gen4'] = GetDB("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND generation=4 AND batch_id='".$batch_id."' AND ledger_type='".$ledger_type."' AND user_id='".$user_id."'");
	}

	return $user;
}


function ShowGeneration($array, $base, $genlimit)
{
	for ($count=1; $count <= $genlimit; $count++)
	{
		echo "<tr><td align=right>Gen ".$count.":</td>";

		for ($index=0; $index <= 5; $index++)
		{
			echo "<td align=right>$".$array[$index][$base."gen".$count]."</td>";
		}
		echo "</tr>";
	}
}

$batch_id = 2;

$user[0] = DisplayUser(3, $batch_id);
$user[1] = DisplayUser(40, $batch_id);
$user[2] = DisplayUser(343, $batch_id);
$user[3] = DisplayUser(189, $batch_id);
//$user[4] = DisplayUser(111, $batch_id);
//$user[5] = DisplayUser(57, $batch_id);
//$user[5] = DisplayUser(25537, $batch_id);
$user[4] = DisplayUser(267444, $batch_id);
$user[5] = DisplayUser(203915, $batch_id);

echo "<table>";
echo "<tr><td align=right><b>User ID:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=center><b>".$user[$index]['id']."</b></td>";
}

echo "<tr><td align=right><b>Tokens Purchased:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['t_bought']."</td>";
}
ShowGeneration($user, "t_bought_", 9);

echo "<tr><td>&nbsp;</td></tr>"; // Spacer //

echo "<tr><td align=right><b>Tokens Played:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['t_played']."</td>";
}
ShowGeneration($user, "t_played_", 4);

echo "<tr><td>&nbsp;</td></tr>"; // Spacer //

echo "<tr><td align=right><b>CheckMatch Purchased:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['cm_bought']."</td>";
}
ShowGeneration($user, "cm_bought_", 4);

echo "<tr><td>&nbsp;</td></tr>"; // Spacer //

echo "<tr><td align=right><b>CheckMatch Played:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['cm_played']."</td>";
}
ShowGeneration($user, "cm_played_", 4);

echo "<tr><td>&nbsp;</td></tr>"; // Spacer //

/*
echo "<tr><td align=right><b>Tokens Played Gen 1:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen1']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 2:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen2']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 3:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen3']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 4:</b></td>";
for ($index=0; $index <= 5; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen4']."</td>";
}
*/

echo "</table>";

?>