<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

use App\TON\Interop\Address;
use App\TON\Transports\Toncenter\Exceptions\ClientException;
use App\TON\Transports\Toncenter\Exceptions\TimeoutException;
use App\TON\Transports\Toncenter\Exceptions\ValidationException;
use App\TON\Transports\Toncenter\Models\TonResponse;

/**
 * Toncenter API client
 */
interface ToncenterV2Client
{
    public function runGetMethod(Address $address, string $method, array $stack = []): object;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain.
     *
     * @link https://toncenter.com/api/v2/#/send/send_boc_sendBoc_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBoc($boc): TonResponse;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain. The method returns message hash.
     *
     * @link https://toncenter.com/api/v2/#/send/send_boc_return_hash_sendBocReturnHash_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBocReturnHash($boc): TonResponse;

    /**
     * Send query - unpacked external message.
     *
     * This method takes address, body and init-params (if any), packs it to external message and sends to network.
     * All params should be boc-serialized.
     *
     * @param array{addres: string, body: string, init_code?: string, init_data?: string} $body
     * @link https://toncenter.com/api/v2/#/send/send_query_sendQuery_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendQuery(array $body): TonResponse;

}
