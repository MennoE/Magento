<?php
class Kega_Contact_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Kega_Contact_IndexController::indexAction
     * Displays and handles contact form
     *
     * @param void
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $block = $this->_getBlock();

        if($this->getRequest()->getPost()) {
            if (!$this->_validateFormKey()) {
                return $this->_redirect('contact/*/*');
            }
            if($block->validateInput($this->getRequest()->getPost())) {
                $block->sendMail($this->getRequest()->getPost());
                $block->sendCustomerMail($this->getRequest()->getPost());
                Mage::getModel('core/session')->addSuccess($this->__('Your inquiry was submitted.'));
                $this->_redirect('contact');
                return;
            }
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('Contact'));
        $this->renderLayout();
    }

    public function receivedAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Contact'));
        $this->renderLayout();
    }

    private function _getBlock()
    {
        return $this->getLayout()->getBlock('contact.form');
    }
}