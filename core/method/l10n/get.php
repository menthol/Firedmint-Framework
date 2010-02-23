<?php 
if (!defined('FM_SECURITY')) die();

function get($key)
{
	$return = fm::obj();
	$return->value = $key;
	
	if (array_key_exists($key,$this->lang_string))
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		$return->value = vsprintf($this->lang_string[$key],$args);
	}

	return $return;
}