<?php 
if (!defined('FM_SECURITY')) die();

function findview($find)
{
	$path = array();
	foreach ($this->view as $view)
	{
		$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_VIEW."$view/$find.{$this->display_type}".FM_PHP_EXTENSION;
		$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_VIEW."$view/$find.{$this->display_type}".FM_PHP_EXTENSION;
		if (strlen(fm::$config['view']['template']))
		{
			if (is_dir(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template']))
				$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$view/$find.{$this->display_type}".FM_PHP_EXTENSION;
			else
				$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$view/$find.{$this->display_type}".FM_PHP_EXTENSION;
		}
		foreach(fm::$extension as $data)
		{
			$path[] = $data['path'].FM_PATH_VIEW."$view/$find.{$this->display_type}".FM_PHP_EXTENSION;
		}
		
	}
	
	$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_VIEW."$find.{$this->display_type}".FM_PHP_EXTENSION;
	$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_VIEW."$find.{$this->display_type}".FM_PHP_EXTENSION;
	if (strlen(fm::$config['view']['template']))
	{
		if (is_dir(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template']))
			$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$find.{$this->display_type}".FM_PHP_EXTENSION;
		else
			$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$find.{$this->display_type}".FM_PHP_EXTENSION;
	}
	foreach(fm::$extension as $data)
	{
		$path[] = $data['path'].FM_PATH_VIEW."$find.{$this->display_type}".FM_PHP_EXTENSION;
	}
	
	foreach ($this->view as $view)
	{
		$path[] = FM_PATH_CORE.FM_PATH_VIEW."$view/$find.{$this->display_type}".FM_PHP_EXTENSION;
	}
	
	$path[] = FM_PATH_CORE.FM_PATH_VIEW."$find.{$this->display_type}".FM_PHP_EXTENSION;
	
	$return = clone $this;
	foreach ($path as $file)
	{
		if (file_exists($file))
		{
			$return->value = $file;
			return $return;
		}
	}
	return $this;
}