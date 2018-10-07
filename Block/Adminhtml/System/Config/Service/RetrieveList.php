<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShopGo\SkyNet\Block\Adminhtml\System\Config\Service;

class RetrieveList extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve Service List Button Label
     *
     * @var string
     */
    protected $_buttonLabel = 'Retrieve Service List';

    /**
     * Set template to itself
     *
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/service/retrieve_list.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData([
            'button_label' => __($this->_buttonLabel),
            'html_id' => $element->getHtmlId(),
            'ajax_url' => $this->_urlBuilder->getUrl('skynet/service/retrievelist'),
        ]);

        return $this->_toHtml();
    }
}
