<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class FileReader
{
    const ENTITY_FILES_DIRECTORY = 'ambuilder_entity_scheme';
    
    /**
     * @var array
     */
    private $customModuleDirectories = [];

    /**
     * @var Dir
     */
    private $moduleDirs;

    /**
     * @var ModuleListInterface
     */
    private $modulesList;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    public function __construct(
        Dir $moduleDirs,
        ModuleListInterface $moduleList,
        ReadFactory $readFactory
    ) {
        $this->moduleDirs = $moduleDirs;
        $this->modulesList = $moduleList;
        $this->readFactory = $readFactory;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getFilesNames(): array
    {
        $result = [];
        foreach ($this->modulesList->getNames() as $moduleName) {
            // skip magento modules
            if (strpos($moduleName, 'Magento_') === 0) {
                continue;
            }
            $moduleDirectory = $this->getModuleDirectory(Dir::MODULE_ETC_DIR, $moduleName);
            $filesDirectory = $moduleDirectory . '/' . self::ENTITY_FILES_DIRECTORY;
            $directoryRead = $this->readFactory->create($filesDirectory);
            if ($directoryRead->isDirectory()) {
                foreach ($directoryRead->read() as $file) {
                    $result[] = $directoryRead->getRelativePath($file);
                }
            }
        }

        return array_unique($result);
    }

    private function getModuleDirectory(string $type, string $moduleName): string
    {
        if (isset($this->customModuleDirectories[$moduleName][$type])) {
            return $this->customModuleDirectories[$moduleName][$type];
        }

        // Getting default module directory if custom directory for this module was not set
        return $this->moduleDirs->getDir($moduleName, $type);
    }

    public function setModuleDirectory(string $moduleName, string $type, string $path): void
    {
        $this->customModuleDirectories[$moduleName][$type] = $path;
    }
}
