<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$json = BuildAndPOST(AFFILIATE, "myupline", $fields);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Upline</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table cellspacing="0" cellpadding="0">
<?php

// Loop through each parent //
$index = 0;
foreach ($json['upline'] as $value)
{
	echo "<tr><td>";
	echo '<svg height="100" width="400">';
	if ($index == 0) // Handle first differently //
	{
		echo '<line x1="50" y1="50" x2="50" y2="200" style="stroke:rgb(55,68,109);stroke-width:4" />';
		echo '<circle cx="50" cy="50" r="12" stroke="#1E2744" stroke-width="3" fill="#6D7693" />';
	}
	else
	{
		echo '<line x1="50" y1="0" x2="50" y2="200" style="stroke:rgb(55,68,109);stroke-width:4" />';
		echo '<circle cx="50" cy="50" r="10" stroke="#1E2744" stroke-width="3" fill="#6D7693" />';
	}
	echo '<text x="70" y="56" fill="blue" font-size="18">'.$value['firstname']." " .$value['lastname'].' ('.$value['userid'].')</text>';
	echo '</svg>';
	echo '</td></tr>';
	$index++;
}

// You //
echo "<tr><td>";
echo '<svg height="100" width="400">';
echo '<line x1="50" y1="0" x2="50" y2="50" style="stroke:rgb(55,68,109);stroke-width:4" />';
echo '<circle cx="50" cy="50" r="10" stroke="#1E2744" stroke-width="3" fill="#6D7693" />';
echo '<text x="70" y="56" fill="blue" font-size="18">You ('.$_SESSION['user_id'].')</text>';
echo '</svg>';
echo '</td></tr>';

echo "</table>";

include 'includes/inc.footer.php';

?>