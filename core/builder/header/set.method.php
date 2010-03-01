<?php
if (!definied('FM_SECURITY')) die();

static function set($key,$value = null)
{
	if (array_key_exists($key,header::$headers))
		header::$headers[$key] = $value;
}