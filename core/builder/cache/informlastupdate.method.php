<?php
if (!definied('FM_SECURITY')) die();

static function informLastUpdate($lastUpdate)
{
	if (!isset(cache::$lastUpdate) || empty(cache::$lastUpdate))
		cache::$lastUpdate = $lastUpdate;
	elseif ($lastUpdate > cache::$lastUpdate)
		cache::$lastUpdate = $lastUpdate;
}