<?php

namespace Otomaties\CarmanClient\Abstracts;

use Otomaties\CarmanClient\Contracts\PostType as PostTypeContract;
use Otomaties\CarmanClient\PostTypes\Car;

/**
 * Read-only post type that holds data synced from Carman
 */
abstract class PostType implements PostTypeContract
{
    public const POST_TYPE = '';

    abstract protected function singularName(): string;

    abstract protected function pluralName(): string;

    public function register(): void
    {
        register_extended_post_type(static::POST_TYPE, $this->args(), $this->names());
    }

    protected function args(): array
    {
        return [
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type='.Car::POST_TYPE,
            'show_in_rest' => false,
            'supports' => ['title', 'thumbnail'],
            'capabilities' => ['create_posts' => 'do_not_allow'],
            'map_meta_cap' => true,
            'admin_cols' => [
                'carman_id' => [
                    'title' => __('Carman ID', 'otomaties-carman-client'),
                    'meta_key' => 'carman_id',
                ],
            ],
        ];
    }

    /**
     * @return array{singular: string, plural: string, slug: string}
     */
    protected function names(): array
    {
        return [
            'singular' => $this->singularName(),
            'plural' => $this->pluralName(),
            'slug' => sanitize_title($this->pluralName()),
        ];
    }
}
