<?php

namespace Foundry\Framework\Storage;

use Exception;
use Foundry\Framework\APIException;
use LaravelDoctrine\ORM\Facades\EntityManager as EntityManager;
use Foundry\Framework\Api\Entities\Entity;

/**
 * Class Store
 *
 * This class is responsible for interacting with Doctrine @EntityManager
 * In order to Create, Update, Delete or Restore data
 *
 * @package Foundry\Framework\Storage
 *
 * @author Medard Ilunga
 */
class Store
{

    /**
     * Soft/Hard Delete an entity
     *
     * @param Entity $obj The object to be deleted
     * @param bool $remove Should it be soft deleted or removed completely
     *
     * @return Entity
     * @throws Exception
     */
    public static function delete(Entity $obj, $remove = false) : Entity
    {
        if($remove){
            EntityManager::remove($obj);
        }else{

            if(property_exists(get_class($obj), 'deleted_at') &&
                method_exists($obj, 'setDeletedAt')){

                $obj->setDeletedAt(new \DateTime('now'));

                EntityManager::merge($obj);
            }else{
                throw new Exception(get_class($obj).' '.APIException::DELETED_AT_REQ);
            }
        }

        self::flush();

        return $obj;
    }

    /**
     * Restore soft deleted Entity/Entities
     *
     * @param Entity ...$entities an array of @Entity objects
     *
     * @return bool
     * @throws Exception
     *
     */
    public static function restore(Entity ...$entities) : bool
    {
        foreach ($entities as $obj){
            if(property_exists(get_class($obj), 'deleted_at') &&
                method_exists($obj, 'setDeletedAt')){

                $obj->setDeletedAt(null);

                EntityManager::merge($obj);

            }else{
                throw new Exception(get_class($obj).' '.APIException::DELETED_AT_REQ);
            }

        }

        self::flush();

        return true;
    }


    /**
     * Persist Entity/Entities
     *
     * @param Entity ...$entities an array of @Entity objects
     *
     * @return bool
     */
    public static function create(Entity ...$entities) : bool
    {

        foreach ($entities as $obj){

            /**
             * Set updated_at value if Entity requires it
             */
            if(property_exists(get_class($obj), 'updated_at') &&
                method_exists($obj, 'setUpdatedAt')){

                $obj->setUpdatedAt(new \DateTime('now'));

            }

            /**
             * Set created_at value if Entity requires it
             */
            if(property_exists(get_class($obj), 'created_at') &&
                method_exists($obj, 'setCreatedAt')){

                $obj->setCreatedAt(new \DateTime('now'));

            }

            EntityManager::persist($obj);

        }

        self::flush();

        return true;

    }

    /**
     * Update Entity/Entities
     *
     * @param Entity ...$entities an array of @Entity objects
     *
     * @return bool
     */
    public static function update(Entity ...$entities) : bool
    {

        foreach ($entities as $obj){

            /**
             * Set updated_at value if Entity requires it
             */
            if(property_exists(get_class($obj), 'updated_at') &&
                method_exists($obj, 'setUpdatedAt')){

                $obj->setUpdatedAt(new \DateTime('now'));

            }

            EntityManager::merge($obj);
        }

        self::flush();

        return true;
    }

    /**
     * Flush an EntityManager session
     */
    private static function flush() : void
    {
        EntityManager::flush();
    }
}
