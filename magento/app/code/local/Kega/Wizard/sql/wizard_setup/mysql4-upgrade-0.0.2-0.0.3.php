<?php
$installer = $this;

$installer->startSetup();

/**
 * When customer logs in and has created a new quote (but has a original quote) we will merge them.
 * During this merge, we want to keep track of what items are merged, or not.
 * In this way we can mark all quote items that are restored on the checkout page.
 */
$installer->getConnection()->addColumn(
	$installer->getTable('sales/quote'),
	'merged_with_quote_id',
	"INT(10) NULL"
);

$installer->getConnection()->addColumn(
	$installer->getTable('sales/quote_item'),
	'merged_from_quote_id',
	"INT(10) NULL"
);

$installer->endSetup();