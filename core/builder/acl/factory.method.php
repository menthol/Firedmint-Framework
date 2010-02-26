<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	if (is_null(acl::$o))
		acl::$o = _subClass('acl',kernel::$config['acl']['engine'],true);
}