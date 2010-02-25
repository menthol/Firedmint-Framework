<?php
if (!defined('FM_SECURITY')) die();

static function add($type,$message,$arguments = array())
{
	log::$message[$type][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $arguments,
	);
	if (preg_match('/^('.kernel::$config['log']['hard_log'].')$/',$type))
	{
		$logfile = kernel::$config['log']['path'].substr(FM_SITE_DIR,0,-1).'_'.date('Y-m').'.log';
		_createDir($logfile);
		error_log(date('[d-m-Y h:i:s]')."[$type] $message".PHP_EOL.(kernel::$config['log']['log_args']?serialize($arguments).PHP_EOL:null), 3, $logfile);
	}
}
