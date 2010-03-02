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
		$user        = new stdClass();
		$user->data  = array();
		$user->group = 'anonymous';
		$user->ip    = _ip();
		$user->login = 'anonymous';
		$user->name  = _l('anonymous',null,true);
		return $user;
	}
	
	static function get($login)
	{
		if (is_object(user::$o))
			return user::$o->get($login);
	}
	
	static function getPassword($login)
	{
		if (is_object(user::$o))
			return user::$o->getPassword($login);
	}
	
	static function exists($login)
	{
		if (is_object(user::$o))
			return user::$o->exists($login);
	}
	
	static function save($user)
	{
		return user::$o->save($user);
	}
}