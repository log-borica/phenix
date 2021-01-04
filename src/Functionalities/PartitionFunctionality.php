<?php

namespace Phenix\Core\Functionalities;

/**
 * Trait PartitionFunctionality
 * @package Phenix\Core\Functionalities
 */
trait PartitionFunctionality
{
    /**
     * @var integer
     */
    public $partition;
    /**
     * @var integer
     */
    public $numPartition;
    /**
     * @param int $partition
     * @param int $partitionNumber
     * @return $this
     */
    public function createPartition(int $partition = 0, int $partitionNumber = 0): self
    {
        $this->partition = $partition;
        $this->numPartition = $partitionNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPartition(): bool
    {
        return isset($this->partition) && isset($this->numPartition);
    }
}
