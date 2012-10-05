<?php
class Aschroder_SMTPPro_Model_Email_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /**
     * Directive for adding free cms banners
     * Supported options:
     *     id - banner id - must be filled
     *
     * @param array $construction
     * @return string
     */
    public function bannerDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);

        if (!isset($params['id'])) {
            return '';
        }

        $params['id'] = 16;

        $blockModule = Mage::getModel('freecms/block')->load($params['id']);

        if (!$blockModule->getId()) return '';

        $content = $blockModule->getContentHtml();

        $processor = Mage::getModel('core/email_template_filter');
		$html = $processor->filter($content);

        return $html;
    }
}