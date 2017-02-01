<?php

/**
 * Class Udder_Deflate_Model_Resource_Image_Collection
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_Resource_Image_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('udder_deflate/image');
    }

}