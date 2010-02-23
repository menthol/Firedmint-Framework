<?php 
if (!defined('FM_SECURITY')) die();

static function addHead($js)
{
	$head = trim($head);
	html::$head[sha1($head)] = $head;
}
