<?php
if (!definied('FM_SECURITY')) die();

static function getUser($login)
{
	if (is_object(user::$o))
	{
		$user = user::$o->getUser($login);
		if (!is_array($user->data = cache::$value->get('user',$user->id.':user_data')))
			$user->data = array();
			
		return $user;
	}
}