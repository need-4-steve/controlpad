<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(AFFILIATE, "myrankgenbonus", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Couturier Bonuses</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 3;
$size["batchid"] = 3;
$size["userid"] = 3;
$size["amount"] = 3;
$size["eventdate"] = "NULL";
$size["myrank"] = 3;
$size["userrank"] = 3;
$size["generation"] = 3;
$size["userdata"] = 3;
$size["createdat"] = "NULL";
$size["updatedat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "Bonus ID";
$sort["batchid"] = "Batch ID";
$sort["userid"] = "User ID";
$sort["amount"] = "Amount";
$sort["eventdate"] = "Bonus Date";
$sort["myrank"] = "My Rank";
$sort["userrank"] = "User Rank";
$sort["generation"] = "Generation";
$sort["userdata"] = "User Data";
$sort["createdat"] = "Created";
$sort["updatedat"] = "Updated";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['rankgenbonus'] as $bonus)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$bonus['id']."</td>";
	echo "<td align=center>".$bonus['batchid']."</td>";
	echo "<td align=center>".$bonus['userid']."</td>";
	echo "<td align=center>$".number_format($bonus['amount'], 2)."</td>";
	echo "<td align=center>".DispDate($bonus['eventdate'])."</td>";
	echo "<td align=center>".$bonus['myrank']."</td>";
	echo "<td align=center>".$bonus['userrank']."</td>";
	echo "<td align=center>".$bonus['generation']."</td>";
	echo "<td align=center>".$bonus['userdata']."</td>";
	echo "<td>".DispTimestamp($bonus['createdat'])."</td>";
	echo "<td>".DispTimestamp($bonus['updatedat'])."</td>";

	echo "</tr>";
}

/*

id         | integer                     | not null default nextval('ce_rankgenbonus_id_seq'::regclass)
 system_id  | bigint                      | 
 batch_id   | integer                     | 
 user_id    | text                        | 
 amount     | numeric(37,4)               | 
 event_date | date                        | 
 my_rank    | integer                     | 
 user_rank  | integer                     | 
 generation | character varying(3)        | 
 userdata   | text                        | 
 rule_id    | integer                     | 
 disabled   | boolean                     | default false
 created_at | timestamp without time zone | default now()
 updated_at | timestamp without time zone | default now()

*/

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>