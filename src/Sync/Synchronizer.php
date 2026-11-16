<?php

namespace Otomaties\CarmanClient\Sync;

use Illuminate\Support\Arr;
use Otomaties\CarmanClient\Api\Client;
use Otomaties\CarmanClient\PostTypes\Car;
use Otomaties\CarmanClient\PostTypes\CarAccessory;
use Otomaties\CarmanClient\PostTypes\CarCategory;
use Otomaties\CarmanClient\PostTypes\CarInsurance;
use Otomaties\CarmanClient\PostTypes\CarOption;
use Otomaties\CarmanClient\PostTypes\Location;
use Otomaties\CarmanClient\PostTypes\Product;
use Otomaties\CarmanClient\PostTypes\ProductCategory;
use Otomaties\CarmanClient\PostTypes\SeasonPrice;
use Otomaties\CarmanClient\PostTypes\ShippingCost;
use Otomaties\WpSyncPosts\Syncer;

/**
 * Mirrors the Carman catalog into the Carman post types.
 *
 * Every record is stored as a post with its Carman ID in the `carman_id` meta field and the
 * complete API payload in the `carman_data` meta field. Inactive records are saved as drafts,
 * records that no longer exist in Carman are deleted.
 */
class Synchronizer
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(private Client $client) {}

    /**
     * @param  callable(string $postType, int $count): void|null  $progress
     * @return array<string, int> Number of synced posts per post type
     */
    public function sync(?callable $progress = null): array
    {
        // Fetch everything first, so a failing request never leads to deleted posts
        $records = collect($this->resources())
            ->map(fn (array $resource) => $this->client->all($resource['endpoint']));

        return collect($this->resources())
            ->map(function (array $resource, string $postType) use ($records, $progress) {
                $syncer = new Syncer($postType);

                foreach ($records->get($postType) as $record) {
                    $syncer->addPost($this->postArgs($record, $resource['map']($record)), [
                        'by' => 'meta_value',
                        'key' => 'carman_id',
                        'value' => $record['id'],
                    ]);
                }

                $count = count($syncer->execute());

                if ($progress) {
                    $progress($postType, $count);
                }

                return $count;
            })
            ->all();
    }

    /**
     * Merge the default post arguments with the resource specific ones
     */
    private function postArgs(array $record, array $args): array
    {
        $args = array_replace_recursive([
            'post_title' => $record['name'] ?? '',
            'post_name' => $record['slug'] ?? '',
            'post_status' => ($record['active'] ?? true) ? 'publish' : 'draft',
            'menu_order' => $record['sort_order'] ?? 0,
            'meta_input' => [
                'carman_id' => $record['id'],
                'carman_data' => $record,
            ],
            'media' => [],
        ], $args);

        // WordPress unslashes post fields and meta values before saving them
        return [
            ...wp_slash(Arr::except($args, 'media')),
            'media' => $args['media'],
        ];
    }

    /**
     * @return array<string, array{endpoint: string, map: callable(array): array}>
     */
    private function resources(): array
    {
        return [
            Location::POST_TYPE => [
                'endpoint' => 'locations',
                'map' => fn (array $location) => [],
            ],
            CarCategory::POST_TYPE => [
                'endpoint' => 'car-categories',
                'map' => fn (array $category) => [],
            ],
            CarOption::POST_TYPE => [
                'endpoint' => 'car-options',
                'map' => fn (array $option) => [],
            ],
            CarAccessory::POST_TYPE => [
                'endpoint' => 'car-accessories',
                'map' => fn (array $accessory) => [
                    'post_content' => $accessory['description'] ?? '',
                    'media' => array_filter([$this->media($accessory['picture_url'], featured: true)]),
                ],
            ],
            CarInsurance::POST_TYPE => [
                'endpoint' => 'car-insurances',
                'map' => fn (array $insurance) => [],
            ],
            SeasonPrice::POST_TYPE => [
                'endpoint' => 'season-prices',
                'map' => fn (array $seasonPrice) => [],
            ],
            ProductCategory::POST_TYPE => [
                'endpoint' => 'product-categories',
                'map' => fn (array $productCategory) => [
                    'post_content' => $productCategory['content'] ?? '',
                    'media' => array_filter([$this->media($productCategory['picture_url'], featured: true)]),
                ],
            ],
            Product::POST_TYPE => [
                'endpoint' => 'products',
                'map' => fn (array $product) => [
                    'post_content' => $product['content'] ?? '',
                    'meta_input' => [
                        'carman_category_id' => $product['product_category_id'],
                    ],
                    'media' => $this->pictures($product),
                ],
            ],
            ShippingCost::POST_TYPE => [
                'endpoint' => 'shipping-costs',
                'map' => fn (array $shippingCost) => [
                    'post_name' => sanitize_title($shippingCost['name']),
                ],
            ],
            Car::POST_TYPE => [
                'endpoint' => 'cars',
                'map' => fn (array $car) => [
                    'post_content' => $car['description'] ?? '',
                    'meta_input' => [
                        'carman_category_id' => $car['car_category_id'],
                    ],
                    'media' => $this->pictures($car),
                ],
            ],
        ];
    }

    /**
     * The main picture as featured image, the other pictures in the `carman_gallery` meta field
     */
    private function pictures(array $record): array
    {
        return array_values(array_filter([
            $this->media($record['picture_url'], featured: true),
            ...array_map(
                fn (string $url) => $this->media($url, group: 'carman_gallery'),
                $record['picture_urls'] ?? [],
            ),
        ]));
    }

    /**
     * Only photos are imported in the media library, icons (often svg) are used from their Carman url
     */
    private function media(?string $url, bool $featured = false, ?string $group = null): ?array
    {
        $extension = strtolower(pathinfo((string) parse_url((string) $url, PHP_URL_PATH), PATHINFO_EXTENSION));

        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return null;
        }

        return array_filter([
            'url' => $url,
            'featured' => $featured,
            'group' => $group,
        ], fn ($value) => $value !== null);
    }
}
