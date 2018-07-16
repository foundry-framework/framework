<?php

namespace src\Api\Entities;


abstract class User extends Entity implements \Illuminate\Contracts\Auth\Authenticatable
{

    use \LaravelDoctrine\ORM\Auth\Authenticatable;

    /**
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function beforeCreate(){

    }
}
