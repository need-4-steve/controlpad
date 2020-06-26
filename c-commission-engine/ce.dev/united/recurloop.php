<?php

include_once("includes/inc.global.php");

function FindRecurrsion($system_id)
{
	$main_parent_id = GetDB("SELECT user_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id='0'");

	$query = "SELECT id, system_id, user_id, parent_id, usertype FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."'";
	$query .= " AND parent_id IN (SELECT user_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."' AND ";
	$query .= " user_id IN (SELECT parent_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."') AND ";
	$query .= " parent_id IN (SELECT user_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."')) ";
	$query .= " AND user_id IN (SELECT parent_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."' AND ";
	$query .= " parent_id IN (SELECT user_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."') AND ";
	$query .= " user_id IN (SELECT parent_id FROM ce_users WHERE system_id='".$system_id."' AND parent_id!='".$main_parent_id."')) ORDER BY user_id::int4";

	$result = pg_query($query) or die('Query failed: ' . pg_last_error());	
	while ($line = pg_fetch_array($result))
	{
		echo "<tr><td>".$line['id']."</td><td>".$line['system_id']."</td><td>".$line['user_id']."</td><td>".$line['parent_id']."</td><td>".$line['usertype']."</td></tr>";
	}

	echo "<tr><td>&nbsp;</td></tr>";
}

echo "<table border=1>";
echo "<tr><td align=center><b>id</b><td align=center><b>system_id</b></td><td align=center><b>user_id</b></td><td align=center><b>parent_id</b></td><td align=center><b>Usertype</b></td></tr>";
FindRecurrsion(100078);
FindRecurrsion(98477);
FindRecurrsion(44694);
FindRecurrsion(35035);
FindRecurrsion(93684);
FindRecurrsion(49034);
FindRecurrsion(43663);
FindRecurrsion(29699);
FindRecurrsion(22727);
FindRecurrsion(2284);
FindRecurrsion(44821);
FindRecurrsion(117438);
FindRecurrsion(91116);
FindRecurrsion(94339);
FindRecurrsion(15272);
FindRecurrsion(7200);
FindRecurrsion(33042);
FindRecurrsion(64800);
FindRecurrsion(789);
FindRecurrsion(62685);
FindRecurrsion(89057);
FindRecurrsion(61083);
FindRecurrsion(15927);
FindRecurrsion(68143);
FindRecurrsion(34802);
FindRecurrsion(113893);
FindRecurrsion(97751);
FindRecurrsion(119271);
FindRecurrsion(99930);
FindRecurrsion(119850);
FindRecurrsion(34868);
FindRecurrsion(64998);
FindRecurrsion(94045);
FindRecurrsion(113415);
FindRecurrsion(78982);
FindRecurrsion(95671);
FindRecurrsion(61777);
FindRecurrsion(37052);
FindRecurrsion(67202);
FindRecurrsion(92500);
FindRecurrsion(20420);
FindRecurrsion(9658);
FindRecurrsion(70478);
FindRecurrsion(116205);

echo "</table>";

?>