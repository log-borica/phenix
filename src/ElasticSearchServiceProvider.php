<?php

namespace Phenix\Core;

use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Support\ServiceProvider;

/**
 * Class ElasticSearchServiceProvider
 * @package Phenix\Core
 */
class ElasticSearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the configuration
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../config/elasticsearch.php');

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('elasticsearch.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('elasticsearch');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Phenix\Core\Commands\CreateIndex::class,
                \Phenix\Core\Commands\UpdateIndexMapping::class,
            ]);
        }

        $this->mergeConfigFrom($source, 'elasticsearch');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('elasticsearch', function ($app) {
            return new ElasticSearch;
        });

        $this->app->alias('elasticsearch', 'Phenix\Core\ElasticSearch');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['elasticsearch', 'Phenix\Core\ElasticSearch'];
    }
}
