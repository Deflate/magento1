<?php

/**
 * Class Udder_Deflate_Block_Adminhtml_Images_View
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Block_Adminhtml_Images_View extends Mage_Core_Block_Template
{

    /**
     * Return the current image
     *
     * @return Udder_Deflate_Model_Image
     */
    public function getImage()
    {
        return Mage::registry('current_image');
    }

    /**
     * Can we view the original on this image?
     *
     * @return bool
     */
    public function canViewOriginal()
    {
        return !!$this->getImage()->getData('original_url');
    }

    /**
     * Return the types label
     *
     * @param $type
     *
     * @return bool
     */
    public function getTypeLabel($type)
    {
        $types = Udder_Deflate_Model_Scan::getTypesAsArray();
        if(isset($types[$type])) {
            return $types[$type];
        }

        return false;
    }

}