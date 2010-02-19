<?php 
if (!defined('FM_SECURITY')) die();

function classStart($lang = null)
{
	if ($lang==null)
		$lang = $this->o->lang[0];
	elseif (is_a($lang,'fm'))
		$this->lang = $lang->value;
	else
		$this->lang = $lang;
	
	if (array_key_exists($this->lang,$this->o->lang_string))
		return $this;
	
	$l = array();
	
	$file = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_L10N.$this->lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$file = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_L10N.$this->lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$file = FM_PATH_CORE.FM_PATH_L10N.$this->lang.FM_PHP_EXTENSION;
	if (file_exists($file) && is_readable($file))
	{
		$tmp_l = $l;
		$l = array();
		include $file;
		$l = array_replace_recursive($l,$tmp_l);
	}
	
	$this->o->lang_string = $l; 
	return $this;
}
