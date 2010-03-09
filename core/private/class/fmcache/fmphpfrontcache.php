<?php
if (!defined('FM_SECURITY')) die();

class FmPhpFrontCache
{
	function setFront($phpFile,$view = null)
	{
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if ($view==null)
			$view = _class('FmView');
		
		if (property_exists($view,'environment'))
			unset($view->environment);
		
		if (property_exists($view,'extension'))
			unset($view->extension);
	
		$phpFileDir = basename(str_replace('/','-',$phpFile),'.php');
		$file = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.php';
		$filePart = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.part.php';
		
		if (!file_exists($phpFile))
			return;
		
		$content = FmCache::executeFront($phpFile,&$view);
		
		if (!property_exists($view,'cache'))
			$view->cache = FmConfig::$config['cache']['file_lifetime'];
		
		$expire = time() + $view->cache; 
		
		FmCache::informExpire($expire);
		FmCache::informLastUpdate(time());
		
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
		
		return FmCache::executeFront($filePart,$view);
	}
	
	function getFront($phpFile,$view = null)
	{
		if (_clear('front'))
			return FmCache::setFront($phpFile,$view);
		
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if ($view==null)
			$view = _class('FmView');
		
		if (property_exists($view,'environment'))
			unset($view->environment);
		
		if (property_exists($view,'extension'))
			unset($view->extension);
		
		$phpFileDir = basename(str_replace('/','-',$phpFile),'.php');		
		if (!file_exists($file = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.php'))
			return FmCache::setFront($phpFile,$view);	
		
		include $file;
		
		if (is_null($data[1]) || $data[1] > time())
		{
			if ($data[1] > time())
				FmCache::informExpire($data[1]);
			
			FmCache::informLastUpdate($data[0]);
			return FmCache::executeFront($data[2],$data[3]);
		}
		else
		{
			@unlink($file);
			@unlink($data[2]);
			return FmCache::setFront($phpFile,$view);
		}
	}
	
	function deleteFront($phpFile,$arguments = array())
	{
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if ($view==null)
			$view = _class('FmView');
		
		if (property_exists($view,'environment'))
			unset($view->environment);
		
		if (property_exists($view,'extension'))
			unset($view->extension);
			
		$phpFileDir = basename(str_replace('/','-',$phpFile),'.php');
		if (file_exists($file = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($view,true)).'.php'))
		{
			@include $file;
			@unlink($data[2]);
			@unlink($file);	
		}
	}
	
	function cleanFront($phpFile = null)
	{
		if (strlen($phpFile)>0)
	    	return _deleteDir(FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".basename(str_replace('/','-',$phpFile),'.php').'/');
	    
	    return _deleteDir(FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/");
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