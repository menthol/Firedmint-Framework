<?php
if (!defined('FM_SECURITY')) die();

static function error($message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	if (count($args)==1)
		$args = array_shift($args);
	log::add('error',$message,$args);
}