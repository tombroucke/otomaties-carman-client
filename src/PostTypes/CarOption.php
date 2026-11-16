<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class CarOption extends PostType
{
    public const POST_TYPE = 'carman_car_option';

    protected function singularName(): string
    {
        return __('Vehicle option', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Vehicle options', 'otomaties-carman-client');
    }
}
