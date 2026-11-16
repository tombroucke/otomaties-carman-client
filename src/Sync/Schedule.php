<?php

namespace Otomaties\CarmanClient\Sync;

use Otomaties\CarmanClient\Api\Client;
use Otomaties\WpFluentHooks\Action;
use Otomaties\WpFluentHooks\Filter;

/**
 * Runs the Carman sync hourly through WP-Cron
 */
class Schedule
{
    public const HOOK = 'carman_client_sync';

    public function __construct(private Client $client) {}

    public function register(): void
    {
        Action::hook('init')
            ->when(fn () => ! wp_next_scheduled(self::HOOK))
            ->register(fn () => wp_schedule_event(time(), 'hourly', self::HOOK));

        Action::hook(self::HOOK)
            ->register(fn () => app(Synchronizer::class)->sync());

        // Images are downloaded with wp_safe_remote_get, which refuses hosts that resolve to a local ip
        Filter::hook('http_request_host_is_external')
            ->register(fn (bool $external, string $host) => $external || $host === $this->client->host(), args: 2);

        // WordPress' CA bundle doesn't trust the self-signed certificates of local development sites
        Filter::hook('http_request_args')
            ->when(fn () => defined('WP_ENV') && WP_ENV === 'development')
            ->register(function (array $args, string $url) {
                if (parse_url($url, PHP_URL_HOST) === $this->client->host()) {
                    $args['sslverify'] = false;
                }

                return $args;
            }, args: 2);
    }
}
