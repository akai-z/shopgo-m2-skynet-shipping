<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Controller\Adminhtml\Account;

use ShopGo\SkyNet\Helper\Data as Helper;

class Verify extends \ShopGo\SkyNet\Controller\Adminhtml\ShippingMethods
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
            'status' => -1,
            'description' => __(
                'An unknown error has occurred during SkyNet account verification process!'
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

        $accountVerificationResult = $this->helper->verifyAccount($data);

        if ($accountVerificationResult != -1) {
            $accountVerificationMessage = $accountVerificationResult
                ? __('SkyNet account is valid.')
                : __('SkyNet account is invalid. If the issue persists, please report it to the module author.');

            $result = [
                'status' => $accountVerificationResult,
                'description' => $accountVerificationMessage
            ];
        }

        $response->setBody(json_encode($result));
    }
}
