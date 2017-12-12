<?php

namespace robjuz\LaravelPodio;

use Podio;
use robjuz\LaravelPodio\Options\PodioOptions;
use robjuz\LaravelPodio\Exceptions\ConfigurationException;

class LaravelPodio
{
    /** @var PodioOptions */
    protected $options;

    public function __construct(array $config)
    {
        $this->options = new PodioOptions($config);
        $this->setUp();
    }

    /**
     * Authenticate with application
     *
     * @param string $appName
     */
    public function authenticateWithApp($appName)
    {
        $app = $this->getApp($appName);

        Podio::$auth_type = array(
            "type"       => "app",
            "identifier" => $app['id']
        );
        Podio::$oauth     = PodioCacheSession::get(Podio::$auth_type);
        if ( ! Podio::is_authenticated()) {
            Podio::authenticate_with_app($app['id'], $app['token']);
        }

    }

    /**
     * Authenticate with password
     */
    public function authenticateWithPassword()
    {
        Podio::authenticate_with_password(
            $this->options->get('username'),
            $this->options->get('password')
        );
    }

    /**
     * Setup
     *
     * @throws ConfigurationException
     */
    public function setUp()
    {
        $options = $this->getOptions();

        /** @var string $clientId */
        $clientId = $options->get('clientId');

        /** @var string $clientSecret */
        $clientSecret = $options->get('clientSecret');

        /** @var array $config */
        $config = $options->get('options');

        if (!$clientId || !$clientSecret) {
            ConfigurationException::message('Please provide a client id & client secret for Podio');
        }

        Podio::setup($clientId, $clientSecret, $config);
    }

    /**
     * Get application from name
     *
     * @param string $appName
     * @return null
     * @throws ConfigurationException
     */
    public function getApp($appName)
    {
        /** @var array $apps */
        $apps = $this->options->get('apps');

        if (!is_array($apps)) {
            ConfigurationException::message('Please provide configuration for your podio application');
        }

        $matchedApp = array_filter($apps, function($item) use ($appName) {
            return $item['name'] === $appName;
        });

        $matchedApp = reset($matchedApp);

        if (!$matchedApp) {
            ConfigurationException::missingApp($appName);
        }

        return $matchedApp;
    }

    /**
     * @return PodioOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param PodioOptions $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
