<?php

$installer = $this;

$installer->startSetup();


$installer->addAttribute('order', 'instorecode', array (
    'backend_type'    => 'text',     // MySQL-DataType
    'frontend_input'  => '', // Type of the HTML-Form-Field
    'is_global'       => '1',
    'is_visible'      => '1',
    'is_required'     => '0',
    'is_user_defined' => '1',
	'frontend_label'  => 'In store order code',
));

$installer->addAttribute('order', 'collected_at', array (
    'backend_type'    => 'datetime',     // MySQL-DataType
    'frontend_input'  => 'date', // Type of the HTML-Form-Field
    'is_global'       => '1',
    'is_visible'      => '1',
    'is_required'     => '0',
    'is_user_defined' => '1',
    'default'		  => '0000-00-00 00:00:00',
	'frontend_label'  => 'Collected at',
));
$installer->addAttribute('order', 'accepted_at', array (
    'backend_type'    => 'datetime',     // MySQL-DataType
    'frontend_input'  => 'date', // Type of the HTML-Form-Field
    'is_global'       => '1',
    'is_visible'      => '1',
    'is_required'     => '0',
    'is_user_defined' => '1',
    'default'		  => '0000-00-00 00:00:00',
	'frontend_label'  => 'Collected at',
));

$installer->addAttribute('invoice', 'accepted_at', array (
    'backend_type'    => 'datetime',
    'frontend_input'  => 'date',
    'is_global'       => '1',
    'is_visible'      => '1',
    'is_required'     => '0',
    'is_user_defined' => '1',
	'default'		  => '0000-00-00 00:00:00',
	'frontend_label'  => 'Accepted at',
));

$installer->addAttribute('invoice', 'collected_at', array (
    'backend_type'    => 'datetime',
    'frontend_input'  => 'date',
    'is_global'       => '1',
    'is_visible'      => '1',
    'is_required'     => '0',
    'is_user_defined' => '1',
    'default'		  => '0000-00-00 00:00:00',
	'frontend_label'  => 'Collected at',
));

$installer->endSetup();