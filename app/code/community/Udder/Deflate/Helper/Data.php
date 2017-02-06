<?php

/**
 * Class Udder_Deflate_Helper_Data
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * The extensions which can be compressed by the system
     *
     * @var
     */
    private $_allowedExtensions;

    /**
     * Store the connection to the SDK in this model
     *
     * @var
     */
    private $_sdk = false;

    /**
     * Return the Deflate SDK
     *
     * @return Deflate_Sdk
     */
    public function getSdk()
    {
        if (!$this->_sdk) {
            try {
                $this->_sdk = new Deflate_Sdk(
                    Mage::getStoreConfig('udder_deflate/general/api_key'),
                    Mage::getStoreConfig('udder_deflate/general/api_secret')
                );
            } catch (Exception $e) {
                Mage::throwException($e);

                return false;
            }
        }

        return $this->_sdk;
    }

    /**
     * Return a human readable size from bytes
     *
     * @param $bytes
     *
     * @return string
     */
    public function readableSize($bytes)
    {
        $base = log($bytes, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }

    /**
     * Return the difference between two numbers as a percentage
     *
     * @param $original
     * @param $deflated
     *
     * @return string
     */
    public function percentageDifference($original, $deflated)
    {
        return number_format((($original - $deflated) / $original * 100), 2);
    }

    /**
     * Return the allowed extensions
     *
     * @return bool|mixed
     */
    public function getAllowedExtensions()
    {
        // Grab the allowed extensions from the API
        if (!$this->_allowedExtensions) {
            $this->_allowedExtensions = $this->getSdk()->supported('extensions');
        }

        return $this->_allowedExtensions;
    }

    /**
     * Check whether the found file is accepted by the API
     *
     * @param SplFileInfo|string $file
     *
     * @return bool
     */
    public function isAccepted($file)
    {
        // Load the file into the SplFileInfo
        if (!$file instanceof SplFileInfo) {
            $file = new SplFileInfo($file);
        }

        // Verify the extension can be compressed by the service
        if (in_array($file->getExtension(), $this->getAllowedExtensions())) {
            return true;
        }

        return false;
    }

    /**
     * Has the module been configured as of yet?
     *
     * @return bool
     */
    public function isConfigured()
    {
        if (!Mage::getStoreConfig('udder_deflate/general/api_key') || !Mage::getStoreConfig('udder_deflate/general/api_secret')) {
            return false;
        }

        return true;
    }
}
