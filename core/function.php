<?php 
if (!defined('FM_SECURITY')) die();

class fm
{
	public  $type;
	public  static  $core;

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
					if (function_exists("{$prefix}_{$this->type}_method_{$name}") || function_exists("{$prefix}_{$this->type}_function_{$name}"))
						fm::$core->function[$this->type][$name] = "{$prefix}_{$this->type}";
			}
			if (!array_key_exists($name,fm::$core->function[$this->type]))
			{
				foreach ($function_prefix as $prefix)
				{
					if (!array_key_exists($name,fm::$core->function[$this->type]))
						if (function_exists("{$prefix}_method_{$name}") || function_exists("{$prefix}_function_{$name}"))
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
			$return = clone $this;
			$return->event("{$this->type}_{$name}",'before');
			$return->event("{$this->type}_{$name}",'main');
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
						if (is_null($tmp_return))
							$return = $arguments[0];
						else
							$return->value = $tmp_return;
					}
					else
						$return = $tmp_return;
				}
				elseif (function_exists(fm::$core->function[$this->type][$name]."_function_$name"))
				{
					array_unshift($arguments,$return->value);
					$tmp_return = call_user_func_array(fm::$core->function[$this->type][$name]."_function_$name",$arguments);
					
					if (!(is_a($tmp_return,'fm')))
						$return->value = $tmp_return;
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
		}
		
		// event trigger after
		if ($name!='event')
			$return->event("{$this->type}_{$name}",'after');
		
		// return
		return $return;	
	}
	
	function save(&$var)
	{
		$var = $this;
	}
}

function fm($value = null, $type = 'fm')
{
	if (!is_object(fm::$core))
	{
		fm::$core            = new fm();
		fm::$core->function  = array();
		fm::$core->extension = array();
		fm::$core->inclusion = array(FM_PATH_CORE.FM_FILE_COMPATIBILITY.FM_PHP_EXTENSION=>true,FM_PATH_CORE.FM_FILE_FUNCTION.FM_PHP_EXTENSION=>true);
		fm::$core->config    = array();
		fm::$core->type      = 'fm';
		fm::$core->event     = array();
		fm::$core->include(FM_PATH_CORE.FM_PATH_CLASS.fm::$core->type.FM_PHP_EXTENSION);
		fm::$core->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.fm::$core->type.FM_PHP_EXTENSION);
		fm::$core->construct();
	}
	
	if($value==null && $type == 'fm')
		return fm::$core;
	
	return fm::$core->new($type,$value);
}

function core_fm_method_new($fm, $type = 'fm', $value = null)
{
	$fm->value = $value;
	$fm->type = trim(strtolower($type));
	fm::$core->include(FM_PATH_CORE.FM_PATH_CLASS.$fm->type.FM_PHP_EXTENSION);
	fm::$core->include(FM_PATH_SITE_ALL.FM_PATH_CLASS.$fm->type.FM_PHP_EXTENSION);
	fm::$core->include(FM_SITE_DIR.FM_PATH_CLASS.$fm->type.FM_PHP_EXTENSION);
	foreach(fm::$core->extension as $extension=>$data)
	{
		fm::$core->include(FM_PATH_SITE_ALL.FM_PATH_EXTENSION."$extension/".FM_PATH_CLASS.$fm->type.FM_PHP_EXTENSION);
		fm::$core->include(FM_SITE_DIR.FM_PATH_EXTENSION."$extension/".FM_PATH_CLASS.$fm->type.FM_PHP_EXTENSION);
	}
	
	$fm->construct();
}

function core_fm_method_include($fm,$file)
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
