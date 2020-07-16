<?php

namespace Phenix\Core\Conditions;

use Phenix\Core\Enumerators\ConditionTypeEnum;

/**
 * Class ElasticSearchTermCondition
 * @package Phenix\Core\Conditions
 */
class ElasticSearchTermCondition extends ElasticSearchCondition
{
    /**
     * ElasticSearchTermCondition constructor.
     * @param string $field
     * @param string $value
     * @param string $conditionDeterminantType
     */
    public function __construct(string $field,
                                string $value,
                                string $conditionDeterminantType)
    {
        $this->type = ConditionTypeEnum::TERM;
        $this->field = $field;
        $this->value = $value;
        $this->determinantType = $conditionDeterminantType;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        return [
            $this->field => $this->value,
        ];
    }
}
