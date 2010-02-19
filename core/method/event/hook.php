<?php
if (!defined('FM_SECURITY')) die();

function hook($event,$callback,$args = array(),$event_part = 'main')
{
	$event = trim(strtolower($event));
	if (!(array_key_exists($event,event::$event) && is_array(event::$event[$event])))
		event::$event[$event] = array('before'=>array(),'main'=>array(),'after'=>array());
	$event_part = trim(strtolower($event_part));
	if (!(array_key_exists($event_part,event::$event[$event]) && is_array(event::$event[$event][$event_part])))
		event::$event[$event][$event_part] = array();
	
	event::$event[$event][$event_part][$callback] = $args;
	return $this;
}