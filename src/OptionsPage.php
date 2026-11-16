<?php

namespace Otomaties\CarmanClient;

use Otomaties\CarmanClient\PostTypes\CarCategory;

class OptionsPage
{
    public const SLUG = 'carman-client-settings';

    public function register(): void
    {
        acf_add_options_sub_page([
            'page_title' => __('Carman settings', 'otomaties-carman-client'),
            'menu_title' => __('Carman', 'otomaties-carman-client'),
            'menu_slug' => self::SLUG,
            'parent_slug' => 'options-general.php',
            'capability' => 'manage_options',
        ]);

        acf_add_local_field_group([
            'key' => 'group_carman_client_settings',
            'title' => __('Carman settings', 'otomaties-carman-client'),
            'fields' => [
                [
                    'key' => 'field_carman_client_booking_page',
                    'label' => __('Booking page', 'otomaties-carman-client'),
                    'name' => 'booking_page',
                    'type' => 'post_object',
                    'post_type' => ['page'],
                    'return_format' => 'object',
                    'allow_null' => 1,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => self::SLUG,
                    ],
                ],
            ],
        ]);

        acf_add_local_field_group([
            'key' => 'group_carman_client_car_category',
            'title' => __('Display', 'otomaties-carman-client'),
            'fields' => [
                [
                    'key' => 'field_carman_client_car_category_singular',
                    'label' => __('Singular name', 'otomaties-carman-client'),
                    'name' => 'singular',
                    'type' => 'text',
                    'instructions' => __('E.g. "van". Used in headings like "Rent your van today".', 'otomaties-carman-client'),
                ],
                [
                    'key' => 'field_carman_client_car_category_icon',
                    'label' => __('Icon', 'otomaties-carman-client'),
                    'name' => 'icon',
                    'type' => 'text',
                    'instructions' => __('Font Awesome icon name, e.g. "fas-van-shuttle". Leave empty to use the icon from Carman.', 'otomaties-carman-client'),
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => CarCategory::POST_TYPE,
                    ],
                ],
            ],
            'position' => 'side',
        ]);
    }
}
