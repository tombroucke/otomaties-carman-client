<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class ProductCategory extends PostType
{
    public const POST_TYPE = 'carman_product_cat';

    protected function singularName(): string
    {
        return __('Product category', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Product categories', 'otomaties-carman-client');
    }
}
