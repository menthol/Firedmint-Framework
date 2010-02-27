<?php 
if (!defined('FM_SECURITY')) die();

$route['/']               = array('index');
$route['/login']          = array('login');
$route['/logout']         = array('logout');
$route['/__ajax/%param%'] = array('__ajax',array('extension'=>'xml|html|json'));
$route['/__cron']         = array('__cron',array('extension'=>'xml|html|json'));

