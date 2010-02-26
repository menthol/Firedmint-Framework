<?php
if (!definied('FM_SECURITY')) die();

static function group($group,$roleGroup,$role)
{
	if (is_object(acl::$o))
		return acl::$o->group($group,$roleGroup,$role);
}