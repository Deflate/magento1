<?php

/**
 * Class Udder_Deflate_Model_Cron
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Cron
{

    /**
     * Find any images, and then compress them using our configuration
     *
     * @return $this
     */
    public function processImages()
    {
        // Start by scanning for any new images
        Mage::getModel('udder_deflate/scan')->findImages();

        // Then compress the list of images in the queue
        Mage::getModel('udder_deflate/compress')->compressAll();

        return $this;
    }
}
