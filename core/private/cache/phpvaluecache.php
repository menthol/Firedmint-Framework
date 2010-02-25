<?php
if (!defined('FM_SECURITY')) die();

class phpValueCache
{
	function set($type,$id,$data,$cacheLifeTime = false)
	{
		$file = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/".sha1($id).FM_PHP_EXTENSION;
		_createDir($file);
		if ($cacheLifeTime===false)
			$cacheLifeTime = kernel::$config['cache']['value_lifetime'];
		
		if ($cacheLifeTime===null)
			$expire = null;
		else
			$expire = time() + $cacheLifeTime;
		
		return file_put_contents($file,FM_PHP_STARTFILE.'$data = array('.var_export($expire,true).','.var_export($data,true).');');
		
	}
	
	function get($type,$id)
	{
		$file = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/".sha1($id).FM_PHP_EXTENSION;
		
		if (file_exists($file))
		{
			include $file;
			if (is_null($data[0]) || $data[0] > time())
				return $data[1];
			else
				unlink($file);
		}
	}
	
	function delete($type,$id)
	{
		$file = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/".sha1($id).FM_PHP_EXTENSION;
		if (file_exists($file))
			return unlink($file);
		
		return false;
	}
	
	function clean($type)
	{
	    _deleteDir(kernel::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/");
	}
	
	function cleanAll()
	{
		_deleteDir(kernel::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/");
	}
}