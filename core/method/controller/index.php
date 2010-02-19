<?php 
if (!defined('FM_SECURITY')) die();

function index()
{
	return view::factory(strlen($this->arguments['extension'])?$this->arguments['extension']:'html',array("{$this->controller}.{$this->action}","{$this->controller}"),$this->arguments);
}