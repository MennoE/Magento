<?php
class Kega_URapidFlow_Model_Config_Weeks
{
    public function toOptionArray()
    {
    	$weeks = array();
      	$i = 1;
        while ($i <= 10) {
        	$weeks[$i] = $i . ' weeks';
        	$i++;
        }
        return $weeks;
    }
}
