<?php

namespace App\Providers;

use App\TON\HttpClients\TonCenterClient;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\MapperJetMasterByAddress;
use App\TON\Transactions\MapperJetMasterByAddressInterface;
use App\TON\Withdraws\WithdrawMemoToMemo;
use App\TON\Withdraws\WithdrawMemoToMemoInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $appServices = [
            TonCenterClientInterface::class => TonCenterClient::class,
            MapperJetMasterByAddressInterface::class => MapperJetMasterByAddress::class,
            WithdrawMemoToMemoInterface::class => WithdrawMemoToMemo::class,
        ];
        foreach ($appServices as $key => $value) {
            $this->app->bind($key, $value);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
