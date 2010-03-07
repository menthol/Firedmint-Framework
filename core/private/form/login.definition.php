<?php
if (!defined('FM_SECURITY')) die();

$form = array(
	'validator' => array(
		array('isUser','username','password','loginform:not a user'),
	),
	'element' => array(
		'username' => array(
			'type' => 'text',
			'required' => true,
			'label'    => 'loginform:user',
			'validator' =>array(
				array('username'),
				array('maxLength',10,'loginform:too long'),
				array('minLength',5,'loginform:too short'),
			),
		),
		'password' => array(
			'type' => 'password',
			'required' => true,
			'label'    => 'loginform:password',
			'validator' => array(
				array('minLength',5,'loginform:too long'),
				array('maxLength',10,'loginform:too short'),
			)
		),
		'submit' => array(
			'type' => 'submit',
			'label' => 'loginform:login',
		),
	),
	'static' => true,
);