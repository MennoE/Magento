<?php
/**
 *
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Model_Observer
{

    private $_logFileDir;


    public function __construct()
    {
        $this->_logFileDir = Mage::helper('kega_productattributedefault')->getLogDir();
    }

    /**
     * Run by cron
     *
     * @param object $schedule
     *
     * Set the product attributes default values and the product categories
     * The attribute values and product categories are overwritten.
     * A log file created every time the method is called in var/log/kega_productattributedefault
     */
    public function setProductDefaultValues($schedule)
    {
        ini_set('memory_limit', '6048M');
        $productAttributeDefaultCollection = Mage::getModel('kega_productattributedefault/productattributedefault')->getCollection()
                                                ->setOrder('productattributedefault_id', 'DESC');

        foreach ($productAttributeDefaultCollection as $productAttributeDefaultItem) {
            try {
                sleep(1);// to make sure we have different log file names

                // don't we want 1 logfile per "run"?
                $logfile = Mage::helper('kega_productattributedefault')->getLogFile();

                // we load the model to have the attributes and categories data set
                $productAttributeDefaultModel = Mage::getModel('kega_productattributedefault/productattributedefault')
                                                    ->load($productAttributeDefaultItem->getId());

                $productAttributeDefaultModel->setData('log_file', basename($logfile));
                $productAttributeDefaultModel->save();

                $productAttributeDefaultModel->runProfile($logfile);

            } catch (Exception $e) {
                $logger->err($e->getMessage());
            }
        }

        if (Mage::helper('kega_productattributedefault')->getUseURapidflow()) {
            Mage::getModel('kega_productattributedefault/urapidflow')->runUrapidFlowImport();
        }

    }



	/**
	 * Observers catalog_product_prepare_save
	 * sets the manually changed product
	 *
	 * @param Varient_Event_Observer $observer
	 */
    public function setManualChangedProduct($observer)
    {
    	$product = $observer->getEvent()->getProduct();
    	$request = $observer->getEvent()->getRequest();


    	$dbProduct = Mage::getModel('catalog/product')->load($product->getId());

    	$session = Mage::getSingleton('adminhtml/session');
    	$session->setManualChangedProduct($product);
    	// $product->getOrigData('category_ids') is null so we need to save the previous category_ids
    	$session->setProductCategoryIds($dbProduct->getCategoryIds());
    }


	/**
	 * Observers catalog_product_save_after
	 * Saves the manually changed product data
	 *
	 * @param Varient_Event_Observer $observer
	 */
    public function saveManualChangedProductAttributes($observer)
    {
    	$product = $observer->getEvent()->getProduct();

    	$manualChangedProduct = Mage::getSingleton('adminhtml/session')->getManualChangedProduct();

    	if (!$manualChangedProduct) return;

    	if ($manualChangedProduct->getId() != $product->getId()) return;

    	Mage::getSingleton('adminhtml/session')->setManualChangedProduct(false);

    	$productAttributes = Mage::helper('kega_productattributedefault')->getProductAttributesOptions();


    	$changedAttributes = array();
        foreach ($productAttributes as $attributeCode => $attributeData) {
        	if ($product->getData($attributeCode) === false) continue;// this means it was not changed in the current store view

        	if ($product->getData($attributeCode) == $product->getOrigData($attributeCode)) continue;

        	$changedAttributes[$attributeCode]  = array(
        		'attribute_code' => $attributeCode,
        		'previous_value' => $product->getOrigData($attributeCode),
        		'new_value' => $product->getData($attributeCode),
        		'changed_at' => date('Y-m-d h:i:s'),
        	);
        }


        if ($product->getData('category_ids') != Mage::getSingleton('adminhtml/session')->getProductCategoryIds()) {
        	$changedAttributes['category_ids']  = array(
        		'attribute_code' => 'category_ids',
        		'previous_value' => implode(',',Mage::getSingleton('adminhtml/session')->getProductCategoryIds()),
        		'new_value' => implode(',',$product->getData('category_ids')),
        		'changed_at' => date('Y-m-d h:i:s'),
        	);
        }

        Mage::getSingleton('adminhtml/session')->setProductCategoryIds(false);

        Mage::getModel('kega_productattributedefault/productattributedefault')
        		->saveManualProductAttributeChanges($product->getId(), $changedAttributes, $product->getStoreId());


        return;
    }


	/**
	 * Observers adminhtml_block_eav_attribute_edit_form_init
	 * adds a new field to the attribute form
	 *
	 * @param Varient_Event_Observer $observer
	 */
    public function addAttributeFormField($observer)
    {
    	$form = $observer->getEvent()->getForm();

    	$fieldset = $form->getElement('base_fieldset');

    	$productEnricherValues = array();

    	$productEnricherValues[] = array(
    		'label' => 'Nee',
    		'value' => 'no',
    	);

    	$productEnricherValues[] = array(
    		'label' => 'Ja, statisch',
    		'value' => 'static',
    	);

    	$productEnricherValues[] = array(
    		'label' => 'Ja, statisch en dinamisch',
    		'value' => 'static_and_dynamic',
    	);

    	$fieldset->addField('product_enricher', 'select', array(
            'name'  => 'product_enricher',
            'label' => Mage::helper('eav')->__('Kan worden ingesteld in Kega Product Verrijker'),
            'title' => Mage::helper('eav')->__('Kan worden ingesteld in Kega Product Verrijker'),
            'values'=> $productEnricherValues,
        ));


    }


}
