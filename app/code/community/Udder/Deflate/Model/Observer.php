<?php

/**
 * Class Udder_Deflate_Model_Observer
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Observer
{
    /**
     * Automatically add an image into the deflate queue once it's uploaded from the catalog
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function catalogImageUpload(Varien_Event_Observer $observer)
    {
        // Check we should action this upload
        if (Mage::helper('udder_deflate')->isConfigured() && Udder_Deflate_Model_Abstract::compressCatalog()) {

            /** @var $product Mage_Catalog_Model_Product */
            $product = clone $observer->getEvent()->getProduct();

            // Retrieve the products images
            $galleryImages = $product->getMediaGalleryImages();

            // Iterate through the images and build up an array
            $images = array();
            foreach ($galleryImages as $galleryImage) {

                // Is this image accepted?
                if (Mage::helper('udder_deflate')->isAccepted($galleryImage['path'])) {

                    // Retrieve the file data
                    $path = str_replace(Mage::getBaseDir(), '', dirname($galleryImage['path']));
                    $fileName = basename($galleryImage['path']);

                    $images[] = array(
                        'magento_type'   => Udder_Deflate_Model_Scan::TYPE_CATALOG,
                        'path'           => $path,
                        'name'           => $fileName,
                        'file_path_hash' => Udder_Deflate_Model_Scan::buildHash($path, $fileName),
                        'file_sha1'      => sha1_file($galleryImage['path']),
                        'original_size'  => filesize($galleryImage['path']),
                        'status'         => Udder_Deflate_Model_Image::STATUS_PENDING,
                        'created_at'     => Mage::getSingleton('core/date')->gmtDate(),
                        'updated_at'     => Mage::getSingleton('core/date')->gmtDate()
                    );

                }
            }

            // Grab an instance of the write connect
            $resource = Mage::getSingleton('core/resource');
            $dbWrite = $resource->getConnection('core_write');

            // Start our counter at 0
            $newImageCount = 0;

            // Insert only new rows
            foreach ($images as $image) {
                // Attempt to insert all this juicy data
                $newImageCount += $dbWrite->insertIgnore($resource->getTableName('udder_deflate/image'), $image);
            }

        }

        return $this;
    }
}
