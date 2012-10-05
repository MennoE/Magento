<?php
/**
 * Magento Ogone Payment extension
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

class Mage_Ogone_Block_Redirect extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

	protected function _toHtml()
	{

		$standard = Mage::getModel('ogone/method_ogone');
        $form = new Varien_Data_Form();
        $form->setAction($standard->getOgoneUrl())
            ->setId('Ogone_payment_checkout')
            ->setName('Ogone_payment_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);

		$form = $standard->addOgoneFields($form);

        $formHTML = $form->toHtml();

        $html = '<html><body>';
        $html.= $this->__('You will be redirected to Ogone in a few seconds.');
		$html.= $formHTML;
        $html.= '<script type="text/javascript">document.getElementById("Ogone_payment_checkout").submit();</script>';
        $html.= '</body></html>';

        if ($standard->getDebugMode()) {
            //TODO manage debug mode
        }

		return $html;
    }
}
?>
