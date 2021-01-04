<?php

namespace Phenix\Core\Aggregations;

use Phenix\Core\Contracts\PartitionFunctionalityContract;
use Phenix\Core\Contracts\SizeFunctionalityContract;
use Phenix\Core\Contracts\SortFunctionalityContract;
use Phenix\Core\Functionalities\PartitionFunctionality;
use Phenix\Core\Functionalities\SortFunctionality;
use Phenix\Core\Handlers\ElasticSearchAggregationResponseHandler;
use Phenix\Core\Enumerators\ElasticSearchAggregationTypeEnum;
use Phenix\Core\Functionalities\SizeFunctionality;

/**
 * Class ElasticSearchTermsAggregation
 * @package Phenix\Core\Aggregations
 */
class ElasticSearchTermsAggregation extends ElasticSearchAggregation
    implements SizeFunctionalityContract, SortFunctionalityContract, PartitionFunctionalityContract
{
    use SizeFunctionality, SortFunctionality, PartitionFunctionality;

    /**
     * ElasticSearchTermsAggregation constructor.
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value)
    {
        $this->type = ElasticSearchAggregationTypeEnum::TERMS;
        $this->name = $name;
        $this->value = $value;
        $this->handleSubAggsHimself = true;
        return $this;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        $payload = ['field' => $this->value];
        if (isset($this->size)) {
            $payload['size'] = $this->size;
        }
        if ($this->hasSorter()) {
            $payload['order'] = [
                $this->sortBy => $this->sortType
            ];
        }
        if ($this->hasPartition()) {
            $payload['include'] = [
                $this->partition,
                $this->numPartition
            ];
        }

        return [
            $this->getSyntaxOfAggregation() => $payload
        ];
    }

    /**
     * @param array $values
     * @return array
     */
    public function treatResponse(array $values)
    {
        $treated = [];
        $buckets = $values['buckets'];
        if (empty($buckets)) {
            return $treated;
        }
        foreach ($buckets as $bucket) {
            $bucketTreated = [
                'key' => $bucket['key'],
                'count' => $bucket['doc_count']
            ];
            if ($this->hasChildren()) {
                foreach ($this->getChildren() as $aggChild) {
                    $aggregationResponse = $bucket[$aggChild->name] ?? [];
                    $bucketTreated[$aggChild->name] = ElasticSearchAggregationResponseHandler::treat($aggChild, $aggregationResponse);
                }
            }
            $treated[] = $bucketTreated;
        }
        return $treated;
    }
}
