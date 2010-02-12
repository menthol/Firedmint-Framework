<?php 
if (!defined('FM_SECURITY')) die();

function core_controller_method_classBoot($fm)
{
	$fm->controller = fm::$config['controller']['default'];
	$fm->l10n = fm::$config['l10n']['local'];
}

function core_controller_method_classConstruct($fm)
{
	return clone $fm;
}

function core_controller_method_classStart($fm,$controller,$l10n,$args)
{
	$c_vars = array();
	fm::$core
		->find(FM_PATH_CONTROLLER.$controller)
		->include()
		->class("controller_$controller",$fm,$controller,$l10n,$args)
			->preController($fm,$controller,$l10n,$args,&$c_vars);
}

