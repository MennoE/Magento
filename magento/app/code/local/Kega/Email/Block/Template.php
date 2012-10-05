<?php
class Kega_Email_Block_Template extends Mage_Core_Block_Template
{
    /**
     * Render block
     * Extended Mage_Core_Block_Template::renderView()
     * to set default area and make use of extra params: package & theme.
     * 
     * Usage: {{block type='email/template' package='packagename' template='email/footer.phtml'}}
     *
     * @return string
     */
    public function renderView()
    {
        Varien_Profiler::start(__METHOD__);

        $this->setScriptPath(Mage::getBaseDir('design'));
        $params = array('_relative'=>true);
        if ($area = $this->getArea()) {
            $params['_area'] = $area;
        } else {
        	// We want to use frontend as default area.
        	$params['_area'] = 'frontend';
        }

        if ($package = $this->getPackage()) {
            $params['_package'] = $package;
        }

    	if ($theme = $this->getTheme()) {
            $params['_theme'] = $theme;
        }

        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        $html = $this->fetchView($templateName);

        Varien_Profiler::stop(__METHOD__);

        return $html;
    }
}
