<?php
if (!defined('FM_SECURITY')) die();

static function notice($message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$message['notice'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}
