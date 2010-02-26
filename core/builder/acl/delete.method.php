<?php
if (!definied('FM_SECURITY')) die();

static function delete($category,$name,$roleGroup,$role)
{
	if (is_object(acl::$o))
		return acl::$o->delete($category,$name,$roleGroup,$role);
}