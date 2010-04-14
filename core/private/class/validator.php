<?php
if (!defined('FM_SECURITY')) die();

class validator
{
	static function maxLength($formName,$element,$length,$l10nKey = null)
	{
		if (empty($l10nKey)) $l10nKey = "{$formName}form:too long";
		if (strlen(form::getValue($formName,$element))<=$length)
			return true;
		
		form::addError($formName,_l(form::$form[$formName]['element'][$element]['label']).' : '._l($l10nKey));
		return false;
	}
	
	static function minLength($formName,$element,$length,$l10nKey = null)
	{
		if (empty($l10nKey)) $l10nKey = "{$formName}form:too short";
		if (strlen(form::getValue($formName,$element))>=$length)
			return true;

		form::addError($formName,_l(form::$form[$formName]['element'][$element]['label']).' : '._l($l10nKey));
		return false;
	}
	
	static function username($formName,$element,$l10nKey = null)
	{
		if (empty($l10nKey)) $l10nKey = "{$formName}form:invalid username";
		if (preg_match('/^([\w@\.]+)$/i',form::getValue($formName,$element)))
			return true;
		
		form::addError($formName,_l(form::$form[$formName]['element'][$element]['label']).' : '._l($l10nKey));
		return false;
	}
	
	static function isLoginable($formName,$elementUsername,$elementPassword,$l10nKey = null)
	{
		if (empty($l10nKey)) $l10nKey = "{$formName}form:invalid username/password";
		
		if (user::exists(form::getValue($formName,$elementUsername)) && user::check(form::getValue($formName,$elementUsername),form::getValue($formName,$elementPassword)))
			return true;
		
		form::addError($formName,_l($l10nKey));
		return false;
	}
}
