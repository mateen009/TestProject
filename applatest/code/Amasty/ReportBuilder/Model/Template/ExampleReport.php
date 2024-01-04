<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\Template;

use Amasty\ReportBuilder\Model\ReportRepository;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Filesystem\Driver\File as Driver;

class ExampleReport
{
    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Driver
     */
    private $driver;

    public function __construct(
        ReportRepository $reportRepository,
        FileReader $fileReader,
        Serializer $serializer,
        LoggerInterface $logger,
        Driver $driver
    ) {
        $this->reportRepository = $reportRepository;
        $this->fileReader = $fileReader;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->driver = $driver;
    }

    public function createExampleReports(string $moduleName): void
    {
        try {
            $files = $this->fileReader->getFilesNames($moduleName);
        } catch (FileSystemException $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            return;
        }

        foreach ($files as $file) {
            $jsonContent = $this->driver->fileGetContents($file);
            $reportData = $this->serializer->unserialize($jsonContent);
            $this->createReport($reportData);
        }
    }

    private function createReport(array $data): void
    {
        try {
            $report = $this->reportRepository->getNew();
            $report->addData($data);
            $this->reportRepository->save($report);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
        }
    }
}
