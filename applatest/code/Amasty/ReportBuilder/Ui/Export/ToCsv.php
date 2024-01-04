<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Ui\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;

class ToCsv
{
    /**
     * @var DirectoryList
     */
    private $directory;

    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    /**
     * @var int|null
     */
    private $pageSize = null;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        $pageSize = 5000
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->pageSize = $pageSize;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = uniqid($component->getName(), true);
        $file = 'export/' . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        /** @var \Amasty\ReportBuilder\Ui\DataProvider\Listing\View\DataProvider $dataProvider */
        $dataProvider = $component->getContext()->getDataProvider();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $fields = $this->metadataProvider->getFields($component);
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i = 1;
        $collection = $dataProvider->getCollection();
        $collection->setCurPage($i)->setPageSize($this->pageSize);
        $totalCount = (int) $collection->getSize();
        while ($totalCount > 0) {
            foreach ($collection->getData() as $itemData) {
                $row = [];
                foreach ($fields as $field) {
                    $row[$field] = $itemData[$field];
                }

                $stream->writeCsv($row);
            }
            $collection->setCurPage(++$i);
            $collection->reset();
            $totalCount -= $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
