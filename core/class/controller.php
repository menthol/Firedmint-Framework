<?php 
if (!defined('FM_SECURITY')) die();

function core_controller_method_classBoot($fm)
{
	$fm->controller = 'default';
	$fm->l10n = fm::$config['l10n']['local'];
}

function core_controller_method_classConstruct($fm)
{
	return clone $fm;
}

function core_controller_method_classStart($fm,$controller=null,$action=null,$args=null)
{
	if ($controller!=null)
	{
		$args += array('l10n'=>fm::$config['l10n']['local']);
		
		$c_vars = array();
		// we don't take care about cache until is implemented
		fm::$core
			->find(FM_PATH_CONTROLLER.$controller)
			->include()
			->class("controller_{$controller}_{$action}")
				->preController($args,&$c_vars)
				->controller($controller,$action,$c_vars);
	}
}

function core_controller_method_preController($fm,$args,&$vars)
{
	$vars += $args;
}

function core_controller_method_view($fm,$display_type,$view,$args,$cacheLifetime=null)
{
	$args += array('l10n'=>fm::$config['l10n']['local']);
	return fm::$core->class('view',$display_type,$view,$args,$cacheLifetime);
}

function core_controller_method_controller($fm,$controller,$action,$vars)
{
	return $fm->view(strlen($vars['extension'])?$vars['extension']:'html',array("$controller.$action","$controller"),$vars);
}