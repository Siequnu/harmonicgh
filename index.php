<?php

/*
echo "<pre>";

var_dump ($_GET);
var_dump ($_POST);
#var_dump ($_SERVER);

echo "</pre>";
#phpinfo();
*/

require_once './classes/chordGenerator.class.php';
$chordGenerator = new chordGenerator;
$chordGenerator->main ();


?>