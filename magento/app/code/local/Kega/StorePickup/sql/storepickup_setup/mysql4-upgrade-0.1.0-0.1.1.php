<?php
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();

// 1 or 0; if store notifiction email was sent or not - for store pickup orders 
$conn->addColumn($installer->getTable('sales_flat_order'), 'store_pickup_notification_sent', 'char(1)');

$installer->endSetup();