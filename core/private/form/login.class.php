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
		return true;
	}
	
	static function process($formName)
	{
		event::trigger('form','login:process','before|after',$formName,form::getValue($formName,'username'));
	}
}