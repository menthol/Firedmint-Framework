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
		
		if (!FmUser::exists(FmForm::getValue($formName,'username')))
			$return = false;
		else if (!FmUser::check(FmForm::getValue($formName,'username'),FmForm::getValue($formName,'password')))
			$return = false;
		
		return $return;
	}
	
	static function process($formName)
	{
		FmEvent::trigger('form','login:process','before|after',$formName,FmForm::getValue($formName,'username'));
	}
}