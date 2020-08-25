<?php

namespace Phenix\Core\Traits;

use Closure;
use Phenix\Core\Aggregations\ElasticSearchAggregation;
use Phenix\Core\Aggregations\ElasticSearchValueCountAggregation;
use Phenix\Core\Builders\ElasticSearchConditionBoolBuilder;
use Phenix\Core\Conditions\ElasticSearchExistsCondition;
use Phenix\Core\Conditions\ElasticSearchMatchCondition;
use Phenix\Core\Conditions\ElasticSearchMatchPhraseCondition;
use Phenix\Core\Conditions\ElasticSearchMultiMatchCondition;
use Phenix\Core\Conditions\ElasticSearchPrefixCondition;
use Phenix\Core\Conditions\ElasticSearchRangeCondition;
use Phenix\Core\Conditions\ElasticSearchRegexCondition;
use Phenix\Core\Conditions\ElasticSearchTermCondition;
use Phenix\Core\Conditions\ElasticSearchTermsCondition;
use Phenix\Core\Enumerators\ConditionRangeTypeEnum;
use Phenix\Core\Enumerators\ConditionDeterminantTypeEnum;
use Phenix\Core\Exceptions\ElasticSearchException;
use Phenix\Core\Handlers\ElasticSearchAggregationResponseHandler;
use Phenix\Core\ElasticSearchResponse;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

/**
 * Trait QueryBuilder
 * @package Phenix\Core\Traits
 */
trait QueryBuilder
{
    /**
     * @var array
     */
    private $columns = [];
    /**
     * @var array
     */
    private $aggregations = [];
    /**
     * @var ElasticSearchConditionBoolBuilder
     */
    private $conditionBoolBuilder;
    /**
     * @var
     */
    private $index;
    /**
     * @var string
     */
    private $type = '_doc';
    /**
     * @var
     */
    private $ordination;
    /**
     * @var int
     */
    private $from;
    /**
     * @var int
     */
    private $size;
    /**
     * @var int|null
     */
    private $terminateAfter;
    /**
     * @var string
     */
    private $scroll;
    /**
     * @var boolean|integer
     */
    private $trackTotalHits = true;

    /**
     * @param string $index
     * @return QueryBuilder
     */
    public static function index(string $index)
    {
        $instance = (new static)->newQuery();
        if (method_exists($instance, 'validateIndex')) {
            $instance::validateIndex($index);
        }
        $instance->index = $index;

        return $instance;
    }

    /**
     * @return mixed
     */
    public static function getAvailableIndexes()
    {
        return app('elasticsearch')
            ->cat()
            ->indices([
                'v' => true,
                'format' => 'json'
            ]);
    }

    /**
     * @return void
     */
    private function setup(): void
    {
        $this->conditionBoolBuilder = new ElasticSearchConditionBoolBuilder();
    }

    /**
     * @return $this
     */
    public function newQuery()
    {
        $this->setup();

        return $this;
    }

    /**
     * @param string $conditionDeterminant
     * @param $field
     * @param $operator
     * @param $value
     * @throws ElasticSearchException
     */
    private function applyCondition(string $conditionDeterminant,
                                    $field,
                                    $operator,
                                    $value): void
    {
        switch ($operator) {
            case '=':
            case '!=':
            case '<>':
                $this->conditionBoolBuilder->addCondition(new ElasticSearchTermCondition($field, $value, $conditionDeterminant));
                break;
            case '>':
                $condition = new ElasticSearchRangeCondition($field, $value, $conditionDeterminant, ConditionRangeTypeEnum::GREATER_THAN);
                $this->conditionBoolBuilder->addCondition($condition);
                break;
            case '<':
                $condition = new ElasticSearchRangeCondition($field, $value, $conditionDeterminant, ConditionRangeTypeEnum::LESS_THAN);
                $this->conditionBoolBuilder->addCondition($condition);
                break;
            case '>=':
                $condition = new ElasticSearchRangeCondition($field, $value, $conditionDeterminant, ConditionRangeTypeEnum::GREATER_THAN_OR_EQUAL);
                $this->conditionBoolBuilder->addCondition($condition);
                break;
            case '<=':
                $condition = new ElasticSearchRangeCondition($field, $value, $conditionDeterminant, ConditionRangeTypeEnum::LESS_THAN_OR_EQUAL);
                $this->conditionBoolBuilder->addCondition($condition);
                break;
            case 'like':
                $condition = new ElasticSearchMatchCondition($field, $value, $conditionDeterminant);
                $condition->logicalOperator('and');

                $this->conditionBoolBuilder->addCondition($condition);
                break;
            case 'inLike':
                $condition = new ElasticSearchMatchCondition($field, $value, $conditionDeterminant);
                $condition->logicalOperator('or');

                $this->conditionBoolBuilder->addCondition($condition);
                break;
        }
    }

    /**
     * @param Closure $field
     * @param string $conditionDeterminant
     */
    private function applyNestedWhere(Closure $field,
                                      string $conditionDeterminant = ConditionDeterminantTypeEnum::MUST): void
    {
        $currentBool = $this->getConditionBoolBuilder();

        $nestedConditionBoolBuilder = new ElasticSearchConditionBoolBuilder();
        $nestedConditionBoolBuilder
            ->setBoolParent($currentBool)
            ->setBoolDeterminantType($conditionDeterminant);

        $currentBool->addNestedBool($nestedConditionBoolBuilder);

        $this->setConditionBoolBuilder($nestedConditionBoolBuilder);
        call_user_func($field, $this);
        $this->setConditionBoolBuilder($currentBool);
    }

    /**
     * @param mixed ...$params
     * @return $this
     * @throws ElasticSearchException
     */
    public function where(...$params)
    {
        $params = array_merge([ConditionDeterminantTypeEnum::MUST], $params);
        $this->applyDeterminantWhere(...$params);

        return $this;
    }

    /**
     * @param string $conditionDeterminant
     * @param $field
     * @param null $value
     * @throws ElasticSearchException
     */
    private function applyDeterminantWhere(string $conditionDeterminant = ConditionDeterminantTypeEnum::MUST,
                                           $field,
                                           $value = null): void
    {
        if ($field instanceof Closure) {
            self::applyNestedWhere($field, $conditionDeterminant);

            return;
        }

        $args = func_get_args();
        if (count($args) == 4) {
            list($conditionDeterminant, $field, $operator, $value) = $args;
        }
        else {
            $operator = '=';
        }

        self::applyCondition($conditionDeterminant, $field, $operator, $value);
    }

    /**
     * @param array $params
     * @return $this
     * @throws ElasticSearchException
     */
    public function whereNot(...$params)
    {
        $params = array_merge([ConditionDeterminantTypeEnum::MUST_NOT], $params);
        $this->applyDeterminantWhere(...$params);

        return $this;
    }

    /**
     * @param $field
     * @param array $value
     * @return $this
     */
    public function whereIn($field, array $value)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchTermsCondition($field, $value, ConditionDeterminantTypeEnum::MUST));

        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function whereInLike(string $field, string $value)
    {
        $condition = new ElasticSearchMatchCondition($field, $value, ConditionDeterminantTypeEnum::MUST);
        $condition->logicalOperator('or');

        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function whereLike(string $field, string $value)
    {
        $condition = new ElasticSearchMatchCondition($field, $value, ConditionDeterminantTypeEnum::MUST);
        $condition->logicalOperator('and');

        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param $field
     * @param array $value
     * @return $this
     */
    public function whereNotIn($field, array $value)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchTermsCondition($field, $value, ConditionDeterminantTypeEnum::MUST_NOT));

        return $this;
    }

    /**
     * @param array $fields
     * @param string $value
     * @return $this
     */
    public function whereMultiMatch(array $fields, string $value)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchMultiMatchCondition($fields, $value, ConditionDeterminantTypeEnum::MUST));

        return $this;
    }

    /**
     * @param $field
     * @param array $values
     * @return $this
     * @throws ElasticSearchException
     */
    public function whereBetween($field, array $values)
    {
        $condition = new ElasticSearchRangeCondition(
            $field,
            $values,
            ConditionDeterminantTypeEnum::MUST,
            ConditionRangeTypeEnum::BETWEEN
        );
        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param $field
     * @param array $values
     * @return $this
     * @throws ElasticSearchException
     */
    public function whereNotBetween($field, array $values)
    {
        $condition = new ElasticSearchRangeCondition(
            $field,
            $values,
            ConditionDeterminantTypeEnum::MUST_NOT,
            ConditionRangeTypeEnum::BETWEEN
        );
        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function whereExists(string $field)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchExistsCondition($field, ConditionDeterminantTypeEnum::MUST));

        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function whereNotExists($field)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchExistsCondition($field, ConditionDeterminantTypeEnum::MUST_NOT));

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param string $flags
     * @return $this
     */
    public function whereRegexp($field, $value, $flags = 'ALL')
    {
        $condition = new ElasticSearchRegexCondition(
            $field,
            $value,
            $flags,
            ConditionDeterminantTypeEnum::MUST
        );

        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @return $this
     */
    public function wherePrefix($field, string $value)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchPrefixCondition($field, $value, ConditionDeterminantTypeEnum::MUST));

        return $this;
    }

    /**
     * @param mixed ...$params
     * @return $this
     * @throws ElasticSearchException
     */
    public function orWhere(...$params)
    {
        $params = array_merge([ConditionDeterminantTypeEnum::SHOULD], $params);
        $this->applyDeterminantWhere(...$params);

        return $this;
    }

    /**
     * @param array $fields
     * @param string $value
     * @return $this
     */
    public function orWhereMultiMatch(array $fields, string $value)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchMultiMatchCondition($fields, $value, ConditionDeterminantTypeEnum::SHOULD));

        return $this;
    }

    /**
     * @param $field
     * @param string $value
     * @return $this
     */
    public function orWherePrefix($field, string $value)
    {
        $this->conditionBoolBuilder->addCondition(new ElasticSearchPrefixCondition($field, $value, ConditionDeterminantTypeEnum::SHOULD));

        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @param string|null $analyzer
     * @return $this
     */
    public function whereMatchPhrase(string $field, string $value, string $analyzer = null)
    {
        $condition = new ElasticSearchMatchPhraseCondition($field, $value);

        if (isset($analyzer)) {
            $condition->analyzer($analyzer);
        }

        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @param string|null $analyzer
     * @return $this
     */
    public function orWhereMatchPhrase(string $field, string $value, string $analyzer = null)
    {
        $condition = new ElasticSearchMatchPhraseCondition($field, $value);
        $condition->setDeterminantType(ConditionDeterminantTypeEnum::SHOULD);

        if (isset($analyzer)) {
            $condition->analyzer($analyzer);
        }

        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @param string $field
     * @param string $value
     * @param string|null $analyzer
     * @return $this
     */
    public function whereNotMatchPhrase(string $field, string $value, string $analyzer = null)
    {
        $condition = new ElasticSearchMatchPhraseCondition($field, $value);
        $condition->setDeterminantType(ConditionDeterminantTypeEnum::MUST_NOT);

        if (isset($analyzer)) {
            $condition->analyzer($analyzer);
        }

        $this->conditionBoolBuilder->addCondition($condition);

        return $this;
    }

    /**
     * @return array
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * @return mixed
     */
    public function getOrdination()
    {
        return $this->ordination;
    }

    /**
     * @param $field
     * @param string $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->ordination[] = [
            $field => strtolower($direction) == 'asc' ? 'asc' : 'desc'
        ];
        return $this;
    }

    /**
     * @param int $perPage
     * @param array $columnsAliases
     * @param int $page
     * @param callable|null $toDoAfterSearch
     * @return array
     */
    public function paginate($perPage = 15, $columnsAliases = [], $page = 1, callable $toDoAfterSearch = null)
    {
        $firstRow = ($page == 1) ? 0 : ((($page - 1) * $perPage) + 1);
        $valueCount = new ElasticSearchValueCountAggregation('rowSums', 'id');

        $result = $this
            ->agg($valueCount)
            ->take($perPage)
            ->offset($firstRow)
            ->get();

        $totalRows = $result->getAggregation('rowSums');
        $lastPage = ceil($totalRows / $perPage);

        $data = $result->getSources();

        if (!is_null($toDoAfterSearch)) {
            $data = $toDoAfterSearch($data, $result->getAggregations());
        }
        if (!is_null($columnsAliases) && !empty($columnsAliases)) {
            $data = $this->applyPaginationAliases($columnsAliases, $data);
        }

        return [
            'current_page' => (int)$page,
            'data' => $data,
            'from' => (int)$page,
            'to' => (int)$perPage,
            'last_page' => (int)$lastPage,
            'per_page' => (int)$perPage,
            'total' => (int)$totalRows
        ];
    }

    /**
     * @return ElasticSearchResponse
     */
    public function get()
    {
        $hasToChangeTheWindowsSizeReturn = ($this->getFrom() + $this->getSize()) > 10000;
        if ($hasToChangeTheWindowsSizeReturn) { //10000 é o limite padrão
            $this->setMaxRowsCanBeSearch();
        }

        $params = $this->buildParameters();
        $response = app('elasticsearch')
            ->search(
                null,
                $params['body'],
                null,
                $this->getType(),
                $this->getIndex(),
                $this->getScroll()
            );

        if ($hasToChangeTheWindowsSizeReturn) { //10000 é o limite padrão
            $this->setMaxRowsCanBeSearch(10000);
        }

        return $this->treatResponse($response);
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from ?? 0;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size ?? 10;
    }

    /**
     * @param int $value
     * @return QueryBuilder
     */
    public function terminateAfter(int $value)
    {
        $this->terminateAfter = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param int|null $maxResult
     */
    private function setMaxRowsCanBeSearch(int $maxResult = null): void
    {
        $firstRow = $this->getFrom();
        $size = $this->getSize();

        app('elasticsearch')
            ->updateSettings([
                'max_result_window' => $maxResult ?? ($firstRow + $size + 1)
            ], $this->getIndex());
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return array
     */
    private function buildParameters()
    {
        $params = ['body' => ['track_total_hits' => $this->getTrackTotalHits()]];
        $params['index'] = $this->index;
        $params['type'] = $this->type;
        $params['body']['query']['bool'] = $this->conditionBoolBuilder->buildForRequest();

        if (!empty($this->aggregations)) {
            foreach ($this->aggregations as $agg) {
                $params['body']['aggs'][$agg->name] = $this->buildAggregationsForRequest($agg);
            }
        }
        if (!empty($this->ordination)) {
            $params['body']['sort'] = $this->getOrdination();
        }
        if (isset($this->size)) {
            $params['body']['size'] = (string)$this->getSize();
        }
        if (isset($this->terminateAfter)) {
            $params['body']['terminate_after'] = $this->getTerminateAfter();
        }
        if (isset($this->from)) {
            $params['body']['from'] = $this->getFrom();
        }
        if (!empty($this->columns)) {
            $params['body']['_source'] = $this->getColumns();
        }

        return $params;
    }

    /**
     * @return mixed
     */
    public function getTerminateAfter()
    {
        return $this->terminateAfter;
    }

    /**
     * @param ElasticSearchAggregation $agg
     * @return array
     */
    private function buildAggregationsForRequest(ElasticSearchAggregation $agg)
    {
        $parameters = $agg->buildForRequest();
        if ($agg->hasChildren()) {
            foreach ($agg->getChildren() as $aggChild) {
                $parameters['aggs'][$aggChild->name] = $this->buildAggregationsForRequest($aggChild);
            }
        }

        return $parameters;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param $response
     * @return ElasticSearchResponse
     */
    private function treatResponse($response): ElasticSearchResponse
    {
        $items = collect();

        if (is_null($response)) {
            return new ElasticSearchResponse($items);
        }

        $hits = $response['hits'];

        // TODO Develop ElasticSearchSourceResponseHandler
        foreach ($hits['hits'] as $hit) {
            $items->push(collect($hit['_source']));
        }

        $aggregations = $response['aggregations'] ?? [];
        $aggregations = ElasticSearchAggregationResponseHandler::go($this->aggregations, $aggregations);

        $elasticSearchResponse = new ElasticSearchResponse($items, collect($aggregations));

        if (key_exists('total', $hits)) {
            $elasticSearchResponse->setTotalHits($hits['total']['value'] ?? $hits['total']);
        }

        if (key_exists('_scroll_id', $response)) {
            $elasticSearchResponse->setScroll($response['_scroll_id']);
        }

        return $elasticSearchResponse;
    }

    /**
     * @param int $row
     * @return $this
     */
    public function offset(int $row = 0)
    {
        $this->from = $row;

        return $this;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function take(int $quantity = 10)
    {
        $this->size = $quantity;

        return $this;
    }

    /**
     * @param array $columnsAliases
     * @param Collection $items
     * @return mixed
     */
    private function applyPaginationAliases(array $columnsAliases, Collection $items)
    {
        return $items
            ->transform(function (Collection $item) use ($columnsAliases) {
                $treatedItem = [];
                foreach ($columnsAliases as $key => $alias) {
                    if ($item->has($key)) {
                        $treatedItem[$alias] = $item->get($key);
                    }
                }

                return $treatedItem;
            })
            ->filter();
    }

    /**
     * @param ElasticSearchAggregation $aggregation
     * @return $this
     */
    public function agg(ElasticSearchAggregation $aggregation)
    {
        $this->aggregations[] = $aggregation;

        return $this;
    }

    /**
     * @param string $sentence
     * @return array
     */
    public function getMainWords(string $sentence): array
    {
        $words = $this->analyzer('autosearch', $sentence);

        return collect($words['tokens'])
            ->pluck('token')
            ->toArray();
    }

    /**
     * @param string $name
     * @param string $toAnalizy
     * @return mixed
     */
    public function analyzer(string $name, string $toAnalizy)
    {
        $client = new Client();
        $index = $this->getIndex();

        $response = $client->get(config('elasticsearch.host') . "/{$index}/_analyze", [
            'json' => [
                'analyzer' => $name,
                'text' => $toAnalizy
            ]
        ]);
        return collect(json_decode($response->getBody()->getContents(), true));
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        $params = $this->buildParameters();

        return $params['body'];
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function select(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param string $time
     * @return $this
     */
    public function setScroll(string $time)
    {
        $this->scroll = $time;

        return $this;
    }

    /**
     * @return string
     */
    public function getScroll()
    {
        return $this->scroll;
    }

    /**
     * @param string $hash
     * @param string $time
     * @return ElasticSearchResponse
     */
    public static function scroll(string $hash, string $time = '1m'): ElasticSearchResponse
    {
        $client = new Client();

        $requestPayload = [
            'json' => [
                'scroll' => $time,
                'scroll_id' => $hash
            ]
        ];
        try {
            $response = $client->post(config('elasticsearch.host') . "/_search/scroll", $requestPayload)
                ->getBody()
                ->getContents();

            $instance = (new static)->newQuery();

            return $instance
                ->newQuery()
                ->treatResponse(json_decode($response, true));
        } catch (\Exception $exception) {
            $failureResponse = new ElasticSearchResponse(collect());
            $failureResponse->scrollHasMissedTheCache = true;

            return $failureResponse;
        }
    }

    /**
     * @return ElasticSearchConditionBoolBuilder
     */
    public function getConditionBoolBuilder(): ElasticSearchConditionBoolBuilder
    {
        return $this->conditionBoolBuilder;
    }

    /**
     * @param ElasticSearchConditionBoolBuilder $conditionBoolBuilder
     */
    public function setConditionBoolBuilder(ElasticSearchConditionBoolBuilder $conditionBoolBuilder): void
    {
        $this->conditionBoolBuilder = $conditionBoolBuilder;
    }

    /**
     * @return bool|int
     */
    public function getTrackTotalHits()
    {
        return $this->trackTotalHits;
    }

    /**
     * @param bool|int $trackTotalHits
     */
    public function setTrackTotalHits($trackTotalHits): void
    {
        $this->trackTotalHits = $trackTotalHits;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
