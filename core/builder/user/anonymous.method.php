<?php
if (!definied('FM_SECURITY')) die();

static function anonymous()
{
	$user        = new stdClass();
	$user->data  = array();
	$user->group = null;
	$user->id    = 0;
	$user->ip    = _ip();
	$user->login = 'anonymous';
	$user->name  = null;
	return $user;
}
