<?php

/**
 * Class Mage_Adminhtml_Model_System_Config_Source_Yesno
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_System_Config_Source_Type
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Udder_Deflate_Model_Compress::TYPE_LOSSLESS, 'label' => Mage::helper('udder_deflate')->__('Lossless')),
            array('value' => Udder_Deflate_Model_Compress::TYPE_LOSSY, 'label' => Mage::helper('udder_deflate')->__('Lossy')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Udder_Deflate_Model_Compress::TYPE_LOSSLESS => Mage::helper('udder_deflate')->__('Lossless'),
            Udder_Deflate_Model_Compress::TYPE_LOSSY    => Mage::helper('udder_deflate')->__('Lossy'),
        );
    }
}
