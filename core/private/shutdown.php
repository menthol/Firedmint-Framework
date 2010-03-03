<?php 
if (!defined('FM_SECURITY')) die();

user::save(auth::getUser());
