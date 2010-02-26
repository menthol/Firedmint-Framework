<?php 
if (!defined('FM_SECURITY')) die();

$c['log'] = array(
	'hard_log'          => 'error|debug',
	'log_args'          => false,
	'path'              => FM_PATH_VAR.FM_PATH_PRIVATE, 
);

$c['cache'] = array(
	'value_engine'      => 'phpValueCache',
	'value_lifetime'    => 2629743,
	'front_engine'      => 'phpFrontCache',
	'front_lifetime'    => 1800,
	'file_engine'       => 'phpFileCache',
	'file_lifetime'     => 2629743,
	'static_engine'     => 'phpStaticCache',
	'var_private'       => FM_PATH_VAR.FM_PATH_PRIVATE,
	'var_public'        => FM_PATH_VAR.FM_PATH_PUBLIC,
	'static_private'    => FM_PATH_STATIC.FM_PATH_PRIVATE,
	'static_public'     => FM_PATH_STATIC.FM_PATH_PUBLIC,
);

$c['user'] = array(
	'engine'            => 'arrayUser',
	'cache_lifetime'	=> 20,
);

$c['auth'] = array(
	'engine'            => 'digestAuth',
);

$c['digestAuth'] = array(
	'realm'             => 'Restricted area',
);

$c['acl'] = array(
	'engine'            => 'phpAcl',
	'cache_lifetime'    => 1800,
);













$c['l10n'] = array(
	'local'             => 'en_US',
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
	'cache'             => 1800,
	'template'          => null,
);

$c['clear'] = array(
	'key'               => 'fm_clear',
	'build'             => 'build',
	'view'              => 'view',
	'model'             => 'model',
	'config'            => 'config',
	'all'               => 'all',
);

$c['site'] = array(
	'site-name'         => 'Default Website',
	'charset'           => 'UTF-8',
);