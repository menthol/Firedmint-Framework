<?php
if (!defined('FM_SECURITY')) die();

function uri($file = '', $arguments = array(),$decorators = array(),$argument_separator = '&amp;')
{
	$base_uri = (dirname($_SERVER['SCRIPT_NAME'])=='/'?null:dirname($_SERVER['SCRIPT_NAME'])).'/';
	$uri = null;
	
	if (strlen($file)>1 && $file[0]=='@')
	{
		$path = array();
		$path[] = FM_PATH_SITE.FM_SITE_DIR.substr($file,1);
		$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.substr($file,1);
		if (strlen(fm::$config['view']['template']))
		{
			if (is_dir(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template']))
				$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'.substr($file,1);
			else
				$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'.substr($file,1);
		}
		
		foreach(fm::$extension as $data)
		{
			$path[] = $data['path'].substr($file,1) ;
		}
		
		$path[] = FM_PATH_CORE.substr($file,1);
		
		foreach ($path as $file_tmp)
		{	
			if (file_exists($file_tmp))
			{
				$uri = $file_tmp;
				if (count($arguments)>0)
				{
					$uri .= '?'.http_build_query($arguments,'__',$argument_separator);
				}
				break;
			}
		}
	
		$return = fm::factory();
		$return->value = (strlen($uri)?$base_uri.$uri:null); 
		return $return;	
	}
	elseif (strlen($file)>0 && $file[0]==':')
	{
		$file_tmp = substr($file,1);
		list($void,$controller,$action) = explode('/',$file_tmp) + array(null,array_key_exists('controller',$arguments)?$arguments['controller']:$this->data['controller'],array_key_exists('action',$arguments)?$arguments['action']:$this->data['action']);
		
		$arguments['controller'] = $controller;
		$arguments['action'] = $action;
		$arguments += array('l10n'=>$this->data['l10n']);
		$uri = route::build($arguments,$decorators,$argument_separator)->substr(1)->value;

		$return = fm::factory();
		$return->value = $base_uri.$uri; 
		return $return;
		
	}
	elseif (strlen($file)>0)
	{
		if ($file[0]=='/')
		{
			$uri = substr($file,1);
		}
		else
		{
			$uri = $file;
		}
		$return = fm::factory();
		$return->value = $base_uri.$uri; 
		return $return;
	}
	else
	{
		$return = fm::factory();
		$return->value = $base_uri; 
		return $return;
	}
	
	
	
	
		

}