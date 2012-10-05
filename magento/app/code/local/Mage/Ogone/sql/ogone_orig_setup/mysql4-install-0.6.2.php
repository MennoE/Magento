<?php
/**
 * Magento SPPLUS extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Mage_Ogone
 * @copyright  Copyright (c) 2008 ALTIC Charly Clairmont (CCH)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

/* @var $installer Mage_Ogone_Model_Entity_Setup */

$installer->startSetup();

// quote_payment ogone data
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_payment'), 'ogone_method', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_payment'), 'ogone_brand', 'varchar(255)');


// order_payment ogone data
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_method', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_brand', 'varchar(255)');

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_payid', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_status', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_cardno', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_ncerror', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_accemptance', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_payment'), 'ogone_currency', 'varchar(255)');


$installer->endSetup();
