<?php 
if (!defined('FM_SECURITY')) die();

function __construct($controller,$action,$args=array())
{
	$args += array('l10n'=>fm::$config['l10n']['local']);
	$this->controller = $controller;
	$this->action = $action;
	$this->arguments = $args + array('controller'=>$controller,'action'=>$action);
}
