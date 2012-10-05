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

class Mage_Ogone_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
	 * folder and filename of log file (under var/log)
	 * @var String
	 */
	protected $_logfile;

	/**
     * Write to ogone logfile.
     *
     * Logfile is found under: var/log/ogone/yyyy-mm-dd.log
     * ogone folder is created if it not already exists
     *
     * @param String $message
     * @param int $level
     */
	public function log($message, $level = null)
	{
		if (!$this->_logfile) {
			$this->_logfile = 'ogone/' . date('Y-m-d') . '.log';
		}

		Mage::log($message, $level, $this->_logfile);
	}
}