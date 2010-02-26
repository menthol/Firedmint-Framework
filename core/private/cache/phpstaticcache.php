<?php
if (!defined('FM_SECURITY')) die();

class phpStaticCache
{
	function set($type,$id,$data)
	{
		$file = kernel::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/".sha1($id).FM_PHP_EXTENSION;
		_createDir($file);
				
		return file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$data),true).';',LOCK_EX);
	}
	
	function get($type,$id)
	{
		$file = kernel::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/".sha1($id).FM_PHP_EXTENSION;
		
		if (file_exists($file))
		{
			include $file;
			return $data[2];
		}
	}
	
	function delete($type,$id)
	{
		$file = kernel::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/".sha1($id).FM_PHP_EXTENSION;
		if (file_exists($file))
			return unlink($file);
		
		return false;
	}
	
	function clean($type = null)
	{
		if (strlen($type)>0)
	    	return _deleteDir(kernel::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/$type/");
	    
	    return _deleteDir(kernel::$config['cache']['static_private'].FM_SITE_DIR."phpstaticcache/");
	}
}