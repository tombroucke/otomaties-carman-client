<?php

namespace Otomaties\CarmanClient;

use Illuminate\Support\Collection;
use Otomaties\CarmanClient\PostTypes\Car;
use Otomaties\CarmanClient\PostTypes\CarAccessory;
use Otomaties\CarmanClient\PostTypes\CarCategory;
use Otomaties\CarmanClient\PostTypes\CarInsurance;
use Otomaties\CarmanClient\PostTypes\CarOption;
use Otomaties\CarmanClient\PostTypes\Location;
use WP_Post;

/**
 * Rental data, synced from Carman into the Carman post types
 */
class Rental
{
    private ?Collection $accessories = null;

    private ?Collection $categories = null;

    private ?Collection $insurances = null;

    private ?Collection $locations = null;

    private ?Collection $options = null;

    private ?Collection $vehicles = null;

    /**
     * Published categories that contain at least one vehicle
     */
    public function categories(): Collection
    {
        return $this->categories ??= $this->posts(CarCategory::POST_TYPE)
            ->map(function (WP_Post $post) {
                $category = $this->carmanData($post);

                return [
                    'id' => $category['id'],
                    'slug' => $post->post_name,
                    'name' => $post->post_title,
                    'singular' => get_field('singular', $post->ID) ?: mb_strtolower($post->post_title),
                    'icon' => get_field('icon', $post->ID) ?: null,
                    'icon_url' => $category['icon_black_url'] ?? null,
                    'min_price' => (float) ($category['min_price'] ?? 0),
                ];
            })
            ->filter(fn (array $category) => $this->vehicles()->contains('category', $category['slug']))
            ->values();
    }

    public function category(string $slug): ?array
    {
        return $this->categories()->firstWhere('slug', $slug);
    }

    /**
     * Pickup and return locations, managed in Carman
     */
    public function locations(): Collection
    {
        return $this->locations ??= $this->posts(Location::POST_TYPE)
            ->map(function (WP_Post $post) {
                $location = $this->carmanData($post);

                return [
                    'id' => $post->post_name,
                    'name' => $post->post_title,
                    'address' => $location['address'] ?? null,
                    'zip' => $location['zip'] ?? null,
                    'city' => $location['city'] ?? null,
                ];
            });
    }

    /**
     * Published vehicle options, keyed by slug
     */
    public function options(): Collection
    {
        return $this->options ??= $this->posts(CarOption::POST_TYPE)
            ->mapWithKeys(function (WP_Post $post) {
                $option = $this->carmanData($post);

                return [$post->post_name => [
                    'id' => $option['id'],
                    'name' => $post->post_title,
                    'icon_url' => $option['icon_url'] ?? null,
                ]];
            });
    }

    /**
     * Published accessories, keyed by Carman ID
     */
    public function accessories(): Collection
    {
        return $this->accessories ??= $this->posts(CarAccessory::POST_TYPE)
            ->mapWithKeys(function (WP_Post $post) {
                $accessory = $this->carmanData($post);

                return [$accessory['id'] => [
                    'id' => $accessory['id'],
                    'slug' => $post->post_name,
                    'name' => $post->post_title,
                    'image' => get_the_post_thumbnail_url($post, 'medium') ?: null,
                    'is_free' => (bool) ($accessory['is_free'] ?? false),
                    'is_recommended' => (bool) ($accessory['is_recommended'] ?? false),
                    'max_amount' => max((int) ($accessory['max_amount'] ?? 1), 1),
                    'max_price' => isset($accessory['max_price']) ? (float) $accessory['max_price'] : null,
                    'prices' => $accessory['prices'] ?? [],
                ]];
            });
    }

    /**
     * Published insurances, keyed by Carman ID
     */
    public function insurances(): Collection
    {
        return $this->insurances ??= $this->posts(CarInsurance::POST_TYPE)
            ->mapWithKeys(function (WP_Post $post) {
                $insurance = $this->carmanData($post);

                return [$insurance['id'] => [
                    'id' => $insurance['id'],
                    'name' => $post->post_title,
                    'subtitle' => $insurance['subtitle'] ?? null,
                    'is_free' => (bool) ($insurance['is_free'] ?? false),
                    'is_recommended' => (bool) ($insurance['is_recommended'] ?? false),
                    'prices' => $insurance['prices'] ?? [],
                ]];
            });
    }

    public function vehicles(?string $category = null): Collection
    {
        $this->vehicles ??= $this->allVehicles();

        return $this->vehicles
            ->when($category, fn ($vehicles) => $vehicles->where('category', $category))
            ->values();
    }

    public function vehicle(string $id): ?array
    {
        return $this->vehicles()->firstWhere('id', $id);
    }

    private function allVehicles(): Collection
    {
        $categorySlugs = $this->posts(CarCategory::POST_TYPE)
            ->mapWithKeys(fn (WP_Post $post) => [$this->carmanData($post)['id'] => $post->post_name]);
        $optionSlugs = $this->options()
            ->mapWithKeys(fn (array $option, string $slug) => [$option['id'] => $slug]);

        return $this->posts(Car::POST_TYPE)
            ->map(function (WP_Post $post) use ($categorySlugs, $optionSlugs) {
                $car = $this->carmanData($post);
                $prices = collect($car['prices'] ?? [])
                    ->reject(fn (array $price) => $price['is_hidden'])
                    ->sortBy(fn (array $price) => (float) $price['period_days'])
                    ->values();
                $shortestPeriod = $prices->first();

                return [
                    'id' => $post->post_name,
                    'carman_id' => $car['id'],
                    'post_id' => $post->ID,
                    'url' => get_permalink($post),
                    'category' => $categorySlugs->get($car['car_category_id']),
                    'name' => $post->post_title,
                    'subtitle' => $car['subtitle'] ?? null,
                    'description' => $car['description'] ?? null,
                    'image' => get_the_post_thumbnail_url($post, 'large') ?: null,
                    'image_id' => get_post_thumbnail_id($post) ?: null,
                    'gallery' => array_filter((array) get_post_meta($post->ID, 'carman_gallery', true)),
                    'price_per_day' => $shortestPeriod
                        ? (float) $shortestPeriod['price'] / max((float) $shortestPeriod['period_days'], 1)
                        : null,
                    'prices' => $prices->all(),
                    'price_table' => $car['prices'] ?? [],
                    'hide_price_table' => (bool) ($car['hide_price_table'] ?? false),
                    'hide_insurance' => (bool) ($car['hide_insurance'] ?? false),
                    'deposit' => (float) ($car['deposit'] ?? 0),
                    'min_age' => $car['min_age'] ?? null,
                    'is_new' => (bool) ($car['is_new'] ?? false),
                    'is_promo' => (bool) ($car['is_promo'] ?? false),
                    'promo_percentage' => $car['promo_percentage'] ?? null,
                    'options' => collect($car['options'] ?? [])
                        ->map(fn (array $option) => $optionSlugs->get($option['id']))
                        ->filter()
                        ->values()
                        ->all(),
                    'accessories' => collect($car['accessories'] ?? [])->pluck('id')->all(),
                ];
            })
            ->filter(fn (array $vehicle) => $vehicle['category'])
            ->values();
    }

    /**
     * @return Collection<int, WP_Post>
     */
    private function posts(string $postType): Collection
    {
        return collect(get_posts([
            'post_type' => $postType,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
        ]));
    }

    private function carmanData(WP_Post $post): array
    {
        return (array) get_post_meta($post->ID, 'carman_data', true);
    }
}
