<?php

/**
 * Class Udder_Deflate_Model_Compress
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Compress extends Udder_Deflate_Model_Abstract
{
    const TYPE_LOSSLESS = 'lossless';
    const TYPE_LOSSY = 'lossy';

    /**
     * Compress the API's limit of images at once
     *
     * @return $this
     */
    public function compressAll()
    {
        // Grab the total amount of images we're able to compress at once
        /* @var $images Udder_Deflate_Model_Resource_Image_Collection */
        $images = Mage::getResourceModel('udder_deflate/image_collection')
            ->addFieldToFilter('status', Udder_Deflate_Model_Image::STATUS_PENDING);

        // Are there any images waiting to be compressed?
        if ($images->getSize()) {

            // Loop through the images
            $this->compressMultiple($images);
        }

        return $this;
    }

    /**
     * Compress multiple images at once
     *
     * @param Udder_Deflate_Model_Resource_Image_Collection $images
     *
     * @return mixed
     * @throws Exception
     */
    public function compressMultiple(Udder_Deflate_Model_Resource_Image_Collection $images)
    {
        // Build up our array of images to compress from the collection
        $batch = array();

        // Retrieve the total batch size
        $batchSize = $this->getSdk()->limit();

        // Start at array key index 0
        $batchNumber = 0;

        // Iterate through the images
        foreach ($images as $image) {

            // Build up an array of images
            $batch[$batchNumber][] = array(
                'url' => $image->getPublicUrl(),
                'id'  => $image->getId()
            );

            // Automatically batch the requests
            if (count($batch[$batchNumber]) == $batchSize) {
                ++$batchNumber;
            }
        }

        // Loop through our batches
        foreach ($batch as $compressImages) {

            // Retrieve the response
            $response = $this->getSdk()->compressMultiple(
                $compressImages,
                Mage::getStoreConfig('udder_deflate/general/type'),
                false,
                Mage::getUrl('deflate/index/callback'),
                array('handshake' => Mage::getStoreConfig('udder_deflate/general/handshake'))
            );

            // Detect the response
            if (isset($response->success) && $response->success == true) {

                // Change the status of the image entity
                foreach ($compressImages as $image) {
                    $imageEntity = $images->getItemById($image['id']);
                    $imageEntity->setData('status', Udder_Deflate_Model_Image::STATUS_COMPRESSING);
                    $imageEntity->save();
                }

            } else {
                $this->_log('Unsuccessful response: compressMultiple', $response);
            }
        }

        return $this;
    }

    /**
     * Compress a single image
     *
     * @param Udder_Deflate_Model_Image|Udder_Deflate_Model_Resource_Image_Collection $image
     *
     * @return bool
     * @throws Exception
     */
    public function compress($image)
    {
        // If someone calls compress with a collection forward it on correctly
        if ($image instanceof Udder_Deflate_Model_Resource_Image_Collection) {
            return $this->compress($image);
        }

        // Make the API request through the SDK
        $response = $this->getSdk()->compress(
            $image->getPublicUrl(),
            Mage::getStoreConfig('udder_deflate/general/type'),
            $image->getId(),
            false,
            Mage::getUrl('deflate/index/callback'),
            array('handshake' => Mage::getStoreConfig('udder_deflate/general/handshake'))
        );

        // Detect the response
        if (isset($response->success) && $response->success == true) {
            $image->setData('status', Udder_Deflate_Model_Image::STATUS_COMPRESSING);
            $image->save();

            return true;
        }

        $this->_log('Unsuccessful response: compress', $response);

        return false;
    }

    /**
     * Handle a callback from Deflate
     *
     * @return $this|bool
     */
    public function handleCallback()
    {
        $response = $this->getSdk()->callbackResponse();
        $this->_log('response', $response);
        if (isset($response->success) && $response->success == true) {

            // This response doesn't have a handshake
            if (!isset($response->custom) || (!isset($response->custom) && !isset($response->custom->handshake))) {
                $this->_log('Response contains no handshake', $response);

                return false;
            }

            // Does the handshake equal the response handshake?
            if ($response->custom->handshake != Mage::getStoreConfig('udder_deflate/general/handshake')) {
                $this->_log('Response contains invalid handshake', $response);

                return false;
            }

            // Determine if we compressed one image, or many
            if (isset($response->images) && is_array($response->images)) {
                foreach ($response->images as $image) {
                    $this->imageCallback($image);
                }
            } else {
                $this->imageCallback($response);
            }

        } else {
            $this->_log('Unsuccessful callback response', $response);
        }

        return $this;
    }

    /**
     * Individual image callback
     *
     * @param $response
     *
     * @return $this
     * @throws Exception
     */
    public function imageCallback($response)
    {
        // Attempt to load the image
        /* @var $image Udder_Deflate_Model_Image */
        $image = Mage::getModel('udder_deflate/image')->load($response->id);
        if ($image->getId()) {
            // Update the image
            $this->updateImage($image, $response);
        }

        return $this;
    }

    /**
     * Update the image in the database
     *
     * @param $image
     * @param $response
     *
     * @return $this
     * @throws Exception
     */
    private function updateImage($image, $response)
    {
        // Load a collection of matching images
        $matchingImages = Mage::getResourceModel('udder_deflate/image_collection')
            ->addFieldToFilter(array('image_id', 'file_sha1'),
                array(
                    array('eq' => $image->getId()),
                    array('eq' => $image->getFileSha1())
                )
            )->addFieldToFilter(array('status', 'status'),
                array(
                    array('eq' => Udder_Deflate_Model_Image::STATUS_COMPRESSING),
                    array('eq' => Udder_Deflate_Model_Image::STATUS_PENDING)
                )
            );

        // Replace matching images
        foreach ($matchingImages as $matchedImage) {

            // If the image was a success update it
            if ($response->success) {

                // Add the basic data from the response
                $matchedImage->addData(array(
                    'deflated_url'     => $response->deflated_url,
                    'deflated_size'    => $response->size->deflated_size,
                    'status'           => Udder_Deflate_Model_Image::STATUS_COMPLETE,
                    'deflate_hash'     => $response->hash,
                    'compression_type' => Mage::getStoreConfig('udder_deflate/general/type'),
                    'compressed_at'    => Mage::getSingleton('core/date')->gmtDate()
                ));

                // If the API returned the original URL update that
                if (isset($response->original_url)) {
                    $matchedImage->setData('original_url', $response->original_url);
                }

                // Replace the image on the file system
                $this->replaceImage($matchedImage);

            } else {
                // The image didn't compress successfully so update it as failed
                $matchedImage->setData('status', Udder_Deflate_Model_Image::STATUS_FAILED);
            }

            // Save the image
            $matchedImage->save();

        }

        return $this;
    }

    /**
     * Replace the image on the file system
     *
     * @param \Udder_Deflate_Model_Image $image
     * @param string                     $type
     *
     * @return bool
     */
    public function replaceImage(Udder_Deflate_Model_Image $image, $type = 'deflated_url')
    {
        // Make the CURL request to the AWS
        $curl = new Varien_Http_Client($image->getData($type));
        $deflatedContents = $curl->request();

        // Did we successfully retrieve the CURL request
        if ($deflatedContents->getStatus() == 200) {
            // @todo validate response mime type

            try {
                // Open a file connection and write the compressed version
                $ioFile = new Varien_Io_File();
                $ioFile->open(array('path' => rtrim(Mage::getBaseDir(), '/') . $image->getPath()));
                $ioFile->filePutContent($image->getName(), $deflatedContents->getBody());

                return true;
            } catch (Exception $e) {
                $this->_log('Unable to write file', $e->getMessage());
                $image->status = Udder_Deflate_Model_Image::STATUS_FAILED;

                return false;
            }
        }
    }
}
