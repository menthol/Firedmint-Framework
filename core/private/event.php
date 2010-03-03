<?php 
if (!defined('FM_SECURITY')) die();

$event['auth']['login']['before'][] = array('auth','login');
$event['auth']['logout']['before'][] = array('auth','logout');
