<?php

declare(strict_types=1);

namespace Amasty\ReportBuilder\Model\EntityScheme;

use Amasty\ReportBuilder\Api\EntityScheme\ProviderInterface;
use Amasty\ReportBuilder\Api\EntityScheme\SchemeInterface;
use Amasty\ReportBuilder\Model\Cache\Type;
use Amasty\ReportBuilder\Model\EntitySchemeFactory;
use Magento\Framework\App\Cache;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Exception\LocalizedException;

class Provider implements ProviderInterface
{
    /**
     * @var SchemeInterface
     */
    private $entityScheme = null;

    /**
     * @var EntitySchemeFactory
     */
    private $entitySchemeFactory;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var Builder
     */
    private $schemeBuilder;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        EntitySchemeFactory $entitySchemeFactory,
        Cache $cache,
        Cache\StateInterface $cacheState,
        Builder $schemeBuilder,
        Serializer $serializer
    ) {
        $this->entitySchemeFactory = $entitySchemeFactory;
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->schemeBuilder = $schemeBuilder;
        $this->serializer = $serializer;
    }

    public function getEntityScheme(): SchemeInterface
    {
        if ($this->entityScheme !== null) {
            return $this->entityScheme;
        }

        $this->entityScheme = $this->entitySchemeFactory->create();

        if ($this->cacheState->isEnabled(Type::TYPE_IDENTIFIER)) {
            $schemeData = $this->cache->load(Type::CACHE_ID);
            if ($schemeData) {
                $schemeData = $this->serializer->unserialize($schemeData);
                $this->entityScheme->init($schemeData);

                return $this->entityScheme;
            }
        }

        $schemeData = $this->schemeBuilder->build();

        if (empty($schemeData)) {
            throw new LocalizedException(__('Scheme should not be empty'));
        }

        if ($this->cacheState->isEnabled(Type::TYPE_IDENTIFIER)) {
            $this->cache->save(
                $this->serializer->serialize($schemeData),
                Type::CACHE_ID,
                [Type::CACHE_TAG]
            );
        }

        $this->entityScheme->init($schemeData);

        return $this->entityScheme;
    }

    public function clear(): void
    {
        $this->entityScheme = null;
    }
}
