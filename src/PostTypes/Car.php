<?php

namespace Otomaties\CarmanClient\PostTypes;

use Otomaties\CarmanClient\Abstracts\PostType;

class Car extends PostType
{
    public const POST_TYPE = 'carman_car';

    protected function singularName(): string
    {
        return __('Vehicle', 'otomaties-carman-client');
    }

    protected function pluralName(): string
    {
        return __('Vehicles', 'otomaties-carman-client');
    }

    /**
     * Vehicles get a detail page, e.g. /wagenpark/peugeot-boxer/
     */
    protected function args(): array
    {
        return [
            ...parent::args(),
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => false,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'menu_icon' => 'dashicons-car',
            'rewrite' => [
                'slug' => config('carman-client.car_slug'),
                'with_front' => false,
            ],
        ];
    }
}
