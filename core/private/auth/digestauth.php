<?php
if (!defined('FM_SECURITY')) die();

class digestAuth
{
	function getUser()
	{
		$user = user::anonymous();
		$realm = kernel::$config['digestAuth']['realm'];
		
		if (array_key_exists('PHP_AUTH_DIGEST',$_SERVER) && is_array($data = digestAuth::digestParse($_SERVER['PHP_AUTH_DIGEST'])))
		{
			if (user::userExists($data['username']))
			{
				$A1 = md5($data['username'] . ':' . $realm . ':' . user::getPassword($data['username']));
				$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
				$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
				
				if ($data['response'] == $valid_response)
				{
					return user::getUser($data['username']);
				}
			}
		}
		
		return $user;
	}
	
	static function digestParse($string)
	{	
		foreach (array('username','nonce','response','opaque','cnonce','uri') as $key)
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
}
