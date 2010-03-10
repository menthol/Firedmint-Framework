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
		return acl::$o->user($user,$roleGroup,$role);
	}
	
	static function group($group,$roleGroup,$role)
	{
		return acl::$o->group($group,$roleGroup,$role);
	}
	
	static function all($roleGroup,$role)
	{
		return acl::$o->all($roleGroup,$role);
	}
	
	static function set($category,$name,$roleGroup,$role,$value)
	{
		return acl::$o->set($roleGroup,$role,$value);
	}
	
	static function delete($category,$name,$roleGroup,$role)
	{
		return acl::$o->delete($category,$name,$roleGroup,$role);
	}

	static function routeControl($user,$route)
	{
		return acl::$o->routeControl($user,$route);
	}
}