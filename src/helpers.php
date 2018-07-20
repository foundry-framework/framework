<?php


use Foundry\Framework\CallService;

if (!function_exists('fndry')) {

    function fndry()
    {
        return CallService::getInstance();
    }
}
