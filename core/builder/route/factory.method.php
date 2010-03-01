<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(route::$o))
		route::$o = _subClass('route',kernel::$config['route']['engine']);
		
	return route::$o;
}