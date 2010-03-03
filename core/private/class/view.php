<?php
if (!defined('FM_SECURITY')) die();

class view
{
	public  $name;
	
	public  $data;
	
	public  $environment;
	
	public  $extension;
	
	public  $l10n;
	
	public  $params = array();
	
	static function __set_state($values = array())
	{
		$view = _class('view');
		foreach ($values as $key=>$value)
			$view->{$key} = $value;
		
		return $view;
	}
	
	static function start($route)
	{
		$view = _class('view');
		list($view->name,$status,$view->extension,$view->data,$view->environment) = $route;
		
		if (array_key_exists('l10n',$view->data))
		{
			$view->l10n = $view->data['l10n'];
			unset($view->data['l10n']);
		}
		else
			$view->l10n = config::$config['l10n']['default'];
		
		l10n::$lang = $view->l10n;
		$view->user = auth::getUser()->login;
		
		header::set('Status',$status);
		header::set('Content-Language',_l('xml-lang',null,true));
		
		$view->params = $view->environment['params'];
		unset($view->environment['params']);
		
		return $view->part('document');
	}
	
	function part($__part,$__arguments = null)
	{
		if (is_array($__arguments))
		{
			$view            = _class('view');
			$view->l10n      = $this->l10n;
			$view->name      = $this->name;
			$view->user      = $this->user;
			$view->extension = $this->extension;
			
			foreach ($__arguments as $key=>$value)
			{
				if (is_numeric($key))
				{
					if (array_key_exists($value,$this->data))
						$view->data[$value] = $this->data[$value];
					elseif (property_exists($this,$value))
						$view->{$value} = $this->{$value};
					else
						$view->data[$value] = null;
				}
				else
				{
					if (property_exists($this,$key) || property_exists($view,$key))
						$view->{$key} = $value;
					else
						$view->data[$key] = $value;
				}
			}
		}
		else
			$view = clone $this;
		
		if (!property_exists($view,'cache'))
			$view->cache = config::$config['view']['cache_lifetime'];
		
		$__paths = array();
		$__paths[] = FM_PATH_SITE.FM_SITE_DIR.'private/view/';
		$__paths[] = FM_PATH_SITE.'all/private/view/';
		foreach (array_keys(extension::$extension) as $__ext)
			$__paths[] = config::$extension[$__ext]['path'].'private/view/';
		
		if (!empty(config::$config['view']['template']))
			if (is_dir(FM_PATH_SITE.FM_SITE_DIR.'template/'.config::$config['view']['template']))
				$__paths[] = FM_PATH_SITE.FM_SITE_DIR.'template/'.config::$config['view']['template'].'/private/view';
			else
				$__paths[] = FM_PATH_SITE.'all/template/'.config::$config['view']['template'].'/private/view';
			
		$__paths[] = FM_PATH_CORE.'private/view';
		
		$__loop     = 0;
		$__filter   = null;
		$__reponse  = null;
		foreach($__paths as $__path)
		{
			if (file_exists("$__path$__part.$view->name.filter".FM_PHP_EXTENSION))
				$__filter = "$__path$__part.$view->name.filter".FM_PHP_EXTENSION;
			elseif ($__part=='document' && file_exists("$__path$view->name.filter".FM_PHP_EXTENSION))
				$__filter = "$__path$view->name.filter".FM_PHP_EXTENSION;
			
			if (!is_null($__filter))
			{
				$__reponse = include $__filter;
				if (is_string($__reponse))
					return $__reponse;
				
				break;
			}
		}
		
		foreach($__paths as $__path)
		{
			if (file_exists("$__path$__part.$view->name.filter".FM_PHP_EXTENSION))
				$__filter = "$__path$__part.$view->name.filter".FM_PHP_EXTENSION;
			elseif ($__part=='document' && file_exists("$__path$view->name.filter".FM_PHP_EXTENSION))
				$__filter = "$__path$view->name.filter".FM_PHP_EXTENSION;
			
			if (!is_null($__filter))
			{
				$__reponse = include $__filter;
				if (is_string($__reponse))
					return $__reponse;
				
				break;
			}
		}
		
		$__template = null;
		foreach($__paths as $__path)
		{
			foreach (config::$config['view']['compilator'] as $__ext=>$__engine)
			{
				if (file_exists("$__path$__part.$view->name.$__ext"))
				{
					$__template = "$__path$__part.$view->name.$__ext";
					$__compilator = $__engine; 
					break 2;
				}
				elseif ($__part=='document' && file_exists("$__path$view->name.$__ext"))
				{
					$__template = "$__path$view->name.$__ext";
					$__compilator = $__engine;
					break 2;
				}
			}
		}
		
		if (is_null($__template))
		{
			foreach($__paths as $__path)
			{
				if (file_exists("$__path$__part.filter".FM_PHP_EXTENSION))
				{
					$__reponse = include "$__path$__part.filter".FM_PHP_EXTENSION;
					
					if (is_string($__reponse))
						return $__reponse;
					
					break;
				}	
			}
			
			foreach($__paths as $__path)
			{
				foreach (config::$config['view']['compilator'] as $__ext=>$__engine)
				{
					if (file_exists("$__path$__part.$__ext"))
					{
						$__template = "$__path$__part.$__ext";
						$__compilator = $__engine;
						break 2;
					}
				}
			}
		}
		
		if (is_null($__template))
			return;
		
		return cache::getFront(template::compil($__compilator,$__template),$view);
	}
}