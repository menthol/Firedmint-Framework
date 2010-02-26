<?php
if (!defined('FM_SECURITY')) die();

class phpAcl
{
	static $acl = array();
	function __construct()
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
	
	function user($name,$roleGroup,$role)
	{
		
	}
	
	function group($name,$roleGroup,$role)
	{
		
	}
	
	function all($roleGroup,$role)
	{
		
	}
}