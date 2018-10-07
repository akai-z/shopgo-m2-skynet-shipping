<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Model;

use ShopGo\SkyNet\Helper\Data as Helper;

/**
 * Service model
 */
class Service extends \Magento\Framework\Model\AbstractModel
{
    /**
     * SkyNet helper data
     *
     * @var \ShopGo\SkyNet\Helper\Data
     */
    protected $helper;

    /**
     * SkyNet service resource model factory
     *
     * @var \ShopGo\SkyNet\Model\ResourceModel\ServiceFactory
     */
    protected $resourceServiceFactory;

    /**
     * SkyNet service source model
     *
     * @var \ShopGo\SkyNet\Model\System\Config\Source\Service
     */
    protected $serviceSource;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     * @param \ShopGo\SkyNet\Model\ResourceModel\ServiceFactory $resourceServiceFactory
     * @param \ShopGo\SkyNet\Model\System\Config\Source\Service $serviceSource
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        \ShopGo\SkyNet\Model\ResourceModel\ServiceFactory $resourceServiceFactory,
        \ShopGo\SkyNet\Model\System\Config\Source\Service $serviceSource,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context, $registry);
        $this->helper = $helper;
        $this->resourceServiceFactory = $resourceServiceFactory;
        $this->serviceSource = $serviceSource;
        $this->objectManager = $objectManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ShopGo\SkyNet\Model\ResourceModel\Service');
    }

    private function getModel()
    {
        return $this->_objectManager->create('ShopGo\SkyNet\Model\Service');
    }

    public function getServiceList($data)
    {
        $servicesList = [];

        $soapResult = $this->helper->callSkynetApi(
            Helper::SKYNET_API_METHOD_GET_SERVICE_LIST,
            $data
        );

        if ($soapResult != '[soapfault]') {
            $response = $this->helper->parseXmlResponse($soapResult, Helper::SKYNET_API_METHOD_GET_SERVICE_LIST);

            if (false === strpos($response['status'], 'ERR')) {
                $this->saveServiceData($response['service_list']);
                $servicesList = $this->serviceSource->toOptionArray();
            }
        }

        return $servicesList;
    }

    protected function saveServiceData($data)
    {
        if (!$data) {
            return;
        }

        $resourceServiceFactory = $this->resourceServiceFactory->create();
        $resourceServiceFactory->getConnection()->truncateTable('skynet_service');

        try {
            foreach ($data as $i) {
                $service = clone $this;
                $service->setName($i);
                $service->save();
            }
        } catch (\Exception $e) {}
    }

    public function getServiceName($serviceId)
    {
        $serviceName = '';

        foreach (clone $this->getCollection() as $service) {
            if ($serviceId == $service->getServiceId()) {
                $serviceName = $service->getName();
                break;
            }
        }

        return $serviceName;
    }
}
