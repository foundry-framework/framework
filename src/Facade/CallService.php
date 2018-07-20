<?php

namespace Illuminate\Support\Facades;

/**
 *
 *
 */
class CallService extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @method call
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CallService::class;
    }
}
