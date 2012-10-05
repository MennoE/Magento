<?php

class Kega_Init_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
        /**
         * Trigger parent construct to setup callbacks for filters
         */
        public function __construct()
        {
                parent::__construct();
        }

        /**
         * Additional email directive to translate phrases from within a transactional e-mail
         *
         * This directive can be used in multiple forms:
         * - {{translate module="<your_module_code>" phrase="Phrase to translate"}}
         * - {{translate phrase="Phrase to translate"}} // auto fallback on Mage_Core module
         *
         * return string
         */
        public function translateDirective($construction)
        {
                $params = $this->_getIncludeParameters($construction[2]);

                $module = 'core';
                if (isset($params['module'])) {
                        $module = $params['module'];
                }

                $phrase = '';
                if (isset($params['phrase'])) {
                        $phrase = $params['phrase'];
                }

                return Mage::helper($module)->__($phrase);
        }
}
