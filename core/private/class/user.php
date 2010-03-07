<?php
if (!defined('FM_SECURITY')) die();

class user
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(user::$o))
			user::$o = _subClass('user',config::$config['user']['engine']);
		
		return user::$o;
	}
	
	static function anonymous()
	{
		$user            = new stdClass();
		$user->anonymous = true;
		$user->data      = array();
		$user->group     = 'anonymous';
		$user->ip        = _ip();
		$user->login     = 'anonymous';
		$user->name       = _l('anonymous');
		return $user;
	}
	
	static function check($login,$password)
	{
		return user::$o->check($login,$password);
	}
	
	static function get($login)
	{
		return user::$o->get($login);
	}
	
	static function getPassword($login)
	{
		return user::$o->getPassword($login);
	}
	
	static function exists($login)
	{
		return user::$o->exists($login);
	}
	
	static function save($user)
	{
		return user::$o->save($user);
	}
}