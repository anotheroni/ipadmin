<?php

function fixCharacters($s)
{
	$s = ereg_replace("å", "&aring;", $s);
	$s = ereg_replace("ä", "&auml;", $s);
	$s = ereg_replace("ö", "&ouml;", $s);
	$s = ereg_replace("Å", "&Aring;", $s);
	$s = ereg_replace("Ä", "&Auml;", $s);
	$s = ereg_replace("Ö", "&Ouml;", $s);
	$s = ereg_replace("ç", "&ccedil;", $s);
	$s = ereg_replace("Ç", "&Ccedil;", $s);

	return $s;

}

?>
