<?php
/**
 * Model for handling instore feedback
 * @todo: Extended testing when real instore feedback files are available
 */
class Kega_Wizard_Model_Instorefeedback extends Varien_Object
{
    protected $_localPath = 'var/instorefeedback/downloaded/';
    protected $_logPath = 'var/log/instorefeedback/';
    protected $_logfile;
    protected $_screenOutput = true;

    /**
     * Process feedback from store cash register
     * - Fetch files from ftp
     * - Loop trough files and process them
     *
     */
    public function processInstoreFeedback()
    {
        $files = $this->_fetchFiles();
        $this->_log(count($files) . ' file(s) dowloaded. Start processing');

        if(empty($files)) {
            echo 'no files found on FTP';
            return;
        }

        foreach($files as $file) {
            $this->_processFile($file);
        }
    }

    /**
     * Kega_Wizard_Model_Instorefeedback::_processFile()
     * Process store feedback file
     * - read file into array
     * - parse all lines from file
     *
     * @param string $file
     * @return void
     */
    protected function _processFile($file)
    {
        $records = file($this->_localPath . $file);
        $this->_log('start processing ' . $file . ' (' . count($records) . ' records)');

        foreach($records as &$record) {
            $record = $this->_parseRecordFields($record);
            $this->_processRow($record);
        }
    }

    /**
     * Process feedback row
     * - Mark invoice as paid if amount from feedback matches invoice amount
     * - Run type specific actions from separate method
     *
     * @param Array $row
     */
    protected function _processRow($row)
    {
        $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($row['invoice_id']);

        if(empty($invoice)) {
            $this->_log('%s: Invoice not found.', $row['invoice_id']);
            return;
        }

        if($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID) {
            $this->_log(sprintf('%s: Invoice already paid.', $row['invoice_id']));
        } else {

	        // mark invoice as paid if paid amount is equal to invoice grand total
	        if(floatval($invoice->getGrandTotal()) == $row['amount']) {
	            try {
	                $invoice->pay();    // is this really needed?
	                $invoice->capture();
	                $invoice->getOrder()->setState('in_store_accepted', true);

	                Mage::getModel('core/resource_transaction')
	                    ->addObject($invoice)
	                    ->addObject($invoice->getOrder())
	                    ->save();

	                $this->_log(sprintf('%s: Set to paid', $row['invoice_id']));
	            } catch(Exception $e) {
	                die("failed: " . $e);
	            }
	        } else {
	            $this->_log(sprintf('%s: Invalid amount for invoice', $row['invoice_id']));
	        }
        }

        if($row['type'] == 'A') {
            $this->_parseAcceptedRow($row, $invoice);
        } else if($row['type'] == 'C') {
            $this->_parseCollectedRow($row, $invoice);
        }
        return;
    }

    /**
     * Specific actions for "Accepted" invoice rows
     * - Set invoice accepted_at datetime
     * - Create new follow up invoice if order is not completely paid.
     * - Save new invoice and order.
     *
     * @param array $row
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function _parseAcceptedRow($row, $invoice)
    {
    	if($invoice->getAcceptedAt() != '0000-00-00 00:00:00') {
    		$this->_log(sprintf('%s: Already accepted at: %s',
                $row['invoice_id'],
                $invoice->getAcceptedAt()
        	));
        	return;
    	}

        $invoice->setAcceptedAt(date('Y-m-d H:i:s'))->save();
        $this->_log(sprintf('%s: Set accepted at to : %s',
                $row['invoice_id'],
                $invoice->getAcceptedAt()
        ));
		$invoice->getOrder()->setAcceptedAt(date('Y-m-d H:i:s'))
        					->save();
        if($invoice->getOrder()->getBaseGrandTotal() != $invoice->getOrder()->getBaseTotalInvoiced()) {

            $followupInvoice = $this->_getWizard()->createFinalpaymentInvoice($invoice->getOrder());

            Mage::getModel('core/resource_transaction')
                ->addObject($followupInvoice)
                ->addObject($followupInvoice->getOrder())
                ->save();
            $this->_log(sprintf('%s: Follow up invoice created: %s',
                $row['invoice_id'],
                $followupInvoice->getIncrementId()
            ));
        }
    }

    /**
     * Specific actions for "Collected" invoice rows
     * - Set invoice collected_at datetime
     * - Set order collected at if all invoices are paid.
     *
     * @param array $row
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function _parseCollectedRow($row, $invoice)
    {
    	if($invoice->getCollectedAt() != '0000-00-00 00:00:00') {
    		$this->_log(sprintf('%s: Already accepted at: %s',
                $row['invoice_id'],
                $invoice->getCollectedAt()
        	));
        	return;
    	}

    	$invoice->setCollectedAt(date('Y-m-d H:i:s'))->save();
        $this->_log(sprintf('%s: Set collected at to : %s',
            $row['invoice_id'],
            $invoice->getCollectedAt()
        ));

        $invoices = $invoice->getOrder()->getInvoiceCollection();
        foreach($invoices as $invoice) {
            if($invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID) {
                $this->_log(sprintf('%s: Not paid. Order NOT set to collected',
                    $invoice->getIncrementId()
                ));
                return;
            }
        }
        $this->_log(sprintf('%s: All %d invoices paid. Set order %s to Collected',
            $invoice->getIncrementId(),
            count($invoices),
            $invoice->getOrder()->getIncrementId()
        ));

        $invoice->getOrder()->setCollectedAt(date('Y-m-d H:i:s'))
        					->setState('in_store_collected', true)
        					->save();
        $this->_log(sprintf('%s: Set order (%s) collected at to : %s',
            $row['invoice_id'],
            $invoice->getOrder()->getIncrementId(),
            $invoice->getCollectedAt()
        ));
    }

    /**
     * Kega_Wizard_Model_Instorefeedback::_parseRecordFields()
     * Parse feedback string into indexed array
     *
     * @param string $record
     * @param Array $record
     */
    protected function _parseRecordFields($record)
    {
        $record = trim($record);
        if(strlen($record) != 50) {
            $this->_log('Wrong record format!');
            return;
        }
        $record = array(
        	'identifier'		=> substr($record, 0, 3),
        	'tillnumber'		=> substr($record, 3, 2),
        	'date'				=> substr($record, 5, 8),
        	'time'				=> substr($record, 13, 4),
        	'transactionnumber'	=> substr($record, 17, 6),
        	'operationnumber'	=> substr($record, 23, 5),
        	'type'				=> substr($record, 28, 1),
        	'invoice_id'   		=> intval(substr($record, 29, 10)),    // use intval() to strip the leading zero
        	'amount'       => floatval(substr($record, 39, 7)/100),   // use floatval() to strip leading zero's
        	'storecode'    => substr($record, 46, 4),
        );

        return $record;
    }

    /**
     * Kega_Wizard_Model_Instorefeedback::_fetchFiles()
     * Download new files from ftp server
     *
     * @todo: Add ftp settings to config
     * @return Array $files Downloaded filenames
     */
    protected function _fetchFiles()
    {
        $host = $carrier = Mage::getStoreConfig('instore/feedback_ftp/host');
        $user = $carrier = Mage::getStoreConfig('instore/feedback_ftp/user');
        $pass = $carrier = Mage::getStoreConfig('instore/feedback_ftp/password');
        $folder = './storefeedback/';
        $passive = $carrier = Mage::getStoreConfig('instore/feedback_ftp/passivemode');;

        $ftp = ftp_connect($host);
        ftp_login($ftp, $user, $pass);
        ftp_pasv($ftp, $passive);

        ftp_chdir($ftp, $folder);

        $files = array();
        foreach(ftp_nlist($ftp, '.') as $file) {

            $filename = date('YmdHi') . '_' . $file;
            $files[] = $filename;
            ftp_get($ftp, $this->_localPath . $filename, $file, FTP_ASCII);

            ftp_delete($ftp, $file);
        }

        return $files;
    }

    /**
     * Get Wizard singleton
     *
     * @return Kega_Wizard_Model_Instorewizard
     */
    protected function _getWizard()
	{
		return Mage::getSingleton('wizard/instorewizard');
	}

	/**
	 * Write message to logfile
	 *
	 * @param String $message
	 * @return void
	 */
	public function _log($message)
	{
        if(empty($this->_logfile)) {
            $this->_createLogfile();
        }
        fwrite($this->_logfile, $message . PHP_EOL);

        if($this->_screenOutput) {
            echo $message . '<br />' . PHP_EOL;
        }
	}

	/**
	 * Create logfile (max 1 file per minute)
	 * - Check if logging folder exists (if not create it)
	 * - Check if a file already exists for this minute. if not create it. if so append.
	 * - Write feedback about above actions to logfile
	 *
	 */
	public function _createLogfile()
	{
	    $filename = date('Y-m-d_Hi'). '.log';
	    $dirCreated = false;
	    $fileCreated = false;

	    if(!file_exists($this->_logPath)) {
	        $dirCreated = mkdir($this->_logPath, 0777, true);
	    }

	    if(!file_exists($this->_logPath . $filename)) {
	        $fileCreated = true;
	    }

	    $this->_logfile = fopen($this->_logPath . $filename, 'a');

	    if($dirCreated) {
	        $this->_log('Log dir created: ' . $this->_logPath);
	    }

	    if($fileCreated) {
	        $this->_log('Log file created: ' . $filename);
	    }
	}
}