<?php

namespace Phenix\Core\Functionalities;

use Phenix\Core\Contracts\SizeFunctionalityContract;
use Phenix\Core\Exceptions\ElasticSearchException;

/**
 * Trait SizeFunctionality
 * @package Phenix\Core\Functionalities
 */
trait SizeFunctionality
{
    /**
     * @var int
     */
    public $size;

    /**
     * @param int $rows
     * @return SizeFunctionality
     * @throws ElasticSearchException
     */
    public function take(int $rows): self
    {
        if ($this instanceof SizeFunctionalityContract) {
            $this->size = $rows;

            return $this;
        }

        throw new ElasticSearchException('Para utilizar função take na classe atual, você precisa contratar SizeFunctionalityContract.');
    }
}
