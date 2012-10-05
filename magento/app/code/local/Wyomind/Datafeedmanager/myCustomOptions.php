<?php

class MyCustomOptions extends Wyomind_Datafeedmanager_Model_Datafeedmanager {

    public $_m_discount = array();
    public $_m_discount_computed = false;

    public function _eval($product, $exp, $value) {
        // Example of custom option {number_attribute,[myFloat],[3],[,]}
        // this converts all number to a float number with 3 decimals with coma separated

        switch ($exp['options'][$this->option]) {
            case "float_int" :

                $value = number_format($value, $exp['options'][$this->option + 1], $exp['options'][$this->option + 2], '');
                //skip the two next options
                $this->skipOptions(3);


                return $value;

                break;

            case "manufacturer_discount" :

                if (!$this->_m_discount_computed) {

                    $this->_m_discount_computed = true;

                    $uri = $_SERVER['REQUEST_URI'];
                    $uri_splitted = preg_split('/\//', $uri);
                    foreach ($uri_splitted as $key => $split) {
                        if ($split == 'feed_id') {
                            $spliter = $key;
                            break;
                        }
                    }
                    if ($spliter == null)
                        $fid = $_POST['feed_id'];
                    else
                        $fid = $uri_splitted[$spliter + 1];



                    $feed = Mage::getModel('datafeedmanager/datafeedmanager')->load($fid);
                    $manuf_discount = $feed->getManufacturerdiscount();

                    $manuf_discount = json_decode($manuf_discount, true);
                    foreach ($manuf_discount['manus'] as $discount) {
                        $this->_m_discount[$discount['id']] = $discount['discount'];
                    }
                }


                $discount = $this->_m_discount[$product->getHersteller()];
                if ($discount === "")
                    $discount = 0;
                $new_price = $value * (100 - $discount) / 100;
                $new_price = number_format($new_price, 2, ',', ''); // to get 2 digits after ','


                $this->skipOptions(1);
                return $new_price;
                break;

            /*             * ************* DO NOT CHANGE THESE LINES ************** */
            default :
                eval('$value=' . $exp['options'][$this->option] . '($value);');
                $this->skipOptions(1);
                return $value;
                break;
            /*             * ************* DO NOT CHANGE THESE LINES ************** */
        }
    }

}
