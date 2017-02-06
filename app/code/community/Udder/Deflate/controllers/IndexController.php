<?php

/**
 * Class Udder_Deflate_IndexController
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Process the callback action
     */
    public function callbackAction()
    {
        // Handle the callback in the compress model
        Mage::getModel('udder_deflate/compress')->handleCallback();

        return $this;
    }
}
