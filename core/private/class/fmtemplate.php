<?php
if (!defined('FM_SECURITY')) die();

class FmTemplate
{
	public  static  $o = array();
	
	static function compil($engine,$template)
	{
		if (!isset(FmTemplate::$o[$engine]) || !is_object(FmTemplate::$o[$engine]))
			FmTemplate::$o[$engine] = _subClass('FmTemplate',$engine);
			
		return FmTemplate::$o[$engine]->compil($template);
	}
}