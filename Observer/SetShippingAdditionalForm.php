<?php
/**
 * Copyright © 2015 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Set SkyNet shipping additional form observer
 */
class SetShippingAdditionalForm implements ObserverInterface
{
    /**
     * Session model
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * SkyNet carrier model
     *
     * @var \ShopGo\SkyNet\Model\Carrier
     */
    protected $skynetCarrier;

    /**
     * SkyNet shipping additional form
     *
     * @var \ShopGo\SkyNet\Block\Adminhtml\Shipping\Additional\Form
     */
    protected $shippingAdditionalForm;

    /**
     * @param \Magento\Backend\Model\Session $session
     * @param \ShopGo\SkyNet\Model\Carrier $skynetCarrier
     * @param \ShopGo\SkyNet\Block\Adminhtml\Shipping\Additional\Form $shippingAdditionalForm
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \ShopGo\SkyNet\Model\Carrier $skynetCarrier,
        \ShopGo\SkyNet\Block\Adminhtml\Shipping\Additional\Form $shippingAdditionalForm
    ) {
        $this->session = $session;
        $this->skynetCarrier = $skynetCarrier;
        $this->shippingAdditionalForm = $shippingAdditionalForm;
    }

    /**
     * Set SkyNet shipping additional form
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $carrier = $observer->getEvent()->getData('carrier');

        if ($this->skynetCarrier->getCarrierCode() == $carrier) {
            $form = [
                'carrier' => [
                    $carrier => $this->shippingAdditionalForm->getFormsHtml()
                ]
            ];

            $this->session->setShippingAdditionalForm($form);
        }
    }
}
