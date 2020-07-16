<?php

namespace Phenix\Core\Aggregations;

use Phenix\Core\Contracts\SizeFunctionalityContract;
use Phenix\Core\Handlers\ElasticSearchAggregationResponseHandler;
use Phenix\Core\Enumerators\ElasticSearchAggregationTypeEnum;
use Phenix\Core\Functionalities\SizeFunctionality;

/**
 * Class ElasticSearchCompositeAggregation
 * @package Phenix\Core\Aggregations
 */
class ElasticSearchCompositeAggregation extends ElasticSearchAggregation implements SizeFunctionalityContract
{
    use SizeFunctionality;

    /**
     * ElasticSearchCompositeAggregation constructor.
     * @param string $name
     * @throws \Phenix\Core\Exceptions\ElasticSearchException
     */
    public function __construct(string $name)
    {
        $this->type = ElasticSearchAggregationTypeEnum::COMPOSITE;
        $this->name = $name;
        // Essa agregação retorna buckets, então as subagregações
        // devem ser tratadas dentro da própria agregação
        $this->handleSubAggsHimself = true;
        $this->take(100);

        return $this;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        $payload = [
            'size' => $this->size,
            'sources' => [],
        ];

        if (empty($this->value)) {
            return $payload;
        }

        foreach ($this->value as $agg) {
            $sourceAggBodyPayload = $agg->buildForRequest();
            // Não precisa do size para o sources do composite
            unset($sourceAggBodyPayload['terms']['size']);

            $payload['sources'][][$agg->name] = $sourceAggBodyPayload;
        }

        return [
            $this->getSyntaxOfAggregation() => $payload
        ];
    }

    /**
     * @param array $values
     * @return array|mixed
     */
    public function treatResponse(array $values)
    {
        $treated = [];

        $buckets = $values['buckets'];
        $nextRowOfResult = $values['after_key'];

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
                    $aggChildValue = $bucket[$aggChild->name] ?? [];
                    if (empty($aggChildValue)) {
                        $bucketTreated[$aggChild->name] = 'no result';
                        continue;
                    }

                    $bucketTreated[$aggChild->name] = ElasticSearchAggregationResponseHandler::treat($aggChild, $aggChildValue);
                }
            }

            $treated[] = $bucketTreated;
        }

        return [
            'data' => $treated,
            'next_row' => $nextRowOfResult
        ];
    }

    /**
     * @param ElasticSearchAggregation $aggregation
     * @return $this
     */
    public function addSource(ElasticSearchAggregation $aggregation): self
    {
        $this->value[] = $aggregation;

        return $this;
    }
}
