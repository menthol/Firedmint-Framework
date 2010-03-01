<?php
if (!definied('FM_SECURITY')) die();

static function start($route)
{
	$view = _class('view');
	list($view->name,$status,$view->extension,$view->data,$view->environment) = $route;
	
	if (array_key_exists('l10n',$view->data))
	{
		$view->l10n = $view->data['l10n'];
		unset($view->data['l10n']);
	}
	else
		$view->l10n = kernel::$config['l10n']['default'];
	
	l10n::$lang = $view->l10n;
	$view->user = auth::getUser()->login;
	
	header::set('Status',$status);
	header::set('Content-Language',_l('xml-lang',null,true));
	
	$view->params = $view->environment['params'];
	unset($view->environment['params']);
	
	return $view->part('document');
}