<?php
if (!defined('FM_SECURITY')) die();

class phpL10n
{
	static $l10n    = array();
	
	function __load($lang)
	{
		if (_clear('l10n') || !is_array(phpL10n::$l10n[$lang] = cache::get('phpl10n',$lang)))
		{
			// compile l10n 
			if (!is_array($__l10n = cache::get('phpl10n','static_'.$lang)))
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
			$lang_config = config::$config['l10n']['default'];
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
			phpL10n::$l10n[$lang] = $__l10n;
			
			cache::set('phpl10n',$lang,$__l10n,config::$config['l10n']['cache_lifetime']);
		}
	}
	
	function get($lang,$key,$args = array())
	{
		if (!isset(phpL10n::$l10n[$lang]))
			$this->__load($lang);
		
		if (isset(phpL10n::$l10n[$lang][$key]))
			$value = phpL10n::$l10n[$lang][$key];
		elseif (preg_match('/^(\w+):(.*)/',$key,$matches))
		{
			if (isset(phpL10n::$l10n[$lang][$matches[2]]))
				$value = phpL10n::$l10n[$lang][$matches[2]];
			else
				$value = $matches[2];
		}
		else
		{
			$value = $key;
		}
			
		if (strlen($value)>=3 && $value[0]=='[' && $value[2]==']' && isset(config::$config['l10n']['parser'][$value[1]]))
			return l10n::parse(config::$config['l10n']['parser'][$value[1]],substr($value,3),$args);
		
		if (strlen($value)>=3 && $value[0]=='[' && $value[2]==']')
			return l10n::parse(config::$config['l10n']['parser']['default'],substr($value,3),$args);
		
		return l10n::parse(config::$config['l10n']['parser']['default'],$value,$args);
	}
}