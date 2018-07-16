<?php

namespace src\Api\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Entity
 *
 * Base class that all entities MUST extend
 *
 * @MappedSupperClass
 *
 * @author Medard Ilunga
 */
class Entity
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
