<?
class Kega_Vacancy_Block_Form extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct() {
        parent::__construct();
    }

    public function getVacancy()
    {
        if (!$this->hasData('vacancy')) {
            $this->setData('vacancy', Mage::registry('vacancy'));
        }
        return $this->getData('vacancy');
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
    public function getPostValue($fieldName)
    {
        if(isset($_POST[$fieldName])) {
            return $_POST[$fieldName];
        } else {
            return '';
        }
    }
}