<?php 
if (!defined('FM_SECURITY')) die();

function __construct($display_type,$view,$data,$cache,$force_reload)
{
	$this->display_type = $display_type;
	$this->view = $view;
	$this->data = $data;
	$this->cache = $cache;
	$this->force_reload = $force_reload;
}
