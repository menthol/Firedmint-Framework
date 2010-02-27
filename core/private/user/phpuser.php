<?php
if (!defined('FM_SECURITY')) die();

class phpUser
{
	static $user = array();
	function __construct()
	{
		if (_clear('user') || !is_array(phpUser::$user = cache::$value->get('arrayuser','cached_user')))
		{
			// compile users
			if (!is_array(phpUser::$user = cache::$static->get('arrayuser','static_user')))
				phpUser::$user = array();
			
			$file = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_PRIVATE.FM_FILE_USER.FM_PHP_EXTENSION;
			if (file_exists($file))
			{				
				$user = array();
				include $file;
				phpUser::$user += $user;
			}
			
			$file = FM_PATH_SITE.FM_PATH_ALL.FM_PATH_PRIVATE.FM_FILE_USER.FM_PHP_EXTENSION;
			if (file_exists($file))
			{
				$user = array();
				include $file;
				phpUser::$user += $user;
			}
			
			list($config,$extension) = _loadConfig();
			
			foreach ($extension as $ext=>$values)
			{
				$path = $values['path'];
				if (file_exists($path.FM_FILE_USER.FM_PHP_EXTENSION))
				{
					$user = array();
					include $path.FM_FILE_USER.FM_PHP_EXTENSION;
					phpUser::$user += $user;
				}
			}
			
			$file = FM_PATH_CORE.FM_PATH_PRIVATE.FM_FILE_USER.FM_PHP_EXTENSION;
			if (file_exists($file))
			{
				$user = array();
				include $file;
				phpUser::$user += $user;
			}
			cache::$value->set('arrayuser','cached_user',phpUser::$user,kernel::$config['user']['cache_lifetime']);
		}
	}
	
	function userExists($login)
	{
	 	return array_key_exists($login,self::$user);
	}
	
	function getPassword($login)
	{
		if (user::userExists($login) && array_key_exists('password',phpUser::$user[$login]))
			return phpUser::$user[$login]['password'];
	}
	
	function getUser($login)
	{
		$user = user::anonymous();
		
		if (user::userExists($login))
		{
			$data = phpUser::$user[$login];
			$user->name  = $login;
			$user->id    = crc32($login);
			$user->login = $login;
			
			if (isset($data['password']))
				unset($data['password']);
			
			foreach ($data as $key=>$value)
				$user->$key = $value;
		}
		return $user;
	}
}