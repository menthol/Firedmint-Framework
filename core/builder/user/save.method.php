<?php
if (!definied('FM_SECURITY')) die();

static function save()
{
	return user::userSave(user::$current);
}