<?php 
if (!defined('FM_SECURITY')) die();

class emptyDriver
{
	private $config = array();
	
	function config($config)
	{
		$this->config = $config;
	}
	
	function __call()
	{
		header::setAlternateContent(view::start(array(config::$config['model']['die_route']) + route::$pageRoute),true);
		header::set('Status',500,true);
	}
}