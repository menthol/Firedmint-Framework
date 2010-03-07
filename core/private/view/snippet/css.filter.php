<?php 
if (!defined('FM_SECURITY')) die();

if (isset($view->data['path']) && strlen($view->data['path'])>0)  return '<link rel="stylesheet" type="text/css" href="'._path($view->data['path']).'" />'.PHP_EOL;

return '';