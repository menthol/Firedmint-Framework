<?php
if (!definied('FM_SECURITY')) die();

static function __set_state($values = array())
{
	$view = _class('view');
	foreach ($values as $key=>$value)
		$view->{$key} = $value;
	
	return $view;
}