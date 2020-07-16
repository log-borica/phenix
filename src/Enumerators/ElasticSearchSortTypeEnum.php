<?php

namespace Phenix\Core\Enumerators;

use Logcomex\PhpUtils\Helpers\EnumHelper;

/**
 * Class ElasticSearchSortTypeEnum
 * @package Phenix\Core\Enumerators
 */
class ElasticSearchSortTypeEnum
{
    use EnumHelper;
    public const ASC = 0;
    public const DESC = 1;
}
