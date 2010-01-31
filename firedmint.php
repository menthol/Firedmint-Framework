<?php 
// security check
if (defined('FM_SECURITY'))
{
	header('HTTP/1.1 500 Internal Server Error');
	print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';
	die();
}

@define('FM_SECURITY',true);

@define('FM_START_TIME',microtime());

// don't show errors
error_reporting(0);
ini_set('display_errors',0);

// define default primary paths
define('FM_PATH_VARS',           'vars/');
define('FM_PATH_VARS_PRIVATE',   FM_PATH_VARS.'private/');
define('FM_PATH_VARS_PUBLIC',    FM_PATH_VARS.'public/');
define('FM_PATH_VARS_LOG',       FM_PATH_VARS.'logs/');
define('FM_PATH_CORE',           'core/');
define('FM_PATH_SITE',           'site/');
define('FM_PATH_SITE_ALL',       FM_PATH_SITE.'all/');
define('FM_PATH_SITE_DEFAULT',   FM_PATH_SITE.'default/');

// define default secondary paths
define('FM_PATH_ACTION',         'action/');
define('FM_PATH_AUTH',           'auth/');
define('FM_PATH_CLASS',          'class/');
define('FM_PATH_CONFIG',         'config/');
define('FM_PATH_CONTROLLER',     'controller/');
define('FM_PATH_DATA',           'data/');
define('FM_PATH_ELEMENT',        'element/');
define('FM_PATH_EXTENSION',      'extension/');
define('FM_PATH_FORMS',          'forms/');
define('FM_PATH_FUCTION',        'function/');
define('FM_PATH_L10N',           'l10n/');
define('FM_PATH_LOG',            'log/');
define('FM_PATH_METHOD',         'method/');
define('FM_PATH_MODEL',          'model/');
define('FM_PATH_PUBLIC',         'public/');
define('FM_PATH_SERVICE',        'service/');
define('FM_PATH_TASK',           'task/');
define('FM_PATH_TEMPLATE',       'template/');
define('FM_PATH_URL',            'url/');
define('FM_PATH_VIEW',           'view/');

// others constants
define('FM_PHP_EXTENSION','.php');


