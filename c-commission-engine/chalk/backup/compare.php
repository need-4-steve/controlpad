#!/usr/bin/php
<?php

// Get first line engine-id.txt //
if (($handle = fopen("engine-id.txt", "r")) !== FALSE)
{
    while (($datacomm = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
    	$datacomm[0] = trim($datacomm[0]);

    	$found = "false";
    	if (($handle2 = fopen("db-id.txt", "r")) !== FALSE)
		{
			$display = "------------------------\n";

			while (($datadb = fgetcsv($handle2, 1000, ",")) !== FALSE)
    		{
    			$datadb[0] = trim($datadb[0]);

    			//echo "comm:".$datacomm[0]." = db:".$datadb[0]."\n";
    			$display .= "comm:".$datacomm[0]." = db:".$datadb[0]."\n";
    			if ($datacomm[0] == $datadb[0])
    			{
    				$found = "true";
    			}
    		}

    		//echo $datacomm[0]."\n";

    		if ($found == "false")
    		{
    			//echo $display;
    			echo "NOT FOUND = ".$datacomm[0]."\n";
    		}
    	}
    }
}

?>