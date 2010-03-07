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
						
			foreach (_getPaths('private/user.php') as $file)
			{
				$user = array();
				include $file;
				phpUser::$user += $user;
			}
			cache::set('phpuser','cached_user',phpUser::$user,config::$config['user']['cache_lifetime']);
		}
	}
	
	function check($login,$password)
	{
		if (isset(phpUser::$user[$login]['password']) && phpUser::$user[$login]['password']==$password)
			return true;
		
		return false;
	}
	
	function exists($login)
	{
	 	return isset(phpUser::$user[$login]);
	}
	
	function getPassword($login)
	{
		if (isset(phpUser::$user[$login]['password']))
			return phpUser::$user[$login]['password'];
	}
	
	function get($login)
	{
		$user = user::anonymous();
		
		if (user::exists($login))
		{
			$data = phpUser::$user[$login];
			$user->name      = $login;
			$user->login     = $login;
			$user->anonymous = false;
			
			if (isset($data['password']))
				unset($data['password']);
			
			foreach ($data as $key=>$value)
				$user->{$key} = $value;
		}
		return $user;
	}
	
	function save($user)
	{
		if ($user->anonymous)
			return;
		
		if (!user::exists($user->login))
			foreach (user::anonymous() as $key=>$value)
				phpUser::$user[$user->login][$key] = $value;
		
		foreach ($user as $key=>$value)
			phpUser::$user[$user->login][$key] = $value;
			
		return cache::setStatic('phpuser','static_user',phpUser::$user);
	}
}