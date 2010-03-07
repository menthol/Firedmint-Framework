<?php
if (!defined('FM_SECURITY')) die();

class phpFileCache
{
	function setFile($originalPath,$cacheLifeTime = false,$public = false,$filename = null)
	{
		if (file_exists($originalPath) || array_key_exists('host',parse_url($originalPath)))
		{
			if (file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfilecache/".sha1($originalPath).'.php'))
			{
				include $file;
				@unlink($data[2]);
			}
			$cacheDir = config::$config['cache'][($cacheLifeTime===null?($public?'static_public':'static_private'):($public?'var_public':'var_private'))].FM_SITE_DIR."phpfilecache/";
			
			// force filename for url
			if (array_key_exists('host',parse_url($originalPath)) && strlen($filename)==0)
				$filename = strtolower(str_replace(array(':','/','.','--'),'-',$originalPath)).'.tmp';
			
			$i = 0;
			do
			{
				$cacheFile = "$cacheDir".(++$i)."/".(strlen($filename)>0?$filename:basename($originalPath));	
			}while (file_exists($cacheFile));
			
			if ($cacheLifeTime===false)
				$cacheLifeTime = config::$config['cache']['file_lifetime'];
			
			if ($cacheLifeTime===null)
				$expire = null;
			else
				$expire = time() + $cacheLifeTime;
			
			cache::informExpire($expire);
			cache::informLastUpdate(time());
			
			_createDir($cacheFile);
			_createDir($file);
			if (copy($originalPath,$cacheFile) && file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$expire,$cacheFile,$originalPath,$public),true).';',LOCK_EX))
				return $cacheFile;
		}
	}
	
	function getFile($originalPath)
	{
		if (file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfilecache/".sha1($originalPath).'.php'))
		{
			include $file;
			if ((is_null($data[1]) || $data[1] > time()) && file_exists($data[1]))
			{
				if ($data[1] > time())
					cache::informExpire($data[1]);
				
				cache::informLastUpdate($data[0]);
				return $data[2];
			}
			else
			{
				@unlink($data[2]);
				unlink($file);
			}	
		}
	}
	
	function setFileContent($id,$fileContent,$cacheLifeTime = false,$public = false,$filename = null)
	{
		if (file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfilecache/".sha1($id).'.php'))
		{
			include $file;
			@unlink($data[2]);
		}
		$cacheDir = config::$config['cache'][($cacheLifeTime===null?($public?'static_public':'static_private'):($public?'var_public':'var_private'))].FM_SITE_DIR."phpfilecache/";
		$i = 0;
				
		do
		{
			$cacheFile = "$cacheDir".(++$i)."/".(strlen($filename)>0?$filename:sha1($id).'.tmp');	
		}while (file_exists($cacheFile));
		
		if ($cacheLifeTime===null)
			$expire = null;
		else
			$expire = time() + $cacheLifeTime;
		
		_createDir($cacheFile);
		_createDir($file);
		if (file_put_contents($cacheFile,$fileContent,LOCK_EX) && file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$expire,$cacheFile,null,$public),true).';',LOCK_EX))
			return $cacheFile;
	}
		
	function getFileContent($id)
	{
		if (file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfilecache/".sha1($id).'.php'))
		{
			include $file;
			if ((is_null($data[1]) || $data[1] > time()) && file_exists($data[1]))
				return file_get_contents($data[2]);
			else
			{
				@unlink($data[2]);
				unlink($file);
			}	
		}
	}
		
	function deleteFile($id)
	{
		if (file_exists($file = config::$config['cache']['var_private'].FM_SITE_DIR."phpfilecache/".sha1($id).'.php'))
		{
			include $file;
			@unlink($data[2]);
			unlink($file);	
		}
	}
	
	function cleanFile($cleanStatic = false)
	{
		_deleteDir(config::$config['cache'][($cleanStatic?'static':'var').'_private'].FM_SITE_DIR."phpfilecache/");
		_deleteDir(config::$config['cache'][($cleanStatic?'static':'var').'_public'].FM_SITE_DIR."phpfilecache/");
	}
}