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
     * @param int $partitionNumber
     * @return mixed
     */
    public function createPartition(int $partition = 0, int $partitionNumber = 0);
}
