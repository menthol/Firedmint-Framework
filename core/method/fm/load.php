<?php
if (!defined('FM_SECURITY')) die();

function load($file = null)
{
	$file = trim($file);
	
	if (strlen($file)==0)
		$file = trim($this->value);
	
	if (strlen($file)>0 && !array_key_exists($file.FM_PHP_EXTENSION,fm::$included) && !array_key_exists($file,fm::$included))
	{
		if (file_exists($file.FM_PHP_EXTENSION))
		{
			include $file.FM_PHP_EXTENSION;
			fm::$included[$file.FM_PHP_EXTENSION]=true;
		}
		elseif (file_exists($file))
		{
			include $file;
			fm::$included[$file]=true;
		}
		else
		{
			fm::$included[$file]=null;
		}
	} elseif(strlen($file)==0)
	{
		fm::$included[$file]=null;
	}
	return $this;
}