<?php
if (!defined('FM_SECURITY')) die();

class printfParser
{
	static function parse($value, $args)
	{
		return vsprintf($value,$args);
	}
}