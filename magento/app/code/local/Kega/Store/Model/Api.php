<?php
class Kega_Store_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
	 * Hello world test.
	 * You can use this to simply test the SOAP service.
	 * Service name: store.hello
	 */
    public function hello($value)
    {
        return array('text' => 'Hello world',
        			 'date' => date('Ymd'),
        			 'your_value' => $value);
    }
}