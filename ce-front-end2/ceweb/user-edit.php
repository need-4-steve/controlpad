<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';

SystemSelectedCheck();
$basetype = "user";
$pagvals = PagValidate("userid", "asc");
$json = BuildQueryPage(CLIENT, $basetype, "userid", $pagvals, $fields);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Edit Users</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build search parameters //
$size["override"] = "NULL";
$size["userid"] = 3;
$size["usertype"] = "selectusertype";
$size["parentid"] = 3;
$size["sponsorid"] = 3;
$size["advisorid"] = 3;
$size["signupdate"] = "NULL";
$size["firstname"] = 8;
$size["lastname"] = 8;
$size["email"] = 8;
$size["cell"] = 8;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["override"] = "Account";
$sort["userid"] = "User ID";
$sort["usertype"] = "User Type";
$sort["parentid"] = "Parent ID";
$sort["sponsorid"] = "Sponsor ID";
$sort["advisorid"] = "Advisor ID";
$sort["signupdate"] = "Signup Date";
$sort["firstname"] = "Firstname";
$sort["lastname"] = "Lastname";
$sort["email"] = "Email";
$sort["cell"] = "Cell";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['user'] as $user)
{
	// Predefine the edit link //
	echo "<tr>";

	// Disable/Enable //
	if ($user['disabled'] == "t")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=enable'>";
		echo "<input type=hidden name='userid' value='".$user['userid']."'>";
		echo "<td align=center><input type=submit value='Enable'></td>";
		echo "</form>";
	}
	else if ($user['disabled'] == "f")
	{
		echo "<form method=POST action='".$basetype."-edit.php?direction=disable'>";
		echo "<input type=hidden name='userid' value='".$user['userid']."'>";
		echo "<td align=center><input type=submit value='Disable'></td>";
		echo "</form>";
	}

	echo "<form method=POST action='user-index.php'>";
	echo "<input type=hidden name='direction' value='override'>";
	echo "<input type=hidden name='userid' value='".$user['userid']."'>";
	echo "<input type=hidden name='email' value='".$user['email']."'>";
	echo "<td align=center><input type='submit' value='Account'></td>";
	echo "</form>";

	echo "<td align=center>".$user['userid']."</td>";
	echo "<td align=center>".DispUserType($user['usertype'])."</td>";
	echo "<td align=center>".$user['parentid']."</td>";
	echo "<td align=center>".$user['sponsorid']."</td>";
	echo "<td align=center>".$user['advisorid']."</td>";
	echo "<td align=center>".DispDate($user['signupdate'])."</td>";
	echo "<td align=center>".$user['firstname']."</td>";
	echo "<td align=center>".$user['lastname']."</td>";
	echo "<td align=center>".$user['email']."</td>";
	echo "<td align=center>".$user['cell']."</td>";
	echo "<td>".DispTimestamp($user['createdat'])."</td>";
	echo "<td>".DispTimestamp($user['updatedat'])."</td>";
	
	echo "<form method=POST action='".$basetype."-add.php'>";
	echo "<input type=hidden name='edit' value='true'>";
	echo "<input type=hidden name='userid' value='".$user['userid']."'>";
	echo "<td align=center><input type='submit' value='Edit'></td>";
	echo "</form>";

	echo "<form method=POST action='bankaccount-add.php'>";
	echo "<input type=hidden name='edit' value='true'>";
	echo "<input type=hidden name='userid' value='".$user['userid']."'>";
	echo "<td align=center><input type='submit' value='Add Bank Account'></td>";
	echo "</form>";

	echo "</tr>";
}

echo "</table>";

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>