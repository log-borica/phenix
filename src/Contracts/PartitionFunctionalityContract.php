<?php

namespace Phenix\Core\Contracts;

/**
 * Interface PartitionFunctionalityContract
 * @package Phenix\Core\Contracts
 */
interface PartitionFunctionalityContract
{
    /**
     * @param int $partition
     * @param int $partitionsNumber
     * @return mixed
     */
    public function createPartition(int $partition = 0, int $partitionsNumber = 0);
}
