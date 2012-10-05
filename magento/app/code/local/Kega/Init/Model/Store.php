<?php
/**
 * Extend core store model to use $_SERVER['HTTP_HOST'] as base url
 *
 * By default Magento stores the base url of the site in it's database. Since
 * we want to be able to run a magento installation with one database on
 * different urls we want to use $_SERVER['HTTP_HOST'] as base url and not the
 * DB value.
 */
class Kega_Init_Model_Store extends Mage_Core_Model_Store
{
	const XML_CONFIG_BASEURL_ENABLED = 'extrasettings/baseurl/active';

    /**
     * Return HTTP_HOST based baseurl and not the DB value
     *
     * @see Mage_Core_Model_Store::getBaseUrl()
     * @param String $type;
     * @param Boolean $secure
     */
	public function getBaseUrl($type=self::URL_TYPE_LINK, $secure=null)
    {
		$enabled = (bool) Mage::getStoreConfig(self::XML_CONFIG_BASEURL_ENABLED);

        // If no HTTP_HOST is set, use the default getBaseUrl.
    	if (!$enabled || !isset($_SERVER['HTTP_HOST'])) {
            return parent::getBaseUrl($type, $secure);
        }

        $cacheKey = 'Kega_Baseurl_' . $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
	    	// Check if we should use https.
	    	$secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;

	    	$url = 'http' . ($secure ? 's' : '') . '://'				// Add http(s)://
	    	     . preg_replace('/http[s]?:\/\/([^\/]+)/',				// Find http(s)://*/
	    	     				$_SERVER['HTTP_HOST'],					// Replace found part with
	    	     				parent::getBaseUrl($type, $secure));	// Original url to adjust

        	$this->_baseUrlCache[$cacheKey] = rtrim($url, '/') . '/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }
}