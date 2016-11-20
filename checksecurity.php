<?php

/**
 * It the user hasn't loaded index.php
 * Force him to do it now
 */
if(!@constant("INDEX_PAGE_IS_USED"))
{
    $path = substr($PHP_SELF, 0, strlen(strrchr($PHP_SELF, '/')) - 1);
    header("Location: http://$SERVER_NAME$path");
    printf("<HTML><HEAD><TITLE>Error</TITLE><BODY>" .
        "<A href=\"http://%s%s\">Click here</A></BODY></HTML>", 
        $SERVER_NAME, $path);
    die;
}


// Start the session
session_name("IPADMIN");
session_start();

if(!session_is_registered("id"))
{
    include("loginpage.php");
    include("gui.php"); 
    printHeader();
    printLoginPage();
    printBottom();
    my_die();
}

?>
