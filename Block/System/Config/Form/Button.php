<?php

namespace Ocode\S3Digital\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    protected $_template = 'Ocode_S3Digital::system/config/button.phtml';

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('s3amazon/checkbucket/index');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 's3amazon_button',
                'label' => __('Check Bucket Availabilty'),
                'onclick'   => 'javascript:validateData(); return false;'
            ]
        );
        return $button->toHtml();
    }
}