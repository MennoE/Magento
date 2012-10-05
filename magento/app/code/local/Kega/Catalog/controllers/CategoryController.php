<?php
require_once 'Mage/Catalog/controllers/CategoryController.php';

class Kega_Catalog_CategoryController extends Mage_Catalog_CategoryController
{
    /**
     * Initialize requested category object
     * Extended because the category description needed to be replaced
     * with processed description to make widgets and variables work
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCatagory()
    {
        Mage::dispatchEvent('catalog_controller_category_init_before', array('controller_action' => $this));
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        // Replace category description with processed description
        $cmsHelper = Mage::helper('cms');
        $processor = $cmsHelper->getBlockTemplateProcessor();

        $processedDescription = $processor->filter($category->getDescription());
        $category->setDescription($processedDescription);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
        Mage::register('current_category', $category);
        try {
            Mage::dispatchEvent(
                'catalog_controller_category_init_after',
                array(
                    'category' => $category,
                    'controller_action' => $this
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $category;
    }
}