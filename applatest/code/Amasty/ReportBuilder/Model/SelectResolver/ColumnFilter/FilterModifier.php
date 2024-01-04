<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnFilter;

class FilterModifier implements FilterModifierInterface
{
    /**
     * @var FilterResolverInterface
     */
    private $resolver;

    /**
     * @var FilterStorageInterface
     */
    private $storage;

    public function __construct(
        FilterResolverInterface $resolver,
        FilterStorageInterface $storage
    ) {
        $this->resolver = $resolver;
        $this->storage = $storage;
    }

    public function modify(string $columnName, ?array $condition = null): void
    {
        $this->resolver->resolve();
        if ($condition) {
            $this->storage->addFilter($columnName, $condition);
        } else {
            $this->storage->removeFilter($columnName);
        }
    }
}
