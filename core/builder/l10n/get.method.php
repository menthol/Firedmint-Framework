<?php
if (!definied('FM_SECURITY')) die();

static function get($lang,$key,$args = array())
{
	if (is_object(l10n::$o))
		return l10n::$o->get($lang,$key,$args);
	
	return $key;
}
