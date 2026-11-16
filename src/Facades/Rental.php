<?php

namespace Otomaties\CarmanClient\Facades;

use Illuminate\Support\Facades\Facade;

class Rental extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'carman-client.rental';
    }
}
