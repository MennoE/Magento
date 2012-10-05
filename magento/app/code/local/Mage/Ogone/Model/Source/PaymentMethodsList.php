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

class Mage_Ogone_Model_Source_PaymentMethodsList
{

	public function getPMList(){

		$pmList = array();
		// Cards: General
		/*$pm =  Mage::getModel('ogone/source_paymentMethod');
		$pm->setPmName('AIRPLUS');
		$pm->setPmValue('CreditCard');
		$pm->setPmBrand('AIRPLUS');
		$pm->setPmUrlLogo('AIRPLUS_choice.gif');
		$pm->setPmFamily('Cards: General');

		//$pmList[] = array('PmName' => 'AIRPLUS', 'PmValue' => 'CreditCard', 'PmBrand' => 'AIRPLUS', 'PmUrlLogo' => 'AIRPLUS_choice.gif', 'PmFamily' => 'Cards: General');

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('AIRPLUS','CreditCard',
									'AIRPLUS','AIRPLUS_choice.gif','Cards: General', false);
		$pmList[] = $pm;
		*/

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Cards: Commons ===','Cards: exceptions',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('CB','CreditCard',
				'','Carte%20bleue_choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('AIRPLUS','CreditCard',
				'AIRPLUS','AIRPLUS _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('American Express','CreditCard',
							 'American Express','American Express _choice.gif',
							 'Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Aurora','CreditCard',
							'Aurora','Aurora _choice.gif','Cards: General'
							, false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Aurore','CreditCard',
						'Aurore','Aurore _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Cofinoga','CreditCard',
				'Cofinoga','Cofinoga _choice.gif','Cards: General', false);
		$pmList[] = $pm;
		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Dankort','CreditCard',
							'Dankort','Dankort _choice.gif',
							'Cards: General', false);
		$pmList[] = $pm;
		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Diners Club','CreditCard',
		 'Diners Club','Diners Club _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('JCB','CreditCard',
		 'JCB','JCB _choice.gif','Cards: General', false);
		$pmList[] = $pm;
		
		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('MaestroUK','CreditCard',
		 'MaestroUK','MaestroUK _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('MasterCard','CreditCard',
		 'MasterCard','Eurocard_choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Solo','CreditCard',
		 'Solo','Solo _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('UATP','CreditCard',
		 'UATP','UATP _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('VISA','CreditCard',
				 'VISA','VISA _choice.gif','Cards: General', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Cards: exceptions ===','Cards: exceptions',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('BCMC','CreditCard',
				 'BCMC','BCMC _choice.gif','Cards: exceptions', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Maestro','CreditCard',
				 'Maestro','Maestro _choice.gif',
				 'Cards: exceptions', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Cards: Online Credit ===','Cards: Online Credit',
				 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('NetReserve','CreditCard',
				 'NetReserve','NetReserve _choice.gif',
				 'Cards: Online Credit', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('UNEUROCOM','UNEUROCOM',
				 'UNEUROCOM','UNEUROCOM _choice.gif',
				 'Cards: Online Credit', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== WebBanking ===','WebBanking',
				 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('CBC Online','CBC Online',
				 'CBC Online','CBC Online _choice.gif',
				 'WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('CENTEA Online','CENTEA Online',
				 'CENTEA Online','CENTEA Online _choice.gif',
				 'WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Dexia Direct Net','Dexia Direct Net',
				 'Dexia Direct Net','Dexia Direct Net _choice.gif',
				 'WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('eDankort','eDankort',
				 'eDankort','eDankort _choice.gif','WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('EPS','EPS',
				 'EPS','EPS _choice.gif','WebBanking', false);
		$pmList[] = $pm;
		
		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('DirectEbanking','DirectEbanking',
				 'Sofort Uberweisung','giropay_choice.gif','WebBanking', false);
		$pmList[] = $pm;
		
		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('giro pay','giro pay',
				 'giro pay','giropay_choice.gif','WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('iDEAL','iDEAL',
				 'iDEAL','iDEAL _choice.gif','WebBanking', false);
		$pmList[] = $pm;	
		

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('ING HomePay','ING HomePay',
				 'ING HomePay','ING HomePay _choice.gif','WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('KBC Online','KBC Online',
		 'KBC Online','KBC Online _choice.gif','WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('PostFinance Debit Direct','PostFinance Debit Direct',
		 'PostFinance Debit Direct','PostFinance Debit Direct _choice.gif','WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('PostFinance yellownet','PostFinance yellownet',
		 'PostFinance yellownet','PostFinance yellownet _choice.gif','WebBanking', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Direct Debit ===','Direct Debits',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Direct Debits DE','Direct Debits DE',
		 'Direct Debits DE','Direct Debits DE _choice.gif','Direct Debits', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Direct Debits NL','Direct Debits NL',
		 'Direct Debits NL','Direct Debits NL _choice.gif','Direct Debits', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Offline payment ===','Offline payment',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Acceptgiro','Acceptgiro',
		 'Acceptgiro','Acceptgiro _choice.gif','Offline payment', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Bank transfer','Bank transfer',
		 'Bank transfer','Bank transfer _choice.gif','Offline payment', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Payment on Delivery','Payment on Delivery',
		 'Payment on Delivery','Payment on Delivery _choice.gif','Offline payment', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Micro ===','Micro',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('MiniTix','MiniTix',
		 'MiniTix','MiniTix _choice.gif','Micro', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Mobile ===','Mobile',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('TUNZ','TUNZ',
		 'TUNZ','TUNZ _choice.gif','Mobile', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('=== Others ===','Others',
		 '','_choice.gif','', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('PAYPAL','PAYPAL',
		 'PAYPAL','PAYPAL _choice.gif','Others', false);
		$pmList[] = $pm;

		$pm = Mage::getModel('ogone/source_paymentMethod');
		$pm->__PaymentMethod('Wallie','Wallie',
		 'Wallie','Wallie _choice.gif','Others', false);
		$pmList[] = $pm;


		/*$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('American Express')
									->setPmValue('CreditCard')
									->setPmBrand('American Express')
									->setPmUrlLogo('American Express_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = $pm;

		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Aurora')
									->setPmValue('CreditCard')
									->setPmBrand('Aurora')
									->setPmUrlLogo('Aurora_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Aurore')
									->setPmValue('CreditCard')
									->setPmBrand('Aurore')
									->setPmUrlLogo('Aurore_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Cofinoga')
									->setPmValue('CreditCard')
									->setPmBrand('Cofinoga')
									->setPmUrlLogo('Cofinoga_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Dankort')
									->setPmValue('CreditCard')
									->setPmBrand('Dankort')
									->setPmUrlLogo('Dankort_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Diners Club')
									->setPmValue('CreditCard')
									->setPmBrand('Diners Club')
									->setPmUrlLogo('Diners Club_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('JCB')
									->setPmValue('CreditCard')
									->setPmBrand('JCB')
									->setPmUrlLogo('JCB_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('MaestroUK')
									->setPmValue('CreditCard')
									->setPmBrand('MaestroUK')
									->setPmUrlLogo('MaestroUK_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('MasterCard')
									->setPmValue('CreditCard')
									->setPmBrand('MasterCard')
									->setPmUrlLogo('MasterCard_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Solo')
									->setPmValue('CreditCard')
									->setPmBrand('Solo')
									->setPmUrlLogo('Solo_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('UATP')
									->setPmValue('CreditCard')
									->setPmBrand('UATP')
									->setPmUrlLogo('UATP_choice.gif')
									->setPmFamily('Cards: General');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('VISA')
									->setPmValue('CreditCard')
									->setPmBrand('VISA')
									->setPmUrlLogo('VISA_choice.gif')
									->setPmFamily('Cards: General');

		// Cards: exceptions
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('BCMC')
									->setPmValue('CreditCard')
									->setPmBrand('BCMC')
									->setPmUrlLogo('BCMC_choice.gif')
									->setPmFamily('Cards: exceptions');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Maestro')
									->setPmValue('CreditCard')
									->setPmBrand('Maestro')
									->setPmUrlLogo('Maestro_choice.gif')
									->setPmFamily('Cards: exceptions');

		// Cards: Online Credit 
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('NetReserve')
									->setPmValue('CreditCard')
									->setPmBrand('NetReserve')
									->setPmUrlLogo('NetReserve_choice.gif')
									->setPmFamily('Cards: Online Credit');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('UNEUROCOM')
									->setPmValue('UNEUROCOM')
									->setPmBrand('UNEUROCOM')
									->setPmUrlLogo('UNEUROCOM_choice.gif')
									->setPmFamily('Cards: Online Credit');


		// WebBanking
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('CBC Online')
									->setPmValue('CBC Online')
									->setPmBrand('CBC Online')
									->setPmUrlLogo('CBC Online_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('CENTEA Online')
									->setPmValue('CENTEA Online')
									->setPmBrand('CENTEA Online')
									->setPmUrlLogo('CENTEA Online_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('Dexia Direct Net')
									->setPmValue('Dexia Direct Net')
									->setPmBrand('Dexia Direct Net')
									->setPmUrlLogo('Dexia Direct Net_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('eDankort')
									->setPmValue('eDankort')
									->setPmBrand('eDankort')
									->setPmUrlLogo('eDankort_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('EPS')
									->setPmValue('EPS')
									->setPmBrand('EPS')
									->setPmUrlLogo('EPS_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('iDEAL')
									->setPmValue('iDEAL')
									->setPmBrand('iDEAL')
									->setPmUrlLogo('iDEAL_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('ING HomePay')
									->setPmValue('ING HomePay')
									->setPmBrand('ING HomePay')
									->setPmUrlLogo('ING HomePay_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('KBC Online')
									->setPmValue('KBC Online')
									->setPmBrand('KBC Online')
									->setPmUrlLogo('KBC Online_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('PostFinance Debit Direct')
									->setPmValue('PostFinance Debit Direct')
									->setPmBrand('PostFinance Debit Direct')
									->setPmUrlLogo('PostFinance Debit Direct_choice.gif')
									->setPmFamily('WebBanking');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
									->setPmName('PostFinance yellownet')
									->setPmValue('PostFinance yellownet')
									->setPmBrand('PostFinance yellownet')
									->setPmUrlLogo('PostFinance yellownet_choice.gif')
									->setPmFamily('WebBanking');

		// Direct Debits
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('Direct Debits DE')
											->setPmValue('Direct Debits DE')
											->setPmBrand('Direct Debits DE')
											->setPmUrlLogo('Direct Debits DE_choice.gif')
											->setPmFamily('Direct Debits');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('Direct Debits NL')
											->setPmValue('Direct Debits NL')
											->setPmBrand('Direct Debits NL')
											->setPmUrlLogo('Direct Debits NL_choice.gif')
											->setPmFamily('Direct Debits');

		// Offline payment
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('Acceptgiro')
											->setPmValue('Acceptgiro')
											->setPmBrand('Acceptgiro')
											->setPmUrlLogo('Acceptgiro _choice.gif')
											->setPmFamily('Offline payment');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('Bank transfer')
											->setPmValue('Bank transfer')
											->setPmBrand('Bank transfer')
											->setPmUrlLogo('Bank transfer _choice.gif')
											->setPmFamily('Offline payment');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('Payment on Delivery')
											->setPmValue('Payment on Delivery')
											->setPmBrand('Payment on Delivery')
											->setPmUrlLogo('Payment on Delivery _choice.gif')
											->setPmFamily('Offline payment');


		// Micro
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('MiniTix')
											->setPmValue('MiniTix')
											->setPmBrand('MiniTix')
											->setPmUrlLogo('MiniTix_choice.gif')
											->setPmFamily('Micro');

		// Mobile
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('TUNZ')
											->setPmValue('TUNZ')
											->setPmBrand('TUNZ')
											->setPmUrlLogo('TUNZ_choice.gif')
											->setPmFamily('Mobile');

		// Others
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('PAYPAL')
											->setPmValue('PAYPAL')
											->setPmBrand('PAYPAL')
											->setPmUrlLogo('PAYPAL_choice.gif')
											->setPmFamily('Others');
		$pmList[] = Mage::getModel('ogone/source_paymentMethod')
											->setPmName('Wallie')
											->setPmValue('Wallie')
											->setPmBrand('Wallie')
											->setPmUrlLogo('Wallie_choice.gif')
											->setPmFamily('Others');*/

		return $pmList;
	}


	/**
	 * get the iDeal bank list
	 *
	 * @return array
	 *
	 */
	public function getIdealBanks()
	{

		$bankList = array();

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('ABN AMRO','0031',
		 'ABN AMRO','abn_amro.png','', false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('Friesland','0091',
		 'Friesland','friesland.png','', false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('ING','0721',
		 'ING','ing.png','', false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('Rabobank','0021',
		 'Rabobank','rabobank.png','', false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('SNS Bank','0751',
		 'SNS Bank','sns_bank.png','', false);
		$bankList[] = $bank;

		// Other banks

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('ASN Bank','0761',
		 'ASN Bank','asn_bank.png','',false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('SNS Regio Bank','0771',
		 'SNS Regio Bank','sns_regio_bank.png','', false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('Triodos Bank','0511',
		 'Triodos Bank','triodos_bank.png','', false);
		$bankList[] = $bank;

		$bank = Mage::getModel('ogone/source_paymentMethod');
		$bank->__PaymentMethod('Van Lanschot Bankiers','0161',
		 'Van Lanschot Bankiers','van_lanschot_bankiers.png','', false);
		$bankList[] = $bank;

		return $bankList;

	}
}

?>
