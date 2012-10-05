<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Ogone
 * @copyright  Copyright (c) 2008 ALTIC Charly Clairmont (CCH)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * */

/**
 * PaymentMethod model
 *
 * @author      altic teams
 */

class Mage_Ogone_Model_Source_Currencies
{

	public function toOptionArray()
    {

		//TODO
		// HUF, ILS, ISK, JPY, LTL, LVL, MAD, MTL,
		// MXN, NOK, NZD, PLN, RUR, SEK, SGD, SKK, THB,
		// TRL, UAH, USD, XAF, XOF and ZAR
		return array(

			array('value' => 'AED', 'label' => Mage::helper('ogone')->__('United Arab Emirates Dirham')),
			array('value' => 'AUD', 'label' => Mage::helper('ogone')->__('Australian Dollar')),
			array('value' => 'CAD', 'label' => Mage::helper('ogone')->__('Canadian Dollar')),
			array('value' => 'CHF', 'label' => Mage::helper('ogone')->__('Swiss Franc')),
			array('value' => 'CNY', 'label' => Mage::helper('ogone')->__('Chinese Yuan Renminbi')),
			array('value' => 'CYP', 'label' => Mage::helper('ogone')->__('Cyprus Pound')),
			array('value' => 'CZK', 'label' => Mage::helper('ogone')->__('Czech koruna')),
			array('value' => 'DKK', 'label' => Mage::helper('ogone')->__('Danish Krone')),
			array('value' => 'EEK', 'label' => Mage::helper('ogone')->__('Estonian Kroon')),
			array('value' => 'EUR', 'label' => Mage::helper('ogone')->__('Euro')),
			array('value' => 'HKD', 'label' => Mage::helper('ogone')->__('Hong Kong Dollar')),
			array('value' => 'HRK', 'label' => Mage::helper('ogone')->__('Croatian Kuna')),
			array('value' => 'USD', 'label' => Mage::helper('ogone')->__('US Dollar')),
		);

	}
}

?>