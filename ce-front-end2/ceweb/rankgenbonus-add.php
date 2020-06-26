<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.date.php';

SystemSelectedCheck();

// Build a list of input fields //
$fields[] = "id";
$fields[] = "myrank";
$fields[] = "userrank";
$fields[] = "generation";
$fields[] = "bonus";

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "rankgenbonusrule", $fields);

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Add Rank Gen Bonus</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if ($_GET['edit'] == "true")
{
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<input type=hidden name='id' value='".$values['id']."'>";
}
else
	echo "<input type=hidden name='direction' value='add'>";

// My Rank //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> My Rank</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="My Rank" name="myrank" value="'.$values['my_rank'].'">';
echo '				</div>';
echo '			</div>';

// User Rank //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> User Rank</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="User Rank" name="userrank" value="'.$values['user_rank'].'">';
echo '				</div>';
echo '			</div>';

// Generation //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Generation</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Generation" name="generation" value="'.$values['generation'].'">';
echo '				</div>';
echo '			</div>';

// Bonus //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Bonus </label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Bonus" name="bonus" value="'.$values['bonus'].'">';
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
