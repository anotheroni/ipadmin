<?php

/**
* HTML page header
*/
function printHeader()
{
    global $level, $SESS_LIFE;
    print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\"\n";
    print "\t\"http://www.w3.org/TR/REC-html40/strict.dtd\">\n\n";

    print "<HTML>\n";
    print "<HEAD>\n";
    print "  <TITLE>IPAdmin</TITLE>\n";
    // Auto-logout after 5 minutes and 5 sec
    if($level > 0)
        printf("  <meta http-equiv=\"refresh\" content='%d'>\n", 
            $SESS_LIFE + 5); 
    print "  <meta http-equiv=\"Content-Type\" " .
        "content=\"text/html; charset=iso-8859-1\">\n";
    print "  <script language=\"JavaScript\" src=\"ipadmin.js\" " .
        "type=\"text/JavaScript\">\n" .
        "  <!--\n  ";
    print "  // -->\n" .
        "  </script>\n";
    print "<LINK REL=\"stylesheet\" TYPE=\"text/css\" HREF=\"style.css\">\n";
        
    print "</HEAD>\n";
    if($level > 0)
    {
        print "<BODY bgcolor=\"white\">\n";
    }
    else
    {
        print "<BODY " .
            "onLoad=\"document.login.username.focus(); return true\" " .
            "bgcolor=\"white\">\n";
    }
}

/**
* Prints the menu table.
*/
function pageStart()
{
	global $db, $level, $lang;

	include("smallfunctions.php");

	$data = pg_exec($db, "SELECT * FROM page_menu WHERE langid='$lang'");
	$result = pg_fetch_object($data, 0);
	pg_freeresult($data);

	print "\n<!--- MAIN MENU --->\n\n";

	print "<TABLE border=\"0\" width=\"770\" cellpadding=\"5\">\n";
	print "<TR><TD valign=\"top\" width=\"170\">\n";

	print "<P align=\"center\"><A href=\"\">\n" .	// TODO href=
        "<IMG src=\"images/foke_small.jpg\" border=\"0\" width=\"150\" " .
       "height=\"43\" alt=\"IP Administration\"></A></P>\n";

	// Top images
	print "<TABLE width=\"100%%\" border=\"0\" cellpadding=\"0\" ".
		"cellspacing=\"0\">".
		"<TR>\n".
		"  <TD valign=\"top\" width=\"10\">".
		"<IMG src=\"images/topleft.gif\" width=\"10\" height=\"10\">".
		"</td>\n".
		"  <td bgcolor=\"#0000BB\" valign=\"top\" width=\"150\">".
		"<IMG src=\"images/pixel.gif\" width=\"1\" height=\"1\"></td>\n".
		"  <td valign=\"top\" width=\"10\">".
		"<IMG src=\"images/topright.gif\" width=\"10\" height=\"10\">".
		"</TD>\n".
		"</TR>\n";

	// M E N U
	print "<TR>\n".
		"<TD bgcolor=\"#0000BB\" valign=\"top\" width=\"170\" colspan=\"3\">".
		"\n";

	print "<BR>\n";

	printf("&nbsp;<a href=\"index.php?a=show_houses\" class=\"mainmenu\">".
		"<FONT color=\"white\">%s</FONT></a><BR>\n",
		fixCharacters($result->text06));
	
	printf("&nbsp;<a href=\"index.php?a=show_houses\" class=\"mainmenu\">".
		"<FONT color=\"white\">%s</FONT></a><BR>\n",
		fixCharacters($result->text06));
	
	if($level <= 3)
		printf("<BR>\n");

	if($level <= 3)
		printf("&nbsp;<a href=\"index.php?a=show_subnets\" class=\"mainmenu\">".
			"<FONT color=\"white\">%s</FONT></a><BR>\n",
			fixCharacters($result->text07));
		

	if($level <= 2)	// Administration
		printf("<B>&nbsp<FONT color=\"#FFFFFF\">%s</FONT></B><BR>\n",
		  	fixCharacters($result->text08));
	if($level <= 2)
		printf("&nbsp;&nbsp;&nbsp;".
			"<a href=\"index.php?a=admin_administrators\" class=\"mainmenu\">".
			"<FONT color=\"white\">%s</FONT></a><BR>\n",
			fixCharacters($result->text09));
	
	print "<BR>\n";
	
	printf("&nbsp;<a href=\"index.php?a=logout\" class=\"mainmenu\">".
		"<FONT color=\"white\">%s</FONT></a><BR>\n",
		fixCharacters($result->text01));

	print "<BR><BR>\n";
	
	// F L A G S
	if($level > 3)
   {
		$languages = pg_exec($db, "SELECT * FROM \"languages\" ORDER BY \"id\"");
		$numLanguages = pg_NumRows($languages);
		print "<P align=\"center\">\n";

		for($i=0 ; $i < $numLanguages ; $i++)
		{
			$language = pg_fetch_object($languages, $i);
			printf("<A href=\"?set_lang=%d\">" .
					"<IMG src=\"images/flag_small_%s.gif\" border=\"0\" " .
					"alt=\"%s\"></A>\n",
					$language->id, $language->postfix, $language->name);
			if((($i + 1) % 3) == 0)
				print "</P><P align=\"center\">";
		}
		for( ; $i % 3 != 0 ; $i++) {
			printf("<IMG src=\"images/flag_small_transparent.gif\">\n");
		}
		print "</P>\n";
		pg_freeresult($languages);

		print "</TD>\n";
	}

	// Bottom images
	print "<TR>\n".
		"  <TD valign=\"top\" width=\"10\">\n".
		"    <IMG src=\"images/bottomleft.gif\" width=\"10\" height=\"10\"></TD>\n".
		"  <TD bgcolor=\"#0000BB\" valign=\"top\">\n".
		"    <IMG src=\"images/pixel.gif\" width=\"1\" height=\"1\"></TD>\n".
		"  <TD valign=\"top\" width=\"10\">\n".
		"    <IMG src=\"images/bottomright.gif\" width=\"10\" height=\"10\"></TD>\n".
		"  </TR>\n".
		"</TABLE>\n";

	print "<TD width=\"650\" valign=\"top\">\n";

	print "\n<!--- END OF MAIN MENU --->\n\n";
}

/**
* Ends the menu table.
*/
function pageEnd()
{
	print "</TD></TR></TABLE>\n";
}

function printBottom()
{
    print "\n</BODY>\n</HTML>\n";
}

?>
