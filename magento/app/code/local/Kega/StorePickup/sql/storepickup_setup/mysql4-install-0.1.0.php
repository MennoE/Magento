<?php
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();

//$installer->addAttribute('order_address', 'pickup_store_id', array('type'=>'int'));
//$installer->addAttribute('order_address', 'pickup_store_name', array());
$conn->addColumn($installer->getTable('sales_flat_quote_address'), 'pickup_store_id', 'int(10) ');
$conn->addColumn($installer->getTable('sales_flat_quote_address'), 'pickup_store_name', 'varchar(40) after pickup_store_id');

$installer->endSetup();