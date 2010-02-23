<?php 
if (!defined('FM_SECURITY')) die();

static function addJs($js)
{
	$js = trim($js);
	html::$js[$js] = $js;
}
