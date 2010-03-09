<?php
if (!defined('FM_SECURITY')) die();

class FmUser
{
	public  static  $o;
	
	static function factory()
	{
		if (!is_object(FmUser::$o))
			FmUser::$o = _subClass('FmUser',FmConfig::$config['user']['engine']);
		
		return FmUser::$o;
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
		return FmUser::$o->check($login,$password);
	}
	
	static function get($login)
	{
		return FmUser::$o->get($login);
	}
	
	static function getPassword($login)
	{
		return FmUser::$o->getPassword($login);
	}
	
	static function exists($login)
	{
		return FmUser::$o->exists($login);
	}
	
	static function save($user)
	{
		return FmUser::$o->save($user);
	}
}