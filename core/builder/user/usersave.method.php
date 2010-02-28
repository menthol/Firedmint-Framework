<?php
if (!definied('FM_SECURITY')) die();

static function userSave($user)
{
	return user::$o->userSave($user);
}