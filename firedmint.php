<?php 
// security check
if (defined('FM_SECURITY'))
{
	header('HTTP/1.1 500 Internal Server Error');
	print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';
	die();
}

@define('FM_SECURITY',true);

@define('FM_START_TIME',microtime(true));

// don't show errors
error_reporting(E_ALL);
ini_set('display_errors',1);
ob_start();

// define default primary paths
define('FM_PATH_VAR',            'var/');
define('FM_PATH_VAR_PRIVATE',    FM_PATH_VAR.'private/');
define('FM_PATH_VAR_PUBLIC',     FM_PATH_VAR.'public/');
define('FM_PATH_VAR_LOG',        FM_PATH_VAR.'log/');
define('FM_PATH_STATIC',         'static/');
define('FM_PATH_STATIC_PRIVATE', FM_PATH_STATIC.'private/');
define('FM_PATH_STATIC_PUBLIC',  FM_PATH_STATIC.'public/');
define('FM_PATH_CORE',           'core/');
define('FM_PATH_SITE',           'site/');
define('FM_PATH_SITE_ALL',       'all/');
define('FM_PATH_SITE_DEFAULT',   'default/');

// define default secondary paths (directories)
define('FM_PATH_AUTH',           'auth/');
define('FM_PATH_CLASS',          'class/');
define('FM_PATH_COMPONENT',      'component/');
define('FM_PATH_CONTROLLER',     'controller/');
define('FM_PATH_DB',             'db/');
define('FM_PATH_ELEMENT',        'element/');
define('FM_PATH_EXTENSION',      'extension/');
define('FM_PATH_FORM',           'form/');
define('FM_PATH_L10N',           'l10n/');
define('FM_PATH_LOG',            'log/');
define('FM_PATH_MODEL',          'model/');
define('FM_PATH_PUBLIC',         'public/');
define('FM_PATH_TASK',           'task/');
define('FM_PATH_TEMPLATE',       'template/');
define('FM_PATH_VIEW',           'view/');

// define default secondary paths (files)
define('FM_FILE_COMPATIBILITY',  'compatibility');
define('FM_FILE_CONFIG',         'config');
define('FM_FILE_EXTENSION',      'extension');
define('FM_FILE_FUNCTION',       'function');
define('FM_FILE_ROUTE',          'route');

// others constants
define('FM_PHP_EXTENSION','.php');

// boot includes
require_once FM_PATH_CORE.FM_FILE_COMPATIBILITY.FM_PHP_EXTENSION;
require_once FM_PATH_CORE.FM_FILE_FUNCTION.FM_PHP_EXTENSION;

// boot Firedmint
fm();
