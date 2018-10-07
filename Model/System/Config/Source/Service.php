<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Source model for SkyNet services
 */
class Service implements ArrayInterface
{
    /**
     * SkyNet service factory model
     *
     * @var \ShopGo\SkyNet\Model\ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @param \ShopGo\SkyNet\Model\ServiceFactory $serviceFactory
     */
    public function __construct(
        \ShopGo\SkyNet\Model\ServiceFactory $serviceFactory
    ) {
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $serviceCollection = $this->serviceFactory->create()->getCollection();

        foreach ($serviceCollection as $service) {
            $options[] = [
                'value' => $service->getServiceId(),
                'label' => $service->getName()
            ];
        }

        if (!$options) {
            $options[] = [
                'value' => '',
                'label' => __('- No Services Available -')
            ];
        } else {
            array_unshift(
                $options,
                [
                    'value' => '',
                    'label' => __('--Please Select--')
                ]
            );
        }

        return $options;
    }
}
