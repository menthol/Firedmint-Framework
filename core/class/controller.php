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

function core_controller_method_classStart($fm,$controller,$action,$args)
{
	$args += array('l10n'=>fm::$config['l10n']['local']);
	$c_vars = array();
	// we don't take care about cache until is implemented
	fm::$core
		->find(FM_PATH_CONTROLLER.$controller)
		->include()
		->class("controller_{$controller}_{$action}",$fm)
			->preController($fm,$args,&$c_vars)
			->controller($fm,&$c_vars);
}

