<?php
if (!defined('FM_SECURITY')) die();

class phpFrontCache
{
	function setFront($phpFile,$view = null)
	{
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if ($view==null)
			$view = _class('view');
		
		if (property_exists($view,'environment'))
			unset($view->environment);
		
		if (property_exists($view,'extension'))
			unset($view->extension);
	
		$phpFileDir = basename(str_replace('/','-',$phpFile),'.php');
		$file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.php';
		$filePart = config::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.part.php';
		
		if (!file_exists($phpFile))
			return;
		
		$content = cache::executeFront($phpFile,&$view);
		
		if (!property_exists($view,'cache'))
			$view->cache = config::$config['cache']['file_lifetime'];
		
		$expire = time() + $view->cache; 
		
		cache::informExpire($expire);
		cache::informLastUpdate(time());
		
		_createDir($filePart);
		_createDir($file);
		
		if (file_exists($file))
		{
			@include $file;
			@unlink($file);
			@unlink($data[2]);
		}
		file_put_contents($filePart,$content,LOCK_EX);
		file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$expire,$filePart,$view),true).';',LOCK_EX);
		
		return cache::executeFront($filePart,$view);
	}
	
	function getFront($phpFile,$view = null)
	{
		if (_clear('front'))
			return cache::setFront($phpFile,$view);
		
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if ($view==null)
			$view = _class('view');
		
		if (property_exists($view,'environment'))
			unset($view->environment);
		
		if (property_exists($view,'extension'))
			unset($view->extension);
		
		$phpFileDir = basename(str_replace('/','-',$phpFile),'.php');		
		if (!file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.php'))
			return cache::setFront($phpFile,$view);	
		
		include $file;
		
		if (is_null($data[1]) || $data[1] > time())
		{
			if ($data[1] > time())
				cache::informExpire($data[1]);
			
			cache::informLastUpdate($data[0]);
			return cache::executeFront($data[2],$data[3]);
		}
		else
		{
			@unlink($file);
			@unlink($data[2]);
			return cache::setFront($phpFile,$view);
		}
	}
	
	function deleteFront($phpFile,$arguments = array())
	{
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if ($view==null)
			$view = _class('view');
		
		if (property_exists($view,'environment'))
			unset($view->environment);
		
		if (property_exists($view,'extension'))
			unset($view->extension);
			
		$phpFileDir = basename(str_replace('/','-',$phpFile),'.php');
		if (file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.php'))
		{
			@include $file;
			@unlink($data[2]);
			@unlink($file);	
		}
	}
	
	function cleanFront($phpFile = null)
	{
		if (strlen($phpFile)>0)
	    	return _deleteDir(config::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".basename(str_replace('/','-',$phpFile),'.php').'/');
	    
	    return _deleteDir(config::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/");
	}
	
	function executeFront($__phpFile,$view)
	{
		if (file_exists($__phpFile))
		{
			ob_start();
			$return = include $__phpFile;
			$content = ob_get_contents();
			ob_end_clean();
			
			if (is_string($return))
				return $return;
			
			return $content;
		}
	}
}