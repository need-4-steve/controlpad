<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.date.php';

SystemSelectedCheck();

// Build a list of input fields //
$fields[] = "userid";
$fields[] = "sponsorid";
$fields[] = "parentid";
$fields[] = "signupdate";
$fields[] = "usertype";
$fields[] = "firstname";
$fields[] = "lastname";
$fields[] = "email";
$fields[] = "cell";
$fields[] = "uplinesponsor";
$fields[] = "address";
$fields[] = "city";
$fields[] = "state";
$fields[] = "zip";

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "user", $fields);
$values['signupdate'] = FixDate($values['signupdate']);

echo '<div class="col-md-16 col-xs-12">';

// Switch heading on edit vs add //
if (($_GET['edit'] == "true") || ($_POST['edit'] == "true"))
{
	echo '<form method="POST" action="user-adminchangepassword.php">';
	echo '<table width="100%"><tr><td><h2>Edit User</h2></td>';
	echo '<td align="right"><input type="submit" value="Change Password"></td></tr></table>';
	echo '<input type="hidden" name="userid" value="'.$values['userid'].'">';
	echo '</form>';
}
else
	echo '	<h2>Add User</h2>';

echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if (($_GET['edit'] == "true") || ($_POST['edit'] == "true"))
{
	echo "<input type=hidden name='edit' value='true'>";
	echo "<input type=hidden name='direction' value='edit'>";
}
else
	echo "<input type=hidden name='direction' value='add'>";

// UserID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="UserID" name="userid" value="'.$values['userid'].'">';
echo '				</div>';
echo '			</div>';

// User Type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserType</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectUserType("usertype", $values['usertype']);
echo '				</div>';
echo '			</div>';

// ParentID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> ParentID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="ParentID" name="parentid" value="'.$values['parentid'].'">';
echo '				</div>';
echo '			</div>';

// SponsorID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">SponsorID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="SponsorID" name="sponsorid" value="'.$values['sponsorid'].'">';
echo '				</div>';
echo '			</div>';

// Signup Date //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Signup Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("signupdate", $values['signupdate']);
echo '				</div>';
echo '			</div>';

// Firstname //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Firstname</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Firstname" name="firstname" value="'.$values['firstname'].'">';
echo '				</div>';
echo '			</div>';

// Lastname //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Lastname</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Lastname" name="lastname" value="'.$values['lastname'].'">';
echo '				</div>';
echo '			</div>';

// Email //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Email</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Email" name="email" value="'.$values['email'].'">';
echo '				</div>';
echo '			</div>';

// Cell //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Cell</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Cell" name="cell" value="'.$values['cell'].'">';
echo '				</div>';
echo '			</div>';

// Sponsor Upline //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Sponsor Upline</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
//echo '					<input type="text" class="form-control" placeholder="Sponsor Upline" name="uplinesponsor" value="'.$values['uplinesponsor'].'">';
echo $values['uplinesponsor'];
echo '				</div>';
echo '			</div>';

// Address //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Address</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Address" name="address" value="'.$values['address'].'">';
echo '				</div>';
echo '			</div>';

// City //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">City</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="City" name="city" value="'.$values['city'].'">';
echo '				</div>';
echo '			</div>';

// State //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">State</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="State" name="state" value="'.$values['state'].'">';
echo '				</div>';
echo '			</div>';

// Zip //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Zip</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Zip" name="zip" value="'.$values['zip'].'">';
echo '				</div>';
echo '			</div>';

// Submit Button //
echo '			<div class="ln_solid"></div>';
echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
echo '				<button type="submit" class="btn btn-success">Submit</button>';
echo '			</div>';

echo '		</div>';
echo '		</form>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>