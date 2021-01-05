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
    public $numPartitions;

    /**
     * @param int $partition
     * @param int $partitionsNumber
     * @return $this
     */
    public function createPartition(int $partition = 0, int $partitionsNumber = 0): self
    {
        $this->partition = $partition;
        $this->numPartitions = $partitionsNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPartition(): bool
    {
        return isset($this->partition) && isset($this->numPartitions);
    }
}
