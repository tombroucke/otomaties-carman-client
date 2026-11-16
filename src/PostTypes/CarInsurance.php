<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class CarInsurance extends PostType
{
    public const POST_TYPE = 'carman_car_insurance';

    protected function singularName(): string
    {
        return __('Insurance', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Insurances', 'otomaties-carman-client');
    }
}
