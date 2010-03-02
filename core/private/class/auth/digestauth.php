<?php
if (!defined('FM_SECURITY')) die();

class digestAuth
{
	function getUser()
	{
		if (array_key_exists('PHP_AUTH_DIGEST',$_SERVER) && is_array($data = digestAuth::__digestParse($_SERVER['PHP_AUTH_DIGEST'])))
		{
			if (user::userExists($data['username']))
			{
				$A1 = md5($data['username'] . ':' . $data['realm'] . ':' . user::getPassword($data['username']));
				$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
				$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);
				
				if ($data['response'] == $valid_response)
				{
					$user = user::getUser($data['username']);
					if (!array_key_exists('digestauth',$user->data))
						$user->data['digestauth'] = array();
					
					if (!array_key_exists('login_places',$user->data['digestauth']))
						$user->data['digestauth']['login_places'] = array();
					
					$connect_key = sha1($data['nonce']._ip());
					if (!array_key_exists($connect_key,$user->data['digestauth']['login_places']))
						$user->data['digestauth']['login_places'][$connect_key] = time();
					
					foreach ($user->data['digestauth']['login_places'] as $key=>$value)
						if (($user->data['digestauth']['login_places'][$key] + config::$config['auth']['max_idle_time'])<time())
							unset($user->data['digestauth']['login_places'][$key]);
					
					if (array_key_exists($connect_key,$user->data['digestauth']['login_places']))
						$user->data['digestauth']['login_places'][$connect_key] = time();
					
					user::userSave($user);
					
					if (array_key_exists($connect_key,$user->data['digestauth']['login_places']))
						return $user;
				}
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
}
