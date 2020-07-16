<?php

namespace Phenix\Core\Schemas;

/**
 * Class ElasticSearchConditionBoolSchema
 * @package Phenix\Core\Schema
 */
class ElasticSearchConditionBoolSchema
{
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
