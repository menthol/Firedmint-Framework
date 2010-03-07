<?php
if (!defined('FM_SECURITY')) die();

class digestAuth
{
	static function factory()
	{
		event::hook('auth','login_filter',array('digestAuth','__login'),'before');
		event::hook('auth','logout_filter',array('digestAuth','__logout'),'before');
		return new digestAuth();
	}
	
	function getUser()
	{
		if (   isset($_SERVER['PHP_AUTH_DIGEST'])
			&& is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST']))
			&& is_array($openNonce = cache::getStatic('digestauth','opennonce'))
			&& isset($openNonce[$data['nonce']])
			&& ($openNonce[$data['nonce']] + config::$config['auth']['max_idle_time']) > time()
			&& user::exists($data['username']))
		{
			$A1 = md5($data['username'] . ':' . $data['realm'] . ':' . user::getPassword($data['username']));
			$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
			
			if ($data['response'] == $valid_response)
			{
				$openNonce[$data['nonce']] = time();
				cache::getStatic('digestauth','opennonce',$openNonce);
				return user::get($data['username']);
			}
		}
		
		return user::anonymous();
	}
	
	static function __digestParse($string)
	{	
		foreach (array('username','nonce','response','opaque','cnonce','uri','realm') as $key)
		{
			preg_match('/'.$key.'="([^"]*)"/i', $string, $match);
			if (!isset($match[1]))
				return;
			$data[$key] = $match[1];
		}
		
		preg_match('/nc="?([0-9a-f]*)/i', $string, $match);
		if (!isset($match[1]))
			return;
		$data['nc'] = $match[1];
		
		// ie compatibility
		preg_match('/qop="?([^," ]*)/i', $string, $match);
		if (!isset($match[1]))
			return;
		$data['qop'] = $match[1];
		
		return $data;
	}
	
	static function __login($view)
	{
		if (isset($_SERVER['PHP_AUTH_DIGEST']))
		{
			if (is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST'])))
				digestAuth::__auth();
			else
			{
				header::set('Status',400,true);
				return $view->part('__400');
			}
		}
		else 
			digestAuth::__auth();
		
		if (is_string(config::$config['auth']['fail_login_route']))
			return $view->part(config::$config['auth']['fail_login_route']);
	}
	
	static function __logout($view)
	{
		if (   isset($_SERVER['PHP_AUTH_DIGEST'])
			&& is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST']))
			&& is_array($openNonce = cache::getStatic('digestauth','opennonce'))
			&& isset($openNonce[$data['nonce']]))
		{
			header::set('Status',401,true);
			unset($openNonce[$data['nonce']]);
			cache::setStatic('digestauth','opennonce',$openNonce);
			digestAuth::__auth(true,'0000000l090c7');
		}
		elseif (   isset($_SERVER['PHP_AUTH_DIGEST'])
				&& is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST']))
				&& $data['nonce']!='0000000l090c7')
		{			
			digestAuth::__auth(true,'0000000l090c7');
		}
		elseif (!isset($_SERVER['PHP_AUTH_DIGEST']))
		{
			digestAuth::__auth(true,'0000000l090c7');
		}
		header::set('Status',401,true);
	}
	
	static function __auth($stale = null,$nonce = null)
	{
		if ($stale===true) $stale = ',stale=true';
		elseif ($stale===false) $stale = ',stale=false';
		else $stale = '';
		
		$realm = utf8_decode(_l('auth-realm'));
		if (is_null($nonce))
			$nonce = uniqid();
		
		$domain = '/ http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'s':null).'://'.$_SERVER["SERVER_NAME"].'/ '.'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'s':null).'://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].'/';
		header::set('WWW-Authenticate','Digest realm="'.$realm.'",qop="auth",nonce="'.$nonce.'",opaque="'.md5($nonce).'",domain="'.$domain.'"'.$stale,true);
		header::set('Status',401,true);
		if($nonce!='0000000l090c7')
		{
			if (!is_array($openNonce = cache::getStatic('digestauth','opennonce')))
				$openNonce = array();
			
			$openNonce[$nonce] = time();
			cache::setStatic('digestauth','opennonce',$openNonce);
		}
	}
}
