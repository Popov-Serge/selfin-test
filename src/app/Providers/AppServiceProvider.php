<?php

namespace App\Providers;

use App\Repositories\Contracts\DeliveryRevenueRepositoryInterface;
use App\Repositories\Contracts\LeadDeliveryRepositoryInterface;
use App\Repositories\Contracts\WebhookEventRepositoryInterface;
use App\Repositories\DeliveryRevenueRepository;
use App\Repositories\LeadDeliveryRepository;
use App\Repositories\WebhookEventRepository;
use App\Services\HttpPartnerClient;
use App\Services\PartnerClientInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            LeadDeliveryRepositoryInterface::class,
            LeadDeliveryRepository::class,
        );

        $this->app->bind(
            WebhookEventRepositoryInterface::class,
            WebhookEventRepository::class,
        );

        $this->app->bind(
            DeliveryRevenueRepositoryInterface::class,
            DeliveryRevenueRepository::class,
        );

        $this->app->bind(
            PartnerClientInterface::class,
            HttpPartnerClient::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
