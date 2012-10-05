<?php
class Kega_Vacancy_Block_Vacancy extends Mage_Core_Block_Template
{
	public function __construct()
    {
        parent::__construct();
	}

	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }

    /**
     * Kega_Vacancy_Block_Vacancy::getVacancytypesWithVacancies()
     * Get all vacancytypes with the active vacancies per type
     */
    public function getVacancytypesWithVacancies()
    {
        $vacancyType = Mage::getModel('vacancytype/vacancytype')->setStoreId(Mage::app()->getStore()->getId());
        return $vacancyType->getVacancyTypesWithVacancies($this->getActiveRegion());
    }

    /**
     * Kega_Vacancy_Block_Vacancy::getRegions()
     * Get all active regions
     */
    public function getRegions()
    {
        $vacancyRegion = Mage::getModel('vacancy/vacancyregion');
        return $vacancyRegion->getActive();
    }

    /**
     * Kega_Vacancy_Block_Vacancy::getActiveRegion()
     * Get currently active region
     */
    public function getActiveRegion()
    {
        return $this->getRequest()->getParam('region', null);
    }

    /**
     * Kega_Vacancy_Block_Vacancy::getActiveType()
     * Get currently active type
     */
    public function getActiveType()
    {
        return $this->getRequest()->getParam('type', null);
    }

    /**
     * Kega_Vacancy_Block_Vacancy::getVacancy()
     * Get the current vacancy
     */
    public function getVacancy()
    {
    	$vacancyId = $this->getRequest()->getParam('show', 0);
        $vacancies = Mage::getModel('vacancy/vacancy');
        return $vacancies->getDetails($vacancyId);
    }

    public function getVacancyApplyValues()
    {
        if (!$this->hasData('vacancy-apply-values')) {
            $this->setData('vacancy-apply-values', Mage::registry('vacancy-apply-values'));
        }
        return $this->getData('vacancy-apply-values');
    }
    public function getVacancyApplyErrors()
    {
        if (!$this->hasData('vacancy-apply-errors')) {
            $this->setData('vacancy-apply-errors', Mage::registry('vacancy-apply-errors'));
        }
        return $this->getData('vacancy-apply-errors');
    }
    public function getPostValue($fieldName, $default = '')
    {
        if(isset($_POST[$fieldName])) {
            return $_POST[$fieldName];
        } else {
            return $default;
        }
    }
    public function formIsSubmitted()
    {
        return !empty($_POST);
    }
    public function hasErrorsClass($fieldName)
    {
        if ($this->hasData('vacancy-apply-errors')) {
            $errors = $this->getData('vacancy-apply-errors');
            if(isset($errors['required']) && in_array($fieldName, $errors['required'])) {
                return 'validation-failed';
            }
        }
        return '';
    }
    public function getFormHtml()
    {
        return $this->getLayout()->createBlock('vacancy/vacancy')
                    ->setTemplate('vacancy/form.phtml')
                    ->toHtml();
    }
    public function getStores()
    {
        $stores = Mage::getModel('store/store')->getCollection();
        $stores->addAttributeToSelect('name');
        $stores->setOrder('name','ASC');
        $stores->load();

        return $stores;
    }

    /**
     * Function that returns an array of english days 
     * for usage in vacancy open application.
     */
    public function getDays()
    {
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        return $days;
    }

    /**
     * Create the url for the vacancy detail page for a vacancy
     * 
     * @param array(vacancy), array(vacancy types)
     * @return string
     */
    public function getVacancyUrl($vacancy, $vacancytype)
    {
        $perma_title = preg_replace('/[^0-9a-z]+/i', '-', strtolower($vacancytype['title']));
        $perma_city = preg_replace('/[^0-9a-z]+/i', '-', strtolower($vacancy['store']->getName()));

        $url = Mage::getUrl('vacatures/' . $perma_title .'/'. $perma_city . '/', array('id' => $vacancy['vacancy_id']));
        return $url;
    }

}