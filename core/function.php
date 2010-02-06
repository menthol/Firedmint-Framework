<?php 
if (!defined('FM_SECURITY')) die();

class fm
{
	public  $value;
	public  $type;
	public  $core;
	public  $extension = array();
	public  $property  = array();
	public  $function  = array();
	
	function __call($name, $arguments)
	{
		if (strlen($this->type)==0)
			$type = 'fm';
		else
			$type = $this->type;
			
		if (is_a($this->core,'fm') && !array_key_exists($type,$this->core->function))
			$this->core->function[$type] = array();
		
		if(is_a($this->core,'fm') && !array_key_exists($name,$this->core->function[$type]))
		{
			// find the good function 
			$function_prefix = array();
			$function_prefix[] = "site";
			$function_prefix[] = "all";
			foreach ($this->core->extension as $extension)
			{
				$function_prefix[] = "{$extension}";
			}
			$function_prefix[] = "core";
			
			if (strlen($this->type)==0)
			{
				foreach ($function_prefix as $prefix)
				{
					if (!array_key_exists($name,$this->core->function[$type]))
						if (function_exists("{$prefix}_{$this->type}_method_{$name}") || function_exists("{$prefix}_{$this->type}_function_{$name}"))
							$this->core->function[$type][$name] = "{$prefix}_{$this->type}";
				}
			}
			
			if (!array_key_exists($name,$this->core->function[$type]))
			{
				foreach ($function_prefix as $prefix)
				{
					if (!array_key_exists($name,$this->core->function[$type]))
						if (function_exists("{$prefix}_method_{$name}") || function_exists("{$prefix}_function_{$name}"))
							$this->core->function[$type][$name] = "{$prefix}";
				}
			}
			
			if (!array_key_exists($name,$this->core->function[$type]) && function_exists($name))
				$this->core->function[$type][$name] = null; // set array_key_exist to true and strlen to 0
		}
		
		$return = clone $this;
		
		// event trigger _before
		
		// function lancher
		if (is_a($this->core,'fm') && array_key_exists($name,$this->core->function[$type]))
		{
			
			if (strlen($this->core->function[$type][$name])>0)
			{
				if (function_exists("{$this->core->function[$type][$name]}_method_$name"))
				{
					array_unshift($arguments,clone $return);
					
					$tmp_return = call_user_func_array("{$this->core->function[$type][$name]}_method_$name",$arguments);
					
					if (!(is_a($tmp_return,'fm')))
					{
						if (is_null($tmp_return))
							$return = $arguments[0];
						else
						{
							$out = clone $return;
							$out->value = $tmp_return;
							$return = $out;
						}
					}
					else
						$return = $tmp_return;
				}
				elseif (function_exists("{$this->core->function[$type][$name]}_function_$name"))
				{
					array_unshift($arguments,$return->value);
					$tmp_return = call_user_func_array("{$this->core->function[$type][$name]}_function_$name",$arguments);
					
					if (!(is_a($tmp_return,'fm')))
					{
						$out = clone $return;
						$out->value = $tmp_return;
						$return = $out;
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
				{
					$out = clone $return;
					$out->value = $tmp_return;
					$return = $out;
				}
				else		
					$return = $tmp_return;
			}
		}
		
		// event trigger_after
		
		
		// return
		
		return $return;	
	}
	
	function save(&$var)
	{
		$var = $this;
	}
}

function fm($value = null, $type = null)
{
	static $fm;
	if (!is_object($fm))
	{
		$fm = new fm();
		$fm->core = $fm;
	}
	return $fm->new($value,$type);
}

function core_method_new($fm,$value,$type)
{
	$fm->value = $value;
	$fm->type = trim(strtolower($type));
}

/**
 * Load all declared config
 * 
 * @return Array configuration table
 */
function core_method_loadConfig($fm)
{
	if (defined('FM_SITE_DIR'))
		return $fm;
	
	$o = array();
	$u = array();
	$tmp_host = explode('.',$_SERVER['SERVER_NAME']);
	
	if (count($tmp_host)==1)
	{
		$u['ext'] = null;
		$u['sub'] = null;
		$u['host'] = $tmp_host[0];
	}
	else
	{
		$u['ext'] = $tmp_host[(count($tmp_host)-1)];
		$u['sub'] = implode('.',array_slice($tmp_host,0,(count($tmp_host)-2)));
		$u['host'] = $tmp_host[(count($tmp_host)-2)];
	}
	
	if ($_SERVER['SCRIPT_NAME'][0]=='/')
		$tmp_dir = explode('/', substr($_SERVER['SCRIPT_NAME'],1));
	else
		$tmp_dir = explode('/', $_SERVER['SCRIPT_NAME']);
	
	array_pop($tmp_dir);
	$u['dir'] = $tmp_dir;
	$u['port'] = $_SERVER['SERVER_PORT'];
	
	do
	{
		$dir = (count($u['dir'])?'.':null).implode('.',$u['dir']);
		
		foreach (array('.'.$u['port'],'') as $port)
		{
			foreach (array($u['ext'],'') as $ext)
			{
				if (strlen($ext))
					$ext = ".$ext";
				
				foreach (array($u['sub'],'') as $sub)
				{
					if (strlen($sub))
						$sub = "$sub.";
					$o[FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}"] = FM_PATH_SITE."{$sub}{$u['host']}{$ext}{$port}{$dir}";
				}
			}
		}
	
	}while (array_pop($u['dir']));
	
	$u['dir'] = $tmp_dir;
	do
	{
		$dir = implode('.',$u['dir']);
		foreach (array($_SERVER['SERVER_PORT'],'') as $port)
		{
			if (strlen($port) && $dir)
					$port = "$port.";
			
			if ($port || $dir)
				$o[FM_PATH_SITE."{$port}{$dir}"] = FM_PATH_SITE."{$port}{$dir}";	
		}
	}while (array_pop($u['dir']));
	
	$o[substr(FM_PATH_SITE_DEFAULT,0,-1)] = substr(FM_PATH_SITE_DEFAULT,0,-1);
	
	$c = array();
	
	foreach ($o as $dir)
	{
		if (!defined('FM_SITE_DIR'))
		{
			$file = $dir.FM_PHP_EXTENSION;
			if (file_exists($file) && is_readable($file))
			{
				$tmp_c = $c;
				$c = array();
				include $file;
				$c = array_replace_recursive($c,$tmp_c);
			}
			if (defined('FM_SITE_DIR'))
			{
				$file = FM_SITE_DIR.FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file) && is_readable($file))
				{				
					$tmp_c = $c;
					$c = array();
					include $file;
					$c = array_replace_recursive($c,$tmp_c);
				}
			}
			else
			{
				$file = "$dir/".FM_FILE_CONFIG.FM_PHP_EXTENSION;
				if (file_exists($file) && is_readable($file))
				{
					define('FM_SITE_DIR',"$dir/");
					$tmp_c = $c;
					$c = array();
					include $file;
					$c = array_replace_recursive($c,$tmp_c);
				}
			}
		}
	}
	
	$file = FM_PATH_SITE_ALL.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}
	
	$file = FM_PATH_CORE.FM_FILE_CONFIG.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_c = $c;
		$c = array();
		include $file;
		$c = array_replace_recursive($c,$tmp_c);
	}
	
	$fm->core->config = $c;
}
