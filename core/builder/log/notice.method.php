<?php
if (!defined('FM_SECURITY')) die();

static function notice($message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	if (count($args)==1)
		$args = array_shift($args);
	
	log::add('notice',$message,$args);
}
