<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class Location extends PostType
{
    public const POST_TYPE = 'carman_location';

    protected function singularName(): string
    {
        return __('Location', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Locations', 'otomaties-carman-client');
    }
}
