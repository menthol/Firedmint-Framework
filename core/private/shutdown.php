<?php 
if (!defined('FM_SECURITY')) die();

FmUser::save(FmAuth::getUser());
