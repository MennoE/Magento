<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Block_Adminhtml_Productattributedefault_Grid_Renderer_Storeviews
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column categories
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {

        $rowHtml = '';

        // the category data is not in the collection because it's added only when a record is loaded
        $productAttributeDefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault')->load($row->getId());

        $applyToStoreIds = $productAttributeDefaultModel->getData('apply_to_stores');
        
        $storeNames = array();
        
        foreach ($applyToStoreIds as $storeId) {
        	if ($storeId == 0) {
        		$storeNames[] =  Mage::helper('adminhtml')->__('All Store Views');
        	} else {
        		$store = Mage::getModel('core/store')->load($storeId);
        		$storeNames[] =  $store->getWebsite()->getName().'/'.$store->getGroup()->getName().'/'.$store->getName();
        	}
        }

        return implode($storeNames, '<br />');
    }
}