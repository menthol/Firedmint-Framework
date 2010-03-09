<?php
if (!defined('FM_SECURITY')) die();

class FmL10n
{
	public  static  $o = array();
	
	public  static  $lang;
	
	static function factory()
	{
		if (!isset(FmL10n::$o['o']) || !is_object(FmL10n::$o['o']))
		{
			FmL10n::$o['o'] = _subClass('FmL10n',FmConfig::$config['l10n']['engine']);
			FmL10n::$lang = FmConfig::$config['l10n']['default'];
		}
		
		return FmL10n::$o['o'];
	}
	
	static function get($lang,$key,$args = array())
	{
		return FmL10n::$o['o']->get($lang,$key,$args);
	}
	
	static function parse($parser,$key,$args = array())
	{
		if (!isset(FmL10n::$o[$parser]) || !is_object(FmL10n::$o[$parser]))
			FmL10n::$o[$parser] = _subClass('FmL10n',$parser);
			
		return FmL10n::$o[$parser]->parse($key,$args);
	}
}