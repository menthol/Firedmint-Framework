<?php 
if (!defined('FM_SECURITY')) die();

function core_l10n_method_classBoot($fm)
{
	$fm->lang = array(fm::$config['l10n']['local']);
	$fm->lang_string = array();
	$fm->ext = array();
}

function core_l10n_method_classConstruct($fm)
{
	$tmp = clone $fm;
	$tmp->o = $fm;
	return $tmp;
}

function core_l10n_method_classStart($fm, $lang = null)
{
	if ($lang==null)
		$lang = $fm->o->lang[0];
	$fm->lang = $lang;
	
	if (array_key_exists($fm->lang,$fm->o->lang_string))
		return $fm;
	
	$l = array();
	
	$file = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_L10N.$fm->lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$file = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_L10N.$fm->lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$file = FM_PATH_CORE.FM_PATH_L10N.$fm->lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$fm->o->lang_string = $l; 
	return $fm;
}

function core_l10n_method_print($fm,$key)
{
	if (array_key_exists($key,$fm->o->lang_string))
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		vprintf($fm->o->lang_string[$key],$args);
	}
	else
		print $key;
	
	return $fm;
}

function core_l10n_method_val($fm,$key)
{
	$return = clone fm::$stdObj;
	$return->value = $key;
	
	if (array_key_exists($key,$fm->o->lang_string))
	{
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		$return->value = vsprintf($fm->o->lang_string[$key],$args);
	}
	else
		$return->value = $key;
	
	return $return;
}