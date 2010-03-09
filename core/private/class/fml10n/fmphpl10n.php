<?php
if (!defined('FM_SECURITY')) die();

class FmPhpL10n
{
	static $l10n    = array();
	
	function __load($lang)
	{
		if (_clear('l10n') || !is_array(FmPhpL10n::$l10n[$lang] = FmCache::get('phpl10n',$lang)))
		{
			// compile l10n 
			if (!is_array($__l10n = FmCache::get('phpl10n','static_'.$lang)))
				$__l10n = array();
			
			foreach (_getPaths() as $path)
			{
				if (file_exists($_path = $path.'l10n/'.strtolower($lang).'.php'))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}

				if (file_exists($_path = $path.'l10n/'.substr(strtolower($lang),0,strpos($lang,'_')).'.php'))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}
			}
			$lang_config = FmConfig::$config['l10n']['default'];
			foreach (_getPaths() as $path)
			{
				if (file_exists($_path = $path.'private/l10n/'.strtolower($lang_config).'.php'))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}

				if (file_exists($_path = $path.'private/l10n/'.substr(strtolower($lang_config),0,strpos($lang_config,'_')).'.php'))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}
			}
			FmPhpL10n::$l10n[$lang] = $__l10n;
			
			FmCache::set('phpl10n',$lang,$__l10n,FmConfig::$config['l10n']['cache_lifetime']);
		}
	}
	
	function get($lang,$key,$args = array())
	{
		if (!isset(FmPhpL10n::$l10n[$lang]))
			$this->__load($lang);
		
		if (isset(FmPhpL10n::$l10n[$lang][$key]))
			$value = FmPhpL10n::$l10n[$lang][$key];
		elseif (preg_match('/^(\w+):(.*)/',$key,$matches))
		{
			if (isset(FmPhpL10n::$l10n[$lang][$matches[2]]))
				$value = FmPhpL10n::$l10n[$lang][$matches[2]];
			else
				$value = $matches[2];
		}
		else
		{
			$value = $key;
		}
			
		if (strlen($value)>=3 && $value[0]=='[' && $value[2]==']' && isset(FmConfig::$config['l10n']['parser'][$value[1]]))
			return FmL10n::parse(FmConfig::$config['l10n']['parser'][$value[1]],substr($value,3),$args);
		
		if (strlen($value)>=3 && $value[0]=='[' && $value[2]==']')
			return FmL10n::parse(FmConfig::$config['l10n']['parser']['default'],substr($value,3),$args);
		
		return FmL10n::parse(FmConfig::$config['l10n']['parser']['default'],$value,$args);
	}
}