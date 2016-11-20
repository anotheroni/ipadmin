<?php

include("checksecurity.php");

$pagetexts = pg_exec($db, "SELECT * FROM page_admin_houses " .
        "WHERE \"langId\" = $lang");
$pagetext = pg_fetch_object($pagetexts, 0);
pg_freeresult($pagetexts);

function print_admin_houses ()
{
	global $level, $id, $hid, $pagetext;

	if($hid > 0) {
		inspectHouse();
	} else {
		admin_houses_view_houses ();
	}
}

function update_admin_houses ()
{
	global $delete, $addnew, $cancel, $name, $hid, $level, $pagetext;
	global $SERVER_NAME, $PHP_SELF;

	// Check user level
	if ($level > 1) {
		printf("%s<BR>\n", htmlentities($pagetext->text01));	// You are not...
		return;
	}

	$hid = (int) $hid;

	if (strlen($delete) > 0)
   {
		pg_exec ($db, "DELETE FROM houses WHERE id = $hid");
	}
	else if (strlen($addnew) > 0)
   {
		$name = addslashes ($name);
		if (strlen($name) > 0)
			pg_exec ($db, "INSERT INTO houses (name) VALUES ('$name')");
	}
	else if (strlen($cancel) > 0)
	{

	}
	else
	{
		$name = addslashes ($name);
		pg_exec ($db, "UPDATE houses SET name = '$name' WHERE id = $hid");
	}

	header ("Location: http://$SERVER_NAME$PHP_SELF?a=admin_house");
}

function inspectHouse()
{
    global $db, $hid, $pagetext;

    $okToDelete = false;

    $hid = (int)$hid;
    $departments = pg_exec($db, "SELECT * FROM apartments " .
        "WHERE houseId = $hid");
    $rooms = pg_exec($db, "SELECT * FROM equipment " .
        "WHERE house = $hid");

    if(pg_numrows($departments) == 0 && pg_numrows($rooms) == 0)
        $okToDelete = true;

    pg_freeresult($departments);
    pg_freeresult($rooms);

    // Edit houses
    printf("<FONT size=\"+1\"><B>%s</B></FONT><BR><BR>\n", 
        htmlentities($pagetext->text07));

    $houses = pg_exec("SELECT * FROM houses WHERE id = $hid");
    if(pg_numrows($houses) == 0)
    {
        // Can't find the requested house
        printf("%s\n", htmlentities($pagetext->text08));
        return;
    }
    $house = pg_fetch_object($houses, 0);
    print "<FORM action=\"?a=admin_house\" method=\"post\">\n";
    printf("  <INPUT type=\"hidden\" name=\"hid\" value=\"%d\">\n", $hid);
    print "<TABLE border=\"0\">\n";
    print "  <TR>\n";
    // House name
    printf("    <TD>%s</TD>\n", htmlentities($pagetext->text09));
    printf("    <TD><INPUT type=\"text\" value=\"%s\" name=\"name\"></TD>\n", 
        $house->name);
    print "  </TR>\n";
    if($okToDelete)
    {
        print "  <TR>\n";
        // Delete
        printf("    <TD><INPUT type=\"submit\" value=\"%s\" " .
            "name=\"delete\"></TD>\n", htmlentities($pagetext->text10));
        print "    <TD>&nbsp;</TD>\n";
        print "  </TR>\n";
    }
    print "  <TR>\n";
    // Save
    printf("    <TD><INPUT type=\"submit\" value=\"%s\"></TD>\n",
        htmlentities($pagetext->text11));
    // Cancel
    printf("    <TD><INPUT type=\"submit\" value=\"%s\" " .
        "name=\"cancel\"></TD>\n", htmlentities($pagetext->text12));
    print "  </TR>\n";
    print "</TABLE>\n";
    print "</FORM>\n";

    pg_freeresult($houses);
}

function admin_houses_view_houses()
{
	global $db, $level, $id, $pagetext;

    // Administrate houses
    printf("<FONT size=\"+1\"><B>%s</B></FONT><BR><BR>\n", 
            htmlentities($pagetext->text02));

    // Add new
    print "<FORM action=\"?a=admin_house2\" method=\"post\" name=\"form\">\n";
    print "<INPUT type=\"hidden\" name=\"addnew\" value=\"house\">\n";
    // Add new house
    printf("<TABLE border=\"0\" cellpadding=\"5\"><TR bgcolor=\"#0000BB\">" .
        "<TD><FONT color=\"white\">%s</FONT></TD>" .
        "<TD>&nbsp;</TD>\n", htmlentities($pagetext->text03));
    print "<TR><TD><INPUT type=\"text\" name=\"name\"></TD>\n";
    // Add
    printf("<TD><INPUT type=\"button\" " .
        "onClick=\"checkHouseAddNew(this.form, '%s')\" " .
        "value=\"%s\"></TD>" .
        "</TR>\n", 
        addslashes(htmlentities($pagetext->text19)),
        htmlentities($pagetext->text03));
    printf("<TR><TD>&nbsp;</TD><TD>&nbsp;</TD></TR>\n");

    // print list of all houses
    $houses = pg_exec($db, "SELECT * FROM \"houses\" ORDER BY \"name\"");

    if(pg_numrows($houses) == 0)
    {
        // No houses found
        printf("%s\n", htmlentities($pagetext->text04));
    }
    else
    {
        // Current houses
        printf("<TR bgcolor=\"#0000BB\">" .
            "<TD><FONT color=\"white\">%s</FONT></TD>" .
            "<TD>&nbsp;</TD>\n", htmlentities($pagetext->text05));	// House
        for($i = 0; $i < pg_numrows($houses); $i++)
        {
            $house = pg_fetch_object($houses, $i);
            // Edit
            printf("<TR><TD>%s&nbsp;</TD><TD align=\"center\">" .
                "<A href=\"?a=admin_house&hid=%d\" class=\"editlink\">" .
                "%s</A></TD></TR>\n", 
                $house->name, $house->id, htmlentities($pagetext->text06));
        }
        print "</TABLE>\n";
    }
    print "</FORM>\n";
    
    pg_freeresult($houses);
}

?>
