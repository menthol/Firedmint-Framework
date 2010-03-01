<?php
if (!definied('FM_SECURITY')) die();

static function informExpire($expire)
{
	if (!isset(cache::$expire) || empty(cache::$expire))
		cache::$expire = $expire;
	elseif ($expire < cache::$expire)
		cache::$expire = $expire;
}