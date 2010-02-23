<?php 
if (!defined('FM_SECURITY')) die();

static function addCss($css,$media = 'all')
{
	$media = strtolower(trim($media));
	$css = trim($css);
	
	if (!array_key_exists($css,html::$css))
		html::$css[$css] = array();
	
	if (!array_key_exists('all',html::$css[$css]))
	{
		if (in_array($media,array('braille','embossed','handheld','print','projection','screen','speech','tty','tv')))
			html::$css[$css][$media] = true;
		else
			html::$css[$css] = array('all'=>true);
		
		if (count(html::$css[$css])==9)
			html::$css[$css] = array('all'=>true);
	}
}
