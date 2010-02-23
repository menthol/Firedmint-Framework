<?php 
if (!defined('FM_SECURITY')) die();

$c['l10n'] = array(
	'local' => 'en_US',
);

$c['route'] = array(
	'default_route'     => array('error','404'),
	'error_route'       => array('error'),
	'redirect_route'    => array('redirect'),
	'magic_route'       => false,
	'default_extension' => 'html',
	'show_extension'    => true,
);

$c['view'] = array(
	'cache'    => 1800,
	'template' => null,
);

$c['clear'] = array(
	'key' => 'fm_clear',
	'build' => 'build',
	'view' => 'view',
	'model' => 'model',
	'all' => 'all',
);

$c['site'] = array(
	'site-name' => 'Default Website',
	'charset' => 'UTF-8',
);