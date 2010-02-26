<?php 
if (!defined('FM_SECURITY')) die();

$acl['all']['route']['*/*']        = true;
$acl['all']['route']['__fm/login'] = true;
$acl['all']['route']['__fm/ajax']  = true;
$acl['all']['route']['__fm/cron']  = true;

$acl['all']['failRoute']['*/*']    = array('__fm','login');

$acl['group'] = array();

$acl['user']  = array();
