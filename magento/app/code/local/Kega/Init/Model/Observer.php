<?php

/**
 * @category   Kega
 * @package    Kega_Init
 */
class Kega_Init_Model_Observer
{
	/**
	 * Observers controller_action_predispatch_adminhtml_targetrule_save
	 * Increases the php memory limit for the triggeree
	 *
	 * @param  Varien_Event_Observer  $observer array('mode' => $this)
	 * @return void
	 */
	public function setMemoryLimit()
	{
		ini_set("memory_limit","2048M");
		return $this;
	}

	/**
	 * Load xml handle for category display mode
	 *
	 * @param Varien_Event_Observer $observer
	 * @return Kega_Init_Model_Observer
	 */
	public function addCatalogDisplaymodeXml($observer)
	{
		$request = $observer->getEvent()->getAction()->getRequest();
		if($request->getControllerName() != 'category' || $request->getActionName() != 'view') {
			return $this;
		}

		$category = Mage::registry('current_category');
		$handle = 'category_displaymode_' . strtolower($category->getDisplayMode());

		$layout = $observer->getEvent()->getLayout();
		$layout->getUpdate()->addHandle($handle);

		return $this;
	}

    /**
    * Observers adminhtml_category_display_mode_options
    *
    * it adds a new option to category display mode options
    *
    * @param  Varien_Event_Observer  $observer array('mode' => $this)
    * @return void
    */
    public function addCatalogDisplayOption($observer)
    {
        $displayModeModel = $observer->getEvent()->getMode();

        $newSubhomeDisplayMode = array(
            'value' => 'SUBHOME',
            'label' => Mage::helper('init')->__('Sub-home'),
        );
        $displayModeModel->addOption($newSubhomeDisplayMode);

        // additional options can be added here.
    }

	/**
	 * Rebuilds all indices and cleans the Magento cache
	 *
	 * @return void
	 */
	public function reindexCatalogData()
	{
		Mage::log('Reindex all');

		$indexer = Mage::getSingleton('index/indexer');
		foreach ($indexer->getProcessesCollection() as $process) {
			Mage::log('Reindexing ' . $process->getIndexerCode());
			$process->reindexEverything();
		}

		Mage::log('Reindex all done, clear cache');
		Mage::app()->cleanCache();
	}
}