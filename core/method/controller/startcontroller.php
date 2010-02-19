<?php 
if (!defined('FM_SECURITY')) die();

function startcontroller()
{
	if (method_exists($this,$this->action))
	{
		return call_user_func(array($this,$this->action));
	}
	else
	{
		return $this->index();
	}
}