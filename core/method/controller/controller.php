<?php 
if (!defined('FM_SECURITY')) die();

function controller($controller,$action,$vars)
{
	return $this->view(strlen($vars['extension'])?$vars['extension']:'html',array("$controller.$action","$controller"),$vars);
}