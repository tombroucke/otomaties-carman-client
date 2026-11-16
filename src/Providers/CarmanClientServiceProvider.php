<?php

namespace Otomaties\CarmanClient\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Otomaties\CarmanClient\Api\Client;
use Otomaties\CarmanClient\Console\SyncCommand;
use Otomaties\CarmanClient\OptionsPage;
use Otomaties\CarmanClient\Rental;
use Otomaties\CarmanClient\Sync\Schedule;
use Otomaties\WpFluentHooks\Action;

class CarmanClientServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/carman-client.php', 'carman-client');

        $this->app->singleton(Client::class, function ($app) {
            return new Client(
                apiUrl: $app['config']->get('carman-client.api_url'),
                apiToken: $app['config']->get('carman-client.api_token'),
                company: $app['config']->get('carman-client.company'),
            );
        });

        $this->app->singleton('carman-client.rental', function () {
            return new Rental;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        load_plugin_textdomain('otomaties-carman-client', false, dirname(plugin_basename(__FILE__), 3).'/resources/languages/');

        Livewire::addNamespace(
            namespace: 'carman-client',
            classNamespace: 'Otomaties\\CarmanClient\\Livewire',
            classPath: __DIR__.'/../Livewire',
            classViewPath: __DIR__.'/../../resources/views/livewire',
        );

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'carman-client');

        $this->commands([
            SyncCommand::class,
        ]);

        Action::hook('init')
            ->when(fn () => function_exists('register_extended_post_type'))
            ->register(function () {
                collect(glob(__DIR__.'/../PostTypes/*.php'))
                    ->map(fn (string $file) => 'Otomaties\\CarmanClient\\PostTypes\\'.Str::before(basename($file), '.php'))
                    ->each(fn (string $postType) => (new $postType)->register());
            });

        Action::hook('acf/init')
            ->when(fn () => function_exists('acf_add_options_sub_page'))
            ->register(function () {
                (new OptionsPage)->register();
            });

        $this->app->make(Schedule::class)->register();
    }
}
