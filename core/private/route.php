<?php 
if (!defined('FM_SECURITY')) die();

$route['/__ajax/%param%'] = array('__ajax');
$route['/__cron']         = array('__cron');
$route['/']               = array('index');
