<?php

include_once("includes/inc.global.php");

$usermax = 5;

function GetVal($query)
{
	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	$line = pg_fetch_array($result, null, PGSQL_ASSOC);
	
	return array_values($line)[0];
}

function DisplayUser($user_id)
{
	$user['id'] = $user_id;
	$user['t_bought'] = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='3' AND user_id='".$user_id."'");
	$user['t_played'] = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='4' AND user_id='".$user_id."'");
	$user['cm_bought'] = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='5' AND user_id='".$user_id."'");
	$user['cm_played'] = GetVal("SELECT SUM(amount) FROM ce_ledger WHERE system_id=1 AND ledger_type='6' AND user_id='".$user_id."'");

	$user['t_play_gen1'] = GetVal("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=1");
	$user['t_play_gen2'] = GetVal("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=2");
	$user['t_play_gen3'] = GetVal("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=3");
	$user['t_play_gen4'] = GetVal("SELECT SUM(amount) FROM ce_breakdown WHERE system_id!=1 AND user_id='".$user_id."' AND generation=4");

	return $user;
}

$user[0] = DisplayUser(139664);
$user[1] = DisplayUser(2340);
$user[2] = DisplayUser(53682);
$user[3] = DisplayUser(2879);
$user[4] = DisplayUser(1236);

echo "<table>";
echo "<tr><td align=right><b>User ID:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=center>".$user[$index]['id']."</td>";
}

echo "<tr><td align=right><b>Tokens Purchased:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['t_bought']."</td>";
}

echo "<tr><td align=right><b>Tokens Played:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['t_played']."</td>";
}

echo "<tr><td align=right><b>CheckMatch Purchased:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['cm_bought']."</td>";
}

echo "<tr><td align=right><b>CheckMatch Played:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['cm_played']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 1:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen1']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 2:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen2']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 3:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen3']."</td>";
}

echo "<tr><td align=right><b>Tokens Played Gen 4:</b></td>";
for ($index=0; $index <= $usermax; $index++)
{
	echo "<td align=right>$".$user[$index]['t_play_gen4']."</td>";
}

echo "</table>";

?>