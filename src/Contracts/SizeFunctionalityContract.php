<?php

namespace Phenix\Core\Contracts;

/**
 * Interface SizeFunctionalityContract
 * @package Phenix\Core\Contracts
 */
interface SizeFunctionalityContract
{
    /**
     * @param int $rows
     * @return mixed
     */
    public function take(int $rows);
}
