<?php

function fixCharacters($s)
{
	$s = ereg_replace("�", "&aring;", $s);
	$s = ereg_replace("�", "&auml;", $s);
	$s = ereg_replace("�", "&ouml;", $s);
	$s = ereg_replace("�", "&Aring;", $s);
	$s = ereg_replace("�", "&Auml;", $s);
	$s = ereg_replace("�", "&Ouml;", $s);
	$s = ereg_replace("�", "&ccedil;", $s);
	$s = ereg_replace("�", "&Ccedil;", $s);

	return $s;

}

?>
