<?php

namespace Foundry\Framework;

/**
 * Trait TimeStampTrait
 *
 * @package Foundry\Framework
 *
 * @author Medard Ilunga
 */
trait TimeStamp{
    /**
     * @var \datetime
     *
     * @ORM\column(type="datetime")
     */
    private $created_at;

    /**
     * @var \datetime
     *
     * @ORM\column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @return \datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }


    /**
     * @param \datetime $created_at
     */
    public function setCreatedAt(\datetime $created_at)
    {
        $this->created_at = $created_at;
    }


    /**
     * @return \datetime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


    /**
     * @param \datetime $updated_at
     */
    public function setUpdatedAt(\datetime $updated_at)
    {
        $this->updated_at = $updated_at;
    }

}
