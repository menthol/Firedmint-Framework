<?php 
if (!defined('FM_SECURITY')) die();


$view->cache = 0;
$view->data['fromKey'] = sha1(var_export(form::getDefinition($view->data['formName']),true));

