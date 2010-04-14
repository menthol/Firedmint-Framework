<?php
if (!defined('FM_SECURITY')) die();

$form = array(
	'validator' => array(
		array('isLoginable','username','password'),
	),
	'element' => array(
		'username' => array(
			'type' => 'text',
			'required' => true,
			'label'    => 'loginform:user',
			'validator' =>array(
				array('username'),
				array('maxLength',20),
				array('minLength',5),
			),
		),
		'password' => array(
			'type' => 'password',
			'required' => true,
			'label'    => 'loginform:password',
			'validator' => array(
				array('maxLength',20),
				array('minLength',5),
			)
		),
		'submit' => array(
			'type' => 'submit',
			'label' => 'loginform:login',
		),
	),
	'static' => true,
);