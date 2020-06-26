#!/usr/bin/php
<?php

$row = 1;
if (($handle = fopen("users.csv", "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        $num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";
        //$row++;
        //for ($c=0; $c < $num; $c++)
        //{
        //    echo $data[$c] . "\n";
        //}

        //echo $data[1]."\n";
	
	if ($data[0] != "")
	{
        	$line = "UPDATE ce_users SET disabled='true' WHERE user_id='".$data[0]."';\n";
		file_put_contents("disable.sql", $line, FILE_APPEND); 
	}
    }
    fclose($handle);
}

