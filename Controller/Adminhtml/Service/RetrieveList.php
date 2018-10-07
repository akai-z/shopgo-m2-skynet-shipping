<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Controller\Adminhtml\Service;

use ShopGo\SkyNet\Helper\Data as Helper;

class RetrieveList extends \ShopGo\SkyNet\Controller\Adminhtml\ShippingMethods
{
    /**
     * SkyNet service model factory
     *
     * @var \ShopGo\SkyNet\Model\ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \ShopGo\SkyNet\Model\ServiceFactory $serviceFactory
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \ShopGo\SkyNet\Model\ServiceFactory $serviceFactory,
        Helper $helper
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Get SkyNet service list action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $response = $this->getResponse()->setHeader(
            'content-type',
            'application/json; charset=utf-8'
        );

        $result = [
            'status' => 0,
            'description' => __(
                'An unknown error has occurred during SkyNet service list retrieval process!'
                . ' Please report this issue to the module author.'
            )
        ];

        if (!isset($params['is_modified_username']) || !isset($params['is_modified_password'])) {
            $result['description'] = $this->__('Bad request');
            $response->setBody(json_encode($result));

            return;
        }

        $requiredParams = array_flip(['username', 'password', 'station_code', 'consignor_account']);
        foreach ($params as $paramName => $param) {
            if (isset($requiredParams[$paramName]) && (!isset($param) || !$param)) {
                $result['description'] = __(
                    'Username, Password, Station Code and Consignee Number fields are required.'
                );

                $response->setBody(json_encode($result));
                return;
            }
        }

        $skynetAccountInfo = $this->helper->getSkynetAccountInfo();

        if (!$params['is_modified_username'] && $params['username'] == Helper::FIELD_MASK) {
            $params['username'] = $skynetAccountInfo['UserName'];
        }
        if (!$params['is_modified_password'] && $params['password'] == Helper::FIELD_MASK) {
            $params['password'] = $skynetAccountInfo['Password'];
        }

        $data = [
            'UserName' => $params['username'],
            'Password' => $params['password'],
            'StationCode' => $params['station_code'],
            'ConsignorAccount' => $params['consignor_account']
        ];

        $servicesList = $this->serviceFactory->create()->getServiceList($data);

        if ($servicesList) {
            $result = [
                'status' => 1,
                'description' => __('SkyNet service list has been retrieved successfully!'),
                'data' => $servicesList
            ];
        } else {
            $result['description'] = __(
                'Could not retrieve SkyNet service list.'
                . ' if the issue persists, please report this issue to the module author.'
            );
        }

        $response->setBody(json_encode($result));
    }
}
