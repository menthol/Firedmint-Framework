<?php 
if (!defined('FM_SECURITY')) die();

$route['/login']          = array('login');
$route['/logout']         = array('logout');
$route['/__ajax/%param%'] = array('__ajax');
$route['/__cron']         = array('__cron');
$route['/']               = array('index');
