<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class Product extends PostType
{
    public const POST_TYPE = 'carman_product';

    protected function singularName(): string
    {
        return __('Product', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Products', 'otomaties-carman-client');
    }
}
