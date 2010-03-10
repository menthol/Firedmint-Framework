<?php
if (!defined('FM_SECURITY')) die();

class loginForm
{
	static function load($formName)
	{
		// none
	}
	
	static function validate($formName)
	{
		$return = true;
		
		if (!user::exists(form::getValue($formName,'username')))
			$return = false;
		else if (!user::check(form::getValue($formName,'username'),form::getValue($formName,'password')))
			$return = false;
		
		return $return;
	}
	
	static function process($formName)
	{
		event::trigger('form','login:process','before|after',$formName,form::getValue($formName,'username'));
	}
}