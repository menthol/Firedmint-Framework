<?php 
if (!defined('FM_SECURITY')) die();

function preController($args,&$vars)
{
	$vars += $args;
	return $this;
}