<?php
if (!defined('FM_SECURITY')) die();

class FmPhpAcl
{
	static $acl = array();
	function __construct()
	{
		$this->__update();
	}
	
	function __update()
	{
		if (_clear('acl') || !is_array(FmPhpAcl::$acl = FmCache::get('phpacl','cached_acl')))
		{
			// compile acls 
			if (!is_array($__acl = FmCache::getStatic('phpacl','static_acl')))
				$__acl = array();
			
			foreach (_getPaths('private/acl.php') as $file)
			{
				$acl = array();
				include $file;
				$__acl = array_replace_recursive($acl,$__acl);
			}
			FmPhpAcl::$acl = $__acl;
			FmCache::set('phpacl','cached_acl',$__acl,FmConfig::$config['acl']['cache_lifetime']);
		}		
	}
		
	function user($user,$roleGroup,$role)
	{
		if (isset(FmPhpAcl::$acl['user'][$user][$roleGroup][$role]))
			return FmPhpAcl::$acl['user'][$user][$roleGroup][$role];
		
		if (isset(FmPhpAcl::$acl['user'][$user][$roleGroup]['*']))
			return FmPhpAcl::$acl['user'][$user][$roleGroup]['*'];
		
		if (!is_object($user = FmUser::get($user)) || !isset($user->group))
			return FmAcl::all($roleGroup,$role);
		
		return FmAcl::group($user->group,$roleGroup,$role);
	}
	
	function group($group,$roleGroup,$role)
	{
		if (isset(FmPhpAcl::$acl['group'][$group][$roleGroup][$role]))
			return FmPhpAcl::$acl['group'][$group][$roleGroup][$role];
		
		if (isset(FmPhpAcl::$acl['group'][$group][$roleGroup]['*']))
			return FmPhpAcl::$acl['group'][$group][$roleGroup]['*'];
		
		return FmAcl::all($roleGroup,$role);
	}
	
	function all($roleGroup,$role)
	{
		if (isset(FmPhpAcl::$acl['group']['*'][$roleGroup][$role]))
			return FmPhpAcl::$acl['group']['*'][$roleGroup][$role];
		
		if (isset(FmPhpAcl::$acl['group']['*'][$roleGroup]['*']))
			return FmPhpAcl::$acl['group']['*'][$roleGroup]['*'];
	}
	
	function set($category,$name,$roleGroup,$role,$value)
	{
		$category = trim(strtolower($category));
		if ($category!='user' && $category!='group')
			return;
		
		if (!is_array($_acl = FmCache::getStatic('phpacl','static_acl')))
				$_acl = array();
		$_acl[$category][$name][$roleGroup][$role] = $value;
		
		$return = FmCache::setStatic('phpacl','static_acl',$_acl);
		$this->update();
		return $return;
	}
	
	function delete($category,$name,$roleGroup,$role)
	{
		$category = trim(strtolower($category));
		if ($category!='user' && $category!='group')
			return;
		
		if (!is_array($_acl = FmCache::getStatic('phpacl','static_acl')))
				$_acl = array();
		unset($_acl[$category][$name][$roleGroup][$role]);
		
		$return = FmCache::setStatic('phpacl','static_acl',$_acl);
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
		
		if (FmAcl::user($user,'route',$route[0]))
			return $route;
		
		if (is_array($return = FmAcl::user($user,'failRoute',$route[0])))
			return $return + $route;
		
		return $route;
	}
}