<?php

namespace Foundry\Framework;

/**
 * Trait DeletedTrait
 *
 * @package Foundry\Framework
 *
 * @author Medard Ilunga
 */
trait Deletable{
    /**
     * @var \datetime
     *
     * @Mapping\column(type="datetime", nullable=true)
     */
    private $deleted_at;

    /**
     * @return \datetime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }


    /**
     * @param \datetime $deleted_at
     */
    public function setDeletedAt(\datetime $deleted_at)
    {
        $this->deleted_at = $deleted_at;
    }
}
