<?php
/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShopGo\SkyNet\Model\ResourceModel;

/**
 * SkyNet Service model
 */
class Service extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('skynet_service', 'service_id');
    }

    /**
     * Load an object using 'name' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'name';
        }

        return parent::load($object, $value, $field);
    }
}
