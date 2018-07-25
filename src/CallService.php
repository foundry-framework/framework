<?php

namespace Foundry\Framework;

use Foundry\Framework\Api\Response\JsonResponse;
use Foundry\Framework\Api\Response\Response;


/**
 *
 * Entry class for all API calls
 *
 * Class CallService
 *
 * @package Foundry\Framework
 *
 * @author Medard Ilunga
 */
class CallService {

    /**
     * The current globally available Foundry container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * Create/return a class instance
     *
     * @return CallService
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     *
     * @param string $plugin | name of the plugin/package
     * @param string $entity | name of the resource entity
     * @param string $method | http request method
     * @param $data
     *
     * @return Response
     */
    public function call(string $plugin, string $entity, string $method, $data){

        /**@var $resp Response*/
        $resp = JsonResponse::external(null,APIException::NO_FOUND, 404);

        $namespace = 'foundry/'.camel_case($plugin).'/Api/Services/'.ucfirst(camel_case($entity)).'Service';

        $service = null;

        //todo check that the service exists

        try{
            $service = new $namespace();
        }catch (\Exception $e){}

        if($service){
            if(method_exists($service, $method)){
                $resp = call_user_func_array(array($service, $method),is_array($data)? $data:[$data]);
            }
        }

        return $resp;
    }
}
