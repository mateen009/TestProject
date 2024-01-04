<?php
declare(strict_types=1);

namespace TradeCentric\Version\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\PackageInfo;
use TradeCentric\Version\Api\ModuleHelperInterface;

/**
 * Class ModuleNameHelper
 * @package TradeCentric\Version\Helper
 */
class ModuleHelper extends AbstractHelper implements ModuleHelperInterface
{
    /**
     * @var PackageInfo
     */
    protected $packageInfo;

    /**
     * @param Context $context
     * @param PackageInfo $packageInfo
     */
    public function __construct(Context $context,  PackageInfo $packageInfo)
    {
        $this->packageInfo = $packageInfo;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->_getModuleName();
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion(): string
    {
        return $this->packageInfo->getVersion($this->getModuleName());
    }
}
