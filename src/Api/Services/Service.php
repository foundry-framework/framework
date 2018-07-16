<?php

namespace src\Api\Services;
use LaravelDoctrine\ORM\Facades\EntityManager;
use src\Api\Entities\Entity;
use src\Api\Models\Model;
use src\Api\Response\JsonResponse;

/**
 * Bridge between controllers and Models that all Services MUST extend
 *
 * Class Service
 * @package src\Api\Services
 *
 * @author Medard Ilunga
 */
abstract class Service {

    /**
     * Get the Model of the Entity Object
     * @return Model
     */
    static abstract function getEntityModel();

    /**
     * Get the Entity
     * @return Model
     */
    static abstract function getEntity();


    /**
     * Create and persist and object of the corresponding Entity
     *
     * @param array $data | associative array
     *
     * @return null|\src\Api\Response\Response
     */
    static function create(array $data){

        $model = self::getEntityModel();
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
            return JsonResponse::external(null, 'Access denied', 403);

    }

    /**
     * Update and persist and object of the corresponding Entity
     *
     * @param $data | associative array or Entity object
     *
     * @return null|\src\Api\Response\Response
     */
    static function edit($data){

        $model = self::getEntityModel();
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
            return JsonResponse::external(null, 'Access denied', 403);
    }

    /**
     * Delete and persist and object of the corresponding Entity
     *
     * @param $id | Id of the Entity object to be deleted
     *
     * @return \src\Api\Response\Response
     */
    static function destroy($id){

        $entity = EntityManager::find(self::getEntity(), $id);

        $model = self::getEntityModel();

        if($entity){

            if($model::authorized($entity)){
                $resp = $model::destroy($entity);

                if($resp->isStatus())
                    $resp->setCode(200);
                else
                    $resp->setStatus(400);

                return $resp;
            }else
                return JsonResponse::external(null, 'Access denied', 403);

        }else
            return JsonResponse::external(null, get_class(self::getEntity()).' Not found', 404);
    }

    static function restore($id){

    }
}
