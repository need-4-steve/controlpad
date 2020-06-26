<?php

echo "testupdatedurl.php has been called<br>\n";

print_r($_POST);

//print_r($_SERVER);

file_put_contents("/tmp/test.txt", json_encode($_POST));

?>