<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav;

use Amasty\ReportBuilder\Model\SelectResolver\ColumnBuilder\Eav\Relation\BuilderInterface;

class RelationResolver
{
    /**
     * @var BuilderInterface[]
     */
    private $pool;

    public function __construct(array $pool = [])
    {
        $this->pool = $pool;
    }

    public function resolve(array $columnData, string $linkedField, string $indexField, string $tableName): array
    {
        $relations = [];
        foreach ($this->pool as $relationDataBuilder) {
            if ($relationDataBuilder instanceof BuilderInterface && $relationDataBuilder->isApplicable($tableName)) {
                $relations[] = $relationDataBuilder->execute($columnData, $linkedField, $indexField, $tableName);
            }
        }

        return $relations;
    }
}
