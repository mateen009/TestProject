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
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\EmailBuilder\Model;

class DefaultConfigProvider extends \Magezon\Builder\Model\DefaultConfigProvider
{
    /**
     * @var string
     */
    protected $_builderArea = 'email';

    /**
     * @var \Magezon\Builder\Data\Elements
     */
    protected $builderElements = 'email';

    /**
     * DefaultConfigProvider constructor.
     *
     * @param \Magezon\Builder\Model\WysiwygConfigProvider $wysiwygConfig
     * @param \Magezon\Builder\Model\CacheManager $builderCacheManager
     * @param \Magezon\Builder\Data\Groups $builderGroups
     * @param \Magezon\Builder\Data\Elements $builderElements
     * @param \Magezon\Builder\Helper\Data $builderHelper
     * @param \Magezon\SimpleBuilder\Data\Elements $builderEmailHelper
     */
    public function __construct(
        \Magezon\Builder\Model\WysiwygConfigProvider $wysiwygConfig,
        \Magezon\Builder\Model\CacheManager $builderCacheManager,
        \Magezon\Builder\Data\Groups $builderGroups,
        \Magezon\Builder\Data\Elements $builderElements,
        \Magezon\Builder\Helper\Data $builderHelper,
        \Magezon\SimpleBuilder\Data\Elements $builderEmailHelper
    ) {
        parent::__construct($wysiwygConfig, $builderCacheManager, $builderGroups, $builderElements, $builderHelper);
        $this->builderElements = $builderEmailHelper;
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $config = parent::getConfig();
        $config['profile'] = [
            'builder'     => \Magezon\EmailBuilder\Block\Builder::class,
            'home'        => 'https://www.magezon.com/magento-2-email-builder-extension.html',
            'templateUrl' => 'https://www.magezon.com/productfile/emailbuilder/templates.php'
        ];
        $config['loadStylesUrl'] = 'mgzsimplebuilder/ajax/loadStyles';
        return $config;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return 'MAGEZON_SIMPLE_BUILDER_CONFIG' . $this->getBuilderArea();
    }
}
