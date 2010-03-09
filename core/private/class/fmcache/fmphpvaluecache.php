<?php
if (!defined('FM_SECURITY')) die();

class FmPhpValueCache
{
	function set($type,$id,$data,$cacheLifeTime = false)
	{
		$file = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/".sha1($id).'.php';
		_createDir($file);
		if ($cacheLifeTime===false)
			$cacheLifeTime = FmConfig::$config['cache']['value_lifetime'];
		
		if ($cacheLifeTime===null)
			$expire = null;
		else
			$expire = time() + $cacheLifeTime;
		
		FmCache::informExpire($expire);
		FmCache::informLastUpdate(time());
		
		return file_put_contents($file,FM_PHP_STARTFILE.'$data = '.var_export(array(time(),$expire,$data),true).';',LOCK_EX);
	}
	
	function get($type,$id)
	{
		if (!_clear('value'))
		{
			if (file_exists($file = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/".sha1($id).'.php'))
			{
				include $file;
				if (is_null($data[1]) || $data[1] > time())
				{
					if ($data[1] > time())
						FmCache::informExpire($data[1]);
					
					FmCache::informLastUpdate($data[0]);
					return $data[2];
				}
				else
					unlink($file);
			}
		}
	}
	
	function delete($type,$id)
	{
		if (file_exists($file = FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/".sha1($id).'.php'))
			return unlink($file);
		
		return false;
	}
	
	function clean($type = null)
	{
		if (strlen($type)>0)
	    	return _deleteDir(FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/$type/");
	    
	    return _deleteDir(FmConfig::$config['cache']['var_private'].FM_SITE_DIR."phpvaluecache/");
	}
}