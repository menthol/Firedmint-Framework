<?php
if (!definied('FM_SECURITY')) die();

static function set($category,$name,$roleGroup,$role,$value)
{
	if (is_object(acl::$o))
		return acl::$o->set($roleGroup,$role,$value);
}