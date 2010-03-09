<?php
if (!defined('FM_SECURITY')) die();

class FmSessionAuth
{
	static $sessionId = null;
	
	static function factory()
	{
		if ((FmSessionAuth::$sessionId = session_id()) == "")
		{
			session_start();
			FmSessionAuth::$sessionId = session_id();
			session_write_close();
		}
		
		FmEvent::hook('form','login:process',array('FmSessionAuth','__login'),'before');
		
		FmEvent::hook('auth','logout_filter',array('FmSessionAuth','__logout'),'before');
		return new FmSessionAuth();
	}
	
	function getUser()
	{
		if (FmUser::exists($user = FmCache::getStatic('FmSessionAuth',FmSessionAuth::$sessionId)))
			return FmUser::get($user);
	
		return FmUser::anonymous();
	}
	
	static function __login($formName,$user)
	{
		if (FmUser::exists($user))
			FmCache::setStatic('FmSessionAuth',FmSessionAuth::$sessionId,$user);
	}
	
	static function __logout()
	{
		if (!FmAuth::getUser()->anonymous)
		{
			if (FmUser::exists($user = FmCache::getStatic('FmSessionAuth',FmSessionAuth::$sessionId)))
				FmCache::deleteStatic('FmSessionAuth',FmSessionAuth::$sessionId);
				
			_redirect(_thisPage());
		}
	}
}
