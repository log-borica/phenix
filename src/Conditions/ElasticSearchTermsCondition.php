<?php

namespace Phenix\Core\Conditions;

use Phenix\Core\Enumerators\ConditionTypeEnum;

/**
 * Class ElasticSearchTermsCondition
 * @package Phenix\Core\Conditions
 */
class ElasticSearchTermsCondition extends ElasticSearchCondition
{
    /**
     * ElasticSearchTermsCondition constructor.
     * @param string $field
     * @param array $value
     * @param string $conditionDeterminantType
     */
    public function __construct(string $field,
                                array $value,
                                string $conditionDeterminantType)
    {
        $this->type = ConditionTypeEnum::TERMS;
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
