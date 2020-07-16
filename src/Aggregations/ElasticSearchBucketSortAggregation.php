<?php

namespace Phenix\Core\Aggregations;

use Phenix\Core\Contracts\OffsetFunctionalityContract;
use Phenix\Core\Contracts\SizeFunctionalityContract;
use Phenix\Core\Enumerators\ElasticSearchAggregationTypeEnum;
use Phenix\Core\Exceptions\ElasticSearchException;
use Phenix\Core\Functionalities\OffsetFunctionality;
use Phenix\Core\Functionalities\SizeFunctionality;

/**
 * Class ElasticSearchBucketSortAggregation
 * @package Phenix\Core\Aggregations
 */
class ElasticSearchBucketSortAggregation
    extends ElasticSearchAggregation
    implements SizeFunctionalityContract, OffsetFunctionalityContract
{
    use SizeFunctionality,
        OffsetFunctionality;

    /**
     * ElasticSearchBucketSortAggregation constructor.
     * @param string $name
     * @throws ElasticSearchException
     */
    public function __construct(string $name)
    {
        $this->type = ElasticSearchAggregationTypeEnum::BUCKET_SORT;
        $this->name = $name;
        // Essa agregação não tem retorno, ela
        // somente altera o retorno de outras agregações
        $this->ignoreHandler = true;
        $this->take(15);

        return $this;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        $aggregationSyntax = [];

        if (!empty($this->value)) {
            $aggregationSyntax['sort'] = $this->value;
        }

        if (!empty($this->from)) {
            $aggregationSyntax['from'] = $this->from;
        }

        if (!empty($this->size)) {
            $aggregationSyntax['size'] = $this->size;
        }

        return [
            $this->getSyntaxOfAggregation() => $aggregationSyntax
        ];
    }

    /**
     * @param array $values
     * @return mixed
     */
    public function treatResponse(array $values)
    {
        return $values['value'];
    }

    /**
     * @param string $aggregationPath
     * @param $sortType
     * @return $this
     */
    public function addSorter(string $aggregationPath, $sortType): self
    {
        $this->value[] = [
            $aggregationPath => [
                'order' => $sortType
            ]
        ];

        return $this;
    }
}
