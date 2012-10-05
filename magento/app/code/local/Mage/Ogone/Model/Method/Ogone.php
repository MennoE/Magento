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
 *
 * @file	   Mage/ogone/Model/Method/Ogone.php
 */

class Mage_Ogone_Model_Method_Ogone extends Mage_Payment_Model_Method_Cc
{
    protected $_code = 'ogone';

    protected $_formBlockType = 'ogone/form_ogone';
    protected $_infoBlockType = 'ogone/info_ogone';

    const OGONE_TEST_URL = 'https://secure.ogone.com/ncol/test/orderstandard.asp';
	const OGONE_PROD_URL = 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
    const TEST_MERCHANT_kEY = '58 6d fc 9c 34 91 9b 86 3f fd 64 63 c9 13 4a 26 ba 29 74 1e c7 e9 80 79';
    const TEST_CODE_SIRET = '00000000000001';
    const TEST_CODE_SITE = '001';

    protected $_customer;
    protected $_checkout;
    protected $_quote;
    protected $_order;
    protected $_allowCurrencyCodes;
    protected $_allowedParamToSend;

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canRefundInvoicePartial = true;

	protected $moduleTitle;
	protected $moduleDebugMode;

	/**
	 * Ogone Settings
	 *
	 **/
	protected $ogone_PSPID;
	protected $ogone_SHA1PASS;

	protected $ogone_Currency;
	protected $ogone_Language;
	/*  Optional fields for design  */
	/* Static template page  */
	protected $ogone_TITLE; 			/*  Title for static template  */
	protected $ogone_BGCOLOR; 			/*  Background color for static template */
	protected $ogone_TXTCOLOR; 			/*  Text color for static template */
	protected $ogone_TBLBGCOLOR; 		/*  Table background color for static template */
	protected $ogone_TBLTXTCOLOR; 		/*  Table text color for static template */
	protected $ogone_BUTTONBGCOLOR; 	/*  Button background color for static template */
	protected $ogone_BUTTONTXTCOLOR; 	/*  Button text color for static template */
	protected $ogone_FONTTYPE; 			/*  Font Type for static template: default = Verdana */
	protected $ogone_LOGO; 				/*  Logo filename for static template: send logo to support with your PSPID in the subject */
	/*  or dynamic template page  */
	protected $ogone_TP; 				/*  The full URL of the Template Page hosted on the merchant's site and containing the "payment string" eg: http://www.MyEcommerceSite.com/TemplatePage.htm or templateSTD3.htm  */
	/* Post-payment redirection  */
	protected $ogone_accepturl; 		/*  demo_accepturl.htm */
	protected $ogone_declineurl; 		/*  demo_declineurl.htm */
	protected $ogone_exceptionurl;		/*  demo_exceptionurl.htm */
	protected $ogone_cancelurl; 		/*  demo_cancelurl.htm */
	/*  Link to your website in case of standard confirmation page built by Ogone  */
	protected $ogone_homeurl;
	protected $ogone_catalogurl;
	/*  Other optional fields  */
	protected $ogone_CN; 				/*  Optional client name  */
	protected $ogone_EMAIL; 			/*  Optional client email */
	protected $ogone_PM; 				/*  Optional Payment Method : <EM>CreditCard, iDEAL, ING HomePay, KBC Online, CBC Online, DEXIA NetBanking</EM>  */
	protected $ogone_BRAND; 			/*  Optional, can be deduced from the card number */
	protected $ogone_SHASign;
	protected $ogone_Signature;
	protected $ogone_ownerZIP;
	protected $ogone_owneraddress;
	protected $ogone_ownercty;
	protected $ogone_Alias;
	protected $ogone_AliasUsage;
	protected $ogone_AliasOperationCOM;	/*  Optional order description */
	protected $ogone_COMPLUS;			/*  Optional additional info for post-payment feedback */
	protected $ogone_PARAMPLUS; 		/*  Optional params for post-payment feedback */
	protected $ogone_PARAMVAR; 			/*  Optional url Variable for post-payment feedback */
	protected $ogone_USERID; 			/*  Optional userid for account with User Manager. */
	protected $ogone_CreditCode; 		/*  Optional CreditCode for Cofinoga/NetReserve. */


	/* Getters */
	public function getTitle(){
		if($this->moduleTitle == null)
			$this->moduleTitle = Mage::getStoreConfig('payment/ogone/title');

		return $this->moduleTitle;
	}

	public function getDebugMode(){
		if($this->moduleDebugMode == null)
			$this->moduleDebugMode = Mage::getStoreConfig('payment/ogone/test');

		return $this->moduleDebugMode;
	}


	public function getOgonePSPID(){

		if($this->ogone_PSPID == null)
			$this->ogone_PSPID = Mage::getStoreConfig('payment/ogone/PSPID');

		return $this->ogone_PSPID;
	}

	public function getOgoneSHA1PASS(){

		if($this->ogone_SHA1PASS == null)
			$this->ogone_SHA1PASS = Mage::getStoreConfig('payment/ogone/SHA1PASS');

		return $this->ogone_SHA1PASS;
	}

	public function getOgoneCurrency(){

		if($this->ogone_Currency == null)
			$this->ogone_Currency = Mage::getStoreConfig('payment/ogone/Currency');

		return $this->ogone_Currency;
	}

	public function getOgoneLanguage(){
		if($this->ogone_Language == null)
			$this->ogone_Language = Mage::getStoreConfig('payment/ogone/Language');

		return $this->ogone_Language;
	}

	public function getOgoneTITLE(){
		if($this->ogone_TITLE == null)
			$this->ogone_TITLE = Mage::getStoreConfig('payment/ogone/TITLE');

		return $this->ogone_TITLE;
	}

	public function getOgoneBGCOLOR(){
		if($this->ogone_BGCOLOR == null)
			$this->ogone_BGCOLOR = Mage::getStoreConfig('payment/ogone/BGCOLOR');

		return $this->ogone_BGCOLOR;
	}

	public function getOgoneTXTCOLOR(){
		if($this->ogone_TXTCOLOR == null)
			$this->ogone_TXTCOLOR = Mage::getStoreConfig('payment/ogone/TXTCOLOR');

		return $this->ogone_TXTCOLOR;
	}

	public function getOgoneTBLBGCOLOR(){
		if($this->ogone_TBLBGCOLOR == null)
			$this->ogone_TBLBGCOLOR = Mage::getStoreConfig('payment/ogone/TBLBGCOLOR');

		return $this->ogone_TBLBGCOLOR;
	}

	public function getOgoneTBLTXTCOLOR(){
		if($this->ogone_TBLTXTCOLOR == null)
			$this->ogone_TBLTXTCOLOR = Mage::getStoreConfig('payment/ogone/TBLTXTCOLOR');

		return $this->ogone_TBLTXTCOLOR;
	}

	public function getOgoneBUTTONBGCOLOR(){
		if($this->ogone_BUTTONBGCOLOR == null)
			$this->ogone_BUTTONBGCOLOR = Mage::getStoreConfig('payment/ogone/BUTTONBGCOLOR');

		return $this->ogone_BUTTONBGCOLOR;
	}

	public function getOgoneBUTTONTXTCOLOR(){
		if($this->ogone_BUTTONTXTCOLOR == null)
			$this->ogone_BUTTONTXTCOLOR = Mage::getStoreConfig('payment/ogone/BUTTONTXTCOLOR');

		return $this->ogone_BUTTONTXTCOLOR;
	}

	public function getOgoneFONTTYPE(){
		if($this->ogone_FONTTYPE == null)
			$this->ogone_FONTTYPE = Mage::getStoreConfig('payment/ogone/FONTTYPE');

		return $this->ogone_FONTTYPE;
	}

	public function getOgoneLOGO(){
		if($this->ogone_LOGO == null)
			$this->ogone_LOGO = Mage::getStoreConfig('payment/ogone/LOGO');

		return $this->ogone_LOGO;
	}

	public function getOgoneTP(){
		if($this->ogone_TP == null)
			$this->ogone_TP = Mage::getStoreConfig('payment/ogone/TP');

		return $this->ogone_TP;
	}

	public function getOgoneAcceptUrl(){
		if($this->ogone_accepturl == null)
			$this->ogone_accepturl = Mage::getStoreConfig('payment/ogone/accepturl');

		return $this->ogone_accepturl;
	}

	public function getOgoneDeclineUrl(){
		if($this->ogone_declineurl == null)
			$this->ogone_declineurl = Mage::getStoreConfig('payment/ogone/declineurl');

		return $this->ogone_declineurl;
	}

	public function getOgoneExceptionUrl(){
		if($this->ogone_exceptionurl == null)
			$this->ogone_exceptionurl = Mage::getStoreConfig('payment/ogone/exceptionurl');

		return $this->ogone_exceptionurl;
	}

	public function getOgoneCancelUrl(){
		if($this->ogone_cancelurl == null)
			$this->ogone_cancelurl = Mage::getStoreConfig('payment/ogone/cancelurl');

		return $this->ogone_cancelurl;
	}

	public function getOgoneHomeUrl(){
		if($this->ogone_homeurl == null)
			$this->ogone_homeurl = Mage::getStoreConfig('payment/ogone/homeurl');

		return $this->ogone_homeurl;
	}

	public function getOgoneCatalogUrl(){
		if($this->ogone_catalogurl == null)
			$this->ogone_catalogurl = Mage::getStoreConfig('payment/ogone/catalogurl');

		return $this->ogone_catalogurl;
	}

	public function getOgoneCN(){
		if($this->ogone_CN == null)
			$this->ogone_CN = Mage::getStoreConfig('payment/ogone/CN');

		return $this->ogone_CN;
	}

	public function getOgoneEMAIL(){
		if($this->ogone_EMAIL == null)
			$this->ogone_EMAIL = Mage::getStoreConfig('payment/ogone/EMAIL');

		return $this->ogone_EMAIL;
	}

	public function getOgonePM(){
		if($this->ogone_PM == null)
			$this->ogone_PM = Mage::getStoreConfig('payment/ogone/PM');

		return $this->ogone_PM;
	}

	public function getOgoneBRAND(){
		if($this->ogone_BRAND == null)
			$this->ogone_BRAND = Mage::getStoreConfig('payment/ogone/BRAND');

		return $this->ogone_BRAND;
	}


	public function getOgoneSignature(){
		if($this->ogone_Signature == null)
			$this->ogone_Signature = Mage::getStoreConfig('payment/ogone/Signature');

		return $this->ogone_Signature;
	}

	public function getOgoneOwnerZIP(){
		if($this->ogone_ownerZIP == null)
			$this->ogone_ownerZIP = Mage::getStoreConfig('payment/ogone/ownerZIP');

		return $this->ogone_ownerZIP;
	}

	public function getOgoneOwnerAddress(){
		if($this->ogone_owneraddress == null)
			$this->ogone_owneraddress = Mage::getStoreConfig('payment/ogone/owneraddress');

		return $this->ogone_owneraddress;
	}

	public function getOgoneOwnerCty(){
		if($this->ogone_ownercty == null)
			$this->ogone_ownercty = Mage::getStoreConfig('payment/ogone/ownercty');

		return $this->ogone_ownercty;
	}

	public function getOgoneAlias(){
		if($this->ogone_Alias == null)
			$this->ogone_Alias = Mage::getStoreConfig('payment/ogone/Alias');

		return $this->ogone_Alias;
	}

	public function getOgoneAliasUsage(){
		if($this->ogone_AliasUsage == null)
			$this->ogone_AliasUsage = Mage::getStoreConfig('payment/ogone/AliasUsage');

		return $this->ogone_AliasUsage;
	}

	public function getOgoneAliasOperationCOM(){
		if($this->ogone_OperationCOM == null)
			$this->ogone_OperationCOM = Mage::getStoreConfig('payment/ogone/OperationCOM');

		return $this->ogone_OperationCOM;
	}

	public function getOgoneCOMPLUS(){
		if($this->ogone_COMPLUS == null)
			$this->ogone_COMPLUS = Mage::getStoreConfig('payment/ogone/COMPLUS');

		return $this->ogone_COMPLUS;
	}

	public function getOgonePARAMPLUS(){
		if($this->ogone_PARAMPLUS == null)
			$this->ogone_PARAMPLUS = Mage::getStoreConfig('payment/ogone/PARAMPLUS');

		return $this->ogone_PARAMPLUS;
	}

	public function getOgonePARAMVAR(){
		if($this->ogone_PARAMVAR == null)
			$this->ogone_PARAMVAR = Mage::getStoreConfig('payment/ogone/PARAMVAR');

		return $this->ogone_PARAMVAR;
	}

	public function getOgoneUSERID(){
		if($this->ogone_USERID == null)
			$this->ogone_USERID = Mage::getStoreConfig('payment/ogone/USERID');

		return $this->ogone_USERID;
	}

	public function getOgoneCreditCode(){
		if($this->ogone_CreditCode == null)
			$this->ogone_CreditCode = Mage::getStoreConfig('payment/ogone/CreditCode');

		return $this->ogone_CreditCode;
	}

	/**
	 * Return the url for the getaway
	 * @return
	 */
	public function getOgoneUrl(){
		if($this->getDebugMode())
			return self::OGONE_TEST_URL;
		else
			return self::OGONE_PROD_URL;
	}

	 /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Ogone_Model_Method_Ogone
     */

   public function assignData($data)
    {
	if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType());
        $info->setCcBank($data->getCcBank());

        Mage::getSingleton('checkout/session')->setPaymentCCBank($data->getCcBank());

        return $this;
    }


   public function validate()
    {

    	$errorMsg = false;
		$info = $this->getInfoInstance();

		$ccType =  $info->getCcType();

    	if(empty($ccType)){
    		$errorMsg = "Veuillez s'il vous plaît choisir un type de carte !";
    	}

    	if($errorMsg){
            Mage::throwException($errorMsg);
        }

        return $this;
    }

	protected function getSuccessURL()
	{
		return Mage::getUrl('/ogone/ogone/success', array('_secure' => true));
	}

	protected function getErrorURL()
    {
        return Mage::getUrl('/ogone/ogone/error', array('_secure' => true));
    }

	/**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }


	/**
	 * add all needed fields for ogone
	 * @return
	 * @param $forms Object
	 */
	public function addOgoneFields($form)
	{
		// get payment mode
		$payment = $this->getQuote()->getPayment();
		$additional = unserialize($this->getOrder()->getPayment()->getAdditionalData());

		$ccType =  $payment->getCcType();

        $bankType = (strtolower($ccType) == 'ideal') ? $payment->getCcBank() : '';

        // @see Mage_Ogone_Model_Method_Ogone::assignData()
        if (empty($bankType)) {
            $bankType = Mage::getSingleton('checkout/session')->getPaymentCCBank();
        }

		$list = Mage::getModel('ogone/source_paymentMethodsList')->getPMList();
		for ($i = 0; $i < sizeof($list); $i++) {
			  if($list[$i]->getPmName() == $ccType){

				$this->ogone_PM = $list[$i]->getPmValue();
				$this->ogone_BRAND = $list[$i]->getPmBrand();
				/*
				echo $this->ogone_PM;
				echo "<br/>";
				echo $this->ogone_BRAND ;
				echo "trouvé <br/>";*/

			  }
        	}

		// set values
		// customer information
		$this->ogone_CN 			= $this->getCustomer()->getFirstname() . ' ' . $this->getCustomer()->getLastname();
		$this->ogone_EMAIL			= $this->getCustomer()->getEmail();
		//$this->ogone_owneraddress	=
		//$this->ogone_ownerZIP		=
		//$this->ogone_ownercty		=

		// order information
		$sha_fields = array();

		if ($this->getOgoneAccepturl()) {
			$form->addField("accepturl", 'hidden', array('name' => 'accepturl', 'value' => $this->getOgoneAccepturl()));
			$sha_fields[] = 'ACCEPTURL=' . $this->getOgoneAccepturl();
		}

		if ($this->getOgoneAlias()) {
			$form->addField("Alias", 'hidden', array('name' => 'Alias', 'value' => $this->getOgoneAlias()));
			$sha_fields[] = 'ALIAS=' . $this->getOgoneAlias();
		}
		if ($this->getOgoneAliasOperationCOM()) {
			$form->addField("AliasOperation", 'hidden', array('name' => 'AliasOperation', 'value' => $this->getOgoneAliasOperationCOM()));
			$sha_fields[] = 'ALIASOPERATION=' . $this->getOgoneAliasOperationCOM();
		}
		if ($this->getOgoneAliasUsage()) {
			$form->addField("AliasUsage", 'hidden', array('name' => 'AliasUsage', 'value' => $this->getOgoneAliasUsage()));
			$sha_fields[] = 'ALIASUSAGE=' . $this->getOgoneAliasUsage();
		}
		if ($this->getOrder()->getBaseGrandTotal()) {
			$total = round($this->getOrder()->getBaseGrandTotal(), 2)*100;
			$form->addField("amount", 'hidden', array('name' => 'amount', 'value' => $total));
			$sha_fields[] = 'AMOUNT=' . $total;
		}

		if ($this->getOgoneLanguage()) {
			$form->addField("BGCOLOR", 'hidden', array('name' => 'Language', 'value' => $this->getOgoneLanguage()));
			//$sha_fields[] = 'BGCOLOR=' . $this->getOgoneLanguage();
		}
		if ($this->getOgoneBRAND()) {
			$form->addField("BRAND", 'hidden', array('name' => 'BRAND', 'value' => $this->getOgoneBRAND()));
			$sha_fields[] = 'BRAND=' . $this->getOgoneBRAND();
		}
		if ($this->getOgoneBUTTONBGCOL()) {
			$form->addField("BUTTONBGCOL", 'hidden', array('name' => 'BUTTONBGCOL', 'value' => $this->getOgoneBUTTONBGCOL()));
			$sha_fields[] = 'BUTTONBGCOL=' . $this->getOgoneBUTTONBGCOL();
		}
		if ($this->getOgoneBUTTONTXTCOLOR()) {
			$form->addField("BUTTONTXTCOLOR", 'hidden', array('name' => 'BUTTONTXTCOLOR', 'value' => $this->getOgoneBUTTONTXTCOLOR()));
			$sha_fields[] = 'BUTTONTXTCOLOR=' . $this->getOgoneBUTTONTXTCOLOR();
		}

		if ($this->getOgoneCancelurl()) {
			$form->addField("cancelurl", 'hidden', array('name' => 'cancelurl', 'value' => $this->getOgoneCancelurl()));
			$sha_fields[] = 'CANCELURL=' . $this->getOgoneCancelurl();
		}
		if ($this->getOgoneCatalogurl()) {
			$form->addField("catalogurl", 'hidden', array('name' => 'catalogurl', 'value' => $this->getOgoneCatalogurl()));
			$sha_fields[] = 'CATALOGURL=' . $this->getOgoneCancelurl();
		}
		if ($this->getOgoneCN()) {
			$form->addField("CN", 'hidden', array('name' => 'CN', 'value' => $this->getOgoneCN()));
			$sha_fields[] = 'CN=' . $this->getOgoneCN();
		}
		if ($this->getOgoneCOMPLUS()) {
			$form->addField("COMPLUS", 'hidden', array('name' => 'COMPLUS', 'value' => $this->getOgoneCOMPLUS()));
			$sha_fields[] = 'COMPLUS=' . $this->getOgoneCOMPLUS();
		}
		if ($this->getOgoneCreditCode()) {
			$form->addField("CreditCode", 'hidden', array('name' => 'CreditCode', 'value' => $this->getOgoneCreditCode()));
			$sha_fields[] = 'CREDITCODE=' . $this->getOgoneCreditCode();
		}
		if ($this->getOgoneCurrency()) {
			$form->addField("currency", 'hidden', array('name' => 'Currency', 'value' => $this->getOgoneCurrency()));
			$sha_fields[] = 'CURRENCY=' . $this->getOgoneCurrency();
		}

		if ($this->getOgoneDeclineurl()) {
			$form->addField("declineurl", 'hidden', array('name' => 'declineurl', 'value' => $this->getOgoneDeclineurl()));
			$sha_fields[] = 'DECLINEURL=' . $this->getOgoneDeclineurl();
		}

		if ($this->getOgoneEMAIL()) {
			$form->addField("EMAIL", 'hidden', array('name' => 'EMAIL', 'value' => $this->getOgoneEMAIL()));
			$sha_fields[] = 'EMAIL=' . $this->getOgoneEMAIL();
		}
		if ($this->getOgoneExceptionurl()) {
			$form->addField("exceptionurl", 'hidden', array('name' => 'exceptionurl', 'value' => $this->getOgoneExceptionurl()));
			$sha_fields[] = 'EXCEPTIONURL=' . $this->getOgoneExceptionurl();
		}

		if ($this->getOgoneFONTTYPE()) {
			$form->addField("FONTTYPE", 'hidden', array('name' => 'FONTTYPE', 'value' => $this->getOgoneFONTTYPE()));
			$sha_fields[] = 'FONTTYPE=' . $this->getOgoneFONTTYPE();
		}

		if ($this->getOgoneHomeurl()) {
			$form->addField("homeurl", 'hidden', array('name' => 'homeurl', 'value' => $this->getOgoneHomeurl()));
			$sha_fields[] = 'HOMEURL=' . $this->getOgoneHomeurl();
		}

        //bank
        if(!empty($bankType)) {
            $form->addField("ISSUERID", 'hidden', array('name' => 'ISSUERID', 'value' => $bankType));
            $sha_fields[] = 'ISSUERID=' . $bankType;
        }

		if (!empty($additional['issuerId'])) {
			//$form->addField("ISSUERID", 'hidden', array('name' => 'ISSUERID', 'value' => $additional['issuerId']));
			//$sha_fields[] = 'ISSUERID=' . $additional['issuerId'];
		}

		if ($this->getOgoneLanguage()) {
			$form->addField("language", 'hidden', array('name' => 'Language', 'value' => $this->getOgoneLanguage()));
			$sha_fields[] = 'LANGUAGE=' . $this->getOgoneLanguage() . ', ' . $this->getOgoneLanguage();
		}
		if ($this->getOgoneLOGO()) {
			$form->addField("LOGO", 'hidden', array('name' => 'LOGO', 'value' => $this->getOgoneLOGO()));
			$sha_fields[] = 'LOGO=' . $this->getOgoneLOGO();
		}

		$form->addField("OPERATION", 'hidden', array('name' => 'Operation', 'value' => 'SAL'));
		$sha_fields[] = 'OPERATION=SAL';

		if ($this->getOrder()->getRealOrderId()) {
			$form->addField("orderID", 'hidden', array('name' => 'orderID', 'value' => $this->getOrder()->getRealOrderId()));
			$sha_fields[] = 'ORDERID=' .  $this->getOrder()->getRealOrderId();
		}
		if ($this->getOgoneOwneraddress()) {
			$form->addField("owneraddress", 'hidden', array('name' => 'owneraddress', 'value' => $this->getOgoneOwneraddress()));
			$sha_fields[] = 'OWNERADDRESS=' .  $this->getOgoneOwneraddress();
		}
		if ($this->getOgoneOwnercty()) {
			$form->addField("ownercty", 'hidden', array('name' => 'ownercty', 'value' => $this->getOgoneOwnercty()));
			$sha_fields[] = 'OWNERCTY=' .  $this->getOgoneOwnercty();
		}
		if ($this->getOgoneOwnerZIP()) {
			$form->addField("ownerZIP", 'hidden', array('name' => 'ownerZIP', 'value' => $this->getOgoneOwnerZIP()));
			$sha_fields[] = 'OWNERZIP=' .  $this->getOgoneOwnerZIP();
		}

		if ($this->getOgoneTITLE()) {
			$form->addField("PAGE_TITLE", 'hidden', array('name' => 'PAGE_TITLE', 'value' => $this->getOgoneTITLE()));
			//$sha_fields[] = 'PAGE_TITLE=' .  $this->getOgoneTITLE();
		}
		if ($this->getOgonePARAMPLUS()) {
			$form->addField("PARAMPLUS", 'hidden', array('name' => 'PARAMPLUS', 'value' => $this->getOgonePARAMPLUS()));
			$sha_fields[] = 'PARAMPLUS=' .  $this->getOgonePARAMPLUS();
		}
		if ($this->getOgonePARAMVAR()) {
			$form->addField("PARAMVAR", 'hidden', array('name' => 'PARAMVAR', 'value' => $this->getOgonePARAMVAR()));
			$sha_fields[] = 'PARAMVAR=' .  $this->getOgonePARAMVAR();
		}
		if ($this->getOgonePM()) {
			$form->addField("PM", 'hidden', array('name' => 'PM', 'value' => $this->getOgonePM()));
			$sha_fields[] = 'PM=' .  $this->getOgonePM();
		}
		if ($this->getOgonePSPID()) {
			$form->addField("PSPID", 'hidden', array('name' => 'PSPID', 'value' => $this->getOgonePSPID()));
			$sha_fields[] = 'PSPID=' .  $this->getOgonePSPID();
		}

		if ($this->getOgoneSignature()) {
			$form->addField("Signature", 'hidden', array('name' => 'Signature', 'value' => $this->getOgoneSignature()));
			//$sha_fields[] = 'SIGNATURE=' .  $this->getOgoneSignature();
		}

		if ($this->getOgoneTBLBGCOLOR()){
			$form->addField("TBLBGCOLOR", 'hidden', array('name' => 'TBLBGCOLOR', 'value' => $this->getOgoneTBLBGCOLOR()));
			$sha_fields[] = 'TBLBGCOLOR=' .  $this->getOgoneTBLBGCOLOR();
		}
		if ($this->getOgoneTBLTXTCOLOR()) {
			$form->addField("TBLTXTCOLOR", 'hidden', array('name' => 'TBLTXTCOLOR', 'value' => $this->getOgoneTBLTXTCOLOR()));
			$sha_fields[] = 'TBLTXTCOLOR=' .  $this->getOgoneTBLTXTCOLOR();
		}
		if ($this->getOgoneTP()) {
			$form->addField("TP", 'hidden', array('name' => 'TP', 'value' => $this->getOgoneTP()));
			$sha_fields[] = 'TP=' .  $this->getOgoneTP();
		}
		if ($this->getOgoneTXTCOLOR()) {
			$form->addField("TXTCOLOR", 'hidden', array('name' => 'TXTCOLOR', 'value' => $this->getOgoneTXTCOLOR()));
			$sha_fields[] = 'TXTCOLOR=' .  $this->getOgoneTXTCOLOR();
		}
		if ($this->getOgoneUSERID()) {
			$form->addField("USERID", 'hidden', array('name' => 'USERID', 'value' => $this->getOgoneUSERID()));
			$sha_fields[] = 'USERID=' .  $this->getOgoneUSERID();
		}

		$sha = strtoupper(sha1(implode($this->getOgoneSHA1PASS(), $sha_fields) . $this->getOgoneSHA1PASS()));
		$form->addField("SHASign", 'hidden', array('name' => 'SHASign', 'value' => $sha));

		return $form;
	}

	 /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl(){
    	return Mage::getUrl('ogone/ogone/redirect');
	}

	public function getCustomer()
    {
        if (empty($this->_customer)) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->_customer;
    }

    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    public function getQuote()
    {
        if (empty($this->_quote)) {
            if (!$this->getCheckout()->getQuoteId()) {
                $this->getCheckout()->setQuoteId($this->getCheckout()->getOgoneQuoteId());
            }

            $storeId = $this->getCheckout()->getQuote()->getStoreId();
            $quoteId = $this->getCheckout()->getLastQuoteId();

            $quote = Mage::getModel('sales/quote')->setStoreId($storeId)->load($quoteId);

            $this->_quote = $quote;
        }
        return $this->_quote;
    }

    public function getOrder()
    {
        if (empty($this->_order)) {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($this->getCheckout()->getLastRealOrderId());
            $this->_order = $order;
        }
        return $this->_order;
    }


 /**
     * Checking response
     *
     * @param array $response
     * @return bool
     */
    public function checkResponse($response)
    {
        //TODO manage debugging
        if (isset(
        	$response['orderID'], $response['amount'],
            $response['STATUS'], $response['PAYID'],
            $response['PM'],$response['BRAND'])) {
            return true;
        }

        return false;
    }
}
?>
