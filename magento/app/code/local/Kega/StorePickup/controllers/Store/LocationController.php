<?php

class Kega_StorePickup_Store_LocationController extends Mage_Core_Controller_Front_Action
{
	protected function _getStoreListHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod_storepickup_list');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

	public function getStoreWithDistanceAction()
	{
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node);

		try {
			$collection = Mage::getModel('store/store')->getCollection()
				->addStoreviewFilter(Mage::app()->getStore()->getId())
				->addDistance($this->getRequest()->getParam('lat'), $this->getRequest()->getParam('lng'))
				->addAttributeToSelect('distance')
				->addAttributeToSelect('lat')
				->addAttributeToSelect('lng');

			$privateFields = Mage::getConfig()->getNode('global/ustorelocator/private_fields');
			foreach ($collection as $loc){
				$node = $dom->createElement("marker");
				$newnode = $parnode->appendChild($node);
				foreach ($loc->getData() as $k=>$v) {
					$newnode->setAttribute($k, $v);
				}
			}
		} catch (Exception $e) {
			$node = $dom->createElement('error');
			$newnode = $parnode->appendChild($node);
			$newnode->setAttribute('message', $e->getMessage());
		}

		$this->getResponse()->setHeader('Content-Type', 'text/xml')->setBody($dom->saveXml());
	}

	public function searchNearestStoreAction()
    {
        $collection = Mage::getModel('store/store')->getCollection()
        		->addStoreviewFilter(Mage::app()->getStore()->getId())
				->addDistance($this->getRequest()->getParam('lat'), $this->getRequest()->getParam('lng'), Mage::getStoreConfig('store/google_map/store_count'))
				->addAttributeToSelect('distance')
				->joinAttribute('name', 'store/name', 'entity_id', null, 'inner', Mage::app()->getStore()->getId())
				->joinAttribute('address', 'store/address', 'entity_id', null, 'inner', Mage::app()->getStore()->getId())
				->addAttributeToSelect('lat')
				->addAttributeToSelect('lng');

    	Mage::register('pickup_store_list',$collection);

    	$result = array(
            'html' => $this->_getStoreListHtml()
        );
    	$this->getResponse()->setBody(Zend_Json::encode($result));
    }
}