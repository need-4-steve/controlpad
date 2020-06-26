#!/usr/bin/php
<?php

// Prepare to write out the SQL file //
$outfile = "real-data.sql";

// Reset to empty file //
file_put_contents($outfile, '');

// Read the csv file into the database //
$filename = "data/Tzin-Sales-September.csv";
if (($handle = fopen($filename, "r")) !== FALSE)
{
	$receipt_count = 1;

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
    	//print_r($data);
    	//return;

        $email = $data[1];
        $user = LookupIDs($email); // There are still 6ish people that don't have accurate information //
        if (!empty($user['userid']))
        {
        	if (empty($UserEmails[$email]))
        	{ 
                //$user_id = $user[1];
        		//$parent_id = $user[2];
        		//if ($userid_count == 1)
        		//	$parent_id = 0;

        		$UserEmails[$email] = "true";

        		$query = "INSERT INTO ce_users (system_id, user_id, email, parent_id, sponsor_id, signup_date, usertype) VALUES ('1', '".$user['userid']."', '".$email."', '".$user['parentid']."', '".$user['parentid']."','".$data[3]."', '1');";

        		// Append to SQL file //
    			file_put_contents($outfile, $query."\n", FILE_APPEND);
        	}

        	$query = "INSERT INTO ce_receipts (system_id, user_id, receipt_id, wholesale_date, wholesale_price, inv_type, commissionable) VALUES ('1', '".$user['userid']."', '".$receipt_count."', '".$data[3]."', '".$data[4]."', '2', 'true');";
        }

    	$receipt_count++;

    	// Append to SQL file //
		file_put_contents($outfile, $query."\n", FILE_APPEND);  
    }
}

////////////////////////////////
// Lookup userid and parentid //
////////////////////////////////
function LookupIDs($email)
{
    $filename = "data/Tzin-Sheet2.csv";
    if (($handle = fopen($filename, "r")) !== FALSE)
    {
        $receipt_count = 1;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
        {
            $email = strtolower($email);
            $data[0] = strtolower($data[0]);

            if (strcmp($email, $data[0]) == 0)
            {
                $data['userid'] = $data[2];
                $data['parentid'] = $data[3];

                return $data;
            }
        }
    }

    echo "Missing email: ".$email."\n";
}

?>