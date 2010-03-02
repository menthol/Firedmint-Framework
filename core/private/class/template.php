<?php
if (!defined('FM_SECURITY')) die();

class template
{
	public  static  $o = array();
	
	static function compil($engine,$template)
	{
		if (!array_key_exists($engine,template::$o) || !is_object(template::$o[$engine]))
			template::$o[$engine] = _subClass('template',$engine);
			
		return template::$o[$engine]->compil($template);
	}
}