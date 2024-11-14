<?php

namespace App\Providers;

use App\TON\HttpClients\TonCenterClient;
use App\TON\HttpClients\TonCenterClientInterface;
use App\TON\Transactions\MapperJetMasterByAddress;
use App\TON\Transactions\MapperJetMasterByAddressInterface;
use App\TON\Withdraws\WithdrawJettonInterface;
use App\TON\Withdraws\WithdrawJettonV4R2;
use App\TON\Withdraws\WithdrawTonV4R2;
use App\TON\Withdraws\WithdrawTonV4R2Interface;
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
            WithdrawTonV4R2Interface::class => WithdrawTonV4R2::class,
            WithdrawJettonInterface::class => WithdrawJettonV4R2::class,
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
