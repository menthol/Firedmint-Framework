<?php
if (!definied('FM_SECURITY')) die();

static function factory()
{
	set_error_handler("_errorHandler");
}