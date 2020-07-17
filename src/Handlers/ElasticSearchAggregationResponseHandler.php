<?php

namespace Phenix\Core\Handlers;

use Phenix\Core\Aggregations\ElasticSearchAggregation;

/**
 * Class ElasticSearchAggregationResponseHandler
 * @package Phenix\Core
 */
class ElasticSearchAggregationResponseHandler
{
    /**
     * @param ElasticSearchAggregation $agg
     * @param array $aggregationsValues
     * @return mixed|string
     */
    public static function treat(ElasticSearchAggregation $agg, array $aggregationsValues)
    {
        if ($agg->hasToIgnoreHandler()) {
            return 'ignored';
        }

        $treated = collect($agg->treatResponse($aggregationsValues));

        if ($agg->hasToHandleSubAggsHimself()) {
            return $treated->toArray();
        }

        if ($agg->hasChildren()) {
            foreach ($agg->getChildren() as $aggChild) {
                $treated->put($aggChild->name, self::treat($aggChild, $aggregationsValues[$aggChild->name]));
            }
        }

        return $treated->toArray();
    }

    /**
     * @param $aggregationsSchema
     * @param $aggregationsValues
     * @return array
     */
    public static function go($aggregationsSchema, $aggregationsValues)
    {
        $treatedResponse = collect();

        foreach ($aggregationsSchema as $aggregation) {
            $aggregationTreatedValues = self::treat($aggregation, $aggregationsValues[$aggregation->name]);
            $treatedResponse->put($aggregation->name, $aggregationTreatedValues);
        }

        return $treatedResponse->toArray();
    }
}
