<?php
if (!defined('FM_SECURITY')) die();

function save(&$var)
{
	$var = $this;
	return $this;
}