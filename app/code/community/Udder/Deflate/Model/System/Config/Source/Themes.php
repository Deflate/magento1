<?php

/**
 * Class Udder_Deflate_Model_System_Config_Source_Themes
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Model_System_Config_Source_Themes
{
    /**
     * Return the themes within a package
     *
     * @return array
     */
    public function getThemes()
    {
        return Mage::getSingleton('core/design_package')->getThemeList();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value' => '', 'label' => Mage::helper('udder_deflate')->__('All Packages & Themes'))
        );
        foreach($this->getThemes() as $package => $themes) {
            $options[$package] = array('label' => $package);
            foreach($themes as $theme) {
                $options[$package]['value'][] = array('value' => $package . '[' . $theme . ']', 'label' => $theme);
            }
        }
        return $options;
    }

}
