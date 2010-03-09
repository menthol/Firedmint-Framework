<?php
if (!defined('FM_SECURITY')) die();

class FmPrintfParser
{
	static function parse($value, $args)
	{
		return vsprintf($value,$args);
	}
}