<?php

namespace Foundry\Framework\Api\Services;
use Foundry\Framework\APIException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Foundry\Framework\Api\Entities\Entity;
use Foundry\Framework\Api\Models\Model;
use Foundry\Framework\Api\Response\JsonResponse;

/**
 * Bridge between controllers and Models that all Services MUST extend
 *
 * Class Service
 * @package Foundry\Framework\Api\Services
 *
 * @author Medard Ilunga
 */
abstract class Service {

    /**
     * Get the Model of the Entity Object
     * @return Model
     */
    static abstract function entityModel();

    /**
     * Get the Entity
     * @return Model
     */
    static abstract function entity();


    /**
     * Create and persist and object of the corresponding Entity
     *
     * @param array $data | associative array
     *
     *
     * @return \Foundry\Framework\Api\Response\Response|null
     */
    static function post(array $data){

        $model = self::entityModel();
        $resp = null;

        if($model::authorized()){
            $entity = $model::createEntity($data);

            $resp = $model::store($entity);

            if($resp->isStatus())
                $resp->setCode(200);
            else
                $resp->setStatus(400);

            return $resp;
        }else
            return JsonResponse::external(null, APIException::ACCESS_DENIED, 403);

    }

    /**
     * Get specific records
     *
     */
    static function get(){

    }

    /**
     * Update and persist and object of the corresponding Entity
     *
     * @param $data | associative array or Entity object
     *
     *
     * @return \Foundry\Framework\Api\Response\Response|null
     */
    static function update($data){

        $model = self::entityModel();
        $resp = null;

        $entity = $data;

        if(!is_a($entity, Entity::class) && is_array($entity))
            $entity = $model::createEntity($data);

        if($model::authorized($entity)){
            $resp = $model::store($entity);

            if($resp->isStatus())
                $resp->setCode(200);
            else
                $resp->setStatus(400);

            return $resp;
        }else
            return JsonResponse::external(null, APIException::ACCESS_DENIED, 403);
    }

    /**
     * Delete and persist and object of the corresponding Entity
     *
     * @param $id | Id of the Entity object to be deleted
     *
     *
     * @return \Foundry\Framework\Api\Response\Response
     */
    static function delete($id){

        $entity = EntityManager::find(self::entity(), $id);

        $model = self::entityModel();

        if($entity){

            if($model::authorized($entity)){
                $resp = $model::destroy($entity);

                if($resp->isStatus())
                    $resp->setCode(200);
                else
                    $resp->setStatus(400);

                return $resp;
            }else
                return JsonResponse::external(null, APIException::ACCESS_DENIED, 403);

        }else
            return JsonResponse::external(null, APIException::NO_FOUND, 404);
    }

    static function restore($id){

    }
}
