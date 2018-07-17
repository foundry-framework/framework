<?php

namespace FoundryFramework\Framework\Api\Response;


class JsonResponse{

    /**
     * An internal response
     *
     * @param null $error
     * @return Response
     */
    static function internal($error = null){

        $resp = new Response();
        $resp->setStatus($error === null);

        $errors = [];

        if($error){
            $errors = is_array($error)? $error : ['error' => $error];
        }

        $resp->setErrors($errors);

        return $resp;

    }


    /**
     * An external http response
     *
     * @param null $data
     * @param null $error
     * @param int $code
     * @return Response
     */
    static function external($data = null, $error = null, $code = 200){

        $resp = new Response();

        if($error){
            $resp->setErrors(is_array($error)? $error: [$error]);

            $code = $code === 200? 400: $code;

            $resp->setStatus(false);
            $resp->setCode($code);
            $resp->setData(null);
        }else{
            $resp->setData(is_array($data)? $data: ['data' => $data]);
            $resp->setCode($code);
            $resp->setStatus(true);
        }

        return $resp;
    }

}
