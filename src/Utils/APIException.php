<?php

namespace Foundry\Framework;

/**
 * Class APIException
 *
 * @package Foundry\Framework
 *
 * @author Medard Ilunga
 */
class APIException
{
    const NO_FOUND                                      = "Requested resource was not found";
    const UNKNOWN_ERROR                                 = "An unknown error occurred.";
    const INVALID_USER_TOKEN                            = "Your session is no longer valid. Please login again";
    const ACCESS_DENIED                                 = "Access Denied";
    const ILLEGAL_ACTION                                = "Oops! That is illegal!";
    const EMAIL_IN_USE                                  = "Email in use";
    const WRONG_PASSWORD                                = "Permission denied, wrong password and username combination";
    const UNABLE_TO_UPLOAD                              = "Oops! We are unable to upload the file at the moment!";
    const WRONG_CURRENT_PASSWORD                        = "Incorrect current password";
    const MISSING_FIELDS                                = "Error! missing fields. Please check corresponding API doc";
    const NOT_AUTHORIZED                                = "You are not authorized to view the requested data";
    const DELETED_AT_REQ                                = "doesn't have deleted_at property in order to be soft deleted";



    public static function get($key)
    {
        $reflect = null;

        try {
            $reflect = new \ReflectionClass("Foundry\Framework\\Utils\\APIException");
        } catch (\ReflectionException $e) {
        }

        if($reflect){
            $values = $reflect->getConstants();

            if (array_key_exists($key, $values))
            {
                return $values[$key];
            }
        }

        return "An unknown error occurred.";

    }

}
