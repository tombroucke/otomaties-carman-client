<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class CarAccessory extends PostType
{
    public const POST_TYPE = 'carman_car_accessory';

    protected function singularName(): string
    {
        return __('Accessory', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Accessories', 'otomaties-carman-client');
    }
}
