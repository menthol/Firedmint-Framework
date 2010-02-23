<?php 
if (!defined('FM_SECURITY')) die();
// $display_type,$view,$args,$cacheLifetime
static function factory($display_type,$view,$data,$cacheLifetime = null,$force_reload = false)
{
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view");
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}");
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}/".implode('_',$view)))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}/".implode('_',$view));
	
	if (array_key_exists('clear',fm::$config)
	 && array_key_exists(fm::$config['clear']['key'],$_GET)
	 && ($_GET[fm::$config['clear']['key']]==fm::$config['clear']['view']
	  || $_GET[fm::$config['clear']['key']]==fm::$config['clear']['all'])
	 )
	{
		$force_reload = true;
	}
	return new view($display_type,is_array($view)?$view:array($view),$data,is_null($cacheLifetime)?fm::$config['view']['cache']:$cacheLifetime,$force_reload);
	
	
	
	
	
	/*
	
	
	$this->cacheInclude('document',$args);	
	return $this;
	
	
	
	return new view();
	*/
}