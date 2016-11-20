<?php

include("checksecurity.php");

$pagetexts = pg_exec($db, "SELECT * FROM \"page_admin_subnets\" ".
        "WHERE \"langId\" = $lang");
$pagetext = pg_fetch_object($pagetexts, 0);
pg_freeresult($pagetexts);

function print_admin_subnets()
{
	global $level, $domain, $eu, $bt1au, $pagetext;
	$domain = (int)$domain;

	// Check user level
	if($level > 2) {
		printf("%s<BR>\n", htmlentities($pagetext->text01)); 	// You are not...
		return;
	}
	
	if(strlen($bt1au) > 0) {
		admin_subnets_add_subnet();
	} else if($eu != 0) {
		admin_subnets_edit_subnet();
	} else {
		admin_subnets_view_subnet();
	}
}

function update_admin_subnets()
{
	global $level, $eu, $pagetext;
	global $btedel, $btec, $btc;

	// Check user level
	if($level > 2) {
		printf("%s<BR>\n", htmlentities($pagetext->text01));	// You are not...
		return;
	}
	
	if(strlen($btedel) > 0) {
		admin_subnets_delete_subnet();
	} else if(strlen($btc) > 0) {
		;
	} else {
		if($eu != 0) {
			admin_subnets_update_subnet();
		} else {
			admin_subnets_insert_subnet();
		}
	}

	global $SERVER_NAME, $PHP_SELF;
	header("Location: http://$SERVER_NAME$PHP_SELF?a=admin_subnets");
}

function admin_subnets_view_subnets()
{
	global $db, $level, $id, $pagetext;

	// Check user level
	if($level > 2) {
		printf("%s<BR>\n", htmlentities($pagetext->text01));	// You are not...
		return;
	}

	// Get all subnets
	$subnets = pg_exec ($db, "SELECT * FROM subnets ORDER BY addr");

	printf("<FONT size=\"+1\"><B>%s</B></FONT><br><BR>\n",
		 htmlentities($pagetext->text02));	// Administrate Subnets

	printf("<FORM method=\"GET\" action=\"\">\n");
	printf("<INPUT type=\"hidden\" name=\"a\" value=\"admin_subnets\">\n");

	printf("<INPUT type=\"submit\" name=\"bt1au\" value=\"%s\"><BR>\n",
		htmlentities($pagetext->text03));	// Add Subnet

	// Print all subnets
	if(pg_numrows($subnets) == 0) {
		printf("<B>%s</B>\n",
			htmlentities($pagetext->text04)); // No subnets in the database 
	} else {
		printf("<TABLE border=\"0\" cellpadding=\"5\">\n");
		// Table header
		printf("  <tr bgcolor=\"#0000BB\">\n".
				"    <td><FONT color=\"white\">%s</FONT></td>\n".
				"    <td><FONT color=\"white\">%s</FONT></td>\n".
				"    <td><FONT color=\"white\">%s</FONT></td>\n".
				"    <td><FONT color=\"white\">%s</FONT></td>\n".
				"    <td>&nbsp;</td>\n  </tr>\n",
				htmlentities($pagetext->text05),	// Network address
				htmlentities($pagetext->text06), // Netmask
				htmlentities($pagetext->text07), // Gateway
				htmlentities($pagetext->text08)	// Local subnet
				);
		// Table data
		for($i=0 ; $i < pg_numrows($subnets) ; $i++) {
			$subnet = pg_fetch_object($subnets, $i);
			printf("  <tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td>".
					"<td><A class=\"editlink\" ".
					"href=\"index.php?a=admin_subnets&amp;eu=%d\">".
					"%s</A></td><tr>\n",
					$subnet->addr,
					$subnet->netmask,
					$subnet->gateway,
					$subnet->local_subnet == 't' ? htmlentities($pagetext->text10) :
						htmlentities($pagetext->text11),
					$subnet->id,
					htmlentities($pagetext->text09)	// Edit
					);
		}
		printf("</TABLE>\n");
	}

	printf("</FORM>");

	pg_freeresult($subnets);

}

function admin_subnets_edit_subnet()
{
	global $db, $level, $id, $domain, $eu, $pagetext;

	print "<FORM method=\"GET\" name=\"user\" action=\"\">\n";

	print "<INPUT type=\"hidden\" name=\"a\" value=\"admin_subnets2\">\n";
	print "<INPUT type=\"hidden\" name=\"eu\" value=\"$eu\">\n";

	$result = pg_exec($db, "SELECT * FROM subnets WHERE id = '$eu'");

	if (pg_numrows($result) == 0)
	{
		print "No subnet";	// TODO
		return;
	}
	$subnet = pg_fetch_object($result, $i);
	pg_freeresult($result);

	printf("<FONT size=\"+1\"><B>%s %s</B></FONT><BR>\n",
	  	htmlentities($pagetext->text12), // Edit Subnet
		$subnet->addr);

	print "<TABLE>\n";
	printf("  <TR><TD>%s</TD>".
			"<TD><INPUT type=\"text\" name=\"n_gateway\" ".
			"value=\"$subnet->gateway\" size=\"30\"></TD></TR>\n",
			htmlentities($pagetext->text07));	// Gateway
	printf("  <TR><TD>%s</TD><TD><INPUT type=\"checkbox\" ".
			"name=\"n_local_subnet\" %s></TD></TR>\n",
			htmlentities($pagetext->text08),	// Local Subnet
			($subnet->local_subnet == "t" ? "checked" : ""));

	printf("  <TR>".
			"<TD><INPUT type=\"submit\" ".
			"onClick=\"return confirmSubmit(this.form, '%s')\" ".
			"name=\"btedel\" value=\"%s\"></TD>\n".
			"</TR>\n",
			htmlentities($pagetext->text13),	// Are you sure you want to delete...
			htmlentities($pagetext->text14));// Delete

	printf("  <TR><TD><INPUT type=\"button\" name=\"btedone\" value=\"%s\" ".
		"onClick=\"checkUser(this.form, '%s')\"></TD>\n", 
		htmlentities($pagetext->text15),	// Done
		htmlentities($pagetext->text17));// You must enter in all data
		
	printf("  <TD><INPUT type=\"submit\" name=\"btc\" value=\"%s\">".
		"</TD></TR>\n",
		htmlentities($pagetext->text16));	// Cancel

	print "</TABLE>\n";

	print "</FORM>\n";

	return;
}

function admin_subnets_add_subnet()
{
	global $db, $level, $id, $eu, $lang, $pagetext;
	$domain = (int)$domain;

	print "<FORM method=\"GET\" action=\"\">\n";

	print "<INPUT type=\"hidden\" name=\"a\" value=\"admin_subnets2\">\n";
	printf("<FONT size=\"+1\"><B>%s</B></FONT><BR>\n",
		htmlentities($pagetext->text03));	// Add Subnet

	print "<TABLE>\n";
	printf("  <TR>\n    <TD>%s</TD>\n".
		"    <TD><INPUT type=\"text\" name=\"n_addr\" size=\"30\"></TD>\n".
		"  </TR>\n",
		htmlentities($pagetext->text05));	// Netwok address
	printf("  <TR><TD>%s</TD><TD><SELECT name=\"n_netmask\">\n",
			htmlentities($pagetext->text06));	// Netmask
	print "    <OPTION value=\"24\">255.255.255.0</OPTION>\n";
	print "  </SELECT></TD></TR>\n";
	printf("  <TR><TD>%s</TD><TD><INPUT type=\"text\" ".
			"name=\"n_gateway\" size=\"30\"></TD></TR>\n",
			htmlentities($pagetext->text07));	// Gateway
	printf("  <TR><TD>%s</TD><TD><INPUT type=\"checkbox\" ".
			"name=\"n_local_subnet\"</TD><TR>\n",
			htmlentities($pagetext->text08));	// Local subnet

	printf("  <TR><TD><INPUT type=\"button\" name=\"btadone\" value=\"%s\" ".
		"onClick=\"checkUser(this.form, '%s')\"></TD>\n",
		htmlentities($pagetext->text15),	// Done 
		htmlentities($pagetext->text17)	// You must enter all data
		);
	printf("  <TD><INPUT type=\"submit\" name=\"btc\" value=\"%s\">".
		"</TD></TR>\n",
		htmlentities($pagetext->text16));	// Cancel

	print "</TABLE>\n";

	print "</FORM>\n";

	return;
}

function admin_subnets_delete_subnet()
{
	global $db, $level, $eu, $pagetext;

	if($level > 2) {
		// You are not allowed to view this page 
		printf("%s<BR>\n", htmlentities($pagetext->text01));
		return;
	}

	pg_exec($db, "DELETE FROM subnets WHERE id = '$eu'");

	return;
}

function admin_subnets_insert_subnet()
{
	global $db, $level, $pagetext;
	global $n_addr, $n_netmask, $n_gateway, $n_local_subnet;	
	global $PHP_SELF, $SERVER_NAME;

	$num_addr;	// The number of ip addresses in the subnet
	$netmask_str;	// Network address as a string

	if($level > 2) {
		// You are not allowed to view this page 
		printf("%s<BR>\n", htmlentities($pagetext->text01));
		return;
	}

	$n_addr = addslashes($n_addr);
	$n_gateway = addslashes($n_gateway);

	// Check if the subnet already exists
	$res = pg_exec($db, "SELECT test_new_subnet (n_addr)");
	if (pg_fetch_object($res, 0) == 'f') {
		printf ("%s\n", $pagetext->text18);	// The subnet already exists
		pg_freeresult ($res);
		return;
	}
	$pg_freeresult ($res);

	switch ($n_netmask) {
		case 24:	// 255.255.255.0
			$num_addr = 255;
			$netmask_str = "255.255.255.0"
		break;
		default:	// TODO
			printf("<B>Netmask ERROR</B>. Unknow netmask %d\n", $n_netmask); 
			return;
	}

	// Parse subnet address
	// Requires 1-3 digits, period, 1-3 digits, 1-3 digits, period, 1-3 digits 
   $regEx = "([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)[0-9]{1,3}";  
   if (!ereg ($regEx, $n_addr, $regs)) {	// TODO
		print "<B>Subnet address ERROR</B>. Can't parse regular expresion\n"; 
		return;
   }

	pg_exec ($db, "BEGIN TRANSACTION");

	$res = pg_exec ($db, "SELECT nextval('subnets_seq') AS val");
	$net_id = pg_fetch_object ($res, 0);
	pg_freeresult ($res);
	
	if ($n_local_subnet != "")
		$local_subnet = 't';
	else
		$local_subnet = 'f';
	
	pg_exec ($db, "INSERT INTO subnets (id, addr, netmask, gateway, ".
		"local_subnet) VALUES ($net_id, '$n_addr', '$netmask_str', ".
		"'$n_gateway', '$local_subnet')");

	// Add all ip-numbers in the subnet
	for ($i=1 ; $i < $num_addr ; $i++) {
		$ip_num = $regs[1].$i;
		pg_exec ($db, "INSERT INTO ip_numbers (ip, subnet) VALUES (".
		"'$ip_num', $net_id)");
	}
 
	pg_exec ($db, "COMMIT");

	return;
}

function admin_subnets_update_subnet()
{
	global $db, $level, $eu, $pagetext;
	global $n_gateway, $n_local_subnet;	
	
	if($level > 2) {
		// You are not allowed to view this page 
		printf("%s<BR>\n", htmlentities($pagetext->text01));
		return;
	}
	
	$n_gateway = addslashes($n_gateway);

	if($n_local_subnet == "on") {
		$n_local_subnet = "t";
	} else {
		$n_local_subnet = "f";
	}

	pg_exec($db, "UPDATE subnets SET gateway = '$n_gateway', ".
		"local_subnet = '$n_local_subnet' WHERE id = '$eu'");

	return;
}

?>
