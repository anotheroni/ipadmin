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

/**
 * Check password
 */
function checkpass()
{
    global $id, $firstname, $surname, $username, $level, 
        $lastaccess, $lang, $year, $mon, $day;

    global $guest, $password, $username, $db, 
        $REMOTE_ADDR, $SERVER_NAME, $PHP_SELF;
    
    $path = substr($PHP_SELF, 0, -9);

    // Check for guestlogin
    if(strlen($guest) > 0)
    {
        // Register session variables
        session_name("IPADMIN");
        session_start();
        session_register("id");
        session_register("username");
        session_register("level");
        session_register("lastaccess");
        session_register("yearS");
        session_register("monS");
        session_register("dayS");
		  
        $id = 0;
        $username = "";
        $level = 4;
        $lastaccess = time();
        
        header("Location: http://$SERVER_NAME$path");
        printf("<HTML><HEAD><TITLE>Error</TITLE><BODY>" .
            "<A href=\"http://%s%s\">Click here</A></BODY></HTML>", 
            $SERVER_NAME, $path);
        
        my_die();
    }

    // Check for blocked IP
    $blocked = pg_exec($db,
		"SELECT * FROM blockedip WHERE ip = '$REMOTE_ADDR'");
    if(pg_numrows($blocked) != 0)
    {
        header("Location: http://$SERVER_NAME$path?error=3");
        printf("<HTML><HEAD><TITLE>Error</TITLE><BODY>" .
            "<A href=\"http://%s%serror=3\">Click here</A></BODY></HTML>", 
            $SERVER_NAME, $path);
        pg_freeresult($blocked);
        my_die();
    }

    pg_freeresult($blocked);

    // Check to see if the user uses a correct username, password
    $username2 = addslashes($username);
    
    $error = 0;
    // See if the user exists
    $user = pg_exec($db, "SELECT * FROM administrators " .
        "WHERE username = '$username2'");

    if(pg_numrows($user) != 1)
    {
        $error = 1;
        // Record this error for later use
        $graylist = @pg_exec($db, "INSERT INTO \"graylist\" (\"ip\") " .
            "VALUES ('$REMOTE_ADDR')");

        if(!$graylist)
        {
            pg_exec($db, "UPDATE \"graylist\" " .
                "SET \"nrOfTries\" = \"nrOfTries\" + 1, " .
                "\"blocked_until\" = now() + '5 minutes' " .
                "WHERE \"ip\" = '$REMOTE_ADDR'");
        }
    }
    else
    {
        $userData = pg_fetch_object($user, 0);
        
        // Check if the users account has been blocked
        if($userData->blocked == 't')
        {
            $error = 2;
        }
        
        // If the user exists check the password
        else if(strcmp(crypt($password, $userData->password), 
                $userData->password))
        {
            $error = 1;

            $graylist = @pg_exec($db, "INSERT INTO \"graylist\" (\"ip\") " .
                "VALUES ('$REMOTE_ADDR')");

            if(!$graylist)
            {
                pg_exec($db, "UPDATE \"graylist\" " .
                    "SET \"nrOfTries\" = \"nrOfTries\" + 1, " .
                    "\"blocked_until\" = now() + '5 minutes' " .
                    "WHERE \"ip\" = '$REMOTE_ADDR'");
            }
        }
    }


    // If the user couldn't be logged in
    if($error != 0)
    {
        header("Location: http://$SERVER_NAME$path?error=" .
            "$error&username=$username&domain=$domain");
        printf("<HTML><HEAD><TITLE>Error</TITLE><BODY>" .
            "<A href=\"http://%s%sa=error=$error" .
            "&username=$username&domain=$domain\">" .
            "Click here</A></BODY></HTML>", 
            $SERVER_NAME, $path);
    }
    else
    {
        // Register session variables
        session_name("IPADMIN");
        session_start();
        session_register("id");
        session_register("firstname");
        session_register("surname");
        session_register("username");
        session_register("level");
        session_register("lastaccess");
        session_register("lang");
        session_register("yearS");
        session_register("monS");
        session_register("dayS");

        // Initiate the variables
        $id = $userData->id;
        $firstname = $userData->firstname;
        $surname = $userData->surname;
        $username = $userData->username;
        $level = $userData->level;
        $lastaccess = time();
        $lang = $userData->languageId;
        
        header("Location: http://$SERVER_NAME$path");
        printf("<HTML><HEAD><TITLE>Error</TITLE><BODY>" .
            "<A href=\"http://%s%s\">Click here</A></BODY></HTML>", 
            $SERVER_NAME, $path);
        
        my_die();
    }
    
    // Clean up...
    pg_freeresult($user);

    my_die();
}

?>
