<?php 
if (!defined('FM_SECURITY')) die();

$c['log'] = array(
	'hard_log'             => 'error|debug',
	'log_args'             => false,
	'path'                 => FM_PATH_VAR.'private/', 
);

$c['cache'] = array(
	'value_engine'         => 'FmPhpValueCache',
	'value_lifetime'       => 2629743,
	'front_engine'         => 'FmPhpFrontCache',
	'front_lifetime'       => 1800,
	'file_engine'          => 'FmPhpFileCache',
	'file_lifetime'        => 2629743,
	'static_engine'        => 'FmPhpStaticCache',
	'var_private'          => FM_PATH_VAR.'private/',
	'var_public'           => FM_PATH_VAR.'public/',
	'static_private'       => FM_PATH_STATIC.'private/',
	'static_public'        => FM_PATH_STATIC.'public/',
);

$c['event'] = array(
	'cache_lifetime'       => 1800,
);


$c['clear'] = array(
	'key'                  => '__clear',
	'acl'                  => 'acl',
	'all'                  => 'all',
	'config'               => 'config',
	'event'                => 'event',
	'front'                => 'front',
	'l10n'                 => 'l10n',
	'route'                => 'route',
	'user'                 => 'user',
	'value'                => 'value',
);

$c['user'] = array(
	'engine'               => 'FmPhpUser',
	'cache_lifetime'       => 20,
);

$c['auth'] = array(
	'engine'               => 'FmDigestAuth',
	'max_idle_time'        => 1800,
	'fail_login_route'     => '__403',
);

$c['acl'] = array(
	'engine'               => 'FmPhpAcl',
	'cache_lifetime'       => 1800,
);

$c['route'] = array(
	'engine'               => 'FmPhpRoute',
	'cache_lifetime'       => 1800,
	'404_route'            => '__404',
	'magic_route'          => false,
	'magic_view'           => '.*',
	'default_extension'    => 'html',
	'show_extension'       => false,
);

$c['l10n'] = array(
	'engine'               => 'FmPhpl10n',
	'cache_lifetime'       => 1800,
	'default'              => 'en_US',
	'parser'               => array('%'=>'FmPrintfParser','default'=>'FmPrintfParser'),
);

$c['view'] = array(
	'cache_lifetime'       => 1800,
	'compilator'           => array('php'=>'FmPhpTemplate'),
	'template'             => null,
);

$c['header'] = array(
	'generator'            => 'Firedmint '.FM_VERSION,
	'page_compression'     => true,
);

$c['html'] = array(
	'js_autoload'          => array('jquery'=>'jquery.js'),
	'css_autoload'         => array('core'=>'core.css','template'=>'template.css','style'=>'style.css'),
);

$c['form'] = array(
	'form_lifetime'       => 1800, 
);