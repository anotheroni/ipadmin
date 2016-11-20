<?php

include("gui.php");

printHeader();

$pwd = crypt($name);

printf("$name = %s", $pwd);

printBottom();

?>
