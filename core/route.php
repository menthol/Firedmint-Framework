<?php 
if (!defined('FM_SECURITY')) die();

$route['/@fm_ajax@/%param%'] = array('fmController','ajax',array('extension'=>'html|json|xml'));
$route['/@fm_cron@'] = array('fmController','cron');
