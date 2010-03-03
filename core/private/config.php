<?php 
if (!defined('FM_SECURITY')) die();

$c['log'] = array(
	'hard_log'          => 'error|debug',
	'log_args'          => false,
	'path'              => FM_PATH_VAR.'private/', 
);

$c['cache'] = array(
	'value_engine'      => 'phpValueCache',
	'value_lifetime'    => 2629743,
	'front_engine'      => 'phpFrontCache',
	'front_lifetime'    => 1800,
	'file_engine'       => 'phpFileCache',
	'file_lifetime'     => 2629743,
	'static_engine'     => 'phpStaticCache',
	'var_private'       => FM_PATH_VAR.'private/',
	'var_public'        => FM_PATH_VAR.'public/',
	'static_private'    => FM_PATH_STATIC.'private/',
	'static_public'     => FM_PATH_STATIC.'public/',
);

$c['event'] = array(
	'cache_lifetime'    => 1800,
);


$c['clear'] = array(
	'key'               => '__clear',
	'acl'               => 'acl',
	'all'               => 'all',
	'config'            => 'config',
	'event'             => 'event',
	'front'             => 'front',
	'l10n'              => 'l10n',
	'route'             => 'route',
	'user'              => 'user',
	'value'             => 'value',
);

$c['user'] = array(
	'engine'            => 'phpUser',
	'cache_lifetime'	=> 20,
);

$c['auth'] = array(
	'engine'            => 'digestAuth',
	'max_idle_time'     => 1800,
);

$c['acl'] = array(
	'engine'            => 'phpAcl',
	'cache_lifetime'    => 1800,
);

$c['route'] = array(
	'engine'            => 'phpRoute',
	'cache_lifetime'    => 1800,
	'404_route'         => '__404',
	'magic_route'       => false,
	'default_extension' => 'html',
	'show_extension'    => false,
);

$c['l10n'] = array(
	'engine'            => 'phpl10n',
	'cache_lifetime'    => 1800,
	'default'           => 'en_US',
	'parser'            => array('%'=>'printfParser','default'=>'printfParser'),
);

$c['view'] = array(
	'cache_lifetime'    => 1800,
	'compilator'		=> array('php'=>'phpTemplate'),
	'template'          => null,
);

$c['header'] = array(
	'generator'         => 'Firedmint '.FM_VERSION,
	'page_compression'  => true,
);