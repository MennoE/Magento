<?php

class Kega_Customer_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
	/**
	 * Remove link from account navigation
	 * Extended: this a whole new method for this block, no core option available
	 *
	 * @param string $name
	 * @return void
	 */
    public function removeLink($name)
    {
		if (isset($this->_links[$name])) {
			unset($this->_links[$name]);
		}
    }
}