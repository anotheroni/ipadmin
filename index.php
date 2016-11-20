<?php

define("INDEX_PAGE_IS_USED", true);

// Connect to the database
$db = @pg_connect(
	"host=127.0.0.1 port=5432 dbname=ipadmin user=ipadmin password=db2admin") 
    or dbNotThere();

// No caching
header ("Cache-Control: no-cache, must-revalidate"); 
header ("Pragma: no-cache");

// Use sessions with postgresql
include("session.php");

sess_gc(0);

if(isset($set_lang))
{
    // Save value for one year
    setcookie("lang", $set_lang, time() + (3600 * 24 * 365));
    $lang = $set_lang;
}
if($lang == 0)
{
    $lang = 1;
}

function my_die()
{
    global $db;
    die;
}

function dbNotThere()
{
    include("gui.php");
    printHeader();
    print "<TABLE width=\"100%%\" height=\"100%%\">\n";
    print "  <TR>\n";
    print "    <TD align=\"center\" valign=\"middle\">\n";
    print "      <FONT size=\"+2\">" .
        "Sorry, the database isn't there...</FONT>\n";
    print "    </TD>\n";
    print "  </TR>\n";
    print "</TABLE>\n";
    printBottom();
    die;
}

// If the user tries to log in. Let him before checking secturity
if($a == "checkpass")
{
    include("checkpass.php");
    checkpass(); // This function never returns
}

// Check security. If User isn't loged in a login page will be displayed
// And the execution will terminate
include("checksecurity.php");

switch($a)
{
	case "admin_house2":
		include("admin_houses.php"); 
      saveHouseName();
      break;
	case "admin_house":
		include("gui.php");
		include("admin_houses.php"); 
		printHeader();
		pageStart();
		print_admin_houses();
		pageEnd();
		printBottom();
		break;
	case "admin_subnets":
		include("gui.php");
		include("admin_subnets.php"); 
		printHeader();
		pageStart();
		print_admin_subnets();
		pageEnd();
		printBottom();
		break;
	case "admin_users2":
		include("admin_subnets.php");
		update_admin_subnets();
		break;
	case "logout":
      session_destroy();
      header("Location: http://$SERVER_NAME$PHP_SELF");
      break;
	default:
      include("gui.php");
      include("loginpage.php");
      printHeader();
      pageStart();
      printMainPage();
      pageEnd();
      printBottom();
      break;
}

?>
