<?php
if (!defined('FM_SECURITY')) die();

class phpUser
{
	static $user = array();
	function __construct()
	{
		if (_clear('user') || !is_array(phpUser::$user = cache::get('phpuser','cached_user')))
		{
			// compile users
			if (!is_array(phpUser::$user = cache::getStatic('phpuser','static_user')))
				phpUser::$user = array();
			
			$file = FM_PATH_SITE.FM_SITE_DIR.'private/user'.FM_PHP_EXTENSION;
			if (file_exists($file))
			{				
				$user = array();
				include $file;
				phpUser::$user += $user;
			}
			
			$file = FM_PATH_SITE.'all/private/user'.FM_PHP_EXTENSION;
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
			
			$file = FM_PATH_CORE.'private/user'.FM_PHP_EXTENSION;
			if (file_exists($file))
			{
				$user = array();
				include $file;
				phpUser::$user += $user;
			}
			cache::set('phpuser','cached_user',phpUser::$user,config::$config['user']['cache_lifetime']);
		}
	}
	
	function exists($login)
	{
	 	return array_key_exists($login,phpUser::$user);
	}
	
	function getPassword($login)
	{
		if (user::userExists($login) && array_key_exists('password',phpUser::$user[$login]))
			return phpUser::$user[$login]['password'];
	}
	
	function get($login)
	{
		$user = user::anonymous();
		
		if (user::exists($login))
		{
			$data = phpUser::$user[$login];
			$user->name  = $login;
			$user->id    = crc32($login);
			$user->login = $login;
			
			if (isset($data['password']))
				unset($data['password']);
			
			foreach ($data as $key=>$value)
				$user->{$key} = $value;
		}
		return $user;
	}
	
	function save($user)
	{
		if (!property_exists($user,'login') || empty($user->login) || $user->login == user::anonymous()->login)
			return;
		
		if (!user::userExists($user->login))
			foreach (user::anonymous() as $key=>$value)
				phpUser::$user[$user->login][$key] = $value;
		
		if (!array_key_exists('id',phpUser::$user[$user->login]))
			phpUser::$user[$user->login]['id'] = crc32($user->login);
		
		foreach ($user as $key=>$value)
			phpUser::$user[$user->login][$key] = $value;
			
		return cache::setStatic('phpuser','static_user',phpUser::$user);
	}
}