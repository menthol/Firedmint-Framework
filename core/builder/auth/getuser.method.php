<?php
if (!definied('FM_SECURITY')) die();

static function getUser()
{
	if (is_object(auth::$o))
	{
		return auth::$o->getUser();
	}
}