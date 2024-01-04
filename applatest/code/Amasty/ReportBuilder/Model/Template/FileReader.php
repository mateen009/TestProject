<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Template;

use Magento\Framework\Module\Dir;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class FileReader
{
    const FILES_DIRECTORY = 'adminhtml/template';

    /**
     * @var Dir
     */
    private $moduleDirs;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    public function __construct(
        Dir $moduleDirs,
        ReadFactory $readFactory
    ) {
        $this->moduleDirs = $moduleDirs;
        $this->readFactory = $readFactory;
    }

    /**
     * @param string|null $moduleName
     * @return array
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getFilesNames(?string $moduleName): array
    {
        $result = [];
        $moduleDirectory = $this->moduleDirs->getDir($moduleName, Dir::MODULE_ETC_DIR);
        $filesDirectory = $moduleDirectory . '/' . self::FILES_DIRECTORY;
        $directoryRead = $this->readFactory->create($filesDirectory);
        if ($directoryRead->isDirectory()) {
            foreach ($directoryRead->read() as $file) {
                $result[] = $directoryRead->getAbsolutePath($file);
            }
        }

        return array_unique($result);
    }
}
