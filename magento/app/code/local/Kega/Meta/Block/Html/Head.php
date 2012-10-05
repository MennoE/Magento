<?php
class Kega_Meta_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    public function getTitle()
    {
        if($rTitle = Mage::registry('kega_meta_title')) {
            return $rTitle;
        }
        return parent::getTitle();
    }
    public function getKeywords()
    {
        if($rKeywords = Mage::registry('kega_meta_keywords')) {
            return $rKeywords;
        }
        return parent::getKeywords();
    }
    public function getDescription()
    {
        if($rDescription = Mage::registry('kega_meta_description')) {
            return $rDescription;
        }
        return parent::getDescription();
    }

    public function setTitle($title)
    {
        parent::setTitle($title);
    }

    public function overwriteTitle($title)
    {
        $this->_set('kega_meta_title', $title);
    }
    public function prependTitle($title)
    {
        return $this->_prepend('kega_meta_title', $title, ', ');
    }
    public function appendTitle($title)
    {
        return $this->_append('kega_meta_title', $title, ', ');
    }

    public function setKeywords($keywords)
    {
        $this->_set('kega_meta_keywords', $keywords);
    }
    public function prependKeywords($keywords)
    {
        return $this->_prepend('kega_meta_keywords', $keywords, ', ');
    }
    public function appendKeywords($keywords)
    {
        return $this->_append('kega_meta_keywords', $keywords, ', ');
    }

    public function setDescription($description)
    {
        $this->_set('kega_meta_description', $description);
    }
    public function prependDescription($description)
    {
        return $this->_prepend('kega_meta_description', $description, ', ');
    }
    public function appendDescription($description)
    {
        return $this->_append('kega_meta_description', $description, ', ');
    }


    public function _set($registerKey, $data)
    {
        Mage::unregister($registerKey);
        Mage::register($registerKey, $data);
    }
    public function _prepend($registerKey, $data, $separator = '')
    {
        $data = $data . (Mage::registry($registerKey) ? $separator . Mage::registry($registerKey) : '');
        Mage::unregister($registerKey);
        Mage::register($registerKey, $data);
    }
    public function _append($registerKey, $data, $separator = '')
    {
        $data = (Mage::registry($registerKey) ? Mage::registry($registerKey) . $separator : '') . $data;
        Mage::unregister($registerKey);
        Mage::register($registerKey, $data);
    }
}