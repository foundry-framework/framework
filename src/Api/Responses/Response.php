<?php

namespace Foundry\Framework\Api\Response;

/**
 * Class Response
 * @package Foundry\Framework\Api\Response
 *
 * @author Medard Ilunga
 */
class Response{

    /**
     * Is it a successful response ?
     *
     * @var bool
     */
    private $status;

    /**
     * An associative array of errors
     *
     * @var array
     */
    private $errors;

    /**
     * If it is an http response, provide http code
     *
     * @var string
     */
    private $code;

    /**
     * Requested data
     *
     * @var array|object
     */
    private $data;

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|object $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }


}
