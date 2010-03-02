<?php
if (!defined('FM_SECURITY')) die();

class log
{
	public  static  $message = array('message'=>array(),'debug'=>array(),'notice'=>array(),'error'=>array());
	
	static function factory()
	{
		set_error_handler(array('log','errorHandler'));
	}
	
	static function errorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
					$errors = "Fatal Error";
					log::error($errstr,array('no'=>$errno,'type'=>$error,'file'=>$errfile,'line'=> $errline));
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
		log::notice($errstr,array('no'=>$errno,'type'=>$errors,'file'=>$errfile,'line'=> $errline));
		return false;
	}
	
	static function add($type,$message,$arguments = array())
	{
		log::$message[$type][] = array(
			'message' => $message,
			'date'    => microtime(true),
			'args'    => $arguments,
		);
		if (preg_match('/^('.config::$config['log']['hard_log'].')$/',$type))
		{
			$logfile = config::$config['log']['path'].'log/'.substr(FM_SITE_DIR,0,-1).'_'.date('Y-m').'.log';
			_createDir($logfile);
			error_log(date('[d-m-Y h:i:s]')."[$type]["._ip()."] $message".PHP_EOL.(config::$config['log']['log_args']?serialize($arguments).PHP_EOL:null), 3, $logfile);
		}
	}
	
	static function debug($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		log::add('debug',$message,$args);
	}
	
	static function error($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		log::add('error',$message,$args);
	}
	
	static function message($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		log::add('message',$message,$args);
	}
	
	static function notice($message)
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (count($args)==1)
			$args = array_shift($args);
		
		log::add('notice',$message,$args);
	}
}