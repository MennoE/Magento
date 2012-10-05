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

/**
 * Ogone Payment Front Controller
 *
 * @category   Mage
 * @package    Mage_Ogone
 * @name       Mage_Mage_Ogone_Mage_OgoneController
 * @author	   Magento Core Team <core@magentocommerce.com>, ALTIC
 */
class Mage_Ogone_OgoneController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;
	protected $_ogoneResponse = null;

	protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with spplus order transaction information
     *
     * @return Mage_Ogone_Model_Method_Ogone
     */
    public function getOgone()
    {
        return Mage::getSingleton('ogone/method_ogone');
    }

    /**
     * Get Checkout Singleton
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        $session = Mage::getSingleton('checkout/session');
    	return $session;
    }

    /**
     *  Get order
     *
     *  @param    none
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

	/**
     * seting response after returning from ogone
     *
     * @param array $response
     * @return object $this
     */
    protected function setOgoneResponse($response)
    {
    	if (count($response)) {
            $this->_ogoneResponse = $response;
        }
        return $this;
    }

    /**
    * Checking response and Ogone session variables
    *
    * @return unknown
    */
    protected function _checkResponse()
    {

        if (!$this->_ogoneResponse) {
        	Mage::throwException($this->__('no html response!'));
            return false;
        }

        //check for valid response
        if ($this->getOgone()->checkResponse($this->_ogoneResponse)) {

        	$order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->_ogoneResponse['orderID']);

            $orderId = $order->getId();
            if(!isset($orderId)){
            	Mage::throwException($this->__('Order identifier is not valid!'));
            	return false;
            }

            // test the amount
            if(	$this->_ogoneResponse['amount']*100 !=
            	round($order->getBaseGrandTotal(), 2)*100 ){
					Mage::throwException($this->__('Amount order is not valid!'));
            		return false;
            }

            // test the payment method
			$payment = $this->getOgone()->getQuote()->getPayment();
			$ccType =  $payment->getCcType();

			$list = Mage::getModel('ogone/source_paymentMethodsList')->getPMList();
			$orderOgonePM = "";
			$orderOgoneBrand = "";

			for ($i = 0; $i < sizeof($list); $i++) {
				  if($list[$i]->getPmName() == $ccType){
				  	$orderOgonePM = $list[$i]->getPmValue();
					$orderOgoneBrand = $list[$i]->getPmBrand();
				  }
	        }

/*	        if($orderOgonePM == null && $orderOgoneBrand == null && $ccType != "CB" ){
	        	Mage::throwException($this->__('No method payment found for this order!'));
            	return false;
	        }

	        if(	$orderOgonePM != $this->_ogoneResponse['PM'] ||
	        	($orderOgoneBrand != $this->_ogoneResponse['BRAND'] && $ccType != "CB")){
	        	Mage::throwException($this->__('No method payment or brand is not valid for this order!'));
            	return false;
	        }
*/
			$whitelist = array(
				'AAVADDRESS', 'AAVCheck', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'amount', 'BRAND',
				'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CREATION_STATUS', 'currency', 'CVCCheck',
				'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE',
				'DCC_EXCHRATETS', 'DCC_INDICATOR', 'DCC_MARGINPERCENTAGE', 'DCC_VALIDHOURS',
				'DIGESTCARDNO', 'ECI', 'ED',
				'ENCCARDNO', 'ISSUERID', 'IP', 'IPCTY', 'NCERROR', 'orderID', 'PAYID', 'PM', 'SCO_CATEGORY', 'SCORING', 'STATUS', 'TRXDATE', 'VC'
			);
			$feedbackSign = array();
			foreach($whitelist as $bit) {
				if (isset($this->_ogoneResponse[$bit]) && $this->_ogoneResponse[$bit]!="") {
					$feedbackSign[] = strtoupper($bit) . '=' . $this->_ogoneResponse[$bit];
				}
			}
			$feedbackSign = strtoupper(sha1(implode($this->getOgone()->getOgoneSHA1PASS(), $feedbackSign) . $this->getOgone()->getOgoneSHA1PASS()));

	       /* echo "<br> orderID : " . $this->_ogoneResponse['orderID'];
			echo "<br> currency : " . $this->_ogoneResponse['currency'];
			echo "<br> amount : " . $this->_ogoneResponse['amount'];
			echo "<br> PM : " . $this->_ogoneResponse['PM'];
			echo "<br> ACCEPTANCE : " . $this->_ogoneResponse['ACCEPTANCE'];
			echo "<br> STATUS : " . $this->_ogoneResponse['STATUS'];
			echo "<br> CARDNO : " . $this->_ogoneResponse['CARDNO'];
			echo "<br> PAYID : " . $this->_ogoneResponse['PAYID'];
			echo "<br> NCERROR : " . $this->_ogoneResponse['NCERROR'];
			echo "<br> BRAND : " . $this->_ogoneResponse['BRAND'];

	        echo "<br><br>feedbackSign :: " . $feedbackSign;
	        echo "<br>SHASIGN :: " . $this->_ogoneResponse['SHASIGN'];

	        echo "<br><br>sha1 de 12EUR15CreditCard12349xxxxxxxxxxxx1111321001230VISAMysecretsig :: " . sha1("12EUR15CreditCard12349xxxxxxxxxxxx1111321001230VISAMysecretsig");
	        echo "<br><br>600000087EUR369.98CreditCardtest1235XXXXXXXXXXXX111125227100VISAxgtpb92 :: " . sha1("600000087EUR369.98CreditCardtest1235XXXXXXXXXXXX111125227100VISAxgtpb92");
	        exit();*/

			Mage::helper('ogone')->log(
				'#' . $orderId . ' - SIGN: ' . $feedbackSign . PHP_EOL .
				'#' . $orderId . ' - SHA-OUT: ' . $this->_ogoneResponse['SHASIGN'] . PHP_EOL .
				'#' . $orderId . ' - Ogone RAW: ' . serialize($this->_ogoneResponse) . PHP_EOL
			);

	        if($this->_ogoneResponse['SHASIGN'] != $feedbackSign){
	        	Mage::throwException($this->__('feedback signature is not valid!'));
            	return false;
	        }

            return true;
        }

        return true;
    }

    /**
     * When a customer chooses Ogone Payment on Checkout/Payment page
     *
     */
	public function redirectAction()
	{
		$session = Mage::getSingleton('checkout/session');
		$order = $this->getOrder();

		if (!$order->getId()) {
			Mage::helper('ogone')->log('Customer Session Lost: ' . date('Y-m-d H:i:s'));
			$this->_redirect('session-lost');
			return;
		}

		$order->addStatusToHistory(
			$order->getStatus(),
			//Mage::helper('ogone')->__('Customer was redirected to OgonePayment')
			'Customer was redirected to OgonePayment'
		);
		$order->save();
		Mage::helper('ogone')->log('RedirectAction - #' . $order->getId() . ' ' . print_r($this->getRequest()->getParams(), true));


		$this->getResponse()
			->setBody($this->getLayout()
				->createBlock('ogone/redirect')
				->setOrder($order)
				->toHtml());

        //$session->unsQuoteId();
    }


	/**
     * Action when customer cancel payment or press button to back to shop
     */
    public function declineAction()
    {
        $this->setOgoneResponse($this->getRequest()->getParams());

        $order = Mage::getModel('sales/order')
             ->loadByIncrementId($this->_ogoneResponse['orderID']);
        $order->cancel();
        $order->addStatusToHistory($order->getStatus(),
        $this->__('Order was canceled by customer')
                . "\n<br>\n<br>Status :" . $this->_ogoneResponse['STATUS']
. "\n<br>\n<br>Error Code:" . $this->_ogoneResponse['NCERROR']
            );
        $order->save();

        $session = $this->getCheckout();
        $session->setQuoteId($session->getOgoneQuoteId(true));
        $session->getQuote()->setIsActive(false)->save();
        $session->unsOgoneQuoteId();

        Mage::helper('ogone')->log('DeclineAction - #' . $order->getId() . ' ' . print_r($this->getRequest()->getParams(), true));
        $this->_redirect('checkout/cart');
    }

	/***
	 * Customer returning to this action if payment was successe
     */
	public function successAction()
	{
		try {
			if ($this->getRequest()->isGet()) {
				$this->setOgoneResponse($this->getRequest()->getParams());
			}
			if ($this->getRequest()->isPost()) {
				$this->setOgoneResponse($this->getRequest()->getParams());
			}

			Mage::helper('ogone')->log('SuccessAction - #' . print_r($this->getRequest()->getParams(), true));

			if ($this->_checkResponse()) {

				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($this->_ogoneResponse['orderID']);

				$canInvoice = false;
				if ($order->getData('is_invoiced') == 0) {
					$canInvoice = true;
				};

				// set order as invoiced
				$order->setData('is_invoiced', 1);
				$order->save();

				Mage::helper('ogone')->log('UNDER WATER - #' . $order->getId());

				if ($this->_ogoneResponse['STATUS'] == '5' || $this->_ogoneResponse['STATUS'] == '9') {
					if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
						&& $order->getState() != Mage_Sales_Model_Order::STATE_NEW) {
						Mage::helper('ogone')->log('UNDER WATER - #' . $order->getId() . ' - ORDER NOT PENDING. STATUS: ' . $order->getState() . ', STOP!');

						return;
					}
					Mage::helper('ogone')->log('UNDER WATER - #' . $order->getId() . ' - SET TO PROCESSING');

					$order->addStatusToHistory(
						$order->getStatus(),
						$this->__('Customer successfully returned from Ogone')
						. "\n<br>Payment Method:" .$this->_ogoneResponse['PM']
						. "\n<br>Operator:" .$this->_ogoneResponse['BRAND']
						. "\n<br>Card No:" . $this->_ogoneResponse['CARDNO']
						. "\n<br>TransactionId:" . $this->_ogoneResponse['PAYID']

						."\n<br>\n<br>Operator Code :" . $this->_ogoneResponse['ACCEPTANCE']
						."\n<br>\n<br>Error Code:" . $this->_ogoneResponse['NCERROR']
				  	);

					$order->getPayment()
						  ->getMethodInstance()
						  ->setTransactionId($this->_ogoneResponse['PAYID']);

			//TODO Still in development
			/*
			$order->getPayment()->setOgoneMethod($this->_ogoneResponse['PM']);
			$order->getPayment()->setOgoneBrand($this->_ogoneResponse['BRAND']);
			$order->getPayment()->setOgonePayid($this->_ogoneResponse['PAYID']);
			$order->getPayment()->setOgoneStatus($this->_ogoneResponse['STATUS']);
			$order->getPayment()->setOgoneCardno($this->_ogoneResponse['CARDNO']);
			$order->getPayment()->setOgoneNceerror($this->_ogoneResponse['NCERROR']);
			$order->getPayment()->setOgoneAccemptance($this->_ogoneResponse['ACCEPTANCE']);
			$order->getPayment()->setOgoneCurrency($this->_ogoneResponse['CURRENCY']);
			*/

			//$order->getPayment()->setLastTransId($this->_ogoneResponse['PAYID']);
					// generate the invoice
					if ($canInvoice) {
						if ($this->_createInvoice($order)) {
						   $order->addStatusToHistory($order->getStatus(), $this->__('Invoice was create successfully'));
						} else {
						   $order->addStatusToHistory($order->getStatus(), $this->__('Can\'t create invoice'));
						   $redirectTo = '*/*/failure';
						}
					}

					$order->sendNewOrderEmail();

					$order->setState(
						Mage_Sales_Model_Order::STATE_PROCESSING,
						'processing', "Order set to processing", true
					);

					$order->save();
					Mage::helper('ogone')->log('SuccessAction - #' . $order->getId() . ' - SET TO PROCESSING');

					$this->_redirect('checkout/wizard/thanks');
					return;

				} else {

					$order->cancel();
					$order->addStatusToHistory($order->getStatus(), $this->__('Customer was rejected by Ogone'));

					Mage::helper('ogone')->log('SuccessAction - #' . $order->getId() . ' - Redirect to falure');
					$this->_redirect('checkout/wizard/failure');
					return;
				}
			} else {
				$this->norouteAction();
				return;
			}
		} catch (Exception $e) {
			Mage::helper('ogone')->log($e->__toString(), Zend_Log::ERR);
		}
    }

    /**
     * this method is called after the payment is processed by
     * Ogone
     *
     */
    public function processAction()
	{
		Mage::helper('ogone')->log('ProcessAction: IN PROCESS RESPONSE (BROWSER) ' . print_r($this->getRequest()->getParams(), true));
    	//TODO log the payment feedback in order to compute statistics

    	// define a log file to store payement transaction
    	/*$logger = new Zend_Log();
    	$curDate = new Zend_Date();
    	$writer = new Zend_Log_Writer_Stream("file:///tmp/payement" . "-". $curDate->get(). ".log");
		$logger = new Zend_Log($writer);

		$logger->log('Informational message');
		*/
    	if ($this->getRequest()->isPost()) {
			$this->setOgoneResponse($this->getRequest()->getPost());
		} else if ($this->getRequest()->isGet()) {
			$this->setOgoneResponse($this->getRequest()->getParams());
		}

		if ($this->_checkResponse()) {

           	/* * *
           	 *
           	 * The authorization has been accepted.
			 *	An authorization code is available in the field
			 *	ACCEPTANCE.
			 *	The status will be 5 if you have defined automatic
			 *	authorization and data capture on request or
			 *	automatic data capture after x days as payment
			 *	procedure in item 9 of the Technical Information
			 *	page in your account.
			 *
			 *  The initial status of a transaction will be 9 if you
			 *	have defined direct sale as payment procedure in
			 *	item 9 of the Technical Information page in your
			 *	account.
			 *
           	 * * * */
            if ($this->_ogoneResponse['STATUS'] == '5' ||
            	$this->_ogoneResponse['STATUS'] == '9'
            ) {
                Mage::helper('ogone')->log('PROCESS RESPONSE - #' . $order->getId().' Redirect to thanks');
	            $this->_redirect('checkout/wizard/thanks');
    	        return;

            } else {

	            $order = Mage::getModel('sales/order');
	            $order->loadByIncrementId($this->_ogoneResponse['orderID']);
				/**
				 * All other response cancel the order
				 * but we save the status
				 */
                $order->cancel();
                $order->addStatusToHistory(
                    $order->getStatus(),
                    $this->__('Customer was rejected by Ogone')
                    . "\n<br>\n<br>Status :" . $this->_ogoneResponse['STATUS']
				    . "\n<br>\n<br>Error Code:" . $this->_ogoneResponse['NCERROR']
                );
				Mage::helper('ogone')->log('PROCESS RESPONSE - #' . $order->getId() . ' Customer was rejected by Ogone');
                $this->_redirect('checkout/wizard/failure');
    	        return;
            }
        } else {
            $this->_redirect('checkout/wizard/failure');
            return;
        }
    }

    /**
     * Error action. If request params to Ogone has mistakes
     *
     */
    public function errorAction()
    {
    	if ($this->getRequest()->isGet()) {
			$this->setOgoneResponse($this->getRequest()->getParams());
		}

        $session = Mage::getSingleton('checkout/session');
        $errorMsg =
            Mage::helper('ogone')->__('\n<br>\n<br>There was an error occurred during paying process.'
            ."\n<br>\n<br>Ogone No Status :: " .$this->_ogoneResponse['STATUS']);

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->_ogoneResponse['orderID']);

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }
        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            $order->addStatusToHistory(
                $order->getStatus(),
                Mage::helper('ogone')->__('Customer returned from Ogone.') . $errorMsg
            );

            $order->save();
        }

        Mage::helper('ogone')->log('ErrorAction - #' . $order->getId() . ' ' . print_r($this->getRequest()->getParams(), true));

        Mage::getSingleton('checkout/session')->unsLastRealOrderId();
		$this->_redirect('checkout/wizard/failure');
        /*$this->loadLayout();
        $this->renderLayout();*/
    }

    /**
     * Failure action.
     * Displaying information if customer was redirecting to cancel or decline actions
     *
     */
    public function failureAction()
    {
    	if ($this->getRequest()->isGet()) {
			$this->setOgoneResponse($this->getRequest()->getParams());
		}

		Mage::helper('ogone')->log('FailureAction - #' . json_encode($this->_ogoneResponse));

        if ($this->_ogoneResponse['NCERROR'] > 0) { //an error occurs
            $this->norouteAction();
            return;
        }

        /*$this->loadLayout();
        $this->renderLayout();*/
		$this->_redirect('checkout/wizard/failure');
    }


  	/**
	 * Creating invoice
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _createInvoice(Mage_Sales_Model_Order $order)
    {
    	//do not create invoice if one already exists
        if ($order->canInvoice() && ($order->hasInvoices() < 1)) {
            $convertor = Mage::getModel('sales/convert_order');

			$invoice = $order->prepareInvoice();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            return true;
        }
        return false;
    }
}
