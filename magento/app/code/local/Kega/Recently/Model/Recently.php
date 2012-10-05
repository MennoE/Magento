<?php

class Kega_Recently_Model_Recently extends Mage_Core_Model_Abstract
{

    /**
     * Removes the recently viewed products for current visitor
     * 
     * we need this new function because the Mage_Reports_Model_Mysql4_Product_Index_Abstract::clean() method
     * removes only report_viewed_product_index records that have log_visitor visitor_id null
     * @see Mage_Reports_Model_Mysql4_Product_Index_Abstract::clean()
     */
    static function removeViewedProductsForCurrentVisitor()
    {
        $coreWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $visitorId = Mage::getSingleton('log/visitor')->getId();

        setcookie('VIEWED_PRODUCT_IDS', '', time() - 3600, '/');
        $coreWrite->delete(
                Mage::getSingleton('core/resource')->getTableName('report_viewed_product_index'),
                $coreWrite->quoteInto('visitor_id = ?', $visitorId)
            );

    }
}