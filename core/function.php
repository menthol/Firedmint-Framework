<?php 
if (!defined('FM_SECURITY')) die();

class fm
{
	public  $type;
	public  static  $core;
	public  static  $config;
	public  static  $stdObj;

	function __call($name, $arguments)
	{
		if (!array_key_exists($this->type,fm::$core->function))
			fm::$core->function[$this->type] = array();
		
		if (!array_key_exists('all',fm::$core->function))
			fm::$core->function['all'] = array();
		
		$return = $this;
		
		$name = strtolower($name);
		
		
		// event trigger before and main
		if ($name!='event')
		{
			$return
				->event("{$this->type}_{$name}",'before')
				->event("{$this->type}_{$name}",'main')
				->save($return);
		}
		
		// method lancher
		if (array_key_exists($name,fm::$core->function[$this->type]))
		{
			array_unshift($arguments,&$return);
			$tmp_return = call_user_func_array(fm::$core->function[$this->type][$name],$arguments);
			
			if (!(is_a($tmp_return,'fm')))
			{
				if (!is_null($tmp_return))
					$return->value = $tmp_return;
			}
			else
				$return = $tmp_return;
		}
		elseif (array_key_exists($name,fm::$core->function['all']))
		{
			array_unshift($arguments,&$return);
			$tmp_return = call_user_func_array(fm::$core->function['all'][$name],$arguments);
			
			if (!(is_a($tmp_return,'fm')))
			{
				if (!is_null($tmp_return))
					$return->value = $tmp_return;
			}
			else
				$return = $tmp_return;
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
		elseif($name!='message')
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
	
	function registerMethods()
	{
		$declared_function = get_defined_functions();
		$registrable_function = array_diff($declared_function['user'],fm::$core->ufunction);
		fm::$core->ufunction = $declared_function['user'];
		
		if (count($registrable_function))
		{
			$function_prefix = array();
			$function_prefix[] = "site";
			$function_prefix[] = "all";
			foreach (fm::$core->extension as $extension=>$data)
				$function_prefix[] = strtolower($extension);
			
			$function_prefix[] = "core";
			$prefix_weight = array_flip($function_prefix);
		
			foreach ($registrable_function as $function)
			{
				$matches = array();
				$function = strtolower($function);
				if (preg_match('/^([^_]*)_?(.*)_method_([^_]*)$/', $function, $matches))
				{
					list($function_name,$prefix,$class,$method) = $matches;
					if (strlen($class)==0)
						$class = 'all';
					
					if (!array_key_exists($class,fm::$core->function))
						fm::$core->function[$class] = array();
					if (!array_key_exists($method,fm::$core->function[$class]))
					{
						fm::$core->function[$class][$method] = $function;
					}
					else
					{
						$f_matches = array();
						preg_match('/^([^_]*)/', fm::$core->function[$class][$method], $f_matches);
						if ($prefix_weight[$f_matches[1]]>$prefix_weight[$prefix])
						{
							fm::$core->function[$class][$method] = $function;
						}
					}
				}
			}
		}
		
		return $this;
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
		fm::$core->extension = array();
		fm::$core->event     = array();
		fm::$core->function  = array();
		fm::$core->ufunction = array();
		fm::$core->inclusion = array(FM_PATH_CORE.FM_FILE_COMPATIBILITY.FM_PHP_EXTENSION=>true,FM_PATH_CORE.FM_FILE_FUNCTION.FM_PHP_EXTENSION=>true);
		fm::$core->message   = array('message'=>array(),'debug'=>array(),'notice'=>array(),'error'=>array());
		fm::$core->type      = 'core';
		fm::$config          = array();
		set_error_handler("fm_ErrorHandler");
		fm::$core
			->registerMethods()
			->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$core->type)
			->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$core->type)
			->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$stdObj->type)
			->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$stdObj->type)
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
			->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$core->class[$class]['object']->type)
			->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$core->class[$class]['object']->type)
			->include(FM_SITE_DIR.FM_PATH_CLASS.fm::$core->class[$class]['object']->type);
		foreach(fm::$core->extension as $data)
		{
			fm::$core->include($data['path'].FM_PATH_CLASS.fm::$core->class[$class]['object']->type);
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
				->include(FM_PATH_CORE.FM_PATH_CLASS.$extension)
				->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.$extension)
				->include(FM_SITE_DIR.FM_PATH_CLASS.$extension);
						
			if (file_exists(FM_SITE_DIR.FM_PATH_EXTENSION."$extension/"))
				$path = FM_SITE_DIR.FM_PATH_EXTENSION."$extension/";
			else
				$path = FM_PATH_SITE_ALL.FM_PATH_EXTENSION."$extension/";
			
			foreach(fm::$core->extension as $data)
			{
				fm::$core->include($data['path'].FM_PATH_CLASS.$extension);
			}
				
			foreach(fm::$core->class as $class=>$data)
			{
				fm::$core->include($path.FM_PATH_CLASS.$class);
			}
			
			fm::$core
				->include($path.FM_PATH_CLASS.$extension)
				->include($path.FM_FILE_FUNCTION);
			
			// load config
			if (file_exists($path.FM_FILE_CONFIG.FM_PHP_EXTENSION) && is_readable($path.FM_FILE_CONFIG.FM_PHP_EXTENSION))
			{
				$c = array();
				include $path.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				fm::$config = array_replace_recursive($c,fm::$core->config);
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

function core_method_include($fm, $file = null)
{
	$file = trim($file);
	
	if (strlen($file)==0)
		$file = trim($fm->value);
	
	if (strlen($file)>0 && !array_key_exists($file.FM_PHP_EXTENSION,fm::$core->inclusion) && !array_key_exists($file,fm::$core->inclusion))
	{
		if (file_exists($file.FM_PHP_EXTENSION))
		{
			include $file.FM_PHP_EXTENSION;
			fm::$core->inclusion[$file.FM_PHP_EXTENSION]=true;
			fm::$core->registerMethods();
		}
		elseif (file_exists($file))
		{
			include $file;
			fm::$core->inclusion[$file]=true;
			fm::$core->registerMethods();
		}
		else
		{
			fm::$core->inclusion[$file]=null;
		}
	}
}

function core_method_find($fm,$file)
{
	$file = trim($file);
	$return = clone fm::$stdObj;
	$return->value = null;
	if (strlen($file)>0)
	{	
		if (file_exists(FM_SITE_DIR.$file.FM_PHP_EXTENSION))
			$return->value = FM_SITE_DIR.$file;
		elseif (file_exists(FM_SITE_DIR.$file))
			$return->value = FM_SITE_DIR.$file;
		elseif (file_exists(FM_PATH_SITE_ALL.$file.FM_PHP_EXTENSION))
			$return->value = FM_PATH_SITE_ALL.$file;
		elseif (file_exists(FM_PATH_SITE_ALL.$file))
			$return->value = FM_PATH_SITE_ALL.$file;
		else
		{
			foreach (fm::$core->extension as $extension)
			{
				if (file_exists($extension['path'].$file.FM_PHP_EXTENSION))
					$return->value = $extension['path'].$file;
				elseif (file_exists($extension['path'].$file))
					$return->value = $extension['path'].$file;
			}
			if ($return->value == null)
			{
				if (file_exists(FM_PATH_CORE.$file.FM_PHP_EXTENSION))
					$return->value = FM_PATH_CORE.$file;
				elseif (file_exists(FM_PATH_CORE.$file))
					$return->value = FM_PATH_CORE.$file;
			}
		}
	}
	if ($return->value == null)
		fm::$core->notice("Can't found $file");
	return $return;
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
