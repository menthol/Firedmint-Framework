<?php 

// start Firedmint Framework
@include 'firedmint.php';

if (!defined(FM_SECURITY))
{
	if (!headers_sent())
		header('HTTP/1.1 500 Internal Server Error');
		
	print '<html><head><title>500 Application Error</title></head><body><h1>Application Error</h1><p>The Firedmint application could not be launched.</p></body></html>';
}
