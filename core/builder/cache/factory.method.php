<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(cache::$file))
		cache::$file = _subClass('cache',kernel::$config['cache']['file_engine'],true);
	
	if (is_null(cache::$value))
		cache::$value = _subClass('cache',kernel::$config['cache']['value_engine'],true);
	
	if (is_null(cache::$front))
		cache::$front = _subClass('cache',kernel::$config['cache']['front_engine'],true);
	
	if (is_null(cache::$static))
		cache::$static = _subClass('cache',kernel::$config['cache']['static_engine'],true);
}