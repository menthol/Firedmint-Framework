<?php
if (!defined('FM_SECURITY')) die();

class sessionAuth
{
	static $sessionId = null;
	
	static function factory()
	{
		if ((sessionAuth::$sessionId = session_id()) == "")
		{
			session_start();
			sessionAuth::$sessionId = session_id();
			session_write_close();
		}
		
		event::hook('form','login:process',array('sessionAuth','__login'),'before');
		
		event::hook('auth','logout_filter',array('sessionAuth','__logout'),'before');
		return new sessionAuth();
	}
	
	function getUser()
	{
		if (user::exists($user = cache::getStatic('sessionAuth',sessionAuth::$sessionId)))
			return user::get($user);
	
		return user::anonymous();
	}
	
	static function __login($formName,$user)
	{
		if (user::exists($user))
			cache::setStatic('sessionAuth',sessionAuth::$sessionId,$user);
	}
	
	static function __logout()
	{
		if (!auth::getUser()->anonymous)
		{
			if (user::exists($user = cache::getStatic('sessionAuth',sessionAuth::$sessionId)))
				cache::deleteStatic('sessionAuth',sessionAuth::$sessionId);
				
			_redirect(_thisPage());
		}
	}
}
