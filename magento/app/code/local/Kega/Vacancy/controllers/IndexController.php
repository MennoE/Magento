<?php
class Kega_Vacancy_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action. Redirect to vacancies controller & regions action
     *
     */
    public function indexAction()
    {
        $this->_forward('index', 'vacancies');
        return;
    }
}