<?php
if (!defined('FM_SECURITY')) die();

class phpFrontCache
{
	function set($phpFile,$arguments = array(),$cacheLifeTime = false)
	{
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if (file_exists($phpFile))
		{	
			$phpFileDir = basename(str_replace('/','-',$phpFile),FM_PHP_EXTENSION);
			
			$file = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($arguments,true)).FM_PHP_EXTENSION;
			$filePart = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($arguments,true)).'.part.'.FM_PHP_EXTENSION;
			
			if (file_exists($file))
			{
				include $file;
				@unlink($data[2]);
			}
			
			$content = $this->execute($phpFile,&$arguments,&$cacheLifeTime);
			if ($cacheLifeTime===false)
				$cacheLifeTime = kernel::$config['cache']['file_lifetime'];
			
			if ($cacheLifeTime===null)
				$expire = null;
			else
				$expire = time() + $cacheLifeTime;	
			
			_createDir($filePart);
			_createDir($file);
			if (file_put_contents($filePart,$content,LOCK_EX) && file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$expire,$filePart,$arguments,$cacheLifeTime),true).';',LOCK_EX))
				return $filePart;
		}
		return false;
	}
	
	function get($phpFile,$arguments = array())
	{
		if ($phpFile[0]=='/')
			$phpFile = substr($phpFile,1);
		
		if (file_exists($phpFile))
		{	
			$phpFileDir = basename(str_replace('/','-',$phpFile),FM_PHP_EXTENSION);
			
			$file = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($arguments,true)).FM_PHP_EXTENSION;
			
			if (file_exists($file))
			{
				include $file;
				if (is_null($data[1]) || $data[1] > time())
					return $this->execute($data[2],$data[3],$data[4],$data);
				else
				{
					@unlink($data[2]);
					unlink($file);
				}
			}
		}
		return false;
	}
	
	function delete($phpFile,$arguments = array())
	{
		$phpFileDir = basename(str_replace('/','-',$phpFile),FM_PHP_EXTENSION);
		$file = kernel::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".$phpFileDir.'/'.sha1($phpFile.var_export($arguments,true)).FM_PHP_EXTENSION;
		if (file_exists($file))
		{
			include $file;
			@unlink($data[2]);
			unlink($file);	
		}
	}
	
	function clean($phpFile = null)
	{
		if (strlen($phpFile)>0)
	    	return _deleteDir(kernel::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/".basename(str_replace('/','-',$phpFile),FM_PHP_EXTENSION).'/');
	    
	    return _deleteDir(kernel::$config['cache']['var_private'].FM_SITE_DIR."phpfrontcache/");
	}
	
	function execute($__phpFile,$data,$cache,$__cacheArgs = null)
	{
		if (file_exists($__phpFile))
		{
			if ($__cacheArgs===null)
				unset($__cacheArgs);
			
			extract($data,EXTR_SKIP);
			ob_start();
			include $__phpFile;
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}
}