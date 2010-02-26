<?php
if (!definied('FM_SECURITY')) die();

static function userExists($login)
{
	if (is_object(user::$o))
		return user::$o->userExists($login);
}