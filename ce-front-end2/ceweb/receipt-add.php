<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.convert.php';
include 'includes/inc.date.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

if (empty($_POST['wholesalecheck']))
	$_POST['wholesaledate'] = "";
if (empty($_POST['retailcheck']))
	$_POST['retaildate'] = "";

// Build a list of input fields //
$fields[] = "id";
$fields[] = "receiptid";
$fields[] = "userid";
$fields[] = "wholesaleprice";
$fields[] = "retailprice";
$fields[] = "retaildate";
$fields[] = "wholesaledate";
$fields[] = "invtype";
$fields[] = "commissionable";
$fields[] = "metadataonadd";
$fields[] = "metadataonupdate";

// Handle checkbox correction // 
$_POST['commissionable'] = ConvCheckbox($_POST['commissionable']);

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "receipt", $fields);
$values['retaildate'] = FixDate($values['retaildate']);
$values['wholesaledate'] = FixDate($values['wholesaledate']);

echo '<div class="col-md-16 col-xs-12">';

if ($_POST['edit'] == "true")
	echo '	<h2>Edit Receipt</h2>';
else
	echo '	<h2>Add Receipt</h2>';

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

// ReceiptID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> ReceiptID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="ReceiptID" name="receiptid" value="'.$values['receiptid'].'">';
echo '				</div>';
echo '			</div>';

// UserID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> UserID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="UserID" name="userid" value="'.$values['userid'].'">';
echo '				</div>';
echo '			</div>';

// Wholesale Price //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Wholesale Price</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Wholesale Price" name="wholesaleprice" value="'.$values['wholesaleprice'].'">';
echo '				</div>';
echo '			</div>';

// Retail Price //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Retail Price</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Retail Price" name="retailprice" value="'.$values['retailprice'].'">';
echo '				</div>';
echo '			</div>';

// Wholesale Date //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Wholesale Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '<table><tr><td><input type=checkbox  name="wholesalecheck"></td><td>';
ChooseDate("wholesaledate", $values['wholesaledate']);
echo "</td></tr></table>";
echo '				</div>';
echo '			</div>';

// RetailDate //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Retail Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '<table><tr><td><input type=checkbox name="retailcheck"></td><td>';
ChooseDate("retaildate", $values['retaildate']);
echo "</td></tr></table>";
echo '				</div>';
echo '			</div>';

// Select inventory type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Inventory Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectInvType("invtype", $values['invtype']);
echo '				</div>';
echo '			</div>';

// Commissionable //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Commissionable</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="checkbox" class="form-control" placeholder="Commissionable" name="commissionable">';
echo '				</div>';
echo '			</div>';

// Metadata On Add //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Metadata On Add</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="edit" class="form-control" placeholder="Metadata On Add" name="metadataonadd" value="'.$values['metadataonadd'].'">';
echo '				</div>';
echo '			</div>';

// Metadata On Update //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Metadata On Update</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="edit" class="form-control" placeholder="Metadata On Update" name="metadataonupdate" value="'.$values['metadataonupdate'].'">';
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