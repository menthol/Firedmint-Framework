<?php
if (!defined('FM_SECURITY')) die();

class FmPhpUser
{
	static $user = array();
	function __construct()
	{
		if (_clear('user') || !is_array(FmPhpUser::$user = FmCache::get('phpuser','cached_user')))
		{
			// compile users
			if (!is_array(FmPhpUser::$user = FmCache::getStatic('phpuser','static_user')))
				FmPhpUser::$user = array();
						
			foreach (_getPaths('private/user.php') as $file)
			{
				$user = array();
				include $file;
				FmPhpUser::$user += $user;
			}
			FmCache::set('phpuser','cached_user',FmPhpUser::$user,FmConfig::$config['user']['cache_lifetime']);
		}
	}
	
	function check($login,$password)
	{
		if (isset(FmPhpUser::$user[$login]['password']) && FmPhpUser::$user[$login]['password']==$password)
			return true;
		
		return false;
	}
	
	function exists($login)
	{
	 	return isset(FmPhpUser::$user[$login]);
	}
	
	function getPassword($login)
	{
		if (isset(FmPhpUser::$user[$login]['password']))
			return FmPhpUser::$user[$login]['password'];
	}
	
	function get($login)
	{
		$user = FmUser::anonymous();
		
		if (FmUser::exists($login))
		{
			$data = FmPhpUser::$user[$login];
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
		
		if (!FmUser::exists($user->login))
			foreach (FmUser::anonymous() as $key=>$value)
				FmPhpUser::$user[$user->login][$key] = $value;
		
		foreach ($user as $key=>$value)
			FmPhpUser::$user[$user->login][$key] = $value;
			
		return FmCache::setStatic('phpuser','static_user',FmPhpUser::$user);
	}
}