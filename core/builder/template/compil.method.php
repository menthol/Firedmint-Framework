<?php
if (!definied('FM_SECURITY')) die();

static function compil($engine,$template)
{
	if (!array_key_exists($engine,template::$o) || !is_object(template::$o[$engine]))
		template::$o[$engine] = _subClass('template',$engine);
		
	return template::$o[$engine]->compil($template);
}