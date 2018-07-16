<?php

namespace src\Api\Models;

use Exception;
use src\Api\Entities\Entity;
use src\Api\Response\JsonResponse;
use src\Api\Response\Response;
use src\Storage\Store;
use src\Validation\Validator;

/**
 * Class Model
 *
 * Base class that all models MUST extend
 *
 * @author Medard Ilunga
 */
abstract class Model
{

    /**
     * Validation rules for corresponding Entity'properties
     *
     * @return array
     */
    static abstract function rules();

    /**
     * Validation custom errors for corresponding Entity'properties
     *
     * @return array
     */
    static abstract function messages();


    /**
     * Update/Create an entity and call required methods
     *
     * @param Entity $entity | Object to be saved|updated
     * @param bool $validate | If the object should be validated
     *
     * @return Response
     */
    static function store(Entity $entity, bool $validate = true){

        /**
         * @var $resp Response
         */
        $resp = JsonResponse::internal();

        if($validate){
            $resp = Validator::validate(self::toArray($entity), self::rules(), self::messages());
        }

        if($resp->isStatus()){
            /**
             * This object is being updated
             */
            if($entity->getId()){

                /**
                 * If beforeUpdate method exists on the Entity Model, call it.
                 */
                if(method_exists($entity, 'beforeUpdate')){
                    call_user_func($entity->beforeUpdate());
                }

                $success = Store::update($entity);

                /**
                 * If afterUpdate method exists on the Entity Model, call it.
                 */
                if(method_exists($entity, 'afterUpdate')){
                    call_user_func($entity->afterUpdate());
                }

            }else{

                /**
                 * This object needs to be created
                 */

                /**
                 * If beforeCreate method exists on the Entity Model, call it.
                 */
                if(method_exists($entity, 'beforeCreate')){
                    call_user_func($entity->beforeCreate());
                }

                $success = Store::create($entity);

                /**
                 * If afterCreate method exists on the Entity Model, call it.
                 */
                if(method_exists($entity, 'afterCreate')){
                    call_user_func($entity->afterCreate());
                }
            }

            if(!$success){
                $resp = JsonResponse::internal('Unable to store '. get_class($entity). ' at the moment');
            }

        }

        return $resp;
    }

    /**
     * @param Entity $entity | Data to be deleted
     * @param bool $force | Soft/Hard destroy
     * @return Response
     */
    static function destroy(Entity $entity, bool $force = false){

        /**
         * Has the object been successfully deleted?
         */
        $deleted = false;
        /**
         * Deleted Entity object
         */
        $obj = null;

        /**
         * If beforeDestroy event exists on the Entity Model, call it.
         */
        if(method_exists($entity, 'beforeDestroy')){
            call_user_func($entity->beforeDestroy());
        }

        try {
           $obj = Store::delete($entity, $force);
           $deleted = true;
        } catch (Exception $e) {
        }

        /**
         * If afterDestroy event exists on the Entity Model, call it.
         */
        if(method_exists($obj, 'afterDestroy')){
            call_user_func($obj->afterDestroy());
        }


        return JsonResponse::internal($deleted? 'Unable to delete '. get_class($entity): null);
    }

    /**
     * @param Entity $entity | entity/entities to be restored
     *
     * @return Response
     */
    static function restore(Entity $entity){

        $restored = false;

        /**
         * If beforeRestore method exists on the Entity Model, call it.
         */
        if(method_exists($entity, 'beforeRestore')){
            call_user_func($entity->beforeRestore());
        }

        try {
            $entity = Store::restore($entity);
            $restored = true;
        } catch (Exception $e) {
        }

        /**
         * If afterRestore method exists on the Entity Model, call it.
         */
        if(method_exists($entity, 'afterRestore')){
            call_user_func($entity->afterRestore());
        }


        return JsonResponse::internal($restored? 'Unable to restore '. get_class($entity): null);
    }

    /**
     * Convert an Entity object to an array
     *
     * @param Entity $entity
     * @return array
     */
    static function toArray(Entity $entity){

        $reflectionClass = null;
        $array = [];

        try {
            $reflectionClass = new \ReflectionClass(get_class($entity));
        } catch (\ReflectionException $e) {
        }

        if($reflectionClass){

            foreach ($reflectionClass->getProperties() as $property) {
                $property->setAccessible(true);
                $array[$property->getName()] = $property->getValue($entity);
                $property->setAccessible(false);
            }

        }

        return $array;
    }
}
