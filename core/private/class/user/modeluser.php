<?php
if (!defined('FM_SECURITY')) die();

class modelUser
{	
	function check($login,$password)
	{
		if (empty($login))
			return false;
		
		$user = _model(config::$config['user']['model']);
		
		$user->{config::$config['user']['user_field']} = $login;
		$user->{config::$config['user']['password_field']} = sha1($password);
		
		if (!$user->first())
			return false;
		
		return true;
	}
	
	function exists($login)
	{
		if (empty($login))
			return false;
		
		$user = _model(config::$config['user']['model']);
		
		$user->{config::$config['user']['user_field']} = $login;
		
		if (!$user->first())
			return false;
		
		return true;
	}
	
	function getPassword($login)
	{
		return sha1(rand().time());
	}
	
	function get($login)
	{
		$user = user::anonymous();
		
		if (user::exists($login))
		{
			$_user = _model(config::$config['user']['model']);
			$_user->{config::$config['user']['user_field']} = $login;
			$return = $_user->first();
			if (!empty($return))
			{		
				$user->login  = $return->{config::$config['user']['user_field']};
				$user->name  = $return->{config::$config['user']['name_field']};
				$user->group = $return->{config::$config['user']['group_field']};
				
				unset($return->{config::$config['user']['user_field']});
				unset($return->{config::$config['user']['name_field']});
				unset($return->{config::$config['user']['group_field']});
				unset($return->{config::$config['user']['password_field']});
				
				$user->anonymous = false;
				
				foreach ($return as $key=>$value)
					$user->{$key} = $value;
			}
		}
		return $user;
	}
	
	function save($user)
	{
		/*
		if ($user->anonymous)
			return;
		
		if (!user::exists($user->login))
			foreach (user::anonymous() as $key=>$value)
				phpUser::$user[$user->login][$key] = $value;
		
		foreach ($user as $key=>$value)
			phpUser::$user[$user->login][$key] = $value;
			
		return cache::setStatic('phpuser','static_user',phpUser::$user);
		*/
	}
	
}