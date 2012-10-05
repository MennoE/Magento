<?php
/**
 * Default Init helper
 *
 * Used for translations and admin pages
 */
class Kega_Init_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Checks if file exists by getting the header only (alot more efficient that
     * file_exists()
     *
     * @param string $url
     * @param String $type
     * @return boolean $ret
     */
    function curlFileExists($url) {

        $return = false;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);

        $result = curl_exec($curl);

        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $return = true;
            }
        }
        curl_close($curl);

        return $return;
    }
    
	/**
     * Check to see if the url of a breadcrumb is relative or absolute. Uses storeview code for localized url's.
     *
     * @param String $url
     * @return String
     */
	public function getCrumbLink($url)
    {
    	$urlpart = substr($url, 0, 4);
    	
    	if ($urlpart === 'http') {
    		return $url;
    	} else {
	    	if (Mage::getStoreConfigFlag('web/url/use_store')) {
	        	$storecode = Mage::app()->getStore()->getCode();
	    		return '/' . $storecode . $url;
	    	} else {
	    		return $url;
	    	}
        }
    }
}
