<?php 
if (!defined('FM_SECURITY')) die();

if (isset($view->data['path']) && strlen($view->data['path'])>0) return '<script type="text/javascript" src="'._path($view->data['path']).'"></script>'.PHP_EOL;
return '';