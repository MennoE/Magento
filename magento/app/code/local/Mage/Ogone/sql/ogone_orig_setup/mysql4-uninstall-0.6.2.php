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

$installer->removeAttribute('quote_payment', 'ogone_method');
$installer->removeAttribute('quote_payment', 'ogone_brand');

$installer->removeAttribute('order_payment', 'ogone_method');
$installer->removeAttribute('order_payment', 'ogone_brand');

$installer->removeAttribute('order_payment', 'ogone_payid');
$installer->removeAttribute('order_payment', 'ogone_status');
$installer->removeAttribute('order_payment', 'ogone_cardno');
$installer->removeAttribute('order_payment', 'ogone_ncerror');
$installer->removeAttribute('order_payment', 'ogone_accemptance');
$installer->removeAttribute('order_payment', 'ogone_currency');

$installer->endSetup();
