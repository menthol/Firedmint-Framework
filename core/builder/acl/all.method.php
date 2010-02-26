<?php
if (!definied('FM_SECURITY')) die();

static function all($roleGroup,$role)
{
	if (is_object(acl::$o))
		return acl::$o->all($roleGroup,$role);
}