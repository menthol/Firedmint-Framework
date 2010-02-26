<?php 
if (!defined('FM_SECURITY')) die();

$route['/__ajax/%param%'] = array('__fm','ajax');
$route['/__cron']         = array('__fm','cron');
