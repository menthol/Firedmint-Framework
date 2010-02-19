<?php 
if (!defined('FM_SECURITY')) die();

$c['l10n'] = array(
	'local' => 'en_US',
);

$c['route'] = array(
	'default_route'  => array('error','404'),
	'error_route'    => array('error'),
	'redirect_route' => array('redirect'),
	'magic_route'    => false,
);

$c['view'] = array(
	'cache'    => 1800,
	'template' => null,
);

$c['build'] = array(
	'key' => 'rebuild',
);