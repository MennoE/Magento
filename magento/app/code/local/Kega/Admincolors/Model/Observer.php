<?php
/**
 * @category   Kega
 * @package    Kega_Init
 */
class Kega_Admincolors_Model_Observer
{
	/**
	 * Check language
	 * @param $observer
	 */
	public function bodyclass($observer)
	{
		try {
			$layout = $observer->getEvent()->getLayout();
			$status = Mage::helper('kega_admincolors')->getStatus();
			$header = $layout->getBlock('root');

			if(!is_object($header)) {
				return;
			}

			$type = $header->getType();
			if(strlen($type) == 0 || $type == 'adminhtml/page') {
				$header->addBodyClass('status-'.$status);
			}

		} catch(Exception $e) {
			error_log('Kega_Admincolors: Failed to add body class: ' . $e->getMessage());
		}
	}
}