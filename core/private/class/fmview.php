<?php
if (!defined('FM_SECURITY')) die();

class FmView
{
	public  $name;
	
	public  $data;
	
	public  $environment;
	
	public  $extension;
	
	public  $l10n;
	
	public  $params = array();
	
	static function __set_state($values = array())
	{
		$view = _class('FmView');
		foreach ($values as $key=>$value)
			$view->{$key} = $value;
		
		return $view;
	}
	
	static function start($route)
	{
		$view = _class('FmView');
		list($view->name,$status,$view->extension,$view->data,$view->environment) = $route;
		
		if (isset($view->data['l10n']))
		{
			$view->l10n = $view->data['l10n'];
			unset($view->data['l10n']);
		}
		else
			$view->l10n = FmConfig::$config['l10n']['default'];
		
		FmL10n::$lang = $view->l10n;
		$view->user = FmAuth::getUser()->login;
		
		FmHeader::set('Status',$status);
		FmHeader::set('Content-Language',_l('xml-lang'));
		
		$view->params = $view->environment['params'];
		unset($view->environment['params']);
		
		return $view->select('document');
	}
	
	function part($__part,$__arguments = null)
	{
		echo '<?php echo $view->select('.var_export($__part,true).','.var_export($__arguments,true).'); ?>';
	}
	
	function virtual($__part,$__arguments = null)
	{
		echo '<?php echo $view->select('.var_export($__part,true).','.var_export($__arguments,true).','.var_export(true,true).'); ?>';
	}
	
	function select($__part,$__arguments = null,$virtual = false)
	{
		if (is_array($__arguments))
		{
			if (array_search('*',$__arguments)!==false)
			{
				$view = clone $this;
				unset($__arguments[array_search('*',$__arguments)]);
			}
			else
			{ 
				$view            = _class('FmView');
				$view->l10n      = $this->l10n;
				$view->name      = $this->name;
				$view->user      = $this->user;
				$view->cache     = $this->cache;
				if (isset($this->extension))
				$view->extension = $this->extension;
			}
			
			foreach ($__arguments as $key=>$value)
			{
				if (is_numeric($key))
				{
					if (isset($view->data[$value]))
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
		
		$view->content = null;
		
		if (!property_exists($view,'cache'))
			$view->cache = FmConfig::$config['view']['cache_lifetime'];
		
		if (is_string($__ = FmEvent::trigger('view:select',$__part,'before',$view))) return $__;
		
		$__paths = array();
		$__paths[] = FM_PATH_SITE.FM_SITE_DIR.'private/view/';
		$__paths[] = FM_PATH_SITE.'all/private/view/';
		foreach (array_keys(FmExtension::$extension) as $__ext)
			$__paths[] = FmConfig::$extension[$__ext]['path'].'private/view/';
		
		if (!empty(FmConfig::$config['view']['template']))
			if (is_dir(FM_PATH_SITE.FM_SITE_DIR.'template/'.FmConfig::$config['view']['template']))
				$__paths[] = FM_PATH_SITE.FM_SITE_DIR.'template/'.FmConfig::$config['view']['template'].'/private/view';
			else
				$__paths[] = FM_PATH_SITE.'all/template/'.FmConfig::$config['view']['template'].'/private/view';
		
		foreach($__paths as $__path)
		{
			$__filter   = null;
			if (file_exists("$__path$__part.$view->name.filter.php"))
				$__filter = "$__path$__part.$view->name.filter.php";
			elseif ($__part=='document' && file_exists("$__path$view->name.filter.php"))
				$__filter = "$__path$view->name.filter.php";
			
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
			$__filter   = null;
			if (file_exists($__filter = "$__path$__part.filter.php"))
			{
				$__reponse = include $__filter;
				if (is_string($__reponse))
					return $__reponse;
				
				break;
			}
		}
		
		$__template = null;
		$__compilator = null;
		if (!$virtual)
		{
			foreach($__paths as $__path)
			{
				foreach (FmConfig::$config['view']['compilator'] as $__ext=>$__engine)
				{
					if (is_null($__template) && file_exists("$__path$__part.$view->name.$__ext"))
					{
						$__template = "$__path$__part.$view->name.$__ext";
						$__compilator = $__engine; 
						break 2;
					}
					elseif (is_null($__template) && $__part=='document' && file_exists("$__path$view->name.$__ext"))
					{
						$__template = "$__path$view->name.$__ext";
						$__compilator = $__engine;
						break 2;
					}
				}
			}
		}
		
		if (is_null($__template))
		{
			foreach($__paths as $__path)
			{
				if (file_exists("$__path$__part.filter.php"))
				{
					$__reponse = include "$__path$__part.filter.php";
					
					if (is_string($__reponse))
						return $__reponse;
					
					break;
				}	
			}
			
			if (!$virtual)
			{
				foreach($__paths as $__path)
				{
					foreach (FmConfig::$config['view']['compilator'] as $__ext=>$__engine)
					{
						if (is_null($__template) && file_exists("$__path$__part.$__ext"))
						{
							$__template = "$__path$__part.$__ext";
							$__compilator = $__engine;
							break 2;
						}
					}
				}
			}
		}
		
		$__path = FM_PATH_CORE.'private/view/';
		if (is_null($__template))
		{
			$__filter   = null;
			if (file_exists("$__path$__part.$view->name.filter.php"))
				$__filter = "$__path$__part.$view->name.filter.php";
			elseif ($__part=='document' && file_exists("$__path$view->name.filter.php"))
				$__filter = "$__path$view->name.filter.php";
			
			if (!is_null($__filter))
			{
				$__reponse = include $__filter;
				if (is_string($__reponse))
					return $__reponse;
			}
			
			$__filter   = null;
			if (file_exists($__filter = "$__path$__part.filter.php"))
			{
				$__reponse = include $__filter;
				if (is_string($__reponse))
					return $__reponse;
			}
			
			if (!$virtual)
			{
				foreach (FmConfig::$config['view']['compilator'] as $__ext=>$__engine)
				{
					if (is_null($__template) && file_exists("$__path$__part.$view->name.$__ext"))
					{
						$__template = "$__path$__part.$view->name.$__ext";
						$__compilator = $__engine; 
						break;
					}
					elseif (is_null($__template) && $__part=='document' && file_exists("$__path$view->name.$__ext"))
					{
						$__template = "$__path$view->name.$__ext";
						$__compilator = $__engine;
						break;
					}
				}
			}
			
			if (is_null($__template))
			{
				if (file_exists("$__path$__part.filter.php"))
				{
					$__reponse = include "$__path$__part.filter.php";
					
					if (is_string($__reponse))
						return $__reponse;
				}

				if (!$virtual)
				{
					foreach (FmConfig::$config['view']['compilator'] as $__ext=>$__engine)
					{
						if (file_exists("$__path$__part.$__ext"))
						{
							$__template = "$__path$__part.$__ext";
							$__compilator = $__engine;
							break;
						}
					}
				}
			}
		}

		if (is_string($__ = FmEvent::trigger('view:select',$__part,'after',$view,$__template,$__compilator))) return $__;

		if (is_null($__template))
			return $view->content;

		return FmCache::getFront(FmTemplate::compil($__compilator,$__template),$view);
	}
}