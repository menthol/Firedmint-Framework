<?php
if (!defined('FM_SECURITY')) die();

class FmCache
{
	public  static  $o = array();
	
	public  static  $expire;
	
	public  static  $lastUpdate;
	
	static function factory()
	{	
		
		if (!isset(FmCache::$o['file']) || !is_object(FmCache::$o['file']))
			FmCache::$o['file'] = _subClass('FmCache',FmConfig::$config['cache']['file_engine']);
		
		if (!isset(FmCache::$o['value']) || !is_object(FmCache::$o['value']))
			FmCache::$o['value'] = _subClass('FmCache',FmConfig::$config['cache']['value_engine']);
		
		if (!isset(FmCache::$o['front']) || !is_object(FmCache::$o['front']))
			FmCache::$o['front'] = _subClass('FmCache',FmConfig::$config['cache']['front_engine']);
		
		if (!isset(FmCache::$o['static']) || !is_object(FmCache::$o['static']))
			FmCache::$o['static'] = _subClass('FmCache',FmConfig::$config['cache']['static_engine']);
	}
	
	static function informExpire($expire)
	{
		if (!isset(FmCache::$expire) || empty(FmCache::$expire))
			FmCache::$expire = $expire;
		elseif ($expire < FmCache::$expire)
			FmCache::$expire = $expire;
	}
	
	static function informLastUpdate($lastUpdate)
	{
		if (!isset(FmCache::$lastUpdate) || empty(FmCache::$lastUpdate))
			FmCache::$lastUpdate = $lastUpdate;
		elseif ($lastUpdate > FmCache::$lastUpdate)
			FmCache::$lastUpdate = $lastUpdate;
	}
	
	static function set($type,$id,$data,$cacheLifeTime = false)
	{
		return FmCache::$o['value']->set($type,$id,$data,$cacheLifeTime);
	}
	
	static function get($type,$id)
	{
		return FmCache::$o['value']->get($type,$id);
	}
	
	static function delete($type,$id)
	{
		return FmCache::$o['value']->delete($type,$id);
	}
	
	static function clean($type = null)
	{
		return FmCache::$o['value']->clean($type);
	}
	
	static function setFile($originalPath,$cacheLifeTime = false,$public = false,$filename = null)
	{
		return FmCache::$o['file']->setFile($originalPath,$cacheLifeTime,$public,$filename);
	}
	
	static function getFile($originalPath)
	{
		return FmCache::$o['file']-> getFile($originalPath);
	}
			
	static function setFileContent($id,$fileContent,$cacheLifeTime = false,$public = false,$filename = null)
	{
		return FmCache::$o['file']->setFileContent($id,$fileContent,$cacheLifeTime,$public,$filename);
	}
				
	static function getFileContent($id)
	{
		return FmCache::$o['file']->getFileContent($id);
	}
				
	static function deleteFile($id)
	{
		return FmCache::$o['file']->deleteFile($id);
	}
			
	static function cleanFile($cleanStatic = false)
	{
		return FmCache::$o['file']->cleanFile($cleanStatic);
	}
	
	static function setFront($phpFile,$view = null)
	{
		return FmCache::$o['front']->setFront($phpFile,$view);
	}
			
	static function getFront($phpFile,$view = null)
	{
		return FmCache::$o['front']->getFront($phpFile,$view);
	}
			
	static function deleteFront($phpFile,$arguments = array())
	{
		return FmCache::$o['front']->deleteFront($phpFile,$arguments);
	}
			
	static function cleanFront($phpFile = null)
	{
		return FmCache::$o['front']->cleanFront($phpFile);
	}
		
	static function executeFront($phpFile,$view)
	{
		return FmCache::$o['front']->executeFront($phpFile,$view);
	}
	
	static function setStatic($type,$id,$data)
	{
		return FmCache::$o['static']->setStatic($type,$id,$data);
	}
	
	static function getStatic($type,$id)
	{
		return FmCache::$o['static']->getStatic($type,$id);
	}
	
	static function deleteStatic($type,$id)
	{
		return FmCache::$o['static']->deleteStatic($type,$id);
	}
	
	static function cleanStatic($type = null)
	{
		return FmCache::$o['static']->cleanStatic($type);
	}
}