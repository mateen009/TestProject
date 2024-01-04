<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Test\Unit\Model\EntityScheme\Builder;

use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\FileReader;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Filesystem;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\FilesystemFactory;
use Amasty\ReportBuilder\Test\Unit\Traits;
use Magento\Framework\Exception\LocalizedException;

/**
 * @see Xml
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class XmlTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Xml
     */
    private $model;

    /**
     * @covers Xml::build
     */
    public function testBuild(): void
    {
        $this->expectException(LocalizedException::class);
        $filesystemFactory = $this->createMock(FilesystemFactory::class);
        $fileReader = $this->createMock(FileReader::class);
        $fileSystem = $this->createMock(Filesystem::class);

        $filesystemFactory->expects($this->any())->method('create')->willReturn($fileSystem);
        $fileSystem->expects($this->any())->method('read')->willReturn(['file1' => []]);
        $fileReader->expects($this->any())->method('getFilesNames')->willReturn(['test1', 'test2']);

        $this->model = $this->getObjectManager()->getObject(
            Xml::class,
            [
                'filesystemFactory' => $filesystemFactory,
                'fileReader' => $fileReader,
            ]
        );

        $this->model->build();
    }
}
