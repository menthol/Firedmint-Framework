<?php
if (!defined('FM_SECURITY')) die();

class phpAcl
{
	static $acl = array();
	function __construct()
	{
		$this->__update();
	}
	
	function __update()
	{
		if (_clear('acl') || !is_array(phpAcl::$acl = cache::get('phpacl','cached_acl')))
		{
			// compile acls 
			if (!is_array($__acl = cache::getStatic('phpacl','static_acl')))
				$__acl = array();
			
			foreach (_getPaths('private/acl'.FM_PHP_EXTENSION) as $file)
			{
				$acl = array();
				include $file;
				$__acl = array_replace_recursive($acl,$__acl);
			}
			phpAcl::$acl = $__acl;
			cache::set('phpacl','cached_acl',$__acl,config::$config['acl']['cache_lifetime']);
		}		
	}
		
	function user($user,$roleGroup,$role)
	{
		if (array_key_exists($user,phpAcl::$acl['user']) && array_key_exists($roleGroup,phpAcl::$acl['user'][$user]) && array_key_exists($role,phpAcl::$acl['user'][$user][$roleGroup]))
			return phpAcl::$acl['user'][$user][$roleGroup][$role];
		
		if (array_key_exists($user,phpAcl::$acl['user']) && array_key_exists($roleGroup,phpAcl::$acl['user'][$user]) && array_key_exists('*',phpAcl::$acl['user'][$user][$roleGroup]))
			return phpAcl::$acl['user'][$user][$roleGroup]['*'];
		
		if (!is_object($user = user::get($user)) || !isset($user->group))
			return acl::all($roleGroup,$role);
		
		return acl::group($user->group,$roleGroup,$role);
	}
	
	function group($group,$roleGroup,$role)
	{
		if (array_key_exists($group,phpAcl::$acl['group']) && array_key_exists($roleGroup,phpAcl::$acl['group'][$group]) && array_key_exists($role,phpAcl::$acl['group'][$group][$roleGroup]))
			return phpAcl::$acl['group'][$group][$roleGroup][$role];
		
		if (array_key_exists($group,phpAcl::$acl['group']) && array_key_exists($roleGroup,phpAcl::$acl['group'][$group]) && array_key_exists('*',phpAcl::$acl['group'][$group][$roleGroup]))
			return phpAcl::$acl['group'][$group][$roleGroup]['*'];
		
		return acl::all($roleGroup,$role);
	}
	
	function all($roleGroup,$role)
	{
		if (array_key_exists('*',phpAcl::$acl['group']) && array_key_exists($roleGroup,phpAcl::$acl['group']['*']) && array_key_exists($role,phpAcl::$acl['group']['*'][$roleGroup]))
			return phpAcl::$acl['group']['*'][$roleGroup][$role];
		
		if (array_key_exists('*',phpAcl::$acl['group']) && array_key_exists($roleGroup,phpAcl::$acl['group']['*']) && array_key_exists('*',phpAcl::$acl['group']['*'][$roleGroup]))
			return phpAcl::$acl['group']['*'][$roleGroup]['*'];
	}
	
	function set($category,$name,$roleGroup,$role,$value)
	{
		$category = trim(strtolower($category));
		if ($category!='user' && $category!='group')
			return;
		
		if (!is_array($_acl = cache::getStatic('phpacl','static_acl')))
				$_acl = array();
		$_acl[$category][$name][$roleGroup][$role] = $value;
		
		$return = cache::setStatic('phpacl','static_acl',$_acl);
		$this->update();
		return $return;
	}
	
	function delete($category,$name,$roleGroup,$role)
	{
		$category = trim(strtolower($category));
		if ($category!='user' && $category!='group')
			return;
		
		if (!is_array($_acl = cache::getStatic('phpacl','static_acl')))
				$_acl = array();
		unset($_acl[$category][$name][$roleGroup][$role]);
		
		$return = cache::setStatic('phpacl','static_acl',$_acl);
		$this->update();
		return $return;
	}
	
	function routeControl($user,$route)
	{
		if (is_object($user))
			if (property_exists($user,'login'))
				$user = $user->login;
			else
				return $route;
		
		if (acl::user($user,'route',$route[0]))
			return $route;
		
		if (is_array($return = acl::user($user,'failRoute',$route[0])))
			return $return + $route;
		
		return $route;
	}
}