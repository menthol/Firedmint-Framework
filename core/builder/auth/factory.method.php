<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(auth::$o))
	{
		auth::$o = _subClass('auth',kernel::$config['auth']['engine'],true);
		user::$current = auth::getUser();
	}
	
	return auth::$o;
}