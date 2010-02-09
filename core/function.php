<?php 
if (!defined('FM_SECURITY')) die();

class fm
{
	public  $type;
	public  static  $core;
	public  static  $stdObj;

	function __call($name, $arguments)
	{
		if (is_a(fm::$core,'fm') && !array_key_exists($this->type,fm::$core->function))
			fm::$core->function[$this->type] = array();
		
		if(is_a(fm::$core,'fm') && !array_key_exists($name,fm::$core->function[$this->type]))
		{
			// find the good function 
			$function_prefix = array();
			$function_prefix[] = "site";
			$function_prefix[] = "all";
			foreach (fm::$core->extension as $extension=>$data)
				$function_prefix[] = $extension;
			
			$function_prefix[] = "core";
			
			foreach ($function_prefix as $prefix)
			{
				if (!array_key_exists($name,fm::$core->function[$this->type]))
					if (function_exists("{$prefix}_{$this->type}_method_{$name}"))
						fm::$core->function[$this->type][$name] = "{$prefix}_{$this->type}";
			}
			if (!array_key_exists($name,fm::$core->function[$this->type]))
			{
				foreach ($function_prefix as $prefix)
				{
					if (!array_key_exists($name,fm::$core->function[$this->type]))
						if (function_exists("{$prefix}_method_{$name}"))
							fm::$core->function[$this->type][$name] = "{$prefix}";
				}
			}
			
			if (!array_key_exists($name,fm::$core->function[$this->type]) && function_exists($name))
				fm::$core->function[$this->type][$name] = null; // set array_key_exist to true and strlen to 0
		}
		
		// do not clone the object on event
		$return = $this;
		
		// event trigger before and main
		if ($name!='event')
		{
			$return
				->event("{$this->type}_{$name}",'before')
				->event("{$this->type}_{$name}",'main')
				->save($return);
		}
		
		// function lancher
		if (is_a(fm::$core,'fm') && array_key_exists($name,fm::$core->function[$this->type]))
		{
			if (strlen(fm::$core->function[$this->type][$name])>0)
			{
				if (function_exists(fm::$core->function[$this->type][$name]."_method_$name"))
				{
					array_unshift($arguments,&$return);
					$tmp_return = call_user_func_array(fm::$core->function[$this->type][$name]."_method_$name",$arguments);
					
					if (!(is_a($tmp_return,'fm')))
					{
						if (!is_null($tmp_return))
							$return->value = $tmp_return;
					}
					else
						$return = $tmp_return;
				}
			}
			elseif (function_exists($name))
			{
				array_unshift($arguments,$return->value);
				
				$tmp_return = call_user_func_array($name,$arguments);
				
				if (!(is_a($tmp_return,'fm')))
					$return->value = $tmp_return;
				else		
					$return = $tmp_return;
			}
		} else
		{
			fm::$core->message['notice'][] = array(
				'message' => "function {$this->type}_{$name} not found",
				'date'    => microtime(true),
				'args'    => array('type'=>$this->type,'name'=>$name,),
			);
		}
		
		// event trigger after
		if ($name!='event')
			$return->event("{$this->type}_{$name}",'after')->save($return);
		
		// return
		return $return;	
	}
	
	function save(&$var)
	{
		$var = $this;
		return $this;
	}
}

function fm($value = null, $type = 'fm')
{
	if (!is_object(fm::$core))
	{
		fm::$core            = new fm();
		fm::$stdObj          = clone fm::$core;
		fm::$stdObj->value   = null;
		fm::$stdObj->type    = 'fm';
		fm::$core->class     = array('fm'=>array('class'=>fm::$stdObj));
		fm::$core->config    = array();
		fm::$core->extension = array();
		fm::$core->event     = array();
		fm::$core->function  = array();
		fm::$core->inclusion = array(FM_PATH_CORE.FM_FILE_COMPATIBILITY.FM_PHP_EXTENSION=>true,FM_PATH_CORE.FM_FILE_FUNCTION.FM_PHP_EXTENSION=>true);
		fm::$core->message   = array('message'=>array(),'debug'=>array(),'notice'=>array(),'error'=>array());
		fm::$core->type      = 'core';
		set_error_handler("fm_ErrorHandler");
		fm::$core
			->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$core->type.FM_PHP_EXTENSION)
			->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$core->type.FM_PHP_EXTENSION)
			->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$stdObj->type.FM_PHP_EXTENSION)
			->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$stdObj->type.FM_PHP_EXTENSION)
			->classConstruct()
			->classStart();
	} 

	if($value==null && $type == 'fm')
		return fm::$core;
	
	return fm::$core->class($type,$value);
}

function core_method_message($fm,$message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$core->message['message'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}

function core_method_debug($fm,$message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$core->message['debug'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}

function core_method_notice($fm,$message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$core->message['notice'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}

function core_method_error($fm,$message)
{
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	fm::$core->message['error'][] = array(
		'message' => $message,
		'date'    => microtime(true),
		'args'    => $args,
	);
}

function fm_ErrorHandler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
		case E_ERROR:
		case E_USER_ERROR:
				$errors = "Fatal Error";
				fm::$core->error($errstr,array('no'=>$errno,'type'=>$error,'file'=>$errfile,'line'=> $errline));
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
	
	fm::$core->notice($errstr,array('no'=>$errno,'type'=>$errors,'file'=>$errfile,'line'=> $errline));
	return false;
}

function core_method_class($fm, $class = 'fm')
{
	if (!array_key_exists($class,fm::$core->class))
	{
		$fm->message("Loading class $class ",$class);
		fm::$core->class[$class]['object'] = clone fm::$stdObj;
		fm::$core->class[$class]['object']->type = trim(strtolower($class));
		fm::$core
			->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$core->class[$class]['object']->type.FM_PHP_EXTENSION)
			->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$core->class[$class]['object']->type.FM_PHP_EXTENSION)
			->include(FM_SITE_DIR.FM_PATH_CLASS.fm::$core->class[$class]['object']->type.FM_PHP_EXTENSION);
		foreach(fm::$core->extension as $data)
		{
			fm::$core->include($data['path'].FM_PATH_CLASS.fm::$core->class[$class]['object']->type.FM_PHP_EXTENSION);
		}
		fm::$core->class[$class]['object']->classBoot();
		$fm->message("Class $class Loaded",$class);
	}
	
	$args = func_get_args();
	array_shift($args);
	array_shift($args);
	return fm::$core->class[$class]['object']->classConstruct()->__call('classStart',$args);
}

function core_method_extension($fm, $extension)
{	
	$extension = trim(strtolower($extension));
	if (!array_key_exists($extension,fm::$core->extension))
	{
		if (file_exists(FM_PATH_SITE_ALL.FM_PATH_EXTENSION."$extension/")||file_exists(FM_SITE_DIR.FM_PATH_EXTENSION."$extension/"))
		{
			$fm->message("Loading extension $extension ",$extension);
			fm::$core
				->include(FM_PATH_CORE.FM_PATH_CLASS.$extension.FM_PHP_EXTENSION)
				->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.$extension.FM_PHP_EXTENSION)
				->include(FM_SITE_DIR.FM_PATH_CLASS.$extension.FM_PHP_EXTENSION);
						
			if (file_exists(FM_SITE_DIR.FM_PATH_EXTENSION."$extension/"))
				$path = FM_SITE_DIR.FM_PATH_EXTENSION."$extension/";
			else
				$path = FM_PATH_SITE_ALL.FM_PATH_EXTENSION."$extension/";
			
			foreach(fm::$core->extension as $data)
			{
				fm::$core->include($data['path'].FM_PATH_CLASS.$extension.FM_PHP_EXTENSION);
			}
				
			foreach(fm::$core->class as $class=>$data)
			{
				fm::$core->include($path.FM_PATH_CLASS.$class.FM_PHP_EXTENSION);
			}
			
			fm::$core
				->include($path.FM_PATH_CLASS.$extension.FM_PHP_EXTENSION)
				->include($path.FM_FILE_FUNCTION.FM_PHP_EXTENSION);
			
			// load config
			if (file_exists($path.FM_FILE_CONFIG.FM_PHP_EXTENSION) && is_readable($path.FM_FILE_CONFIG.FM_PHP_EXTENSION))
			{
				$c = array();
				include $path.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				fm::$core->config = array_replace_recursive($c,fm::$core->config);
			}

						
			fm::$core->extension[$extension] = array('path'=>$path);
			$obj = clone fm::$stdObj;
			$obj->type = $extension;
			$obj->classBoot();
			
			// move extension to the begining of the fm::$core->extension array
			unset(fm::$core->extension[$extension]);
			fm::$core->extension = array_merge(array($extension=>array('object'=>$obj,'path'=>$path)),fm::$core->extension);
			
			fm::$core->extension[$extension]['object']
				->classConstruct()
				->classStart();
			$fm->message("Extension $extension Loaded",$extension);
		}
		else
		{
			$fm->error("Extension not found : ".$extension,$extension);
		}
	}elseif (!array_key_exists('object',fm::$core->extension[$extension]))
	{
		$fm->error("Try to charge an extension ( $extension ) in loading progress.",$extension);
	}
	
}

function core_method_include($fm,$file)
{
	$file = trim($file);
	if (!array_key_exists($file,fm::$core->inclusion))
	{
		fm::$core->inclusion[$file]=null;
		if (file_exists($file))
		{
			$tmp_f = get_defined_functions();
			include $file;
			fm::$core->inclusion[$file]=true;
			$tmp_f2 = get_defined_functions();
			if (count($tmp_f['user'])!=count($tmp_f2['user']))
				fm::$core->function = array();
		}
	}
}

function core_method_hook($fm,$event,$callback,$args = array(),$event_part = 'main')
{
	$event = trim(strtolower($event));
	if (!(array_key_exists($event,fm::$core->event) && is_array(fm::$core->event[$event])))
		fm::$core->event[$event] = array('before'=>array(),'main'=>array(),'after'=>array());
	$event_part = trim(strtolower($event_part));
	if (!(array_key_exists($event_part,fm::$core->event[$event]) && is_array(fm::$core->event[$event][$event_part])))
		fm::$core->event[$event][$event_part] = array();
	
	fm::$core->event[$event][$event_part][$callback] = $args;
}

function core_method_event($fm,$event,$event_part=null)
{
	$function_prefix = array();
	$function_prefix[] = "site";
	$function_prefix[] = "all";
	foreach (fm::$core->extension as $extension=>$data)
		$function_prefix[] = $extension;
	$function_prefix[] = "core";
	
	$event = trim(strtolower($event));
	
	if (array_key_exists($event,fm::$core->event) && is_array(fm::$core->event[$event]))
	{
		foreach (array('before','main','after') as $event_part_tmp)
		{
			if ((array_key_exists($event_part_tmp,fm::$core->event[$event])
				&& is_array(fm::$core->event[$event][$event_part_tmp]))
				&& ($event_part==null || $event_part==$event_part_tmp))
			{
				foreach (fm::$core->event[$event][$event_part_tmp] as $callback=>$arguments)
				{
					$is_find = false;
					foreach ($function_prefix as $prefix)
					{
						if (!$is_find)
						{
							if (function_exists("{$prefix}_event_callback_{$callback}"))
							{
								$is_find = true;
								array_unshift($arguments,$event);
								array_unshift($arguments,&$fm);
								$tmp_return = call_user_func_array("{$prefix}_event_callback_{$callback}",$arguments);
					
								if (!(is_a($tmp_return,'fm')))
								{
									if (!is_null($tmp_return))
										$fm->value = $tmp_return;
								}
								else
									$fm = $tmp_return;
							}
						}
					}
				}
			}
		}
	}
	
	return $fm;
}
