<?php
/**
 *
 * Extend core footer block. Add functionality for footer columns.
 *
 */
class Kega_Init_Block_Page_Html_Footer extends Mage_Page_Block_Html_Footer
{

    /**
     * Get the columns from the config. The $position is required
     * for getting the config values.
     *
     * @param string
     * @return array
     */
    public function getColumns($position)
    {
        $categories = array();

        $config = Mage::getStoreConfig('extrasettings/footer/'.$position);
        $config = explode(",", $config);

        foreach($config as $categoryIds) {
            $categories[] = Mage::getModel('catalog/category')->load($categoryIds);
        }

        return $categories;
    }

}