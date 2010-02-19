<?php 
if (!defined('FM_SECURITY')) die();
// $display_type,$view,$args,$cacheLifetime
static function factory($display_type,$view,$args,$cacheLifetime = null,$force_reload = false)
{
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view");
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}");
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}/".implode('_',$view)))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$display_type}/".implode('_',$view));
	
	return new view($display_type,is_array($view)?$view:array($view),$args,is_null($cacheLifetime)?fm::$config['view']['cache']:$cacheLifetime,$force_reload);
	
	
	
	
	
	/*
	
	
	$this->cacheInclude('document',$args);	
	return $this;
	
	
	
	return new view();
	*/
}