<?php
if (!definied('FM_SECURITY')) die();

static function printf($value,$args = array())
{
	if (is_object(l10n::$o))
		return vsprintf($value,$args);
	
	return $value;
}
