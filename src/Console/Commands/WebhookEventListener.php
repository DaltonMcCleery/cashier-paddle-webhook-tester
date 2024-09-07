<?php

namespace CashierPaddleWebhookTester\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Paddle\Http\Controllers\WebhookController;
use function Laravel\Prompts\{spin, info};

class WebhookEventListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tester:paddle-listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously poll for the most recent events dispatched by Paddle.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Continuously run command to fetch updated Paddle models and simulate events
        info('Starting Paddle event listener... [ctrl + c to stop]');

        // Fetch the most recent events every 10 seconds.
        while (true) {
            // Use timestamp to see if the event occurred in the last 10 seconds.
            // Add 1 second for any API call latency.
            $timestamp = now()->subSeconds(11);
            $this->callApi($timestamp);

            usleep(100 * 100000);
        }
    }

    protected function callApi($timestamp): void
    {
        // Call Paddle API
        $response = Http::timeout(5)
            ->withToken(config('cashier.api_key'))
            ->get('https://sandbox-api.paddle.com/events?order_by=id[DESC]');

        collect($response->json('data'))
            ->each(function ($event) use ($timestamp) {
                // Find if the event occurred_at is in the last 10 seconds.
                if (isset($event['occurred_at']) && $timestamp->isBefore($event['occurred_at'])) {
                    info('Processing event ' . $event['event_type'] . ' at ' . $event['occurred_at']);
                    $this->triggerWebhook($event);
                }
            });
    }

    protected function triggerWebhook($event): void
    {
        (new WebhookController())->__invoke(request: new Request(query: $event));
    }
}
