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


define('FM_VERSION',             '0.3-svn');
define('FM_PHP_STARTFILE',       '<?php'.PHP_EOL.'if (!defined(\'FM_SECURITY\')) die();'.PHP_EOL);


// define root paths
define('FM_PATH_CORE',           'core/');
define('FM_PATH_SITE',           'site/');
define('FM_PATH_STATIC',         'static/');
define('FM_PATH_VAR',            'var/');

// boot includes
require_once FM_PATH_CORE.'private/compatibility.php';
require_once FM_PATH_CORE.'private/function.php';

// Firedmint Live sequance
$__content = _boot();
ob_end_clean();
FmHeader::send();
@ini_set('zlib.output_compression_level', 1);
if(!FmConfig::$config['header']['page_compression'] || !ob_start("ob_gzhandler"))
	ob_start();

if (FmHeader::$showContent)
	echo $__content;

ob_end_flush();
ob_start();
_shutdown();
ob_end_clean();