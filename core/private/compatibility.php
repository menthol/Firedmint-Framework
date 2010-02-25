<?php 
if (!defined('FM_SECURITY')) die();

if (!function_exists('array_replace_recursive'))
{
	function array_replace_recursive() 
	{ 
	    $arrays = func_get_args(); 
	
	    $original = array_shift($arrays); 
	
	    foreach ($arrays as $array) 
	    { 
	        foreach ($array as $key => $value) 
	        { 
	            if (is_array($value)) 
	            { 
	                $original[$key] = array_replace_recursive($original[$key], $array[$key]); 
	            }
	            else 
	            { 
	                $original[$key] = $value; 
	            } 
	        } 
	    } 
	    return $original; 
	} 
}