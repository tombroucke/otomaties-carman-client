<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class ShippingCost extends PostType
{
    public const POST_TYPE = 'carman_shipping_cost';

    protected function singularName(): string
    {
        return __('Shipping cost', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Shipping costs', 'otomaties-carman-client');
    }
}
