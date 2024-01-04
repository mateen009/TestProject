<?php
declare(strict_types=1);

namespace TradeCentric\Version\Api;

/**
 * Interface ModuleNameHelperInterface
 * @package TradeCentric\Version\Api
 */
interface ModuleHelperInterface
{
    /**
     * @return string
     */
    public function getModuleName(): string;

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion(): string;
}
