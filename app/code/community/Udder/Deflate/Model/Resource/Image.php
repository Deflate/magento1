<?php

/**
 * Class Udder_Deflate_Model_Resource_Image
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Resource_Image extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('udder_deflate/image', 'image_id');
    }

}