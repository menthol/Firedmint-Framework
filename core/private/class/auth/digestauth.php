<?php
if (!defined('FM_SECURITY')) die();

class digestAuth
{
	function getUser()
	{
		if (   array_key_exists('PHP_AUTH_DIGEST',$_SERVER)
			&& is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST']))
			&& is_array($openNonce = cache::getStatic('digestauth','opennonce'))
			&& array_key_exists($data['opaque'],$openNonce)
			&& ($openNonce[$data['opaque']] + config::$config['auth']['max_idle_time']) > time()
			&& user::exists($data['username']))
		{
			$A1 = md5($data['username'] . ':' . $data['realm'] . ':' . user::getPassword($data['username']));
			$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
			
			if ($data['response'] == $valid_response)
			{
				$openNonce[$data['opaque']] = time();
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
	
	function login($view,$deniedView = '__403')
	{
		$realm = _l('restricted-area',null,true);
		$nonce = uniqid();
		header::set('WWW-Authenticate','Digest realm="'.$realm.'",qop="auth",nonce="'.$nonce.'",opaque="'.md5($nonce).'"',true);
		header::set('Status',401,true);
		if (!is_array($openNonce = cache::getStatic('digestauth','opennonce')))
			$openNonce = array();
		
		$openNonce[md5($nonce)] = time();
		cache::setStatic('digestauth','opennonce',$openNonce);
		if (is_string($deniedView))
			return $view->part($deniedView);
	}
	
	function logout($view)
	{
		
		if (   array_key_exists('PHP_AUTH_DIGEST',$_SERVER)
			&& is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST']))
			&& is_array($openNonce = cache::getStatic('digestauth','opennonce'))
			&& array_key_exists($data['opaque'],$openNonce))
			{
				unset($openNonce[$data['opaque']]);
				cache::setStatic('digestauth','opennonce',$openNonce);
			}
	}
}
