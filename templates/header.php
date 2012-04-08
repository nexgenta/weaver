<?php

if(!isset($page_type)) $page_type = '';
$page_type .= ' weaver';

$global_nav = array(
	'home' => array('link' => 'http://nexgenta.com/', 'name' => 'Nexgenta'),
	'baird' => array('link' => 'http://projectbaird.com/', 'name' => 'Project Baird'),
	'programmes' => array('link' => 'http://programmes.nexgenta.com', 'name' => 'Programmes'),
	);


require_once($templates_path . 'nexgenta/header.php');
