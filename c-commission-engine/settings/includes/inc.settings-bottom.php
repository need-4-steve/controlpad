<?php

///////////////////////////////////////
// Handle the sim database switching //
///////////////////////////////////////
$result = BasicGetSettingsValue($g_coredomain, $g_masterauthemail, $g_masterapikey, "0", "varname=sim-inuse", "");
$g_siminuse = $result['settings'][0]['varname'];
if ($result['settings'][0]['varname'] == "sim-inuse")
{
	$g_siminuse = $result['settings'][0]['value'];
	if ($g_siminuse == "sim1")
		$g_simdomain = $g_sim1; // Point at the simulations server to run simulations //
	else if ($g_siminuse == "sim2")
		$g_simdomain = $g_sim2; // Point at the simulations server to run simulations //
	else if ($g_siminuse == "sim3")
		$g_simdomain = $g_sim3; // Point at the simulations server to run simulations //
}
else
{
	echo "settings: Error with variable name<br>\n";
	Pre($result);
}

unset($result);

?>