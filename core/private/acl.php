<?php 
if (!defined('FM_SECURITY')) die();

$acl['group']['*']['route']['*/*']        = true;
$acl['group']['*']['route']['__fm/login'] = true;
$acl['group']['*']['route']['__fm/ajax']  = true;
$acl['group']['*']['route']['__fm/cron']  = true;

$acl['group']['*']['failRoute']['*/*']    = array('__fm','login');

$acl['user']  = array();
