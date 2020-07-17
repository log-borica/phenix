<?php

namespace Phenix\Core\Enumerators;

use Logcomex\PhpUtils\Helpers\EnumHelper;

/**
 * Class ElasticSearchAggregationTypeEnum
 * @package Phenix\Core\Enumerators
 */
class ElasticSearchAggregationTypeEnum
{
    use EnumHelper;

    /**
     * @var int
     */
    public const MIN = 0;
    /**
     * @var int
     */
    public const AVG = 1;
    /**
     * @var int
     */
    public const MAX = 2;
    /**
     * @var int
     */
    public const SUM = 3;
    /**
     * @var int
     */
    public const VALUE_COUNT = 4;
    /**
     * @var int
     */
    public const TERMS = 5;
    /**
     * @var int
     */
    public const STATS_BUCKET = 6;
    /**
     * @var int
     */
    public const COMPOSITE = 7;
    /**
     * @var int
     */
    public const BUCKET_SORT = 8;
    /**
     * @var int
     */
    public const CARDINALITY = 9;
    /**
     * @var int
     */
    public const FILTER = 10;
}
