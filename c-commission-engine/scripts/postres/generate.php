#!/usr/bin/php
<?php

// Generate the SQL for users //
file_put_contents("users.sql", ""); // Clear it out //
$user_id = 0;
for ($index=1; $index <= 40; $index++)
{
	// Add a user into the system //
	$query = "INSERT INTO users(system_id, user_id, sponsor_id, signup_date) VALUES ";
	for ($count=0; $count < 5000; $count++)
	{
		$user_id++;
		$sponsor_id = rand(0, $user_id-1); // Make sure the sponsor comes before //
		$query .= "(1, ".$user_id.", ".$sponsor_id.", '".date("Y-m-d")."'),";
	}
	$query = rtrim($query, ",");
	$query .= ";\n";
	
	file_put_contents("users.sql", $query, FILE_APPEND);
}

// Generate the SQL for receipts //
file_put_contents("receipts.sql", ""); // Clear it out //
for ($index=1; $index <= 200; $index++)
{
	$query = "INSERT INTO receipts(system_id, receipt_id, user_id, amount, purchase_date, commissionable) VALUES ";

	// Put multiple together on one INSERT to speed things up //
	for ($count=0; $count < 5000; $count++)
	{
		// Add a user into the system //
		$system_id = 1;
		$receipt_id = $index;
		$user_id = rand(1, 500); 
		$amount = rand(1, 100).".".rand(0, 99); // Random dollar amounts //
		$purchase_date = date("Y-m-d");
		$commissionable = true;
		
		$query .= "(1, ".$receipt_id.", ".$user_id.", '".$amount."', '".$purchase_date."', true),";
	}
	$query = rtrim($query, ",");
	$query .= ";\n";

	file_put_contents("receipts.sql", $query, FILE_APPEND);
}

?>