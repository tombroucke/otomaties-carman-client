<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class SeasonPrice extends PostType
{
    public const POST_TYPE = 'carman_season_price';

    protected function singularName(): string
    {
        return __('Season price', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Season prices', 'otomaties-carman-client');
    }
}
