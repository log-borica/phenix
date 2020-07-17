<?php

namespace Phenix\Core\Schemas;

use Illuminate\Contracts\Support\Arrayable;
use Logcomex\PhpUtils\Functionalities\PropertiesExporterFunctionality;
use Logcomex\PhpUtils\Functionalities\ValuesExporterToArrayFunctionality;

/**
 * Class ElasticSearchConditionBoolSchema
 * @package Phenix\Core\Schema
 */
class ElasticSearchConditionBoolSchema implements Arrayable
{
    use PropertiesExporterFunctionality,
        ValuesExporterToArrayFunctionality;
    /**
     * @var array
     */
    public $must;
    /**
     * @var array
     */
    public $must_not;
    /**
     * @var array
     */
    public $should;

    /**
     * ElasticSearchConditionBoolSchema constructor.
     * @param array $must
     * @param array $must_not
     * @param array $should
     */
    public function __construct(array $must = [],
                                array $must_not = [],
                                array $should = [])
    {
        $this->must = $must;
        $this->must_not = $must_not;
        $this->should = $should;
    }
}
