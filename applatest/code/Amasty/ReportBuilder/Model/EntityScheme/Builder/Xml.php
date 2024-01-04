<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme\Builder;

use Amasty\ReportBuilder\Api\EntityInterface;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\Filesystem;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\Reader\FilesystemFactory;
use Amasty\ReportBuilder\Model\EntityScheme\Builder\Xml\FileReader;
use Magento\Framework\Exception\LocalizedException;

class Xml
{
    /**
     * @var FilesystemFactory
     */
    private $filesystemFactory;

    /**
     * @var FileReader
     */
    private $fileReader;

    /**
     * @var array
     */
    private $requireFields = [
        EntityInterface::NAME,
        EntityInterface::TITLE,
        EntityInterface::MAIN_TABLE,
        EntityInterface::RELATIONS,
        EntityInterface::COLUMNS,
    ];

    public function __construct(
        FilesystemFactory $filesystemFactory,
        FileReader $fileReader
    ) {
        $this->filesystemFactory = $filesystemFactory;
        $this->fileReader = $fileReader;
    }

    /**
     * Extract content of XMLs
     *
     * @return array of all XMLs
     */
    public function build(): array
    {
        $data = [];
        $fileNames = $this->fileReader->getFilesNames();
        foreach ($fileNames as $fileName) {
            /** @var Filesystem $fileSystem */
            $fileSystem = $this->filesystemFactory->create(
                ['fileName' => $fileName]
            );
            $data[] = $fileSystem->read();
        }

        // join all XML contents into single array
        $data = array_merge(...$data);

        $this->validateData($data);

        return $data;
    }

    private function validateData(array $data): void
    {
        $fields = [];
        $entities = [];
        foreach ($data as $name => $entity) {
            foreach ($this->requireFields as $requireField) {
                $isExist = isset($entity[$requireField]) && $entity[$requireField];
                if (!$isExist) {
                    $fields[$name][] = $requireField;
                    $entities[] = $name;
                }
            }
        }

        if ($fields) {
            $this->throwError($fields, $entities);
        }
    }

    /**
     * @param array $fields
     * @param array $entities
     *
     * @throws LocalizedException
     */
    private function throwError(array $fields, array $entities): void
    {
        throw new LocalizedException(
            __(
                'Node(s) %1 is(are) invalid in %2 accordingly.',
                $this->prepareFieldsForMessage($fields),
                implode(',', $entities)
            )
        );
    }

    private function prepareFieldsForMessage(array $fields): string
    {
        foreach ($fields as $name => $entityField) {
            $fields[$name] = sprintf('(%s)', implode(',', $entityField));
        }

        return implode(', ', $fields);
    }
}
