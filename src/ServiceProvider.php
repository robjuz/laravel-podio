<?php
namespace stefanmdt\LaravelPodio;

use Illuminate\Foundation\Application;
use stefanmdt\LaravelPodio\Exceptions\ConfigurationException;

class ServiceProvider extends \Illuminate\Support\ServiceProvider

{
    /**
     * Publish config
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('podio.php'),
        ]);
    }

    /**
     * Register package
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerServices();
    }

    /**
     * Merge config
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php',
            'podio'
        );
    }

    /**
     * Register services
     */
    protected function registerServices()
    {
        // PodioService
        $this->app->bind(LaravelPodio::class, function (Application $app) {
            /** @var array $config */
            $config = config('podio');

            if (!$config) {
                ConfigurationException::message('Please provide a podio configuration');
            }

            return new LaravelPodio($config);
        });
    }
}
