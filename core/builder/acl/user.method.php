<?php
if (!definied('FM_SECURITY')) die();

static function user($user,$roleGroup,$role)
{
	if (is_object(acl::$o))
		return acl::$o->user($user,$roleGroup,$role);
}