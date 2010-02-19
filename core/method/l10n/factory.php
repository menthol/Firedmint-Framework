<?php
if (!definied('FM_SECURITY')) die();

static function factory($l10n = null)
{
	if (is_null($l10n))
	{
		$l10n = fm::$config['l10n']['local'];
	}
	
	if (is_object($l10n))
		$l10n = $l10n->value;
	
	if (array_key_exists($l10n,l10n::$lang))
	{
		return l10n::$lang[$l10n];
	}
	l10n::$lang[$l10n] = new l10n($l10n);
	return l10n::$lang[$l10n];
}