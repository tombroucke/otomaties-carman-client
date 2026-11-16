<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class CarCategory extends PostType
{
    public const POST_TYPE = 'carman_car_category';

    protected function singularName(): string
    {
        return __('Vehicle category', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Vehicle categories', 'otomaties-carman-client');
    }
}
