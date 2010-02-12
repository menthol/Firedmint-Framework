<?php 
if (!defined('FM_SECURITY')) die();

function core_url_method_classBoot($fm)
{
	fm::$core->find($fm->type.'/'.fm::$config['url']['type'])->include();
}