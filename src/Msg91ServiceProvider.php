<?php

namespace Msg91\Laravel;

use Msg91\OtpClient;
use Msg91\SmsClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;

class Msg91ServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/msg91.php' => config_path('msg91.php'),
            ], 'msg91-config');
        }
    }

    public function register()
    {
        if (!$this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/msg91.php', 'msg91');
        }

        // Bind Msg91 OtpClient in Service Container.
        $this->app->singleton(OTPClient::class, function ($app) {
            return $this->createOtpClient($app['config']);
        });

        // Bind Msg91 SmsClient in Service Container.
        $this->app->singleton(SmsClient::class, function ($app) {
            return $this->createSmsClient($app['config']);
        });
    }

    public function provides()
    {
        return [OTPClient::class];
    }

    protected function createOtpClient(Config $config): OtpClient
    {
        if (!$this->hasMsg91ConfigSection()) {
            $this->raiseRunTimeException('Missing msg91 configuration section.');
        }

        // Get Client Options.
        $options = array_diff_key($config->get('msg91'), ['auth_key', 'template_id']);

        return new OtpClient($options['auth_key'], $options['template_id']);
    }

    protected function createSmsClient(Config $config)
    {
        if (!$this->hasMsg91ConfigSection()) {
            $this->raiseRunTimeException('Missing msg91 configuration section.');
        }

        // Get Client Options.
        $options = array_diff_key($config->get('msg91'), ['auth_key', 'sender']);

        return new SmsClient($options['auth_key'], $options['sender']);
    }

    protected function hasMsg91ConfigSection()
    {
        return $this->app->make(Config::class)
            ->has('msg91');
    }

    protected function msg91ConfigHasNo($key)
    {
        return !$this->msg91ConfigHas($key);
    }

    protected function msg91ConfigHas($key)
    {
        /** @var Config $config */
        $config = $this->app->make(Config::class);

        if (!$config->has('msg91')) {
            return false;
        }

        return
            $config->has('msg91.' . $key) &&
            !is_null($config->get('msg91.' . $key)) &&
            !empty($config->get('msg91.' . $key));
    }

    protected function raiseRunTimeException($message)
    {
        throw new \RuntimeException($message);
    }
}
