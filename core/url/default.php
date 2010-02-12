<?php 
if (!defined('FM_SECURITY')) die();

function core_url_method_getController($fm)
{
	$controller = 'default';
	
	if (array_key_exists('fm_controller',$_GET) && strlen($_GET['fm_controller'])>0)
		$controller = trim($_GET['fm_controller']);
	
	$l10n = fm::$config['l10n']['local'];	
	
	if (array_key_exists('fm_l10n',$_GET) && strlen($_GET['fm_l10n'])>0)
		$l10n = trim($_GET['fm_l10n']);
	
	return fm::$core->class('controller',$controller,$l10n,$_GET);
}