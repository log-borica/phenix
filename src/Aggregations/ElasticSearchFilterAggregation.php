<?php

namespace Phenix\Core\Aggregations;


use Phenix\Core\Enumerators\ElasticSearchAggregationTypeEnum;

/**
 * Class ElasticSearchFilterAggregation
 * @package Phenix\Core\Aggregations
 */
class ElasticSearchFilterAggregation extends ElasticSearchAggregation
{
    /**
     * ElasticSearchFilterAggregation constructor.
     * @param string $name
     * @param array $value
     */
    public function __construct(string $name, array $value)
    {
        $this->type = ElasticSearchAggregationTypeEnum::FILTER;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        return [
            $this->getSyntaxOfAggregation() => $this->value,
        ];
    }

    /**
     * @param array $values
     * @return mixed
     */
    public function treatResponse(array $values)
    {
        return $values['doc_count'];
    }
}
