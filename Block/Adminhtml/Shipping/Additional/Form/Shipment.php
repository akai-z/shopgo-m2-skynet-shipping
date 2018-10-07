<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShopGo\SkyNet\Block\Adminhtml\Shipping\Additional\Form;

use Magento\Backend\Block\Template as BackendTemplate;

class Shipment extends BackendTemplate
{
    /**
     * @var \ShopGo\SkyNet\Model\Source\ShipmentTypes
     */
    protected $shipmentTypes;

    /**
     * @param BackendTemplate\Context $context
     * @param \ShopGo\SkyNet\Model\Source\ShipmentTypes $shipmentTypes
     * @param array $data
     */
    public function __construct(
        BackendTemplate\Context $context,
        \ShopGo\SkyNet\Model\Source\ShipmentTypes $shipmentTypes,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->shipmentTypes = $shipmentTypes;
        $this->setFormTemplate();
    }

    /**
     * Get SkyNet shipment types
     *
     * @return array
     */
    protected function getShipmentTypes()
    {
        return $this->shipmentTypes->toOptionArray();
    }

    /**
     * Set form template
     */
    protected function setFormTemplate()
    {
        $this->setTemplate('shipping/additional/form/shipment.phtml');
        $this->setData(
            'form_data',
            [
                'shipment_types' => $this->getShipmentTypes()
            ]
        );
    }
}
