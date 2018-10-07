<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShopGo\SkyNet\Block\Adminhtml\Shipping\Additional;

use Magento\Backend\Block\Template as BackendTemplate;

class Form extends BackendTemplate
{
    /**
     * @param BackendTemplate\Context $context
     * @param array $data
     */
    public function __construct(
        BackendTemplate\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function getFormsBlocks()
    {
        $formsPath = 'ShopGo\SkyNet\Block\Adminhtml\Shipping\Additional\Form';

        $formsBlocks = [
            $this->getLayout()->createBlock($formsPath . '\Shipment')
        ];

        return $formsBlocks;
    }

    /**
     * Get SkyNet additional shipping forms HTML
     *
     * @return string
     */
    public function getFormsHtml()
    {
        $forms = '';

        foreach ($this->getFormsBlocks() as $formBlock) {
            $forms .= $formBlock->toHtml();
        }

        return $forms;
    }
}
