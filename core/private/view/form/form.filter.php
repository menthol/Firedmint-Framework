<?php 
if (!defined('FM_SECURITY')) die();

$view->data['formAction'] = _thisPage();
$view->cache = 0;
$view->data['fromKey'] = sha1(var_export(FmForm::getDefinition($view->data['formName']),true));
