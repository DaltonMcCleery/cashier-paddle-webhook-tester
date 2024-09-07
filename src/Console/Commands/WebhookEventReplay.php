<?php

namespace CashierPaddleWebhookTester\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Paddle\Http\Controllers\WebhookController;
use function Laravel\Prompts\{info, select};

class WebhookEventReplay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tester:paddle-replay {events=15}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replay a sequence of Paddle events, in order, to test webhook handling.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $events = $this->getEvents();

        $startingEvent = select(
            label: 'Select your Event starting point. We will replay all events from this point onwards.',
            options: collect($events)
                ->reverse()
                ->mapWithKeys(fn ($event) => [$event['event_id'] => $event['event_type'] . ' at ' . $event['occurred_at']])
                ->toArray(),
        );

        collect($events)
            ->reverse()
            ->skipUntil(fn ($event) => $event['event_id'] === $startingEvent)
            ->each(function ($event) {
                info('Processing event ' . $event['event_type'] . ' at ' . $event['occurred_at']);
                $this->triggerWebhook($event);
            });
    }

    protected function getEvents(): array
    {
        $response = Http::timeout(5)
            ->withToken(config('cashier.api_key'))
            ->get('https://sandbox-api.paddle.com/events?order_by=id[DESC]&per_page=' . $this->argument('events') ?? '15');

        if ($response->successful()) {
            return $response->json('data');
        }

        return [];
    }

    protected function triggerWebhook($event): void
    {
        (new WebhookController())->__invoke(request: new Request(query: $event));
    }
}
