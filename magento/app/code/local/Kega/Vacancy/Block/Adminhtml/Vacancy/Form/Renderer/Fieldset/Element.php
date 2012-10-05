<?php
class Kega_Vacancy_Block_Adminhtml_Vacancy_Form_Renderer_Fieldset_Element extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
	const SCOPE_GLOBAL = 'global';
	const SCOPE_WEBSITE = 'website';
	const SCOPE_STORE_VIEW = 'storeview';
	
    /**
     * Initialize block template
     */
    protected function _construct()
    {
        $this->setTemplate('catalog/form/renderer/fieldset/element.phtml');
    }

    /**
     * Retrieve data object related with form
     *
     * @return
     */
    public function getDataObject()
    {
        return $this->getElement()->getForm()->getDataObject();
    }

    /**
     * Retireve associated with element attribute object
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute()
    {
        return $this->getElement()->getEntityAttribute();
    }

    /**
     * Retrieve associated attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {    	
        return $this->getElement()->getName();
    }

    /**
     * Check "Use default" checkbox display availability
     *
     * @return bool
     */
    public function canDisplayUseDefault()
    {    	
    	$elementScope = $this->getElement()->getData('scope');
    	
        if ($elementScope != self::SCOPE_GLOBAL
              && $this->getElement()->getForm()->getObjectId()
              && $this->getElement()->getForm()->getStoreId()) {
              return true;
        }
        
        return false;
    }

    /**
     * Check default value usage fact
     *
     * @return bool
     */
    public function usedDefault()
    {    	
    	$useDefault = $this->getElement()->getForm()->getUseDefault();
    	
        $defaultValue = $this->getElement()->getValue();
        
        if (!empty($useDefault) && in_array($this->getElement()->getName(), $useDefault)) {
        	return true;
        }
        return false;
    }

    /**
     * Disable field in default value using case
     *
     * @return Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
     */
    public function checkFieldDisable()
    {
        if ($this->canDisplayUseDefault() && $this->usedDefault()) {
            $this->getElement()->setDisabled(true);
        }
        return $this;
    }

    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @return string
     */
    public function getScopeLabel()
    {		
    	$elementScope = $this->getElement()->getData('scope');
        $html = '';
        if ($elementScope == self::SCOPE_GLOBAL) {
            $html.= '[GLOBAL]';
        }
        elseif ($elementScope == self::SCOPE_WEBSITE) {
            $html.= '[WEBSITE]';
        }
        elseif ($elementScope == self::SCOPE_STORE_VIEW) {
            $html.= '[STORE VIEW]';
        }

        return $html;
    }

    /**
     * Retrieve element label html
     *
     * @return string
     */
    public function getElementLabelHtml()
    {
        return $this->getElement()->getLabelHtml();
    }

    /**
     * Retrieve element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        return $this->getElement()->getElementHtml();
    }
}