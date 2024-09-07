<?php

namespace CashierPaddleWebhookTester;

use CashierPaddleWebhookTester\Console\Commands\WebhookEventListener;
use CashierPaddleWebhookTester\Console\Commands\WebhookEventReplay;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

/**
 * Class CashierPaddleWebhookTesterServiceProvider.
 */
class CashierPaddleWebhookTesterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Router $router): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookEventListener::class,
                WebhookEventReplay::class,
            ]);
        }
    }
}
