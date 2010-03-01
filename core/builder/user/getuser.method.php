<?php
if (!definied('FM_SECURITY')) die();

static function getUser($login)
{
	if (is_object(user::$o))
		return user::$o->getUser($login);
}