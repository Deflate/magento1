<?php

/**
 * Class Udder_Deflate_Model_Image
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Image extends Mage_Core_Model_Abstract
{
    /**
     * The various statuses for an image
     */
    const STATUS_PENDING = 1;
    const STATUS_COMPRESSING = 2;
    const STATUS_FAILED = 3;
    const STATUS_COMPLETE = 4;
    const STATUS_REVERTED = 5;

    protected function _construct()
    {
        $this->_init('udder_deflate/image');
    }

    /**
     * Return the Magento types as an array
     *
     * @return array
     */
    public static function getStatusesAsArray()
    {
        return array(
            self::STATUS_PENDING => Mage::helper('udder_deflate')->__('Pending'),
            self::STATUS_COMPRESSING => Mage::helper('udder_deflate')->__('Compressing'),
            self::STATUS_FAILED => Mage::helper('udder_deflate')->__('Failed'),
            self::STATUS_COMPLETE => Mage::helper('udder_deflate')->__('Complete'),
            self::STATUS_REVERTED => Mage::helper('udder_deflate')->__('Reverted')
        );
    }

    /**
     * Return the public URL
     *
     * @return string
     */
    public function getPublicUrl()
    {
        return rtrim(Mage::getBaseUrl('web'), '/') . $this->getPath() . '/' . $this->getName();
    }

    /**
     * Revert this image
     *
     * @return $this|bool
     * @throws Exception
     */
    public function revert()
    {
        /* @var $compress Udder_Deflate_Model_Compress */
        $compress = Mage::getModel('udder_deflate/compress');

        // Does this image has an original url?
        if(!$this->getData('original_url')) {
            return false;
        }

        // Attempt to replace the image
        if($compress->replaceImage($this, 'original_url')) {
            $this
                ->setData('status', self::STATUS_REVERTED)
                ->setData('deflated_size', NULL)
                ->setData('deflated_url', NULL)
                ->save();
            return $this;
        } else {
            return false;
        }
    }

}