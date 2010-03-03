<?php 
if (!defined('FM_SECURITY')) die();

$acl['group']['*']['route']['*']       = true;
$acl['group']['*']['route']['__ajax']  = true;
$acl['group']['*']['route']['__cron']  = true;

$acl['group']['*']['failRoute']['*']   = array('__403');

$acl['user'] = array();
