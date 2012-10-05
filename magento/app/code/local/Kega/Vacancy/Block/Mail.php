<?php
class Kega_Vacancy_Block_Mail extends Mage_Core_Block_Template
{
    protected $_candidate;
    protected $_vacancy;

    public function getCandidate()
    {
        if(empty($this->_candidate)) {
            $candidates = Mage::getModel('vacancy/vacancycandidate');
            $this->_candidate = $candidates->getDetails($this->candidateId);
        }
        return $this->_candidate;
    }

    public function getVacancy($id)
    {
        if(empty($this->_vacancy)) {
            $vacancies = Mage::getModel('vacancy/vacancy');
            $this->_vacancy = $vacancies->getDetails($id);
        }
        return $this->_vacancy;
    }
}