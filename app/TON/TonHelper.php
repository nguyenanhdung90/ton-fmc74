<?php

namespace App\TON;

use App\Models\CoinInfo;
use App\Models\CoinInfoAddress;
use App\TON\Exceptions\InvalidJettonException;
use App\TON\Exceptions\WithdrawTonException;
use App\TON\Interop\Boc\Cell;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Boc\Exceptions\SliceException;
use App\TON\Interop\Bytes;
use App\TON\Transports\Toncenter\ClientOptions;
use App\TON\Transports\Toncenter\ToncenterHttpV2Client;
use App\TON\Transports\Toncenter\ToncenterTransport;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Illuminate\Support\Collection;

class TonHelper
{
    const BATCH_NUMBER_JETTON_WALLET = 20;
    const MAX_LIMIT_TRANSACTION = 100;
    const TON = 'TON';
    const USDT = 'USDT';
    const PAYN = 'PAYN';
    const NOT = 'NOT';
    const AIOTX = 'AIOTX';
    const DEPOSIT = 'DEPOSIT';
    const WITHDRAW = 'WITHDRAW';
    const WITHDRAW_EXCESS = 'WITHDRAW_EXCESS';
    const JET_OPCODE = '7362d09c';
    const JET_OPCODE_FAILED_WITHDRAW = '0f8a7ea5';
    const EXCESS_OPCODE = 'd53276db';
    const INITIATED = 'INITIATED';
    const PROCESSING = 'PROCESSING';
    const SUCCESS = 'SUCCESS';
    const FAILED = 'FAILED';
    const ENVIRONMENT_MAIN = 'MAIN';
    const ENVIRONMENT_TEST = 'TEST';
    const ACTIVE = 1;
    const IN_ACTIVE = 0;

    const NONSUPPORT_JETTON = [
        'decimals' => null,
        'currency' => self::NONSUPPORT_CURRENCY
    ];
    const NONSUPPORT_CURRENCY = 'NONSUPPORT';

    public static function getJettonAttribute(string $hexAddressJettonMaster): array
    {
        $coin = CoinInfoAddress::where('hex_master_address', strtolower($hexAddressJettonMaster))
            ->with('coin_info')->first();
        if (!$coin || !$coin->coin_info) {
            $attribute = self::NONSUPPORT_JETTON;
        } else {
            $attribute = $coin->coin_info->only(['currency']);
        }
        return array_merge($attribute, ['hex_address' => $hexAddressJettonMaster]);
    }

    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ceil($length / strlen($x)))), 1, $length);
    }

    public static function getBaseUri(): string
    {
        return config('services.ton.is_main') ? ClientOptions::MAIN_BASE_URL : ClientOptions::TEST_BASE_URL;
    }

    public static function getTransport(): ToncenterTransport
    {
        $httpClient = new HttpMethodsClient(
            Psr18ClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory(),
        );
        $tonCenter = new ToncenterHttpV2Client(
            $httpClient,
            new ClientOptions(
                self::getBaseUri(),
                config('services.ton.api_key')
            )
        );
        return new ToncenterTransport($tonCenter);
    }

    /**
     * @throws WithdrawTonException
     */
    public static function validGetJettonInfo(string $currency)
    {
        if ($currency === TonHelper::TON) {
            throw new WithdrawTonException("Jetton transfer without TON currency");
        }
        $jettonInfo = CoinInfo::where('currency', $currency)->where('is_active', TonHelper::ACTIVE)
            ->with('coin_info_address')->first();
        if (!$jettonInfo) {
            throw new WithdrawTonException("This coin doest not support.");
        }
        if (empty($jettonInfo->coin_info_address)) {
            throw new WithdrawTonException("Master jetton config is empty.");
        }

        if (empty($jettonInfo->decimals)) {
            throw new WithdrawTonException("Decimals coin is empty");
        }
        if (empty($jettonInfo->coin_info_address->hex_master_address)) {
            throw new WithdrawTonException("Master jetton config is empty.");
        }
        return $jettonInfo;
    }

    /**
     * @throws SliceException
     * @throws InvalidJettonException
     * @throws CellException
     */
    public static function parseJetBody(string $body): Collection
    {
        $bytes = Bytes::base64ToBytes($body);
        $cell = Cell::oneFromBoc($bytes, true);
        $slice = $cell->beginParse();
        $remainBit = count($slice->getRemainingBits());
        if ($remainBit < 32) {
            throw new InvalidJettonException("Invalid Jetton, this is simple transfer TON: " . $body);
        }
        $opcode = Bytes::bytesToHexString($slice->loadBits(32));
        if ($opcode !== TonHelper::JET_OPCODE) {
            throw new InvalidJettonException("Invalid Jetton opcode: " . $body);
        }

        $slice->skipBits(64);
        $amount = (string)$slice->loadCoins();
        $fromAddress = $slice->loadAddress();

        $comment = null;
        if ($cellForward = $slice->loadMaybeRef()) {
            $forwardPayload = $cellForward->beginParse();
            $comment = $forwardPayload->loadString();
        } else {
            $remainBitJet = count($slice->getRemainingBits());
            if ($remainBitJet >= 32) {
                $forwardOp = Bytes::bytesToHexString($slice->loadBits(32));
                if ($forwardOp == 0) {
                    $comment = $slice->loadString(32);
                }
            }
        }
        return collect([
            'amount' => (int)$amount,
            'from_address' => $fromAddress,
            'comment' => $comment,
            'opcode' => $opcode,
        ]);
    }
}
