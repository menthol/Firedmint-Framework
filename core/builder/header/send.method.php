<?php
if (!definied('FM_SECURITY')) die();

static function send($content = null)
{		
	if (empty(header::$headers['X-Generator']))
		header::$headers['X-Generator'] = kernel::$config['header']['generator'];
	
	if (class_exists('cache'))
	{
		header::$headers['Expires'] = gmdate("D, d M Y H:i:s",cache::$expire) . " GMT";
		header::$headers['Last-Modified'] = gmdate("D, d M Y H:i:s",cache::$lastUpdate) . " GMT";
	}
	
	if (empty(header::$headers['Content-Type']))
		header::$headers['Content-Type'] = 'text/html; charset=utf-8';
		
	if (array_key_exists(header::$headers['Content-Type'],header::$mimeCodes))
		header::$headers['Content-Type'] = header::$mimeCodes[header::$headers['Content-Type']];
	
	if (array_key_exists('Status',header::$headers) && is_numeric(header::$headers['Status']))
	{
		header('x',true,header::$headers['Status']);
		unset(header::$headers['Status']);
	}
	
	foreach (header::$headers as $key=>$header)
		if (!empty($header))
			header("$key: $header");
}