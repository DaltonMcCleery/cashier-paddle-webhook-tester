# Cashier Paddle Webhook Tester

Testing Paddle events and webhook handling locally without Ngrok.

## Requirements

You will need to have the [Laravel Cashier - Paddle](https://arc.net/l/quote/jtqyreev) package installed and your 
Paddle API keys set up in your `.env` file.

## Install
```
composer require --dev daltonmccleery/cashier-paddle-webhook-tester
```

## Usage

Continuously poll for the new events dispatched by Paddle when making transactions or updating customers and subscriptions.

```bash
php artisan tester:paddle-listen
```

Replay a sequence of Paddle events, in order, and fire their appropriate webhook.

```bash
php artisan tester:paddle-replay
```
