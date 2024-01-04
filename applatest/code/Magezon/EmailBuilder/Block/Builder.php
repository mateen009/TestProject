<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_EmailBuilder
 * @copyright Copyright (C) 2020 Magezon (https://www.magezon.com)
 */

namespace Magezon\EmailBuilder\Block;

class Builder extends \Magezon\Builder\Block\Builder
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Magezon_EmailBuilder::builder.phtml';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magezon\EmailBuilder\Helper\Data
     */
    protected $dataHelper;

    /**
     * Builder constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magezon\EmailBuilder\Model\CompositeConfigProvider $configProvider
     * @param \Magezon\EmailBuilder\Helper\Data $dataHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magezon\EmailBuilder\Model\CompositeConfigProvider $configProvider,
        \Magezon\EmailBuilder\Helper\Data $dataHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $configProvider, $data);
    }

    /**
     * Get Email Template Id
     * @return int
     */
    public function getTemplateId()
    {
        $getId = $this->_request->getParam('id');
        $id = ($getId) ? $getId : 0;

        return $id;
    }

    /**
     * @return boolean
     */
    public function isModuleEnable()
    {
        return $this->dataHelper->isModuleEnable();
    }

    /**
     * Get current Magento version
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
