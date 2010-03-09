<?php
if (!defined('FM_SECURITY')) die();

class FmLog
{
	public  static  $message = array('message'=>array(),'debug'=>array(),'notice'=>array(),'error'=>array());
	
	static function factory()
	{
		set_error_handler(array('FmLog','errorHandler'));
	}
	
	static function errorHandler($errno, $errstr, $errfile, $errline) {
		$errstr = "$errstr in $errfile on line $errline";
		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
					$errors = "Fatal Error";
					FmLog::error($errstr,array('no'=>$errno,'type'=>$error,'file'=>$errfile,'line'=> $errline));
					return false;
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
					$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
					$errors = "Warning";
				break;
	
			default:
					$errors = "Unknown";
			break;
		}
		FmLog::notice($errstr,array('no'=>$errno,'type'=>$errors,'file'=>$errfile,'line'=> $errline));
		return false;
	}
	
	static function add($type,$message,$arguments = array())
	{
		FmLog::$message[$type][] = array(
			'message' => $message,
			'date'    => microtime(true),
			'args'    => $arguments,
		);
		if (preg_match('/^('.FmConfig::$config['log']['hard_log'].')$/',$type))
		{
			$logfile = FmConfig::$config['log']['path'].'log/'.substr(FM_SITE_DIR,0,-1).'_'.date('Y-m-d').'.log';
			_createDir($logfile);
			error_log(date('[d-m-Y H:i:s]')."[$type]["._ip()."] $message".PHP_EOL.(FmConfig::$config['log']['log_args']?serialize($arguments).PHP_EOL:null), 3, $logfile);
		}
	}
	
	static function debug($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		FmLog::add('debug',$message,$args);
	}
	
	static function error($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		FmLog::add('error',$message,$args);
	}
	
	static function message($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		FmLog::add('message',$message,$args);
	}
	
	static function notice($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		FmLog::add('notice',$message,$args);
	}
}