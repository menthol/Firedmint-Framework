<?php
if (!defined('FM_SECURITY')) die();

class acl
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(acl::$o))
			acl::$o = _subClass('acl',config::$config['acl']['engine']);
	}
	
	static function user($user,$roleGroup,$role)
	{
		if (is_object(acl::$o))
			return acl::$o->user($user,$roleGroup,$role);
	}
	
	static function group($group,$roleGroup,$role)
	{
		if (is_object(acl::$o))
			return acl::$o->group($group,$roleGroup,$role);
	}
	
	static function all($roleGroup,$role)
	{
		if (is_object(acl::$o))
			return acl::$o->all($roleGroup,$role);
	}
	
	static function set($category,$name,$roleGroup,$role,$value)
	{
		if (is_object(acl::$o))
			return acl::$o->set($roleGroup,$role,$value);
	}
	
	static function delete($category,$name,$roleGroup,$role)
	{
		if (is_object(acl::$o))
			return acl::$o->delete($category,$name,$roleGroup,$role);
	}

	static function routeControl($user,$route)
	{
		if (is_object(acl::$o))
			return acl::$o->routeControl($user,$route);
		
		return $route;
	}
}