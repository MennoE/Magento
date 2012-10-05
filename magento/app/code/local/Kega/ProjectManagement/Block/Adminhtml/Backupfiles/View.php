<?php
class Kega_ProjectManagement_Block_Adminhtml_Backupfiles_View extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('projectmanagement/backupfiles/view.phtml');
    }

    protected function _prepareLayout()
    {
        $recordData = $this->getFileRecordData();

        if ($recordData) {
            $this->setHeaderText(Mage::helper('projectmanagement')->__('View File %s', $recordData['filename']));
        } else {
            $this->setHeaderText(Mage::helper('projectmanagement')->__('Invalid file'));
        }

        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('projectmanagement')->__('Back'),
                        'onclick' => "window.location.href = '" . $this->getUrl('*/*/backupfilesList'). "'",
                        'class'   => 'back',
                    )
                )
        );

        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getFileRecordData()
    {
        return Mage::registry('file_record_data');
    }

    public function getFilePath()
    {
        return Mage::registry('real_file_path');
    }

    public function getFileContentHtml()
    {

        $filePath = $this->getFilePath();

        if (!$filePath) {
            return '';
        }

        return file_get_contents($filePath);
    }
}