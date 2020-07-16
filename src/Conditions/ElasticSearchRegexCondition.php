<?php

namespace Phenix\Core\Conditions;

use Phenix\Core\Enumerators\ConditionTypeEnum;

/**
 * Class ElasticSearchRegexCondition
 * @package Phenix\Core\Conditions
 */
class ElasticSearchRegexCondition extends ElasticSearchCondition
{
    /**
     * @var string
     */
    private $flags = 'ALL';

    /**
     * ElasticSearchRegexCondition constructor.
     * @param string $field
     * @param string $value
     * @param $flags
     * @param string $conditionDeterminantType
     */
    public function __construct(string $field,
                                string $value,
                                $flags,
                                string $conditionDeterminantType)
    {
        $this->type = ConditionTypeEnum::TERM;
        $this->field = $field;
        $this->value = $value;
        $this->flags = $flags;
        $this->determinantType = $conditionDeterminantType;
    }

    /**
     * @return array
     */
    public function buildForRequest(): array
    {
        return [
            $this->field => [
                'value' => $this->value,
                'flags' => $this->flags
            ]
        ];
    }
}
