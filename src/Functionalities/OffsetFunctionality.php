<?php

namespace Phenix\Core\Functionalities;

use Phenix\Core\Contracts\OffsetFunctionalityContract;
use Phenix\Core\Exceptions\ElasticSearchException;

/**
 * Trait OffesetFunctionality
 * @package Phenix\Core\Functionalities
 */
trait OffsetFunctionality
{
    /**
     * @var int
     */
    public $from = 0;

    /**
     * @param int $row
     * @return $this
     * @throws ElasticSearchException
     */
    public function offset(int $row): self
    {
        if ($this instanceof OffsetFunctionalityContract) {
            $this->from = $row;

            return $this;
        }

        throw new ElasticSearchException('Para utilizar função offset na classe atual, você precisa contratar OffsetFunctionalityContract.');
    }
}
