<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	$class = get_called_class();
	if (!array_key_exists($class,fm::$obj) || !is_a(fm::$obj[$class],$class))
	{
		fm::$obj[$class] = new $class();
	}
	return fm::$obj[$class];
}