<?

/**
 * Product search result block
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @module     Catalog
 */
class Kega_CatalogSearch_Block_Result extends Mage_CatalogSearch_Block_Result
{
	/**
	 * Kega_CatalogSearch_Block_Result->getGoogleResults
	 * Retrieves the google results from the register
	 *
	 * @param void
	 * @return void
     */
	public function getGoogleResults()
	{
		return Mage::registry('googleresult');
	}
}