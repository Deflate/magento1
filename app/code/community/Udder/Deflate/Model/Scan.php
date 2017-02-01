<?php

/**
 * Class Udder_Deflate_Model_Scan
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Scan extends Udder_Deflate_Model_Abstract
{
    /**
     * The various Magento types
     */
    const TYPE_CATALOG = 1;
    const TYPE_CMS = 2;
    const TYPE_SKIN = 3;

    /**
     * Return the Magento types as an array
     *
     * @return array
     */
    public static function getTypesAsArray()
    {
        return array(
            self::TYPE_CATALOG => Mage::helper('udder_deflate')->__('Catalog'),
            self::TYPE_CMS => Mage::helper('udder_deflate')->__('CMS'),
            self::TYPE_SKIN => Mage::helper('udder_deflate')->__('Skin/Template')
        );
    }

    /**
     * Recursively loop through the file system to find images
     *
     * @param bool|false $returnCount
     * @return $this|int
     */
    public function findImages($returnCount = false)
    {
        // Only run if at least one image directory exists
        if ($dirs = $this->getImageDirectories()) {

            // Recursively loop through our directories and pull out any images
            $images = array();
            $this->iterateDirectories($images, $dirs);

            // Grab an instance of the write connect
            $resource = Mage::getSingleton('core/resource');
            $dbWrite = $resource->getConnection('core_write');

            // Start our counter at 0
            $newImageCount = 0;

            // Insert only new rows
            foreach($images as $image) {
                // Attempt to insert all this juicy data
                $newImageCount += $dbWrite->insertIgnore($resource->getTableName('udder_deflate/image'), $image);
            }

            // Should we return the new images we've found?
            if($returnCount) {
                return $newImageCount;
            }

        }

        return $this;
    }

    /**
     * Iterate through the directories to find images
     *
     * @param $images
     * @param $dirs
     * @param $previousType
     */
    protected function iterateDirectories(&$images, $dirs, $previousType = false)
    {
        // Iterate through the provided directories
        foreach ($dirs as $type => $dir) {

            // If the current dir, is actually an array, loop it
            if(is_array($dir)) {
                $this->iterateDirectories($images, $dir, $type);
                continue;
            }

            // If we've become nested in an associative array, use the parent type
            if($previousType) {
                $type = $previousType;
            }

            // Check the directory is a directory before continuing
            if(is_dir($dir)) {

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $dir, RecursiveDirectoryIterator::SKIP_DOTS
                    ),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                // Loop through the iterator
                /* @var $info SplFileInfo */
                foreach ($iterator as $info) {
                    $fileName = $info->getFileName();

                    // Check this file is accepted by the API, and is in a valid directory
                    if (Mage::helper('udder_deflate')->isAccepted($info)
                        && $this->isAllowedDirectory($info)
                    ) {

                        // Pull the path
                        $path = str_replace(
                            Mage::getBaseDir(), '', $info->getPath()
                        );

                        // Build up the required data to easily insert this into the data
                        $images[] = array(
                            'magento_type' => $type,
                            'path' => $path,
                            'name' => $fileName,
                            'file_path_hash' => $this->buildHash($path, $fileName),
                            'file_sha1' => sha1_file($info->getPathname()),
                            'original_size' => $info->getSize(),
                            'status' => Udder_Deflate_Model_Image::STATUS_PENDING,
                            'created_at' => Mage::getSingleton('core/date')->gmtDate(),
                            'updated_at' => Mage::getSingleton('core/date')->gmtDate()
                        );
                    }
                }

            }

        }
    }

    /**
     * Build a hash for the file path and name
     *
     * @param $path
     * @param $fileName
     * @return string
     */
    public static function buildHash($path, $fileName)
    {
        return md5(rtrim($path, '/') . DS . $fileName);
    }

    /**
     * Determine whether or not the image is within an excluded directory
     *
     * @param SplFileInfo $file
     * @return bool
     */
    public function isAllowedDirectory(SplFileInfo $file)
    {
        $allowed = true;
        foreach($this->getExcludedImageDirectories() as $directory) {
            if(strpos($file->getPath(), $directory) !== false) {
                $allowed = false;
            }
        }
        return $allowed;
    }

    /**
     * Return an array of excluded directories
     *
     * @return array
     */
    public function getExcludedImageDirectories()
    {
        $directories = array();

        // If we aren't compressing the resized images we need to exclude the directory
        if(!$this->compressCatalogResized()) {
            $directories[self::TYPE_CATALOG] = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath() . DS . 'cache';
        }

        // Ignore the thumb directory, as it's not utilised on the front-end
        if($this->compressCms()) {
            $directories[self::TYPE_CMS] = Mage::getBaseDir('media') . DS . 'wysiwyg' . DS . '.thumbs';
        }

        return $directories;
    }

    /**
     * Return the various directories we should scan
     *
     * @return array
     */
    public function getImageDirectories()
    {
        $directories = array();

        // Include the catalog directory
        if ($this->compressCatalog()) {
            $directories[self::TYPE_CATALOG] = Mage::getBaseDir('media') . DS . 'catalog';
        }

        // Include anything within the CMS path
        if ($this->compressCms()) {
            $directories[self::TYPE_CMS] = Mage::getBaseDir('media') . DS . 'wysiwyg';
        }

        // Include all files within the skin directory
        if ($this->compressSkin()) {
            if($templateString = $this->compressSpecificSkin()) {

                $templates = explode(',', $templateString);
                foreach($templates as $template) {

                    // Retrieve the package and theme from the string
                    list($package, $theme) = explode('[', rtrim($template, ']'));

                    // Load the design package
                    $skinBaseDir = Mage::getSingleton('core/design_package')->getSkinBaseDir(array(
                        '_area' => 'frontend',
                        '_package' => $package,
                        '_theme' => $theme
                    ));

                    if($skinBaseDir) {
                        $directories[self::TYPE_SKIN][] = $skinBaseDir;
                    }
                }

            } else {
                $directories[self::TYPE_SKIN] = Mage::getBaseDir('skin');
            }
        }

        return $directories;
    }

}