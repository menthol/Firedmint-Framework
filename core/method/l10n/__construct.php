<?php 
if (!defined('FM_SECURITY')) die();

function __construct($lang)
{
	if (is_a($lang,'l10n'))
		$lang = $lang->value;

		
	$l = array();
	
	$file = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_L10N.$lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$file = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_L10N.$lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$file = FM_PATH_CORE.FM_PATH_L10N.$lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$this->lang_string = $l; 
	return $this;
}
