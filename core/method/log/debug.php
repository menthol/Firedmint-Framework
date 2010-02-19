<?php
if (!defined('FM_SECURITY')) die();

static function debug($message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$message['debug'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}
