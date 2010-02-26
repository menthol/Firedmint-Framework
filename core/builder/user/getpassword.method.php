<?php
if (!definied('FM_SECURITY')) die();

static function getPassword($login)
{
	if (is_object(user::$o))
	{
		return user::$o->getPassword($login);
	}
}