<?php

namespace Phenix\Core\Contracts;

/**
 * Interface SortFunctionalityContract
 * @package Phenix\Core\Contracts
 */
interface SortFunctionalityContract
{
    /**
     * @param string $by
     * @param string $type
     * @return mixed
     */
    public function sort(string $by, string $type);
}
