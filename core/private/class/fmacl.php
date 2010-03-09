<?php
if (!defined('FM_SECURITY')) die();

class FmAcl
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(FmAcl::$o))
			FmAcl::$o = _subClass('FmAcl',FmConfig::$config['acl']['engine']);
	}
	
	static function user($user,$roleGroup,$role)
	{
		return FmAcl::$o->user($user,$roleGroup,$role);
	}
	
	static function group($group,$roleGroup,$role)
	{
		return FmAcl::$o->group($group,$roleGroup,$role);
	}
	
	static function all($roleGroup,$role)
	{
		return FmAcl::$o->all($roleGroup,$role);
	}
	
	static function set($category,$name,$roleGroup,$role,$value)
	{
		return FmAcl::$o->set($roleGroup,$role,$value);
	}
	
	static function delete($category,$name,$roleGroup,$role)
	{
		return FmAcl::$o->delete($category,$name,$roleGroup,$role);
	}

	static function routeControl($user,$route)
	{
		return FmAcl::$o->routeControl($user,$route);
	}
}