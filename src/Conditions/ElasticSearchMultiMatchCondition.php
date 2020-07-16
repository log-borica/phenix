<?php

namespace Phenix\Core\Conditions;

use Phenix\Core\Enumerators\ConditionTypeEnum;

/**
 * Class ElasticSearchMultiMatchCondition
 * @package Phenix\Core\Conditions
 */
class ElasticSearchMultiMatchCondition extends ElasticSearchCondition
{
    /**
     * ElasticSearchMultiMatchCondition constructor.
     * @param array $fields
     * @param string $value
     * @param string $conditionDeterminantType
     */
    public function __construct(array $fields,
                                string $value,
                                string $conditionDeterminantType)
    {
        $this->type = ConditionTypeEnum::MULTI_MATCH;
        $this->field = $fields;
        $this->value = $value;
        $this->determinantType = $conditionDeterminantType;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        return [
            'query' => $this->value,
            'fields' => $this->field
        ];
    }
}
