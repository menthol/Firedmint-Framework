<?php
if (!defined('FM_SECURITY')) die();

static function message($message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$message['message'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}
