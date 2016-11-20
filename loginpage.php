<?php

function printMainPage()
{
    global $level;
    
    printf("hej du är nu inloggad<BR>\n");
    printf("Du är inloggad på level: %d<br>\n", $level);
}

function printLoginPage()
{
    global $PHP_SELF, $SERVER_NAME, $error, $username, $db, $lang;

    include("smallfunctions.php");

    $data = pg_exec($db, "SELECT * FROM page_login WHERE langid='$lang'");
    $result = pg_fetch_object($data, 0);
    pg_freeresult($data);

    printf("<FORM method=\"post\" action=\"http://%s%s?a=checkpass\" " .
        "name=\"login\">\n", 
        $SERVER_NAME, $PHP_SELF);
    print "<TABLE width=\"100%%\" height=\"95%%\">\n";
    print "  <TR>\n";
    print "    <TD align=\"center\" valign=\"middle\">\n";
    print "<TABLE>\n";
    print "  <TR>\n";
    print "    <TD colspan=\"3\" align=\"center\">\n";
    printf("      <IMG src=\"images/foke.jpg\" alt=\"%s\" " .
        "width=\"300\" height=\"85\"><BR>\n", fixCharacters($result->text07));
    print "    </TD>\n";
    print "  </TR>\n";
    print "  <TR>\n";
    printf("    <TD align=\"center\"><B>%s</B></TD>\n", 
        fixCharacters($result->text01));
    printf("    <TD align=\"center\"><INPUT type=\"text\" name=\"username\" " .
        "value=\"%s\"></TD>\n", $username);
    print "  </TR>\n";
    print "  <TR>\n";
    printf("    <TD align=\"center\"><B>%s</B></TD>\n",
		 fixCharacters($result->text02));
    printf("    <TD align=\"center\">" .
        "<INPUT type=\"password\" name=\"password\"></TD>\n");
    print "  </TR>\n";
    print "\n";
    print "\n";
    print "\n";
    print "  <TR>\n";
    printf("    <TD align=\"center\"><INPUT type=\"submit\" value=\"%s\">\n",
			fixCharacters($result->text03));
    printf("    </TD>\n");
    printf("  </TR>\n");
    printf("</TABLE>\n");
    
    if($error == 1) // Wrong user/password
    {
        printf("<BR><FONT color=\"red\">%s</FONT>", 
            fixCharacters($result->text04));
    }
    else if($error == 2) // User blocked 
    {
        printf("<BR><FONT color=\"red\">%s</FONT>", 
            fixCharacters($result->text05));
    }
    else if($error == 3) // IP blocked
    {
        printf("<BR><FONT color=\"red\">%s</FONT>", 
            fixCharacters(wordwrap($result->text06, 50, "<br>\n")));
    }

    print "<BR><BR><BR>\n";

    // Print all flags
    $languages = pg_exec($db, "SELECT * FROM languages ORDER BY id");
    for($i = 0; $i < pg_numrows($languages); $i++)
    {
        $language = pg_fetch_object($languages, $i);
        printf("<A href=\"?set_lang=%d\">" .
            "<IMG src=\"images/flag_%s.jpg\" border=\"0\" alt=\"%s\"></A> ",
            $language->id, $language->postfix, $language->name);
    }
    pg_freeresult($languages);
    print "    </TD>\n";
    print "  </TR>\n";
    print "</TABLE>\n";
    print "</FORM>\n";
}

?>
