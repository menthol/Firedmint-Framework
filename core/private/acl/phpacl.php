<?php
if (!defined('FM_SECURITY')) die();

class phpAcl
{
	static $acl = array();
	function __construct()
	{
		$this->update();
	}
	
	function update()
	{
		if (!is_array(phpAcl::$acl = cache::$value->get('phpacl','cached_acl')))
		{
			// compile acls 
			if (!is_array(phpAcl::$acl = cache::$static->get('phpacl','static_acl')))
				phpAcl::$acl = array();
			
			$file = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_PRIVATE.FM_FILE_ACL.FM_PHP_EXTENSION;
			if (file_exists($file))
			{				
				$acl = array();
				include $file;
				phpAcl::$acl = array_replace_recursive($acl,phpAcl::$acl);
			}
			
			$file = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_PRIVATE.FM_FILE_ACL.FM_PHP_EXTENSION;
			if (file_exists($file))
			{
				$acl = array();
				include $file;
				phpAcl::$acl = array_replace_recursive($acl,phpAcl::$acl);
			}
			
			list($config,$extension) = _loadConfig();
			
			foreach ($extension as $ext=>$values)
			{
				$path = $values['path'];
				if (file_exists($path.FM_FILE_ACL.FM_PHP_EXTENSION))
				{
					$acl = array();
					include $path.FM_FILE_ACL.FM_PHP_EXTENSION;
					phpAcl::$acl = array_replace_recursive($acl,phpAcl::$acl);
				}
			}
			
			$file = FM_PATH_CORE.FM_PATH_PRIVATE.FM_FILE_ACL.FM_PHP_EXTENSION;
			if (file_exists($file))
			{
				$acl = array();
				include $file;
				phpAcl::$acl = array_replace_recursive($acl,phpAcl::$acl);
			}
			cache::$value->set('phpacl','cached_acl',phpAcl::$acl,kernel::$config['acl']['cache_lifetime']);
		}		
	}
		
	function user($user,$roleGroup,$role)
	{
		if (array_key_exists($user,phpAcl::$acl['user']) && array_key_exists($roleGroup,phpAcl::$acl['user'][$user]) && array_key_exists($role,phpAcl::$acl['user'][$user][$roleGroup]))
			return phpAcl::$acl['user'][$user][$roleGroup][$role];
		
		if (!is_object($user = user::getUser($user)) || !isset($user->group))
			return $this->all($roleGroup,$role);
		
		return $this->group($user->group,$roleGroup,$role);
	}
	
	function group($group,$roleGroup,$role)
	{
		if (array_key_exists($group,phpAcl::$acl['group']) && array_key_exists($roleGroup,phpAcl::$acl['group'][$group]) && array_key_exists($role,phpAcl::$acl['group'][$group][$roleGroup]))
			return phpAcl::$acl['group'][$group][$roleGroup][$role];
		
		return $this->all($roleGroup,$role);
	}
	
	function all($roleGroup,$role)
	{
		if (array_key_exists('*',phpAcl::$acl['group']) && array_key_exists($roleGroup,phpAcl::$acl['group']['*']) && array_key_exists($role,phpAcl::$acl['group']['*'][$roleGroup]))
			return phpAcl::$acl['group']['*'][$roleGroup][$role];
	}
	
	function set($category,$name,$roleGroup,$role,$value)
	{
		$category = trim(strtolower($category));
		if ($category!='user' && $category!='group')
			return;
		
		if (!is_array($_acl = cache::$static->get('phpacl','static_acl')))
				$_acl = array();
		$_acl[$category][$name][$roleGroup][$role] = $value;
		
		$return = cache::$static->set('phpacl','static_acl',$_acl);
		$this->update();
		return $return;
	}
	
	function delete($category,$name,$roleGroup,$role)
	{
		$category = trim(strtolower($category));
		if ($category!='user' && $category!='group')
			return;
		
		if (!is_array($_acl = cache::$static->get('phpacl','static_acl')))
				$_acl = array();
		unset($_acl[$category][$name][$roleGroup][$role]);
		
		$return = cache::$static->set('phpacl','static_acl',$_acl);
		$this->update();
		return $return;
	}
}