<?php

/**
 * Class Mage_Adminhtml_Block_System_Config_Form_Field_Heading
 *
 * @author Dave Macaulay <dave@udder.io>
 */
class Udder_Deflate_Block_Adminhtml_System_Config_Form_Field_Link
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');

        return sprintf('<tr class="deflate-link-button" id="row_%s"><td></td><td colspan="4" id="%s" style="padding: 10px 4px;">%s</td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $this->getLinkButton()
        );
    }

    /**
     * Return the JavaScript needed for the link button
     *
     * @return string
     */
    protected function getLinkButton()
    {
        return "<script type='text/javascript' id='deflate-link'>
            var deflateConfig = {
                apiKeyField: '#udder_deflate_general_api_key',
                apiSecretField: '#udder_deflate_general_api_secret'
            };
            (function(d, t) {
            var g = d.createElement(t),
                s = d.getElementsByTagName(t)[0];
                g.src = '//deflate.io/js/link.js';
                s.parentNode.insertBefore(g, s);
            }(document, 'script'));
        </script>
        <p class='note'>" . Mage::helper('udder_deflate')->__('Use the button above to easily link your Deflate account') . "</p>";
    }
}
