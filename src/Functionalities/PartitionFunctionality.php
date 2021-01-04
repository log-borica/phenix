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
     * @param int $partitionValue
     * @param int $partitionNumber
     * @return $this
     */
    public function createPartition(int $partitionValue = 0, int $partitionNumber = 0): self
    {
        $this->partition = $partitionValue;
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
