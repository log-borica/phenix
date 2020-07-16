<?php

namespace Phenix\Core\Conditions;

use Phenix\Core\Enumerators\ConditionTypeEnum;

/**
 * Class ElasticSearchExistsCondition
 * @package Phenix\Core\Conditions
 */
class ElasticSearchExistsCondition extends ElasticSearchCondition
{
    /**
     * ElasticSearchExistsCondition constructor.
     * @param string $field
     * @param string $conditionDeterminantType
     */
    public function __construct(string $field, string $conditionDeterminantType)
    {
        $this->type = ConditionTypeEnum::EXISTS;
        $this->value = null;
        $this->field = $field;
        $this->determinantType = $conditionDeterminantType;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        return [
            'field' => $this->field
        ];
    }
}
