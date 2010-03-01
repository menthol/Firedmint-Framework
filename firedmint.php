<?php 
// security check
if (defined('FM_SECURITY'))
{
	header('HTTP/1.1 500 Internal Server Error');
	print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';
	die();
}

@define('FM_SECURITY',           true);

@define('FM_START_TIME',         microtime(true));

// don't show errors
error_reporting(E_ALL);
ini_set('display_errors',1);
ob_start();


define('FM_VERSION',             '0.2-svn');
define('FM_PHP_STARTFILE',       '<?php'.PHP_EOL.'if (!defined(\'FM_SECURITY\')) die();'.PHP_EOL);
define('FM_PHP_EXTENSION',       '.php');


// define root paths
define('FM_PATH_CORE',           'core/');
define('FM_PATH_SITE',           'site/');
define('FM_PATH_STATIC',         'static/');
define('FM_PATH_VAR',            'var/');

// define secondary paths
define('FM_PATH_BUILDER',        'builder/');
define('FM_PATH_PRIVATE',        'private/');
define('FM_PATH_PUBLIC',         'public/');
define('FM_PATH_BUILD',          'build/'); // /var/build

// public 
define('FM_PATH_CSS',            'css/');
define('FM_PATH_FLAG',           'flag/');
define('FM_PATH_ICON',           'icon/');
define('FM_PATH_IMG',            'img/');
define('FM_PATH_JS',             'js/');
define('FM_PATH_MEDIA',          'media/');

// private 
define('FM_PATH_AUTH',           'auth/');
define('FM_PATH_CACHE',          'cache/');
define('FM_PATH_COMPONENT',      'component/');
define('FM_PATH_DB',             'db/');
define('FM_PATH_DRIVER',         'driver/');
define('FM_PATH_ELEMENT',        'element/');
define('FM_PATH_FORM',           'form/');
define('FM_PATH_HELPER',         'helper/');
define('FM_PATH_KERNEL',         'kernel/');
define('FM_PATH_L10N',           'l10n/');
define('FM_PATH_LOG',            'log/');
define('FM_PATH_MODEL',          'model/');
define('FM_PATH_TASK',           'task/');
define('FM_PATH_VIEW',           'view/');

// site 
define('FM_PATH_ALL',            'all/');
define('FM_PATH_DEFAULT',        'default/');
define('FM_PATH_EXTENSION',      'extension/');
define('FM_PATH_TEMPLATE',       'template/');

// define secondary paths (files)
define('FM_FILE_ACL',            'acl');
define('FM_FILE_COMPATIBILITY',  'compatibility');
define('FM_FILE_CONFIG',         'config');
define('FM_FILE_EXTENSION',      'extension');
define('FM_FILE_FUNCTION',       'function');
define('FM_FILE_ROUTE',          'route');
define('FM_FILE_USER',           'user');

// others constants
define('FM_BUILD_KEY',           sha1($_SERVER['SERVER_NAME'].$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME']));

// boot includes
require_once FM_PATH_CORE.FM_PATH_PRIVATE.FM_FILE_COMPATIBILITY.FM_PHP_EXTENSION;
require_once FM_PATH_CORE.FM_PATH_PRIVATE.FM_FILE_FUNCTION.FM_PHP_EXTENSION;

// Firedmint Live sequance
$__content = _boot();
ob_end_clean();
header::send($__content);
echo $__content;
ob_start();
_shutdown();
ob_end_clean();