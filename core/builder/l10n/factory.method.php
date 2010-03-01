<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(l10n::$o))
		l10n::$o = _subClass('l10n',kernel::$config['l10n']['engine']);
	
	l10n::$lang = kernel::$config['l10n']['default'];
	return l10n::$o;
}