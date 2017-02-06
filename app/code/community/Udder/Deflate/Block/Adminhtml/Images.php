<?php

/**
 * Class Udder_Deflate_Block_Adminhtml_Images
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Block_Adminhtml_Images extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Construct the grids container
     */
    public function __construct()
    {
        $this->_blockGroup = 'udder_deflate';
        $this->_controller = 'adminhtml_images';
        $this->_headerText = $this->__('Deflate - Image Compression');
        parent::__construct();

        // Currently we don't allow new questions from the admin
        $this->_removeButton('add');

        // Add in our new buttons
        $this->_addButton('scan', array(
            'label'   => Mage::helper('udder_deflate')->__('Scan for Images'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/scan') . '\')',
            'class'   => 'add',
        ));
    }
}
