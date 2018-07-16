<?php

namespace src\Validation;

use Illuminate\Support\Facades\Validator as IlluminateValidator;
use src\Api\Response\JsonResponse;
use src\Api\Response\Response;

/**
 * Responsible for validating data
 *
 * Class Validator
 * @package src\Validation
 *
 * @author Medard Ilunga
 */
class Validator{

    /**
     * @param array $data | Associative array of properties with their values
     * @param array $rules | Associative array of properties with their validation rules
     * @param array $messages | Associative array of properties with their custom validation messages
     *
     * @return Response
     */
    static function validate(array $data, array $rules, array $messages = []){

        $validator = IlluminateValidator::make($data, $rules, $messages);

        if($validator->fails()){
            return JsonResponse::internal($validator->errors());
        }else{
            return JsonResponse::internal();
        }
    }
}
