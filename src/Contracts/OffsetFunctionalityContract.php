<?php

namespace Phenix\Core\Contracts;

/**
 * Interface OffsetFunctionalityContract
 * @package Phenix\Core\Contracts
 */
interface OffsetFunctionalityContract
{
    /**
     * @param int $row
     * @return mixed
     */
    public function offset(int $row);
}
