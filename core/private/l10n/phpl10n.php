<?php
if (!defined('FM_SECURITY')) die();

class phpL10n
{
	static $l10n    = array();
	
	function load($lang)
	{
		if (_clear('l10n') || !is_array(phpL10n::$l10n[$lang] = cache::$value->get('phpl10n',$lang)))
		{
			// compile l10n 
			if (!is_array($__l10n = cache::$static->get('phpl10n','static_'.$lang)))
				$__l10n = array();
			
			foreach (_getPaths() as $path)
			{
				$_path = $path.FM_PATH_L10N.strtolower($lang).FM_PHP_EXTENSION;
				if (file_exists($_path))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}

				$_path = $path.FM_PATH_L10N.substr(strtolower($lang),0,strpos($lang,'_')).FM_PHP_EXTENSION;
				if (file_exists($_path))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}
			}
			$lang_config = kernel::$config['l10n']['default'];
			foreach (_getPaths() as $path)
			{
				$_path = $path.FM_PATH_L10N.strtolower($lang_config).FM_PHP_EXTENSION;
				if (file_exists($_path))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}

				$_path = $path.FM_PATH_L10N.substr(strtolower($lang_config),0,strpos($lang_config,'_')).FM_PHP_EXTENSION;
				if (file_exists($_path))
				{
					$l10n = array();
					include $_path;
					$__l10n += $l10n;
				}
			}
			phpL10n::$l10n[$lang] = $__l10n;
			cache::$value->set('phpl10n',$lang,$__l10n,kernel::$config['l10n']['cache_lifetime']);
		}
	}
	
	function get($lang,$key,$args = array())
	{
		if (!array_key_exists($lang,phpL10n::$l10n))
			$this->load($lang);
		
		$value = $key;
		if (array_key_exists($key,phpL10n::$l10n[$lang]))
			$value = phpL10n::$l10n[$lang][$key];
		
		if (strlen($value)>=3 && $value[0]=='[' && $value[2]==']' && array_key_exists($value[1],kernel::$config['l10n']['parser']))
			return call_user_func(array('l10n',kernel::$config['l10n']['parser'][$value[1]]),substr($value,3),$args);
		
		if (strlen($value)>=3 && $value[0]=='[' && $value[2]==']')
			return call_user_func(array('l10n',kernel::$config['l10n']['parser']['default']),substr($value,3),$args);
		
		return call_user_func(array('l10n',kernel::$config['l10n']['parser']['default']),$value,$args);
	}
}