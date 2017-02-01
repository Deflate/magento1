<?php

/**
 * Class Udder_Deflate_DeflateController
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Adminhtml_DeflateController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Display images grid
     */
    public function indexAction()
    {
        // Verify the system has been configured before loading the page
        if(!Mage::helper('udder_deflate')->isConfigured()) {
            $this->_getSession()->addError($this->__('You must configure the module before you\'re able to utilise it\'s compression.'));
            return $this->_redirect('adminhtml/system_config/edit/section/udder_deflate');
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Deflate - Image Compression'));
        $this->renderLayout();
    }

    /**
     * Grid action for images grid
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Allow the admin to scan for images
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function scanAction()
    {
        // Complete the scan for images
        /* @var $scan Udder_Deflate_Model_Scan */
        $scan = Mage::getModel('udder_deflate/scan');
        $foundImages = $scan->findImages(true);

        // Prompt the user with a flash message
        if($foundImages > 0) {
            $this->_getSession()->addSuccess($this->__('We scanned your file system and found %d image(s).', $foundImages));
        } else {
            $this->_getSession()->addNotice($this->__('Looks like we\'ve already located all of your images, no new images found. If you expected to find images check your configuration.'));
        }

        // Redirect them back to the grid
        return $this->_redirectReferer();
    }

    /**
     * Allow users to compress a single image
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function compressAction()
    {
        if($id = $this->getRequest()->getParam('id')) {
            /* @var $image Udder_Deflate_Model_Image */
            $image = Mage::getModel('udder_deflate/image')->load($id);
            if($image && $image->getId()) {

                // Attempt the compression
                /* @var $compress Udder_Deflate_Model_Compress */
                $compress = Mage::getModel('udder_deflate/compress');
                if($compress->compress($image)) {
                    $this->_getSession()->addSuccess($this->__('We have successfully pushed %s to Deflate for compression.', $image->getName()));
                } else {
                    $this->_getSession()->addError($this->__('We were unable to compress this image.'));
                }

                // Always send them back
                return $this->_redirectReferer();
            }
        }

        $this->_getSession()->addError($this->__('We\'re unable to load the requested image for compression.'));
        return $this->_redirectReferer();
    }

    /**
     * Allow users to compress multiple images at once
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function massCompressAction()
    {
        // Retrieve ids for compression
        $ids = $this->getRequest()->getParam('ids');
        if (is_array($ids)) {

            // Load an images collection
            /* @var $images Udder_Deflate_Model_Resource_Image_Collection */
            $images = Mage::getResourceModel('udder_deflate/image_collection')
                ->addFieldToFilter('image_id', array('in' => $ids));

            if($images->getSize()) {
                /* @var $compress Udder_Deflate_Model_Compress */
                $compress = Mage::getModel('udder_deflate/compress');
                $compress->compressMultiple($images);

                $this->_getSession()->addSuccess($this->__('We\'ve successfully pushed multiple images to Deflate for compression.'));
                return $this->_redirectReferer();
            }
        }

        $this->_getSession()->addError($this->__('Please select images(s) before trying to complete a mass action.'));
        return $this->_redirectReferer();
    }

    /**
     * Viewing a single images compression results
     *
     * @return Mage_Adminhtml_Controller_Action|Mage_Core_Controller_Varien_Action
     */
    public function viewAction()
    {
        if($id = $this->getRequest()->getParam('id')) {
            /* @var $image Udder_Deflate_Model_Image */
            $image = Mage::getModel('udder_deflate/image')->load($id);
            if($image && $image->getId()) {

                // Verify the image is complete
                if($image->getData('status') != Udder_Deflate_Model_Image::STATUS_COMPLETE && $image->getData('status') != Udder_Deflate_Model_Image::STATUS_REVERTED) {
                    $this->_getSession()->addError($this->__('You can only view images that have completed the compression process.'));
                    return $this->_redirectReferer();
                }

                // Store the image in the registry for the view block
                Mage::register('current_image', $image);

                $this->loadLayout();
                $this->getLayout()->getBlock('head')->setTitle($this->__('Viewing Compressed Image'));
                return $this->renderLayout();
            }
        }

        $this->_getSession()->addError($this->__('We\'re unable to load the requested image for viewing.'));
        return $this->_redirectReferer();
    }

    /**
     * Revert an image back to the original
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function revertAction()
    {
        if($id = $this->getRequest()->getParam('id')) {
            /* @var $image Udder_Deflate_Model_Image */
            $image = Mage::getModel('udder_deflate/image')->load($id);
            if($image && $image->getId()) {

                // Verify the image is complete
                if(!$image->getData('original_url')) {
                    $this->_getSession()->addError($this->__('This image was originally compressed without a subscription package, due to this reason it cannot be reverted.'));
                    return $this->_redirectReferer();
                }

                // Revert the image
                if($image->revert()) {
                    $this->_getSession()->addSuccess($this->__('The image has been reverted back to the original.'));
                    return $this->_redirect('*/*/index');
                }
            }
        }

        $this->_getSession()->addError($this->__('This image cannot be reverted.'));
        return $this->_redirectReferer();
    }
}