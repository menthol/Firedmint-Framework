<?php
if (!definied('FM_SECURITY')) die();

static function routeControl($user,$route)
{
	if (is_object(acl::$o))
		return acl::$o->routeControl($user,$route);
	
	return $route;
}