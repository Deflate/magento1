<?php

/**
 * Class Udder_Deflate_Model_Abstract
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Return the Deflate SDK
     *
     * @return Deflate_Sdk
     */
    public function getSdk()
    {
        return Mage::helper('udder_deflate')->getSdk();
    }

    /**
     * Log data to a deflate.log
     *
     * @param $message
     * @param bool|false $data
     * @return $this
     */
    protected function _log($message, $data = false)
    {
        Mage::log($message, null, 'deflate.log', true);
        if($data) {
            Mage::log(print_r($data, true), null, 'deflate.log', true);
        }

        return $this;
    }

    /**
     * Should we compress the catalog images?
     *
     * @return bool
     */
    public static function compressCatalog()
    {
        return Mage::getStoreConfigFlag('udder_deflate/areas/catalog_images');
    }

    /**
     * Should we compress resized images in the catalog?
     *
     * @return bool
     */
    public static function compressCatalogResized()
    {
        return Mage::getStoreConfigFlag('udder_deflate/areas/catalog_images_resized');
    }

    /**
     * Should we compress CMS images?
     *
     * @return bool
     */
    public static function compressCms()
    {
        return Mage::getStoreConfigFlag('udder_deflate/areas/cms_images');
    }

    /**
     * Should we compress skin images?
     *
     * @return bool
     */
    public static function compressSkin()
    {
        return Mage::getStoreConfigFlag('udder_deflate/areas/skin_images');
    }

    /**
     * Are we only wanting to compress certain areas of the skin directory?
     *
     * @return string
     */
    public static function compressSpecificSkin()
    {
        return Mage::getStoreConfig('udder_deflate/areas/skin_theme_images');
    }
}