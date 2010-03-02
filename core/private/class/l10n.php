<?php
if (!defined('FM_SECURITY')) die();

class l10n
{
	public  static  $o = array();
	
	public  static  $lang;
	
	static function factory()
	{
		if (!array_key_exists('o',l10n::$o) || !is_object(l10n::$o['o']))
		{
			l10n::$o['o'] = _subClass('l10n',config::$config['l10n']['engine']);
			l10n::$lang = config::$config['l10n']['default'];
		}
		
		return l10n::$o['o'];
	}
	
	static function get($lang,$key,$args = array())
	{
		if (array_key_exists('o',l10n::$o) || is_object(l10n::$o['o']))
			return l10n::$o['o']->get($lang,$key,$args);
		
		return $key;
	}
	
	static function parse($parser,$key,$args = array())
	{
		if (!array_key_exists($parser,l10n::$o) || !is_object(l10n::$o[$parser]))
			l10n::$o[$parser] = _subClass('l10n',$parser);
			
		l10n::$o[$parser]->parse($key,$args);
	}
}