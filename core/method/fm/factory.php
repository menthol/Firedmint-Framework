<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (!is_a(self::$obj,get_called_class()))
	{
		$class = get_called_class();
		self::$obj = new $class();
	}
	
	return self::$obj;
}