<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(user::$o))
		user::$o = _subClass('user',kernel::$config['user']['engine'],true);
	
	return user::$o;
}