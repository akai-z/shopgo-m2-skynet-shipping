<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * XML path carriers SkyNet
     */
    const CONFIG_XPATH_CARRIERS_SKYNET = 'carriers/skynet/';

    /**
     * XML path carriers SkyNet
     */
    const CONFIG_XPATH_SHIPPING_ORIGIN = 'shipping/origin/';

    /**
     * XML path carriers SkyNet
     */
    const CONFIG_XPATH_SKYNET_SETTINGS = 'shipping/skynet_settings/';

    /**
     * SkyNet API get rate response XML paths
     */
    const RATE_RESPONSE_XPATH_CURRENCY       = 'TARIF/RATECURRENCY';
    const RATE_RESPONSE_XPATH_FINAL_CHARGES  = 'TARIF/FINALCHARGES';
    const RATE_RESPONSE_XPATH_VOL_WEIGHT     = 'TARIF/VOLWEIGHTOFSHIPMENT';
    const RATE_RESPONSE_XPATH_CHARGED_WEIGHT = 'TARIF/CHARGEDWEIGHT';

    /**
     * SkyNet API create shipment response XML path
     */
    const SHIPMENT_RESPONSE_XPATH_NUMBER = 'ShipmentNumber';

    /**
     * SkyNet API get service list response XML path
     */
    const SERVICE_LIST_RESPONSE_XPATH = 'ServiceList';

    /**
     * SkyNet API WSDL URL
     */
    const SKYNET_API_WSDL_URL = 'http://api.skynetwwe.info/Service1.svc?wsdl';

    /**
     * SkyNet API get tracking WSDL URL
     */
    const SKYNET_API_TRACKING_WSDL_URL = 'https://iskynettrack.skynetwwe.info/TrackingService/TrackingService_v1.asmx?wsdl';

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
     * SOAP header namespace
     */
    const SOAP_HEADER_NAMESPACE = 'http://www.w3.org/2005/08/addressing';

    /**
     * SkyNet API methods SOAP header namespace URIs
     *
     * @var array
     */
    protected $soapHeaderNamespaceUris = [
        self::SKYNET_API_METHOD_RATE_REQUEST       => 'http://tempuri.org/IService1/RequestRatesByObject',
        self::SKYNET_API_METHOD_CREATE_SHIPMENT    => 'http://tempuri.org/IService1/CreateShipmentByObject',
        self::SKYNET_API_METHOD_PICKUP_REQUEST     => 'http://tempuri.org/IService1/PickupRequestByObject',
        self::SKYNET_API_METHOD_GET_TRACKING       => 'http://tempuri.org/GetSkyBillTrack',
        self::SKYNET_API_METHOD_GET_SHIPPING_LABEL => 'http://tempuri.org/IService1/GetLabelPDF',
        self::SKYNET_API_METHOD_VERIFY_ACCOUNT     => 'http://tempuri.org/IService1/VerifyUserAccount',
        self::SKYNET_API_METHOD_GET_SERVICE_LIST   => 'http://tempuri.org/IService1/GetServiceList'
    ];

    /**
     * Get SOAP header
     *
     * @param string $method
     * @return \SoapHeader|null
     */
    protected function getSoapHeader($method)
    {
        $header = null;

        if (isset($soapHeaderNamespaceUris[$method])) {
            $header = new \SoapHeader(
                self::SOAP_HEADER_NAMESPACE,
                'Action',
                $soapHeaderNamespaceUris[$method],
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
        $xmlObj = new \Varien_Simplexml_Config($xml);

        if (empty($xmlObj)) {
            return $result;
        }

        $result['status'] = $xmlObj->getNode('StatusCode')->asArray();
        $result['status_description'] = $xmlObj->getNode('StatusDescription')->asArray();
        $result['request_id'] = $xmlObj->getNode('RequestID')->asArray();

        if (false === strpos($result['status'], 'ERR')) {
            switch ($method) {
                case self::SKYNET_API_METHOD_RATE_REQUEST:
                    $result['currency'] = $xmlObj->getNode(self::RATE_RESPONSE_XPATH_CURRENCY)->asArray();
                    $result['price'] = $xmlObj->getNode(self::RATE_RESPONSE_XPATH_FINAL_CHARGES)->asArray();
                    $result['vol_weight'] = $xmlObj->getNode(self::RATE_RESPONSE_XPATH_VOL_WEIGHT)->asArray();
                    $result['charged_weight'] = $xmlObj->getNode(self::RATE_RESPONSE_XPATH_CHARGED_WEIGHT)->asArray();
                    break;
                case self::SKYNET_API_METHOD_CREATE_SHIPMENT:
                    $result['shipment_number'] = $xmlObj->getNode(self::SHIPMENT_RESPONSE_XPATH_NUMBER)->asArray();
                    break;
                case self::SKYNET_API_METHOD_VERIFY_ACCOUNT:
                    $result['verify_account'] = $xmlObj->getNode()->asArray();
                    break;
                case self::SKYNET_API_METHOD_GET_SERVICE_LIST:
                    $result['service_list'] = (array) $xmlObj->getNode(self::SERVICE_LIST_RESPONSE_XPATH);
                    $result['service_list'] = $result['service_list']['string'];
                    break;
            }
        }

        return $result;
    }
}
