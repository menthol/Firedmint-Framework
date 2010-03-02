<?php
if (!defined('FM_SECURITY')) die();

class phpStaticCache
{
	function setStatic($type,$id,$data)
	{
		$file = config::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/".sha1($id).FM_PHP_EXTENSION;
		_createDir($file);
				
		return file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$data),true).';',LOCK_EX);
	}
	
	function getStatic($type,$id)
	{
		$file = config::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/".sha1($id).FM_PHP_EXTENSION;
		
		if (file_exists($file))
		{
			include $file;
			return $data[1];
		}
	}
	
	function deleteStatic($type,$id)
	{
		$file = config::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/".sha1($id).FM_PHP_EXTENSION;
		if (file_exists($file))
			return unlink($file);
		
		return false;
	}
	
	function cleanStatic($type = null)
	{
		if (strlen($type)>0)
	    	return _deleteDir(config::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/");
	    
	    return _deleteDir(config::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/");
	}
}