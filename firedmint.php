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
error_reporting(0);
ini_set('display_errors',0);

// define default primary paths
define('FM_PATH_VAR',            'var/');
define('FM_PATH_VAR_PRIVATE',    FM_PATH_VAR.'private/');
define('FM_PATH_VAR_PUBLIC',     FM_PATH_VAR.'public/');
define('FM_PATH_VAR_LOG',        FM_PATH_VAR.'log/');
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

// define the firedmint class
// the firedmint class alow to use 
// Chained PHP
class fm_chaine
{
	public $value;
	
	public function __construct($value = null)
	{
		$this->value = $value;
	}
	
	public function __call($name, $arguments) {
		if (function_exists($name))
		{
			array_unshift($arguments,$this->value);

			$return = call_user_func_array($name,$arguments);
			if ($return)
				$this->value = $return;
		}
		//echo "Appel de la méthode '$name' ".implode(', ', $arguments). "\n";
		return $this;
	}
}

// primary firedmint function
function fm($value = null)
{
	return new fm_chaine($value);
}

print fm('md5')->hash('cool')->value.'<br />'.md5('cool');


print "<br /><br /><br /><br />".(microtime(1)-FM_START_TIME).' secondes';