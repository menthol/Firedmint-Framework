<?php
if (!defined('FM_SECURITY')) die();

function __call($name, $arguments)
{	
	$return = $this;
	
	$name = strtolower($name);
		
	if (function_exists($name))
	{
		array_unshift($arguments,$return->value);
		$function_name = $name;
		switch (count($arguments))
		{
			case 1 : $tmp_return = $function_name($arguments[0]); break;
			case 2 : $tmp_return = $function_name($arguments[0],$arguments[1]); break;
			case 3 : $tmp_return = $function_name($arguments[0],$arguments[1],$arguments[2]); break;
			default: $tmp_return = call_user_func_array($function_name,$arguments);
		}
		
		if (!(is_a($tmp_return,'fm')))
		{
			if (!is_null($tmp_return))
				$return->value = $tmp_return;
		}
		else
			$return = $tmp_return;	
	}
	elseif($name!='message')
	{
		fm::$message['notice'][] = array(
			'message' => "function {$name} not found",
			'date'    => microtime(true),
			'args'    => array('name'=>$name,),
		);
	}
	
	return $return;	
}