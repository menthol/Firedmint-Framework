<?php 
if (!defined('FM_SECURITY')) die();

function classBoot()
{
	$this->lang = array(fm::$config['l10n']['local']);
	$this->lang_string = array();
	$this->ext = array();
	return $this;
}
