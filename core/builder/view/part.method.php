<?php
if (!definied('FM_SECURITY')) die();

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
		$view->cache = kernel::$config['view']['cache_lifetime'];
	
	$__paths = array();
	$__paths[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_PRIVATE.FM_PATH_VIEW;
	$__paths[] = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_PRIVATE.FM_PATH_VIEW;
	foreach (array_keys(kernel::$extension) as $__ext)
		$__paths[] = kernel::$extension[$__ext]['path'].FM_PATH_PRIVATE.FM_PATH_VIEW;
	
	if (!empty(kernel::$config['view']['template']))
		if (is_dir(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.kernel::$config['view']['template']))
			$__paths[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.kernel::$config['view']['template'].'/'.FM_PATH_PRIVATE.FM_PATH_VIEW;
		else
			$__paths[] = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_TEMPLATE.kernel::$config['view']['template'].'/'.FM_PATH_PRIVATE.FM_PATH_VIEW;
		
	$__paths[] = FM_PATH_CORE.FM_PATH_PRIVATE.FM_PATH_VIEW;
	
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
	
	foreach($__paths as $__path)
	{
		foreach (kernel::$config['view']['compilator'] as $__ext=>$__engine)
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
			foreach (kernel::$config['view']['compilator'] as $__ext=>$__engine)
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
	
	return cache::$front->get(template::compil($__compilator,$__template),$view);
}