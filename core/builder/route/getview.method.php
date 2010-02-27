<?php
if (!definied('FM_SECURITY')) die();

static function getView($uri,$GetArgs,$magicRoute)
{
	if (is_object(route::$o))
		return route::$o->getView($uri,$GetArgs,$magicRoute);
}