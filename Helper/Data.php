<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Helper;

use Monolog\Logger as MonologLogger;
use ShopGo\ShippingCore\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ShopGo\DimensionalWeightAttributes\Helper\Data as DimensionalWeightAttributesHelper;

class Data extends AbstractHelper
{
    /**
     * XML path carriers SkyNet active
     */
    const XPATH_CARRIERS_SKYNET_ACTIVE = 'carriers/skynet/active';

    /**
     * XML path SkyNet settings consignor name
     */
    const XPATH_SKYNET_SETTINGS_CONSIGNOR_NAME = 'shipping/skynet_settings/consignor_name';

    /**
     * SkyNet account fields XML paths
     */
    const XPATH_CARRIERS_SKYNET_USERNAME = 'carriers/skynet/username';
    const XPATH_CARRIERS_SKYNET_PASSWORD = 'carriers/skynet/password';
    const XPATH_CARRIERS_SKYNET_STATION_CODE = 'carriers/skynet/station_code';
    const XPATH_CARRIERS_SKYNET_CONSIGNOR_ACCOUNT = 'carriers/skynet/consignor_account';

    /**
     * XML path carriers SkyNet debug
     */
    const XPATH_CARRIERS_SKYNET_DEBUG = 'carriers/skynet/debug';

    /**
     * SkyNet API WSDL URL
     */
    const SKYNET_API_WSDL_URL = 'http://api.skynetwwe.info/Service1.svc?wsdl';

    /**
     * SkyNet API get tracking WSDL URL
     */
    const SKYNET_API_TRACKING_WSDL_URL = 'http://iskynettrack.skynetwwe.info/TrackingService/TrackingService_v1.asmx?wsdl';

    /**
     * SkyNet API methods names
     */
    const SKYNET_API_METHOD_RATE_REQUEST       = 'rate_request';
    const SKYNET_API_METHOD_CREATE_SHIPMENT    = 'create_shipment';
    const SKYNET_API_METHOD_PICKUP_REQUEST     = 'pickup_request';
    const SKYNET_API_METHOD_GET_TRACKING       = 'get_tracking';
    const SKYNET_API_METHOD_GET_SHIPPING_LABEL = 'get_shipping_label';
    const SKYNET_API_METHOD_VERIFY_ACCOUNT     = 'verify_account';
    const SKYNET_API_METHOD_GET_SERVICE_LIST   = 'get_service_list';

    /**
     * SkyNet account info fields names
     */
    const SKYNET_ACCOUNT_INFO_USERNAME          = 'UserName';
    const SKYNET_ACCOUNT_INFO_PASSWORD          = 'Password';
    const SKYNET_ACCOUNT_INFO_STATION_CODE      = 'StationCode';
    const SKYNET_ACCOUNT_INFO_CONSIGNOR_ACCOUNT = 'ConsignorAccount';

    /**
     * SOAP header namespace
     */
    const SOAP_HEADER_NAMESPACE = 'http://www.w3.org/2005/08/addressing';

    /**
     * Log module directory path
     */
    const LOG_MODULE_PATH = 'shipping/skynet/';

    /**
     * Log types
     */
    const LOG_TYPE_DEBUG = 'debug';
    const LOG_TYPE_DEBUG_RATE_CALCULATION = 'rate-calculation';

    /**
     * Account validation invalid response code
     */
    const INVALID_ACCOUNT_RESPONSE_CODE = 'ERR002';

    /**
     * XML security
     *
     * @var \Magento\Framework\Xml\Security
     */
    protected $xmlSecurity;

    /**
     * Log types data
     *
     * @var array
     */
    protected $logTypes = [
        self::LOG_TYPE_DEBUG => [
            'level' => MonologLogger::DEBUG
        ],
        self::LOG_TYPE_DEBUG_RATE_CALCULATION => [
            'level' => MonologLogger::DEBUG,
            'file'  => 'rate_calculation.log'
        ]
    ];

    /**
     * SkyNet API methods SOAP header namespace URIs
     *
     * @var array
     */
    protected $soapHeaderNamespaceUris = [
        self::SKYNET_API_METHOD_RATE_REQUEST       => 'http://tempuri.org/IService1/RequestRatesByObject',
        self::SKYNET_API_METHOD_CREATE_SHIPMENT    => 'http://tempuri.org/IService1/CreateShipmentByObject',
        self::SKYNET_API_METHOD_PICKUP_REQUEST     => 'http://tempuri.org/IService1/PickupRequestByObject',
        //self::SKYNET_API_METHOD_GET_TRACKING       => 'http://tempuri.org/GetSkyBillTrack',
        self::SKYNET_API_METHOD_GET_SHIPPING_LABEL => 'http://tempuri.org/IService1/GetLabelPDF',
        self::SKYNET_API_METHOD_VERIFY_ACCOUNT     => 'http://tempuri.org/IService1/VerifyUserAccount',
        self::SKYNET_API_METHOD_GET_SERVICE_LIST   => 'http://tempuri.org/IService1/GetServiceList'
    ];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \ShopGo\Core\Helper\Utility $utility
     * @param DimensionalWeightAttributesHelper $dimensionalWeightAttributesHelper
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \ShopGo\Core\Helper\Utility $utility,
        DimensionalWeightAttributesHelper $dimensionalWeightAttributesHelper,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Xml\Security $xmlSecurity
    ) {
        $this->xmlSecurity = $xmlSecurity;
        parent::__construct($context, $utility, $dimensionalWeightAttributesHelper, $currency);
    }

    /**
     * Get shipping origin settings
     *
     * @param string $scope
     * @param null|string $scopeCode
     * @return array
     */
    public function getShippingOriginSettings(
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $settings = parent::getShippingOriginSettings($scope, $scopeCode);
        $additionalSettings = [
            'consignor_name' => ucwords(strtolower(trim(
                $this->getConfig()->getValue(self::XPATH_SKYNET_SETTINGS_CONSIGNOR_NAME, $scope, $scopeCode)
            )))
        ];

        return array_merge($settings, $additionalSettings);
    }

    /**
     * Get SkyNet account info
     *
     * @return array
     */
    public function getSkynetAccountInfo()
    {
        $accountInfo = [
            self::SKYNET_ACCOUNT_INFO_USERNAME => trim($this->getConfig()->getValue(
                self::XPATH_CARRIERS_SKYNET_USERNAME
            )),
            self::SKYNET_ACCOUNT_INFO_PASSWORD => trim($this->getConfig()->getValue(
                self::XPATH_CARRIERS_SKYNET_PASSWORD
            )),
            self::SKYNET_ACCOUNT_INFO_STATION_CODE => strtoupper(trim($this->getConfig()->getValue(
                self::XPATH_CARRIERS_SKYNET_STATION_CODE
            ))),
            self::SKYNET_ACCOUNT_INFO_CONSIGNOR_ACCOUNT => trim($this->getConfig()->getValue(
                self::XPATH_CARRIERS_SKYNET_CONSIGNOR_ACCOUNT
            ))
        ];

        return $accountInfo;
    }

    /**
     * Get SOAP header
     *
     * @param string $message
     * @param string $type
     * @return void
     */
    public function log($message, $type = '')
    {
        $level = '';
        $file  = '';

        if (isset($this->logTypes[$type])) {
            $level = $this->logTypes[$type]['level'];
            $file  = isset($this->logTypes[$type]['file']) ? $this->logTypes[$type]['file'] : '';
        }

        $this->_log($level, $message, [], $file);
    }

    /**
     * Get SOAP header
     *
     * @param string $message
     * @param string $type
     * @return void
     */
    public function debug($message, $type = self::LOG_TYPE_DEBUG)
    {
        if ($this->getConfig()->getValue(self::XPATH_CARRIERS_SKYNET_DEBUG)) {
            $this->log($message, $type);
        }
    }

    /**
     * Get SOAP header
     *
     * @param string $method
     * @return \SoapHeader|null
     */
    protected function getSoapHeader($method)
    {
        $header = null;

        if (isset($this->soapHeaderNamespaceUris[$method])) {
            $header = new \SoapHeader(
                self::SOAP_HEADER_NAMESPACE,
                'Action',
                $this->soapHeaderNamespaceUris[$method],
                true
            );
        }

        return $header;
    }

    /**
     * Call SOAP client action
     *
     * @param string $method
     * @param \SoapClient $soapClient
     * @param array $callParams
     * @return mixed
     */
    protected function callSoapClientAction($method, $soapClient, $callParams)
    {
        $result = null;

        switch ($method) {
            case self::SKYNET_API_METHOD_RATE_REQUEST:
                $result = $soapClient->RequestRatesByObject($callParams)->RequestRatesByObjectResult;
                break;
            case self::SKYNET_API_METHOD_CREATE_SHIPMENT:
                $result = $soapClient->CreateShipmentByObject($callParams)->CreateShipmentByObjectResult;
                break;
            case self::SKYNET_API_METHOD_PICKUP_REQUEST:
                $result = $soapClient->PickupRequestByObject($callParams)->PickupRequestByObjectResult;
                break;
            case self::SKYNET_API_METHOD_GET_TRACKING:
                $result = $soapClient->GetSkyBillTrack($callParams)->GetSkyBillTrackResult;
                break;
            case self::SKYNET_API_METHOD_GET_SHIPPING_LABEL:
                $result = $soapClient->GetLabelPDF($callParams)->GetLabelPDFResult;
                break;
            case self::SKYNET_API_METHOD_VERIFY_ACCOUNT:
                $result = $soapClient->VerifyUserAccount($callParams)->VerifyUserAccountResult;
                break;
            case self::SKYNET_API_METHOD_GET_SERVICE_LIST:
                $result = $soapClient->GetServiceList($callParams)->GetServiceListResult;
                break;
        }

        return $result;
    }

    /**
     * Get WSDL
     *
     * @param string $method
     * @return string
     */
    protected function getWsdl($method)
    {
        $wsdl = self::SKYNET_API_WSDL_URL;

        switch ($method) {
            case self::SKYNET_API_METHOD_GET_TRACKING:
                $wsdl = self::SKYNET_API_TRACKING_WSDL_URL;
                break;
        }

        return $wsdl;
    }

    /**
     * Call SkyNet API
     *
     * @param string $method
     * @param array $callParams
     * @param array $scOptions
     * @throws \SoapFault
     * @return string
     */
    public function callSkynetApi($method, $callParams, $scOptions = [])
    {
        $result = '';
        $wsdl = $this->getWsdl($method);

        if (!isset($scOptions['soap_version'])) {
            $scOptions['soap_version'] = SOAP_1_2;
        }
        if (!isset($scOptions['trace'])) {
            $scOptions['trace'] = 1;
        }
        if (!isset($scOptions['exceptions'])) {
            $scOptions['exceptions'] = 0;
        }

        try {
            $soapClient = new \SoapClient($wsdl, $scOptions);

            if ($actionHeader = $this->getSoapHeader($method)) {
                $soapClient->__setSoapHeaders($actionHeader);
            }

            $result = $this->callSoapClientAction($method, $soapClient, $callParams);

            if ($result instanceof \SoapFault) {
                $result = '[soapfault]';
            }
        } catch (\SoapFault $sf) {
            $result = '[soapfault]';
        }

        return $result;
    }

    public function verifyAccount($data)
    {
        $result = -1;

        $soapResult = $this->callSkynetApi(self::SKYNET_API_METHOD_VERIFY_ACCOUNT, $data);

        if ($soapResult != '[soapfault]') {
            $response = $this->parseXmlResponse($soapResult, self::SKYNET_API_METHOD_VERIFY_ACCOUNT);

            if (!isset($response['status'])) {
                return $result;
            }

            switch (true) {
                case false !== strpos($response['status'], 'SUC'):
                    $result = 1;
                    break;
                case $response['status'] = self::INVALID_ACCOUNT_RESPONSE_CODE:
                    $result = 0;
                    break;
            }
        }

        return $result;
    }

    /**
     * Parse XML string and return XML document object or false
     *
     * @param string $xmlContent
     * @param string $customSimplexml
     * @return \SimpleXMLElement|bool
     * @throws LocalizedException
     */
    public function parseXml($xmlContent, $customSimplexml = 'SimpleXMLElement')
    {
        if (!$this->xmlSecurity->scan($xmlContent)) {
            throw new LocalizedException(__('Security validation of XML document has been failed.'));
        }

        $xmlElement = simplexml_load_string($xmlContent, $customSimplexml);

        return $xmlElement;
    }

    /**
     * Parse SOAP XML response
     *
     * @param string $xml
     * @param string $method
     * @return array
     */
    public function parseXmlResponse($xml, $method)
    {
        $result = [];
        $defaultStatusDescription = __('Unknown Error');
        $xmlObj = $this->parseXml($xml);

        if (!is_object($xmlObj)) {
            return $result;
        }

        $result['status'] = isset($xmlObj->StatusCode) ? (string) $xmlObj->StatusCode : 'ERR';
        $result['status_description'] = isset($xmlObj->StatusDescription)
            ? (string) $xmlObj->StatusDescription : $defaultStatusDescription;
        $result['request_id'] = isset($xmlObj->RequestID) ? (string) $xmlObj->RequestID : '';

        if (false === strpos($result['status'], 'ERR')) {
            switch ($method) {
                case self::SKYNET_API_METHOD_RATE_REQUEST:
                    $result['currency'] = isset($xmlObj->TARIF->RATECURRENCY)
                        ? (string) $xmlObj->TARIF->RATECURRENCY : '';
                    $result['price'] = isset($xmlObj->TARIF->FINALCHARGES) ? (string) $xmlObj->TARIF->FINALCHARGES : '';
                    $result['vol_weight'] = isset($xmlObj->TARIF->VOLWEIGHTOFSHIPMENT)
                        ? (string) $xmlObj->TARIF->VOLWEIGHTOFSHIPMENT : '';
                    $result['charged_weight'] = isset($xmlObj->TARIF->CHARGEDWEIGHT)
                        ? (string) $xmlObj->TARIF->CHARGEDWEIGHT : '';
                    break;
                case self::SKYNET_API_METHOD_CREATE_SHIPMENT:
                    $result['shipment_number'] = isset($xmlObj->ShipmentNumber) ? (string) $xmlObj->ShipmentNumber : '';
                    break;
                case self::SKYNET_API_METHOD_GET_SHIPPING_LABEL:
                    $result['shipment_label'] = isset($xmlObj->ShipmentLabel) ? (string) $xmlObj->ShipmentLabel : '';
                    break;
                case self::SKYNET_API_METHOD_VERIFY_ACCOUNT:
                    // Do nothing
                    break;
                case self::SKYNET_API_METHOD_GET_SERVICE_LIST:
                    $result['service_list'] = isset($xmlObj->ServiceList->string)
                        ? (array) $xmlObj->ServiceList->string : [];
                    break;
            }
        }

        return $result;
    }
}
