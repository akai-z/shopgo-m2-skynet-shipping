<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace ShopGo\SkyNet\Model;

use Magento\Framework\Module\Dir;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use ShopGo\DimensionalWeightAttributes\Model\DimensionalWeightAttributes as DWA;
use ShopGo\SkyNet\Helper\Data as Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier
     */
    const CODE = 'skynet';

    /**
     * Shipping rate request method standard
     */
    const RATE_REQUEST_STANDARD = 'standard';

    /**
     * Bill date format
     */
    const BILL_DATE_FORMAT = 'c';

    /**
     * Shipping label report URL
     */
    const SHIPPING_LABEL_REPORT_URL = 'https://www.skynetwwe.info/Reports/AWBPrint.aspx?Skybill=';

    /**
     * Code of the carrier
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory
     */
    protected $quoteAddressCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $origSettings;

    /**
     * @var array
     */
    protected $accountInfo;

    /**
     * @var string
     */
    protected $destCountry;

    /**
     * @var bool
     */
    protected $isShipmentRequest = false;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Helper $helper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\CollectionFactory $quoteAddressCollectionFactory,
        DateTime $dateTime,
        Helper $helper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteAddressCollectionFactory = $quoteAddressCollectionFactory;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );

        $this->accountInfo  = $helper->getSkynetAccountInfo();
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->canCollectRates()) {
            return $this->getErrorMessage();
        }
        if ($this->registry->registry('isDoSkynetShipmentRequest')) {
            $this->registry->register('skynetShipmentRequest', $request);
            return;
        }

        $this->setRequest($request);
        $this->getQuotes();
        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    /**
     * Prepare and set request to this instance
     *
     * @param RateRequest $request
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setRequest(RateRequest $request)
    {
        $this->_request = $request;
        $this->setStore($request->getStoreId());

        $requestObject = new \Magento\Framework\DataObject();

        $consignorAccount = $this->accountInfo[Helper::SKYNET_ACCOUNT_INFO_CONSIGNOR_ACCOUNT];
        $stationCode = $this->accountInfo[Helper::SKYNET_ACCOUNT_INFO_STATION_CODE];

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->helper->getConfig()->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $origCountry = $this->_countryFactory->create()->load($origCountry)->getData('iso2_code');

        if (is_numeric($request->getOrigState())) {
            $origState = $this->_regionFactory->create()->load($request->getOrigState())->getCode();
        } else {
            $origState = $request->getOrigState();
        }

        if ($request->getOrigCity()) {
            $origCity = $request->getOrigCountry();
        } else {
            $origCity = $this->helper->getConfig()->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }

        if ($request->getOrigPostcode()) {
            $origPostal = $request->getOrigPostcode();
        } else {
            $origPostal = $this->helper->getConfig()->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }

        $destCountry = $request->getDestCountryId();
        $destCountry = $this->_countryFactory->create()->load($destCountry)->getData('iso2_code');
        $this->destCountry = $destCountry;

        $service = 'Courier';

        $requestObject->setConsignorAccount($consignorAccount)
            ->setStationCode($stationCode)
            ->setOrigCountry($origCountry)
            ->setOrigState($origState)
            ->setOrigCity($origCity)
            ->setOrigPostal($origPostal)
            ->setDestCountry($destCountry)
            ->setDestState($request->getDestRegionCode())
            ->setDestCity($request->getDestCity())
            ->setDestPostal($request->getDestPostal())
            ->setPackageQty($request->getPackageQty())
            ->setService($service)
            ->setIsReturn($request->getIsReturn())
            ->setBaseSubtotalInclTax($request->getBaseSubtotalInclTax());

        $this->setRawRequest($requestObject);

        return $this;
    }

    /**
     * Get result of request
     *
     * @return Result|null
     */
    public function getResult()
    {
        return $this->_result;
    }

    protected function getPackageItemsData()
    {
        $dwa    = [];
        $pieces = ['Piece' => []];

        $dwaData = $this->helper->getDimensionalWeightAttributes();

        foreach ($this->_request->getAllItems() as $item) {
            $product =  $this->productFactory->create()->load($item->getProductId());

            foreach ($dwaData as $attributeName => $attribute) {
                $productDwa = $product->getData($dwaData[$attributeName]);
                // Default dimensional weight attribute is 1.
                $dwa[$attributeName] = !empty($productDwa) ? $productDwa : 1;
            }

            $weight = $item->getWeight();

            for ($i = 0; $i < $item->getQty(); $i++) {
                $pieces['Piece'][] = [
                    'HEIGHTINCENTIMETERS' => $dwa[DWA::ATTRIBUTE_HEIGHT_CODE],
                    'LENGTHINCENTIMETERS' => $dwa[DWA::ATTRIBUTE_LENGTH_CODE],
                    'WIDHTINCENTIMETERS'  => $dwa[DWA::ATTRIBUTE_WIDTH_CODE],
                    'WEIGHTINKGS'         => $weight
                ];
            }
        }

        return $pieces;
    }

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @return array
     */
    protected function formRateRequest()
    {
        $request = $this->_rawRequest;

        $rateRequest = $this->accountInfo;
        $rateRequest['TR'] = [
            'CONSIGNORACCOUNT'        => $request->getConsignorAccount(),
            'DESTINATIONCOUNTRYCODE'  => $request->getDestCountry(),
            'DESTINATIONDELIVERYAREA' => $request->getDestPostal(),
            'NOOFPIECES'              => $request->getPackageQty(),
            'ORIGINCOUNTRYCODE'       => $request->getOrigCountry(),
            'PIECESOFSHIPMENT'        => $this->getPackageItemsData(),
            'SERVICE'                 => $request->getService(),
            'STATIONCODE'             => $request->getStationCode()
        ];

        return $rateRequest;
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @return mixed
     */
    protected function doRateRequest()
    {
        $rateRequest = $this->formRateRequest();
        $requestString = serialize($rateRequest);
        $response = $this->_getCachedQuotes($requestString);

        $debugData = ['request' => $rateRequest];

        if ($response === null) {
            try {
                $response = $this->helper->callSkynetApi(Helper::SKYNET_API_METHOD_RATE_REQUEST, $rateRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            }
        } else {
            $debugData['result'] = $response;
        }

        return $response;
    }

    /**
     * Do remote request for and handle errors
     *
     * @return Result
     */
    protected function getQuotes()
    {
        $this->_result = $this->_rateFactory->create();

        $response = $this->doRateRequest();
        $preparedResponse = $this->prepareRateResponse($response);

        if (!$preparedResponse->getError() || $this->getConfigData('showmethod') && $preparedResponse->getError()) {
            $this->_result->append($preparedResponse);
        }

        return $this->_result;
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareRateResponse($response)
    {
        $cost  = 0;
        $price = 0;
        $errorTitle = $this->getConfigData('specificerrmsg');

        if (!$errorTitle) {
            $errorTitle = __('For some reason we cannot retrieve tracking info right now.');
        }

        if ($response != '[soapfault]') {
            $response = $this->helper->parseXmlResponse($response, Helper::SKYNET_API_METHOD_RATE_REQUEST);
            if (false === strpos($response['status'], 'ERR')) {
                $price = $this->getMethodPrice($response['price']);

                $convertedRate = $this->helper->convertCurrency(
                    $price,
                    $this->_request->getPackageCurrency()->getCurrencyCode(),
                    $response['currency']
                );

                $cost  = $response['price'];
                $price = $convertedRate;
            } else {
                if ($this->getConfigData('show_skynet_error')) {
                    $errorTitle = '[SKYNET ERROR]: ' . $response['status_description'];
                }
            }
        }

        $result = $this->_rateFactory->create();

        if (!$price) {
            $error = $this->_rateErrorFactory->create();

            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorTitle);

            $result->append($error);
        } else {
            $rate = $this->_rateMethodFactory->create();

            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod(self::RATE_REQUEST_STANDARD);
            $rate->setMethodTitle($this->getCode('method', self::RATE_REQUEST_STANDARD));
            $rate->setCost($cost);
            $rate->setPrice($price);

            $result->append($rate);
        }

        return $result;
    }

    protected function getShipmentItemsData(\Magento\Framework\DataObject $request)
    {
        $data = [
            'TOTALWEIGHT' => 0,
            'TOTALLENGTH' => 0,
            'TOTALWIDTH'  => 0,
            'TOTALHEIGHT' => 0,
            'ITEMNAME' => [],
            'SKYBILLITEMDESC' => []
        ];

        $dwaData = $this->helper->getDimensionalWeightAttributes();
        $productIds = [];

        foreach ($request->getPackageItems() as $item) {
            $productIds[] = $item['product_id'];
            $qty = $item['qty'];

            $data['TOTALWEIGHT'] += $item['weight'] * $qty;
            $data['ITEMNAME'][] = $item['name'];

            $data['SKYBILLITEMDESC']['product_id-' . $item['product_id']] = [
                'ITEMQTY' => $qty,
                'ITEMDESC' => $item['name'],
                'UNITPRICE' => $item['price'],
                'ORIGINSTATIONID' => 0,
                'REASONFOREXPORT' => '',
                'SKYBILLITEMNO' => 0
            ];
        }

        $productCollection = $this->_productCollectionFactory->create()->addStoreFilter(
            $request->getStoreId()
        )->addFieldToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToSelect([
            'hs_code',
            'country_of_manufacture'
        ]);

        foreach ($productCollection as $product) {
            $qty = $item['qty'];

            $data['TOTALLENGTH'] += ($product->getData($dwaData[DWA::ATTRIBUTE_LENGTH_CODE]) * $qty) + 0;
            $data['TOTALWIDTH']  += ($product->getData($dwaData[DWA::ATTRIBUTE_WIDTH_CODE]) * $qty) + 0;
            $data['TOTALHEIGHT'] += ($product->getData($dwaData[DWA::ATTRIBUTE_HEIGHT_CODE]) * $qty) + 0;

            $data['SKYBILLITEMDESC']['product_id-' . $product->getEntityId()]['CUSTOMCODE'] = $product->getHsCode();
            $data['SKYBILLITEMDESC']['product_id-' . $product->getEntityId()]['MFGCOUNTRY'] = $product->getCountryOfManufacture();
        }

        $data['SKYBILLITEMDESC'] = array_values($data['SKYBILLITEMDESC']);

        return $data;
    }

    /**
     * Form array with appropriate structure for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $shipmentItemsData = $this->getShipmentItemsData($request);

        $service = 'Courier';

        $shippingAdditionalData = $request->getShippingAdditionalData();

        $contents = implode(',', $shipmentItemsData['ITEMNAME']);
        if (strlen($contents) > 100) {
            $contents = substr($contents, 0, 96) . '...';
        }

        if ($request->getReferenceData()) {
            $referenceData = $request->getReferenceData() . $request->getPackageId();
        } else {
            $referenceData = 'Order #' .
                $request->getOrderShipment()->getOrder()->getIncrementId() .
                ' P' .
                $request->getPackageId();
        }

        $quoteId = $request->getOrderShipment()->getOrder()->getQuoteId();
        $quote = $this->quoteFactory->create()->load($quoteId);

        $quoteAddressCollection = $this->quoteAddressCollectionFactory->create();
        $quoteAddress = $quoteAddressCollection
            ->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('address_type', 'shipping')
            ->getFirstItem();

        $quoteAddress->setQuote($quote);
        $quoteAddress->setLimitCarrier($this->_code);
        $quoteAddress->requestShippingRates();

        $this->setRequest($this->registry->registry('skynetShipmentRequest'));

        $rateRequestResponse = $this->helper->parseXmlResponse(
            $this->doRateRequest(),
            Helper::SKYNET_API_METHOD_RATE_REQUEST
        );

        if (false === strpos($rateRequestResponse['status'], 'ERR')) {
            $calculatedWeights = $rateRequestResponse;
        } else {
            return [];
        }

        $data = [
            'SkybillObject' => [
                'BILLDATE' => $this->dateTime->date(self::BILL_DATE_FORMAT),

                'CONSIGNORACCOUNT' => $this->accountInfo['ConsignorAccount'],
                'CONSIGNOR' => $request->getShipperContactPersonName(),
                'CONSIGNORADDRESS' => $request->getShipperAddressStreet(),
                'CONSIGNORCITY' => $request->getShipperAddressCity(),
                'CONSIGNORCOUNTRY' => $request->getShipperAddressCountryCode(),
                'CONSIGNOREMAIL' => $request->getShipperEmail(),
                'CONSIGNORFAX' => '',
                'CONSIGNORPHONE' => $request->getShipperContactPhoneNumber(),
                'CONSIGNORREF' => $referenceData,
                'CONSIGNORSTATE' => $request->getShipperAddressStateOrProvinceCode(),
                'CONSIGNORZIPCODE' => $request->getShipperAddressPostalCode(),

                'CONSIGNEE' => $request->getRecipientContactPersonName(),
                'CONSIGNEEADDRESS' => $request->getRecipientAddressStreet(),
                'CONSIGNEECOUNTRY' => $request->getRecipientAddressCountryCode(),
                'CONSIGNEEEMAILADDRESS' => $request->getRecipientEmail(),
                'CONSIGNEETOWN' => $request->getRecipientAddressCity(),
                'CONSIGNEESTATE' => $request->getRecipientAddressStateOrProvinceCode(),
                'CONSIGNEEZIPCODE' => $request->getRecipientAddressPostalCode(),
                'CONSIGNEETELEPHONE' => $request->getRecipientContactPhoneNumber(),
                'CONSIGNEEATTENTION' => $request->getRecipientContactPersonName(),
                'CONSIGNEEFAX' => '',
                'CONSIGNEEMOBILE' => '',
                'CONSIGNEETAXID' => 0,

                'TYPEOFSHIPMENT' => $shippingAdditionalData['shipment_type'],
                'SERVICES' => $service,
                'PIECES' => 1, // In our case, we will only have 1 SKYBILLITEM.
                'TOTALWEIGHT' => $shipmentItemsData['TOTALWEIGHT'],
                'VALUEAMT' => $request->getPackageCustomsValue(),
                'CURRENCY' => $request->getBaseCurrencyCode(),
                'CODAMOUNT' => $this->getFinalPriceWithHandlingFee($request->getPackageCustomsValue()),
                'CODCURRENCY' => $request->getBaseCurrencyCode(),
                'DESTINATIONCODE' => '',
                'ORIGINSTATION' => $this->accountInfo['StationCode'],

                'TOTALVOLWEIGHT' => $calculatedWeights['vol_weight'],
                'CHARGABLEWEIGHT' => $calculatedWeights['charged_weight'],
                'CONTENTS' => $contents,
                'CONTRACTORNAME' => '',
                'CONTRACTORREF' => '',
                'NEWCONTENTS' => '',
                'ORIGINSTATIONID' => 0,
                'SKYBILLID' => 0,
                'SKYBILLNUMBER' => '',
                'SKYBILLPREFIX' => '',
                'TOSTATIONID' => 0,

                'SKYBILLITEMS' => [
                    'SKYBILLITEM' => [
                        [
                            'WEIGHT' => $shipmentItemsData['TOTALWEIGHT'],
                            'VOLWEIGHT' => 0,
                            'LEN' => $shipmentItemsData['TOTALLENGTH'],
                            'WIDTH' => $shipmentItemsData['TOTALWIDTH'],
                            'HEIGHT' => $shipmentItemsData['TOTALHEIGHT'],
                            'DECLAREDWEIGHT' => 0,
                            'ITEMDESCRIPTION' => '',
                            'ITEMNO' => 0,
                            'ORIGINSTATIONID' => 0,
                            'SKYBILLID' => 0,
                            'SkybillItemDescs' => [
                                'SKYBILLITEMDESC' => $shipmentItemsData['SKYBILLITEMDESC']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data = array_merge($data, $this->accountInfo);

        return $data;
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->registry->register('isDoSkynetShipmentRequest', true);
        $this->_prepareShipmentRequest($request);

        $result = new \Magento\Framework\DataObject();

        $shipmentRequest = $this->formShipmentRequest($request);
        $response = $this->helper->callSkynetApi(Helper::SKYNET_API_METHOD_CREATE_SHIPMENT, $shipmentRequest);

        if ($response != '[soapfault]') {
            $response = $this->helper->parseXmlResponse($response, Helper::SKYNET_API_METHOD_CREATE_SHIPMENT);

            if (false === strpos($response['status'], 'ERR')) {
                // TODO: Handle shipping label.
                // $shippingLabel = $this->getShippingLabel($response['shipment_number']);
                // if ($shippingLabel) {
                //     $result->setShippingLabelContent(base64_decode($shippingLabel));
                // }

                $shippingLabelUrl = __('Shipping Label URL: ')
                                  . '<a href="'
                                  . self::SHIPPING_LABEL_REPORT_URL . $response['shipment_number']
                                  . '" target="blank">'
                                  . '</a>';

                $request->getOrderShipment()->addComment($shippingLabelUrl, true, true);
                $result->setShippingLabelContent($response['shipment_number']);
                $result->setTrackingNumber($response['shipment_number']);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Could not create shipment')
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not create shipment')
            );
        }

        $this->registry->unregister('isDoSkynetShipmentRequest');

        return $result;
    }

    public function getShippingLabel($shipmentNumber)
    {
        $result = '';

        $request  = ['ShipmentNo' => $shipmentNumber];
        $request += $this->accountInfo;

        $response = $this->helper->callSkynetApi(Helper::SKYNET_API_METHOD_GET_SHIPPING_LABEL, $request);
        $response = $this->helper->parseXmlResponse($response, Helper::SKYNET_API_METHOD_GET_SHIPPING_LABEL);

        if (false === strpos($response['status'], 'ERR')) {
            $result = $response['shipment_label'];
        }

        return $result;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        foreach ($trackings as $tracking) {
            $this->_getXMLTracking($tracking);
        }

        return $this->_result;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $tracking
     * @return void
     */
    protected function _getXMLTracking($tracking)
    {
        $accountInfo = $this->accountInfo;
        $request = [
            'consignorUsername' => $accountInfo['UserName'],
            'consignorPassword' => $accountInfo['Password'],
            'consignorAccount' => $accountInfo['ConsignorAccount'],
            'stationCode' => $accountInfo['StationCode'],
            'isRef' => 0,
            'shipmentNumber' => $tracking
        ];

        $requestString = serialize($request);
        $response = $this->_getCachedQuotes($requestString);

        $debugData = ['request' => $request];

        if ($response === null) {
            try {
                $response = $this->helper->callSkynetApi(Helper::SKYNET_API_METHOD_GET_TRACKING, $request);

                $this->_setCachedQuotes($requestString, serialize($response));

                $debugData['result'] = $response;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }

        $this->_parseTrackingResponse($tracking, $response);
    }

    /**
     * Parse tracking response
     *
     * @param string[] $trackingValue
     * @param \stdClass $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _parseTrackingResponse($trackingValue, $response)
    {
        $errorTitle = __('For some reason we cannot retrieve tracking info right now...');

        if (is_object($response)) {
            if (isset($response->errorMessage)) {
                $errorTitle = $response->errorMessage;
            } elseif (isset($response->skybillInfo) && isset($response->currentStatus)) {
                $trackInfo = $response->skybillInfo;
                $trackCurrentStatus = $response->currentStatus;

                $resultArray['status'] = $trackCurrentStatus->statusDescription;

                if ($trackCurrentStatus->status == 'DELIVERED') {
                    $trackTimestamp = strtotime($trackCurrentStatus->trackDateTime);
                    $resultArray['deliverydate'] = date('Y-m-d', $trackTimestamp);
                    $resultArray['deliverytime'] = date('H:i:s', $trackTimestamp);
                }

                $deliveryLocationArray = [];
                if (isset($trackInfo->consigneeCity)) {
                    $deliveryLocationArray[] = $trackInfo->consigneeCity;
                }
                if (isset($trackInfo->consigneeCountry)) {
                    $deliveryLocationArray[] = $trackInfo->consigneeCountry;
                }
                if ($deliveryLocationArray) {
                    $resultArray['deliverylocation'] = implode(', ', $deliveryLocationArray);
                }

                if ($trackCurrentStatus->status == 'DELIVERED' && $trackCurrentStatus->recipient) {
                    $resultArray['signedby'] = $trackCurrentStatus->recipient;
                }

                $resultArray['shippeddate'] = date('Y-m-d', strtotime($trackInfo->shipmentDate));

                $packageProgress = [];
                if (isset($response->tracks)) {
                    $trackEvents = $response->tracks->Track;
                    if (!is_array($trackEvents)) {
                        $trackEvents = [$trackEvents];
                    }

                    foreach ($trackEvents as $trackEvent) {
                        $tempArray = [];
                        $tempArray['activity'] = $trackEvent->statusDescription;

                        $trackTimestamp = strtotime($trackEvent->trackDateTime);
                        $tempArray['deliverydate'] = date('Y-m-d', $trackTimestamp);
                        $tempArray['deliverytime'] = date('H:i:s', $trackTimestamp);

                        $tempArray['deliverylocation'] = $trackEvent->location;

                        $packageProgress[] = $tempArray;
                    }
                }

                $resultArray['progressdetail'] = $packageProgress;
            }
        }

        if (!$this->_result) {
            $this->_result = $this->_trackFactory->create();
        }

        if (isset($resultArray)) {
            $tracking = $this->_trackStatusFactory->create();

            $tracking->setCarrier($this->_code);
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingValue);
            $tracking->addData($resultArray);

            $this->_result->append($tracking);
        } else {
            $error = $this->_trackErrorFactory->create();

            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingValue);
            $error->setErrorMessage($errorTitle);

            $this->_result->append($error);
        }
    }

    /**
     * Calculate price considering free shipping and handling fee
     *
     * @param string $cost
     * @param string $method
     * @return float|string
     */
    public function getMethodPrice($cost, $method = '')
    {
        $isFreeShippingCountry = false;
        $freeShippingCountries = $this->getConfigData('free_shipping_specificcountry');

        if ($freeShippingCountries) {
            if (is_string($freeShippingCountries) && $freeShippingCountries == $this->destCountry) {
                $isFreeShippingCountry = true;
            } else {
                $freeShippingCountries = array_flip($freeShippingCountries);
                if (isset($freeShippingCountries[$this->destCountry])) {
                    $isFreeShippingCountry = true;
                }
            }
        }

        return $method == $this->getConfigData(
            $this->_freeMethod
        ) && $this->getConfigFlag(
            'free_shipping_enable'
        ) && $isFreeShippingCountry
          && $this->getConfigData(
            'free_shipping_subtotal'
        ) <= $this->_rawRequest->getBaseSubtotalInclTax() ? '0.00' : $this->getFinalPriceWithHandlingFee(
            $cost
        );
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'unit_of_measure' => [
                'LB' => __('Pounds'),
                'KG' => __('Kilograms'),
            ],
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];

        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }

    /**
     * Check whether shipping additional forms available
     *
     * @return bool
     */
    public function isShippingAdditionalFormAvailable()
    {
        return true;
    }
}
