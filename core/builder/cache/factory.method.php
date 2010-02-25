<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(cache::$file))
		cache::$file = _subClass('cache',kernel::$config['cache']['file'],true);
	
	if (is_null(cache::$value))
		cache::$value = _subClass('cache',kernel::$config['cache']['value'],true);
	
	if (is_null(cache::$front))
		cache::$front = _subClass('cache',kernel::$config['cache']['front'],true);
}