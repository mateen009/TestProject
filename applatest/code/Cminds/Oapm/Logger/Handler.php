<?php
namespace Cminds\Oapm\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystem,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        $fileName = null
    ) {
        $filePath = $dir->getPath('log') . '/';
        if (! $fileName) {
            $fileName = 'cminds.log';
        } else {
            $filePath .= '/cminds/';
        }
        parent::__construct(
            $filesystem,
            $filePath,
            $fileName
        );
    }
}
