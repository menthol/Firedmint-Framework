<?php 
if (!defined('FM_SECURITY')) die();

static function factory($controller,$action,$arguments)
{
	$class = get_called_class();

	return new $class($controller,$action,$arguments);
}