<?php
if (!definied('FM_SECURITY')) die();

static function anonymous()
{
	$user        = new stdClass();
	$user->data  = array();
	$user->group = 'anonymous';
	$user->ip    = _ip();
	$user->login = 'anonymous';
	$user->name  = _l('anonymous',null,true);
	return $user;
}
