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


$SESS_LIFE = 900; // 15 minutes

function sess_open ($save_path, $session_name) 
{
    return true;
}

function sess_close() 
{
    global $db;
    pg_close($db);
    return true;
}


function sess_read($id) 
{
    global $db;

    $data = pg_exec($db, "SELECT * FROM sessions " .
        "WHERE sesskey = '$id' AND expiry > now()");

    if(pg_numrows($data) == 0)
    {
        return false;
    }

    $value = pg_fetch_object($data, 0);
    pg_freeresult($data);
    return $value->value;
}

function sess_write($key, $val) 
{
    global $db, $SESS_LIFE;

    $value = addslashes($val);
    
    $query = @pg_exec($db, "INSERT INTO sessions " .
        "VALUES ('$key', now() + '$SESS_LIFE seconds', '$value')");
    
    if(!$query)
    {
        $query = pg_exec($db, "UPDATE sessions " .
            "SET expiry = now() + '$SESS_LIFE seconds', value = '$value' " .
            "WHERE sesskey = '$key' AND expiry > now()");
    }
    
    return $query;
}

function sess_destroy ($key) 
{
    global $db;

    $query = pg_exec($db, "DELETE FROM sessions WHERE sesskey = '$key'");

    return $query;
}

function sess_gc ($maxlifetime) 
{
    global $db;

    $query = pg_exec($db, "DELETE FROM sessions WHERE expiry < now()");

    return true;
}

session_set_save_handler(
   "sess_open",
   "sess_close",
   "sess_read",
   "sess_write",
   "sess_destroy",
   "sess_gc");

?>
