<?php 
if (!defined('FM_SECURITY')) die();

// Need to do my function
if (!function_exists('array_replace_recursive'))
{
	function array_replace_recursive($array, $array1)
	{
		if (!function_exists('recurse'))
		{
			function recurse($array, $array1)
			{
				foreach ($array1 as $key => $value)
				{
					if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])))
					{
						$array[$key] = array();
					}
			
					if (is_array($value))
					{
						$value = recurse($array[$key], $value);
					}
					$array[$key] = $value;
				}
				return $array;
			}
		}
	
		$args = func_get_args();
		$array = $args[0];
		if (!is_array($array))
		{
			return $array;
		}
		for ($i = 1; $i < count($args); $i++)
		{
			if (is_array($args[$i]))
			{
				$array = recurse($array, $args[$i]);
			}
		}
		return $array;
	}
}