#!/usr/bin/php
<?php

$count = 0;
$row = 1;
if (($handle = fopen("test.txt", "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, " ")) !== FALSE)
    {
        $num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";

        if (!empty($data[9]))
        {
        	echo "userid = ".$data[9]."\n";

        	$count++;
        }
        //$row++;
        //for ($c=0; $c < $num; $c++)
        //{
        //    echo $data[$c] . "<br />\n";
        //}
    }
    fclose($handle);
}

echo "count = ".$count."\n";

?>