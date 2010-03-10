<?php
if (!defined('FM_SECURITY')) die();

class cache
{
	public  static  $o = array();
	
	public  static  $expire;
	
	public  static  $lastUpdate;
	
	static function factory()
	{	
		
		if (!isset(cache::$o['file']) || !is_object(cache::$o['file']))
			cache::$o['file'] = _subClass('cache',config::$config['cache']['file_engine']);
		
		if (!isset(cache::$o['value']) || !is_object(cache::$o['value']))
			cache::$o['value'] = _subClass('cache',config::$config['cache']['value_engine']);
		
		if (!isset(cache::$o['front']) || !is_object(cache::$o['front']))
			cache::$o['front'] = _subClass('cache',config::$config['cache']['front_engine']);
		
		if (!isset(cache::$o['static']) || !is_object(cache::$o['static']))
			cache::$o['static'] = _subClass('cache',config::$config['cache']['static_engine']);
	}
	
	static function informExpire($expire)
	{
		if (!isset(cache::$expire) || empty(cache::$expire))
			cache::$expire = $expire;
		elseif ($expire < cache::$expire)
			cache::$expire = $expire;
	}
	
	static function informLastUpdate($lastUpdate)
	{
		if (!isset(cache::$lastUpdate) || empty(cache::$lastUpdate))
			cache::$lastUpdate = $lastUpdate;
		elseif ($lastUpdate > cache::$lastUpdate)
			cache::$lastUpdate = $lastUpdate;
	}
	
	static function set($type,$id,$data,$cacheLifeTime = false)
	{
		return cache::$o['value']->set($type,$id,$data,$cacheLifeTime);
	}
	
	static function get($type,$id)
	{
		return cache::$o['value']->get($type,$id);
	}
	
	static function delete($type,$id)
	{
		return cache::$o['value']->delete($type,$id);
	}
	
	static function clean($type = null)
	{
		return cache::$o['value']->clean($type);
	}
	
	static function setFile($originalPath,$cacheLifeTime = false,$public = false,$filename = null)
	{
		return cache::$o['file']->setFile($originalPath,$cacheLifeTime,$public,$filename);
	}
	
	static function getFile($originalPath)
	{
		return cache::$o['file']-> getFile($originalPath);
	}
			
	static function setFileContent($id,$fileContent,$cacheLifeTime = false,$public = false,$filename = null)
	{
		return cache::$o['file']->setFileContent($id,$fileContent,$cacheLifeTime,$public,$filename);
	}
				
	static function getFileContent($id)
	{
		return cache::$o['file']->getFileContent($id);
	}
				
	static function deleteFile($id)
	{
		return cache::$o['file']->deleteFile($id);
	}
			
	static function cleanFile($cleanStatic = false)
	{
		return cache::$o['file']->cleanFile($cleanStatic);
	}
	
	static function setFront($phpFile,$view = null)
	{
		return cache::$o['front']->setFront($phpFile,$view);
	}
			
	static function getFront($phpFile,$view = null)
	{
		return cache::$o['front']->getFront($phpFile,$view);
	}
			
	static function deleteFront($phpFile,$arguments = array())
	{
		return cache::$o['front']->deleteFront($phpFile,$arguments);
	}
			
	static function cleanFront($phpFile = null)
	{
		return cache::$o['front']->cleanFront($phpFile);
	}
		
	static function executeFront($phpFile,$view)
	{
		return cache::$o['front']->executeFront($phpFile,$view);
	}
	
	static function setStatic($type,$id,$data)
	{
		return cache::$o['static']->setStatic($type,$id,$data);
	}
	
	static function getStatic($type,$id)
	{
		return cache::$o['static']->getStatic($type,$id);
	}
	
	static function deleteStatic($type,$id)
	{
		return cache::$o['static']->deleteStatic($type,$id);
	}
	
	static function cleanStatic($type = null)
	{
		return cache::$o['static']->cleanStatic($type);
	}
}