<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Downloadable Product  Samples resource model
 *
 * @category   Mage
 * @package    Mage_Downloadable
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Mysql4_Link extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initialize connection and define resource
     *
     */
    protected function  _construct()
    {
        $this->_init('downloadable/link', 'link_id');
    }

    /**
     * Save title and price of link item
     *
     * @param Mage_Downloadable_Model_Link $linkObject
     * @return Mage_Downloadable_Model_Mysql4_link
     */
    public function saveItemTitleAndPrice($linkObject)
    {
        $stmt = $this->_getReadAdapter()->select()
            ->from($this->getTable('downloadable/link_title'))
            ->where('link_id = ?', $linkObject->getId())
            ->where('store_id = ?', $linkObject->getStoreId());
        if ($this->_getReadAdapter()->fetchOne($stmt)) {
            $where = $this->_getReadAdapter()->quoteInto('link_id = ?', $linkObject->getId()) .
                ' AND ' . $this->_getReadAdapter()->quoteInto('store_id = ?', $linkObject->getStoreId());
            if ($linkObject->getUseDefaultTitle()) {
                $this->_getWriteAdapter()->delete(
                    $this->getTable('downloadable/link_title'), $where);
            } else {
                $this->_getWriteAdapter()->update(
                    $this->getTable('downloadable/link_title'),
                    array('title' => $linkObject->getTitle()), $where);
            }
        } else {
            if (!$linkObject->getUseDefaultTitle()) {
                $this->_getWriteAdapter()->insert(
                    $this->getTable('downloadable/link_title'),
                    array(
                        'link_id' => $linkObject->getId(),
                        'store_id' => $linkObject->getStoreId(),
                        'title' => $linkObject->getTitle(),
                    ));
            }
        }
        $stmt = null;
        $stmt = $this->_getReadAdapter()->select()
            ->from($this->getTable('downloadable/link_price'))
            ->where('link_id = ?', $linkObject->getId())
            ->where('website_id = ?', $linkObject->getWebsiteId());
        if ($this->_getReadAdapter()->fetchOne($stmt)) {
            $where = $this->_getReadAdapter()->quoteInto('link_id = ?', $linkObject->getId()) .
                ' AND ' . $this->_getReadAdapter()->quoteInto('website_id = ?', $linkObject->getWebsiteId());
            if ($linkObject->getUseDefaultPrice()) {
                $this->_getReadAdapter()->delete(
                    $this->getTable('downloadable/link_price'), $where);
            } else {
                $this->_getWriteAdapter()->update(
                    $this->getTable('downloadable/link_price'),
                    array('price' => $linkObject->getPrice()), $where);
            }
        } else {
            if (!$linkObject->getUseDefaultPrice()) {
                $dataToInsert[] = array(
                    'link_id' => $linkObject->getId(),
                    'website_id' => $linkObject->getWebsiteId(),
                    'price' => $linkObject->getPrice()
                );
                $_isNew = $linkObject->getOrigData('link_id') != $linkObject->getLinkId();
                if ($linkObject->getWebsiteId() == 0 && $_isNew && !Mage::helper('catalog')->isPriceGlobal()) {
                    $websiteIds = $linkObject->getProductWebsiteIds();
                    foreach ($websiteIds as $websiteId) {
                        $baseCurrency = Mage::app()->getBaseCurrencyCode();
                        $websiteCurrency = Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode();
                        if ($websiteCurrency == $baseCurrency) {
                            continue;
                        }
                        $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($websiteCurrency);
                        if (!$rate) {
                            $rate = 1;
                        }
                        $newPrice = $linkObject->getPrice() * $rate;
                        $dataToInsert[] = array(
                            'link_id' => $linkObject->getId(),
                            'website_id' => $websiteId,
                            'price' => $newPrice
                        );
                    }
                }
                foreach ($dataToInsert as $_data) {
                    $this->_getWriteAdapter()->insert($this->getTable('downloadable/link_price'), $_data);
                }
            }
        }
        return $this;
    }

    /**
     * Delete data by item(s)
     *
     * @param Mage_Downloadable_Model_Link|array|int $items
     * @return Mage_Downloadable_Model_Mysql4_Link
     */
    public function deleteItems($items)
    {
        $where = '';
        if ($items instanceof Mage_Downloadable_Model_Link) {
            $where = $this->_getReadAdapter()->quoteInto('link_id = ?', $items->getId());
        }
        elseif (is_array($items)) {
            $where = $this->_getReadAdapter()->quoteInto('link_id in (?)', $items);
        }
        else {
            $where = $this->_getReadAdapter()->quoteInto('sample_id = ?', $items);
        }
        if ($where) {
            $this->_getWriteAdapter()->delete(
                $this->getTable('downloadable/link'), $where);
            $this->_getWriteAdapter()->delete(
                $this->getTable('downloadable/link_title'), $where);
            $this->_getWriteAdapter()->delete(
                $this->getTable('downloadable/link_price'), $where);
        }
        return $this;
    }

    /**
     * Retrieve links searchable data
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getSearchableData($productId, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('link' => $this->getMainTable()), null)
            ->join(
                array('link_title_default' => $this->getTable('downloadable/link_title')),
                'link_title_default.link_id=link.link_id AND link_title_default.store_id=0',
                array())
            ->joinLeft(
                array('link_title_store' => $this->getTable('downloadable/link_title')),
                'link_title_store.link_id=link.link_id AND link_title_store.store_id=' . intval($storeId),
                array('title' => 'IFNULL(link_title_store.title, link_title_default.title)'))
            ->where('link.product_id=?', $productId);
        if (!$searchData = $this->_getReadAdapter()->fetchCol($select)) {
            $searchData = array();
        }
        return $searchData;
    }
}
