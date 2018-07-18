<?php

namespace Foundry\Framework\Api\Entities;

use Doctrine\ORM\Mapping as ORM;
use  Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * Class Entity
 *
 * Base class that all entities MUST extend
 *
 * @MappedSuperclass
 *
 * @package Foundry\Framework\Api\Entities
 *
 * @author Medard Ilunga
 */
abstract class Entity
{
    /**
     * @var $id: Primary key
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id){
        $this->id = $id;
    }
}
