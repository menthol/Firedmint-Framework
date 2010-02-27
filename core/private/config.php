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

$c['clear'] = array(
	'key'               => '__clear',
	'acl'               => 'acl',
	'all'               => 'all',
	'build'             => 'build',
	'config'            => 'config',
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
);

// --> l10n
$c['digestAuth'] = array(
	'realm'             => 'Restricted area',
);

$c['acl'] = array(
	'engine'            => 'phpAcl',
	'cache_lifetime'    => 1800,
);

$c['route'] = array(
	'engine'            => 'phpRoute',
	'cache_lifetime'    => 1800,
	'404_route'         => '404',
	'redirect_route'    => '__redirect',
	'magic_route'       => false,
	'default_extension' => 'html',
	'show_extension'    => false,
);

$c['l10n'] = array(
	'engine'            => 'phpl10n',
	'cache_lifetime'    => 1800,
	'default'           => 'en_US',
	'parser'            => array('@'=>'magicL10n','%'=>'printf',),
);











$c['view'] = array(
	'cache'             => 1800,
	'template'          => null,
);



$c['site'] = array(
	'site-name'         => 'Default Website',
	'charset'           => 'UTF-8',
);