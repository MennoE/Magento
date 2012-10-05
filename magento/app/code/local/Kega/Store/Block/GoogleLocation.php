<?
/**
 * 
 * 
 *
 * @author Svetlana Rapshtynskaya
 */
class GoogleLocation
{
	/**
	 * GoogleLocation->getListMarkersXML
	 * get XML with marker items
	 * 
	 * @param float latitude
	 * @param float longitude
	 * @param float radius
	 * @return xml
	 * 
	 * @author Svetlana Rapshtynskaya
	 */
	public function getListMarkersXML($latitude, $longitude)
	{
		$locationArr = Mage::getModel('store/store')->getByCodeByGoogle($latitude, $longitude);

		$dom = new DOMDocument("1.0");
		
		$node = $dom->createElement("markers");
		
		$parnode = $dom->appendChild($node);

		// Iterate through the rows, adding XML nodes for each
		foreach($locationArr as $row)
		{
			$node = $dom->createElement("marker");
			
			$newnode = $parnode->appendChild($node);

			$newnode->setAttribute("name", $row->getNameInternal());
			
			$newnode->setAttribute("address", $row->getAddress().($row->getAddress()?", ":"").$row->getZipcode().", ".$row->getCity());
			$newnode->setAttribute("lat", $row->getLat());
			
			$newnode->setAttribute("lng", $row->getLng());
			
			$newnode->setAttribute("distance", $row->getDistance());
			
			$newnode->setAttribute("phone", $row->getPhone());
			
			$newnode->setAttribute("id", $row->getId());
			
			$newnode->setAttribute("city", $row->getCity());
			
			$newnode->setAttribute("email", $row->getEmail());
			
			$newnode->setAttribute("url", $row->getUrl());
			
			$newnode->setAttribute("custom_url", $row->getCustomUrl());
		}
		
		$xml = $dom->saveXML();
		
		unset($dom);
		
		unset($locations);

		return $xml;
	}
	
	
	

	public function getMarkerXML($storeId)
	{
		$obj = Mage::getModel('store/store')->load($storeId);
		
		$dom = new DOMDocument("1.0");
		
		$node = $dom->createElement("markers");
		
		$parnode = $dom->appendChild($node);
		
		// Iterate through the rows, adding XML nodes for each
		$node = $dom->createElement("marker");
		
		$newnode = $parnode->appendChild($node);
		
		$newnode->setAttribute("name", $obj->getNameInternal());
		
		$newnode->setAttribute("address", $obj->getAddress().($obj->getAddress()?", ":"").$obj->getZipcode().", ".$obj->getCity());
		
		$newnode->setAttribute("lat", $obj->getLat());
		
		$newnode->setAttribute("lng", $obj->getLng());
		
		$newnode->setAttribute("distance", $obj->getDistance());
		
		$newnode->setAttribute("phone", $obj->getPhone());
		
		$newnode->setAttribute("id", $obj->getId());
		
		$newnode->setAttribute("city", $obj->getCity());
		
		$newnode->setAttribute("email", $obj->getEmail());
		
		$newnode->setAttribute("url", $obj->getUrl());
		
		$xml = $dom->saveXML();
		
		unset($dom);
		
		unset($locations);
		
		return $xml;
	}



	/**
	 * GoogleLocation->getPlaceXML
	 * get XML with place
	 * 
	 * @param string place
	 * @return xml
	 *
	 * @author Svetlana Rapshtynskaya
	 */
	public function getPlaceXML($string)
	{
		$string = str_replace("%20", " ", $string);
		
		$replace = "[^0-9A-z,() ]";
		$string = ereg_replace($replace, "", $string);
	//	$string = substr ($string, 0, -13);
		$arr = explode(",", $string);
		$string = $arr[0].",".$arr[1];

		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("strings");
		$parnode = $dom->appendChild($node);
	
		$node = $dom->createElement("string");
		$newnode = $parnode->appendChild($node);
		$newnode->setAttribute("text", $string);

		$xml = $dom->saveXML();
		unset($dom);

		return $xml;
	}
	
}