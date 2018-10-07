<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Source model for SkyNet shipment types
 */
class ShipmentTypes implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('--Please Select--'),
                'value' => ''
            ],
            [
                'label' => __('NON DOCS'),
                'value' => 'NON DOCS'
            ],
            [
                'label' => __('DOCS'),
                'value' => 'DOCS'
            ],
            [
                'label' => __('DOCS & NON'),
                'value' => 'DOCS & NON'
            ]
        ];

        return $options;
    }
}
