<?php
/**
 * Adminhtml backup files list grid block
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Block_Adminhtml_Backupfiles_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('backupfilesGrid');
        $this->setDefaultSort('created_on');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('projectmanagement/backupfile_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('dirname', array(
            'header'    => Mage::helper('projectmanagement')->__('Directory Name'),
            'index'     => 'dirname',
            'sortable' => false,
        ));

        $this->addColumn('filename', array(
            'header'    => Mage::helper('projectmanagement')->__('File Name'),
            'index'     => 'filename',
            'sortable' => false,
        ));


        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/backupfilesView', array(
            'id'=>$row->getId())
        );
    }

}
