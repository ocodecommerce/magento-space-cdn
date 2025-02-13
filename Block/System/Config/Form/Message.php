<?php

namespace Ocode\S3Digital\Block\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class Message extends Field
{
    /**
     * Render the field HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $value = $element->getEscapedValue() ?: 'No message'; // Default message if no value

        // Check the value and assign a color based on the message content
        $color = (stripos($value, 'not available') !== false || stripos($value, 'not accessible') !== false) 
            ? 'red' 
            : 'green';

        $html = '<div style="color: ' . $color . '; font-weight: bold;">' . $value . '</div>';
        return $html;
    }
}
