<?php 
if (!defined('FM_SECURITY')) die();

function classConstruct()
{
	$tmp = clone $this;
	$tmp->o = $this;
	return $tmp;
}