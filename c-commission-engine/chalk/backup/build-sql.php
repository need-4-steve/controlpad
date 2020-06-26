#!/usr/bin/php
<?php

$row = 1;
if (($handle = fopen("userids.txt", "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
    {
        //$num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";
        //$row++;
        //for ($c=0; $c < $num; $c++)
        //{
        //    echo $data[$c] . "\n";
        //}

        //echo $data[1]."\n";

        echo "UPDATE ce_users SET usertype='1' WHERE user_id='".$data[0]."';\n";
    }
    fclose($handle);
}