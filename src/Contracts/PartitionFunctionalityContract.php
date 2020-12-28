<?php

namespace Phenix\Core\Contracts;

/**
 * Interface PartitionFunctionalityContract
 * @package Phenix\Core\Contracts
 */
interface PartitionFunctionalityContract
{
    /**
     * @param int $partitionValue
     * @param int $partitionNumber
     * @return mixed
     */
    public function createPartition(int $partitionValue = 0, int $partitionNumber = 0);
}
