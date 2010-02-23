<?php 
if (!defined('FM_SECURITY')) die();

static function obj()
{
	$class = get_called_class();
	if (!array_key_exists($class,fm::$obj) || !is_a(fm::$obj[$class],$class))
		fm::$obj[$class] = call_user_func(array($class,'factory'));

	return clone fm::$obj[$class];
}