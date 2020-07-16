<?php

namespace Phenix\Core\Functionalities;

/**
 * Trait SortFunctionality
 * @package Phenix\Core\Functionalities
 */
trait SortFunctionality
{
    /**
     * @var string
     */
    public $sortBy;
    /**
     * @var string
     */
    public $sortType;

    /**
     * @param string $by
     * @param string $type
     * @return $this
     */
    public function sort(string $by = '', string $type = 'asc'): self
    {
        $this->sortBy = $by;
        $this->sortType = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSorter(): bool
    {
        return isset($this->sortBy) && isset($this->sortType);
    }
}
