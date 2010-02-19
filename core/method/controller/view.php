<?php 
if (!defined('FM_SECURITY')) die();

function view($display_type,$view,$args,$cacheLifetime=null)
{
	$args += array('l10n'=>fm::$config['l10n']['local']);
	return view::factory($display_type,$view,$args,$cacheLifetime);
}