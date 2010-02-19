<?php 
if (!defined('FM_SECURITY')) die();

function __construct($display_type,$view,$argument,$cache,$force_reload)
{
	$this->display_type = $display_type;
	$this->view = $view;
	$this->argument = $argument;
	$this->cache = $cache;
	$this->force_reload = $force_reload;
}
